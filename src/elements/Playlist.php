<?php

namespace datagutten\Tidal\elements;

use datagutten\Tidal\Tidal;
use datagutten\Tidal\TidalError;

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
    /**
     * @var Track[]
     */
    public array $tracks;
    public Tidal $tidal_api;

    protected array $fields = [
        'uuid', 'type', 'title', 'duration', 'numberOfTracks',
        'created', 'lastUpdated', 'lastItemAddedAt', 'url', 'image',
    ];
    protected static array $optional_fields = ['lastItemAddedAt', 'image'];

    public function __construct(array $data, Tidal $tidal = null)
    {
        parent::__construct($data, $tidal->info);
        $this->tidal_api = $tidal;
        foreach ($data['items'] ?? [] as $item)
        {
            $this->tracks[] = new static::$track_class($item, $this->tidal);
        }
    }

    /**
     * Get the playlist tracks
     * @return self New playlist instance with tracks
     * @throws TidalError API error
     */
    public function get_tracks(): self
    {
        $playlist = $this->tidal_api->playlist($this->uuid);
        $this->tracks = $playlist->tracks;
        return $playlist;
    }
}