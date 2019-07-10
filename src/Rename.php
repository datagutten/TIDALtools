<?php


namespace datagutten\Tidal;
use AudioMetadata;
use InvalidArgumentException;
use Exception;

class Rename extends Info
{
    /**
     * @var string Folder for files with id as file name
     */
    public $input_path_id;
    /**
     * @var string Folder for files with playlist or album position as file name
     */
    public $input_path_order;
    /**
     * @var string Path for renamed files
     */
    public $output_path;
    /**
     * @var array Config
     */
    public $config;
    /**
     * @var AudioMetadata
     */
    public $audio_metadata;

    function __construct($config = null)
    {
        if(empty($config))
            $this->config = require 'config_tidal.php';
        else
            $this->config = $config;

        foreach (array('input_path_id', 'input_path_order', 'output_path') as $key)
        {
            if(!isset($this->config[$key]))
                throw new InvalidArgumentException("Config missing $key");
            $this->$key = $this->config[$key];
        }
        $this->audio_metadata = new AudioMetadata();
    }

    function load_id_files($extensions = array('m4a', 'flac'))
    {
        $files = array();
        foreach ($extensions as $extension)
        {
            $files += glob(sprintf('%s/*.%s', $this->input_path_id, $extension));
        }
        return $files;
    }

    function load_ordered_files($extensions = array('m4a', 'flac'))
    {
        $files = array();
        foreach ($extensions as $extension)
        {
            $files += glob(sprintf('%s/*.%s', $this->input_path_order, $extension));
        }
        sort($files);
        return $files;
    }

    /**
     * Prepare metadata from TIDAL to be passed to AudioMetadata methods
     * @param string $track Track ID or URL
     * @param bool $playlist Part of a playlist
     * @return array Metadata
     * @throws TidalError
     */
    function track_metadata($track, $playlist=false)
    {
        $track_info=$this->track($track);
        $album_info=$this->album($track_info['album']['id']);
        return self::prepare_metadata($track_info, $album_info, $playlist);
    }

    /**
     * Get filename for a track
     * @param string $track Track ID or URL
     * @param string $extension File extension
     * @return array File name and metadata
     * @throws TidalError
     */
    function track_file($track, $extension='')
    {
        $metadata = $this->track_metadata($track);
        $file = AudioMetadata::build_file_name($metadata, $extension);
        $folder = AudioMetadata::build_directory_name($metadata, $extension);
        $file = sprintf('%s/%s/%s', $this->output_path, $folder, $file);
        return [$file, $metadata];
    }

    /**
     * @param string $file File to be renamed
     * @param string|array $track Can be track id or array with return value of track_metadata()
     * @throws TidalError Error fetching info from TIDAL
     * @throws Exception Error writing metadata
     * @return string Renamed file
     */
    function rename($file, $track)
    {
        if(is_array($track))
            $metadata = $track;
        else
            $metadata = $this->track_metadata($track);

        return $this->audio_metadata->metadata($file, $this->output_path, $metadata);
    }

    /**
     * Prepare metadata from TIDAL to be passed to AudioMetadata methods
     * @param array $trackinfo Return value from TidalInfo::Track
     * @param array $albuminfo Return value from TidalInfo::Album
     * @param bool $playlist
     * @return array
     */
    public static function prepare_metadata($trackinfo, $albuminfo, $playlist = false)
    {
        if (!is_array($trackinfo) || !is_array($albuminfo)) {
            throw new InvalidArgumentException('Track info or album info not array');
        }
        $trackinfo['track'] = $trackinfo['trackNumber'];
        $trackinfo['artist'] = $trackinfo['artist']['name'];
        $trackinfo['albumyear'] = date('Y', strtotime($albuminfo['releaseDate']));
        if (!$playlist) {
            $trackinfo['album'] = $trackinfo['album']['title'];
            $trackinfo['albumartist']   = $albuminfo['artist']['name'];
            $trackinfo['tracknumber']   = $trackinfo['trackNumber'];
            $trackinfo['volumenumber']  = $trackinfo['volumeNumber'];
            $trackinfo['totaltracks']   = $albuminfo['numberOfTracks'];
            $trackinfo['totalvolumes']  = $albuminfo['numberOfVolumes'];
            $trackinfo['cover'] = $albuminfo['cover'];
            if ($albuminfo['artist']['id'] == 2935) //If album artist is "Various Artists" the album is a compilation
                $trackinfo['compilation'] = true;
            if (empty($trackinfo['year']) && preg_match('/([0-9]{4})/', $trackinfo['copyright'], $year))
                $trackinfo['year'] = $year[1];
        } else {
            //TODO: Playlist renaming
            die("Playlists not working\n");
            $trackinfo['album']         = $tracklist['title'];
            $trackinfo['track']         = $trackcounter;
            $trackinfo['totaltracks']   = $tracklist['numberOfTracks'];
            $trackinfo['cover']         = $tracklist['image'];
            $trackinfo['compilation']   = true; //Playlists are always compilations
        }
        return $trackinfo;
    }
}