<?php


namespace datagutten\Tidal\elements;


use datagutten\AudioMetadata\AudioMetadata;
use datagutten\Tidal\Info;
use datagutten\Tidal\TidalError;
use datagutten\tools\files\files;

class Track extends Element
{
    protected array $fields = ['id', 'title', 'duration', 'trackNumber', 'volumeNumber', 'url', 'isrc', 'copyright', 'audioQuality'];
    protected static array $optional_fields = ['copyright', 'image'];

    /**
     * @var string Track title
     */
    public string $title;
    /**
     * @var int Track duration in seconds
     */
    public int $duration;
    /**
     * @var int Track number
     */
    public int $trackNumber;
    /**
     * @var int Volume number
     */
    public int $volumeNumber;
    /**
     * @var string Track ISRC
     */
    public string $isrc;
    /**
     * @var string Track URL
     */
    public string $url;
    /**
     * @var int Track ID
     */
    public int $id;
    /**
     * @var int Album ID
     */
    public int $album_id;
    /**
     * @var Artist[] Track artists
     */
    public array $artists;
    /**
     * @var Album Album object
     */
    public Album $album;
    /**
     * @var string Copyright string
     */
    public string $copyright;
    /**
     * @var string Audio quality
     */
    public string $audioQuality;

    public function __construct($data, Info $tidal = null)
    {
        parent::__construct($data);
        $this->album_id = $data['album']['id'];
        foreach ($data['artists'] as $artist)
        {
            $this->artists[] = new static::$artist_class($artist, $tidal);
        }
    }

    /**
     * Get artist names as combined string
     * @return string
     */
    public function artist(): string
    {
        return self::artistString($this->artists);
    }

    /**
     * Get album metadata
     * @return Album Album object
     * @throws TidalError API request failed
     */
    public function getAlbum(): Album
    {
        $album = $this->tidal->api_request('albums', $this->album_id);
        $this->album = new Album($album, null, $this->tidal);
        return $this->album;
    }

    /**
     * Prepare metadata from TIDAL to be passed to AudioMetadata methods
     * @return array Metadata array
     * @throws TidalError API request failed
     */
    public function metadata(): array
    {
        $track['artist'] = $this->artist();
        if (empty($this->album))
            $this->getAlbum();

        $track['title'] = $this->title;
        $track['albumyear'] = date('Y', strtotime($this->album->releaseDate));
        $track['album'] = $this->album->title;
        $track['albumartist'] = $this->album->artist();
        $track['tracknumber'] = $this->trackNumber;
        $track['volumenumber'] = $this->volumeNumber;
        $track['totaltracks'] = $this->album->numberOfTracks;
        $track['totalvolumes'] = $this->album->numberOfVolumes;
        $track['isrc'] = $this->isrc;
        $track['cover'] = $this->album->cover;
        $track['artists'] = [];
        foreach ($this->artists as $artist)
        {
            $track['artists'][] = $artist->name;
        }
        /*if ($album['artist']['id'] == 2935) //If album artist is "Various Artists" the album is a compilation
            $track['compilation'] = true;*/
        if (preg_match('/([0-9]{4})/', $this->copyright, $year))
            $track['year'] = $year[1];
        return $track;
    }

    /**
     * Get filename for a track
     * @param string $extension File extension
     * @return array File name and metadata
     * @throws TidalError
     */
    function file(string $extension = ''): array
    {
        $metadata = $this->metadata();
        $file = AudioMetadata::build_file_name($metadata, $extension);
        $folder = AudioMetadata::build_directory_name($metadata, $extension);
        $file = files::path_join($folder, $file);
        return [$file, $metadata];
    }
}