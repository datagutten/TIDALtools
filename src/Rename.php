<?php


namespace datagutten\Tidal;
use datagutten\AudioMetadata\AudioMetadata;
use Exception;
use InvalidArgumentException;

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
            $this->config = require 'config.php';
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
     * Get filename for a track
     * @param string $track Track ID or URL
     * @param string $extension File extension
     * @return array File name and metadata
     * @throws TidalError
     */
    function track_file($track, $extension='')
    {
        $metadata = Info::track_metadata($track);
        $file = AudioMetadata::build_file_name($metadata, $extension);
        $folder = AudioMetadata::build_directory_name($metadata, $extension);
        $file = sprintf('%s/%s/%s', $this->output_path, $folder, $file);
        return [$file, $metadata];
    }

    /**
     * @param string $file File to be renamed
     * @param string|array $track Can be track id, array with return value of track_metadata() or return value of Info::track()
     * @param array $album Array with album info from Info::album
     * @throws TidalError Error fetching info from TIDAL
     * @throws Exception Error writing metadata
     * @return string Renamed file
     */
    public function rename($file, $track, $album = [])
    {
        if(!is_array($track))
            $track = Info::track_metadata($track);
        else
        {
            if(is_array($track['artist'])) //Data from TIDAL
            {
                if(!empty($album))
                    $album = $this->album($track['album']['id']);

                $track = self::prepare_metadata($track, $album);
            }
        }

        return AudioMetadata::metadata($file, $this->output_path, $track);
    }

}