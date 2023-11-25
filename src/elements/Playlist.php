<?php

namespace datagutten\Tidal\elements;

use datagutten\Tidal\Info;

class Playlist extends Element
{
    public string $uuid;
    public string $title;
    public string $description;
    public string $image;
    public string $url;
    public string $created;
    public string $lastUpdated;
    public string $lastItemAddedAt;
    public int $duration;
    public int $numberOfTracks;
    public array $tracks;

    protected array $fields = [
        'uuid', 'type', 'title', 'duration', 'numberOfTracks',
        'created', 'lastUpdated', 'lastItemAddedAt', 'url', 'image',
    ];
    protected static array $optional_fields = ['lastItemAddedAt', 'image'];

    public function __construct(array $data, Info $tidal = null)
    {
        parent::__construct($data, $tidal);
        foreach ($data['items'] as $item)
        {
            $this->tracks[] = new static::$track_class($item, $this->tidal);
        }
    }
}