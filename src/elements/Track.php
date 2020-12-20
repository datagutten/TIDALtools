<?php


namespace datagutten\Tidal\elements;


use datagutten\Tidal\Info;

class Track extends Element
{
    protected $fields = ['id', 'title', 'duration', 'trackNumber', 'volumeNumber', 'url', 'isrc'];
    /**
     * @var int Track duration in seconds
     */
    public $duration;
    /**
     * @var int Track number
     */
    public $trackNumber;
    /**
     * @var int Volume number
     */
    public $volumeNumber;
    /**
     * @var string Track ISRC
     */
    public $isrc;
    /**
     * @var string Track URL
     */
    public $url;
    /**
     * @var int Track ID
     */
    public $id;
    /**
     * @var int Album ID
     */
    public $album_id;
    /**
     * @var Artist[] Track artists
     */
    public $artists;

    public function __construct($data, Info $tidal = null)
    {
        parent::__construct($data);
        $this->album_id = $data['album']['id'];
        foreach ($data['artists'] as $artist)
        {
            $this->artists[] = new Artist($artist, $tidal);
        }
    }

    public static function from_tidal(string $track_id_or_url)
    {
        $tidal = new Info();
        $track = $tidal->track($track_id_or_url);
        return new static($track);
    }
}