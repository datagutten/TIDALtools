<?php

namespace datagutten\Tidal;

use datagutten\Tidal\elements;

class Tidal
{
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
    public function album(string $id): elements\Album
    {
        $album = $this->info->album($id);
        $tracks = $this->info->album($id, true);
        return new elements\Album($album, $tracks, $this->info);
    }

    /**
     * Get artist
     * @param string $id Artist id or URL
     * @return elements\Artist Artist object
     * @throws TidalError
     */
    public function artist(string $id): elements\Artist
    {
        $artist = $this->info->artist($id);
        return new elements\Artist([
            'name' => $artist['title'],
            'id' => $artist['id']
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
        return new elements\Track($track, $this->info);
    }
}