<?php

namespace datagutten\Tidal;

use datagutten\Tidal\elements;

class Tidal
{
    //Override in child class to use other elements classes
    protected static string $track_class = elements\Track::class;
    protected static string $album_class = elements\Album::class;
    protected static string $artist_class = elements\Artist::class;
    protected static string $element_class = elements\Element::class;

    /**
     * @var Info Info class
     */
    public Info $info;

    /**
     * @throws TidalError Unable to get token
     */
    public function __construct()
    {
        $this->info = new Info();
        $this->info->token = Info::get_token();
    }

    /**
     * Get album
     * @param string $id Album ID or URL
     * @return elements\Album Album object
     * @throws TidalError
     */
    public function album(string $id, bool $tracks = true): elements\Album
    {
        $album = $this->info->album($id);
        if ($tracks)
            $tracks = $this->info->album($id, true);
        else
            $tracks = null;

        return new static::$album_class($album, $tracks ?? null, $this->info);
    }

    /**
     * Get artist
     * @param string $id Artist id or URL
     * @return elements\Artist Artist object
     * @throws TidalError
     */
    public function artist(string $id): elements\Artist
    {
        if (substr($id, 0, 4) == 'http')
            $url = $id;
        else
            $url = sprintf('https://tidal.com/browse/artist/%s', $id);

        $artist = $this->info->artist($id);
        return new static::$artist_class([
            'name' => $artist['title'],
            'id' => $artist['id'],
            'url' => $url,
        ], $this->info);
    }

    /**
     * Get track
     * @param string $id Track id or URL
     * @return elements\Track Track object
     * @throws TidalError
     */
    public function track(string $id): elements\Track
    {
        $track = $this->info->track($id);
        return new static::$track_class($track, $this->info);
    }
}