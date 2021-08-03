<?php


namespace datagutten\Tidal\elements;


use datagutten\Tidal\Info;
use datagutten\Tidal\TidalError;

class Album extends Element
{
    protected $fields = [
        'id', 'title', 'duration', 'numberOfTracks', 'numberOfVolumes',
        'releaseDate', 'copyright', 'type', 'url', 'cover', 'upc'
    ];
    /**
     * @var int Album ID
     */
    public $id;
    /**
     * @var string Album title
     */
    public $title;
    /**
     * @var int Album duration in seconds
     */
    public $duration;
    /**
     * @var int Number of tracks
     */
    public $numberOfTracks;
    /**
     * @var int Number of volumes
     */
    public $numberOfVolumes;
    /**
     * @var string Album release date as Y-m-d format
     */
    public $releaseDate;
    /**
     * @var string Copyright
     */
    public $copyright;
    /**
     * @var string Type
     */
    public $type;
    /**
     * @var string Album URL
     */
    public $url;
    /**
     * @var string Album cover URL
     */
    public $cover;
    /**
     * @var string Album UPC
     */
    public $upc;
    /**
     * @var Artist[] Album artists
     */
    public $artists;
    /**
     * @var Track[] Album tracks
     */
    public $tracks;

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
            $this->tracks[] = new static::$track_class($track, $tidal);
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