<?php


namespace datagutten\Tidal\elements;


use datagutten\Tidal\Info;

class Track extends Element
{
    protected array $fields = ['id', 'title', 'duration', 'trackNumber', 'volumeNumber', 'url', 'isrc'];

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
}