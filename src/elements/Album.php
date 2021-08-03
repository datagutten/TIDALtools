<?php


namespace datagutten\Tidal\elements;


use datagutten\Tidal\Info;
use datagutten\Tidal\TidalError;

class Album extends Element
{
    protected array $fields = [
        'id', 'title', 'duration', 'numberOfTracks', 'numberOfVolumes',
        'releaseDate', 'copyright', 'type', 'url', 'cover', 'upc'
    ];
    /**
     * @var int Album ID
     */
    public int $id;
    /**
     * @var string Album title
     */
    public string $title;
    /**
     * @var int Album duration in seconds
     */
    public int $duration;
    /**
     * @var int Number of tracks
     */
    public int $numberOfTracks;
    /**
     * @var int Number of volumes
     */
    public int $numberOfVolumes;
    /**
     * @var string Album release date as Y-m-d format
     */
    public string $releaseDate;
    /**
     * @var string Copyright
     */
    public string $copyright;
    /**
     * @var string Type
     */
    public string $type;
    /**
     * @var string Album URL
     */
    public string $url;
    /**
     * @var string Album cover URL
     */
    public string $cover;
    /**
     * @var string Album UPC
     */
    public string $upc;
    /**
     * @var Artist[] Album artists
     */
    public array $artists;
    /**
     * @var Track[] Album tracks
     */
    public array $tracks;

    /**
     * Album constructor.
     * @param array $album
     * @param array|null $tracks
     * @throws TidalError
     */
    public function __construct(array $album, array $tracks = null, Info $tidal = null)
    {
        parent::__construct($album, $tidal);

        foreach ($tracks['items'] as $track)
        {
            $track_obj = new static::$track_class($track, $tidal);
            $track_obj->album = $this;
            $this->tracks[] = $track_obj;
        }

        foreach ($album['artists'] as $artist)
        {
            $this->artists[] = new static::$artist_class($artist, $tidal);
        }
    }

    /**
     * Get track from album
     * @param int $track_number Track number
     * @param int $medium Medium number
     * @return Track|null
     */
    public function get_track(int $track_number, int $medium=1): ?Track
    {
        foreach ($this->tracks as $track)
        {
            if($track->trackNumber==$track_number && $track->volumeNumber==$medium)
                return $track;
        }
        return null;
    }
}