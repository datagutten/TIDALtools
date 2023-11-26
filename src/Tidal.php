<?php

namespace datagutten\Tidal;

use datagutten\Tidal\elements;

class Tidal extends TidalAPI
{
    //Override in child class to use other elements classes
    protected static string $track_class = elements\Track::class;
    protected static string $album_class = elements\Album::class;
    protected static string $artist_class = elements\Artist::class;
    protected static string $playlist_class = elements\Playlist::class;
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
        parent::__construct();
    }

    /**
     * Get album
     * @param string $id Album ID or URL
     * @param bool $tracks Fetch album tracks
     * @return elements\Album Album object
     * @throws TidalError
     */
    public function album(string $id, bool $tracks = true): elements\Album
    {
        $id = Info::get_id($id, 'album');
        $album = $this->info->api_request('albums', $id);
        /** @var elements\Album $album_obj */
        $album_obj = new static::$album_class($album, $this->info);
        if ($tracks)
            $album_obj->get_tracks();
        return $album_obj;
    }

    /**
     * Get artist
     * @param string $id Artist id or URL
     * @return elements\Artist Artist object
     * @throws TidalError
     */
    public function artist(string $id): elements\Artist
    {
        $id = Info::get_id($id, 'artist');
        $artist = $this->info->api_request('artists', $id);
        return new static::$artist_class($artist, $this->info);
    }

    /**
     * Get track
     * @param string $id Track id or URL
     * @return elements\Track Track object
     * @throws TidalError
     */
    public function track(string $id): elements\Track
    {
        $id = Info::get_id($id, 'track');
        $track = $this->info->api_request('tracks', $id);
        return new static::$track_class($track, $this->info);
    }

    /**
     * Get a playlist
     * @param string $id_or_url Playlist id or URL
     * @return elements\Playlist Playlist object
     * @throws TidalError
     */
    public function playlist(string $id_or_url): elements\Playlist
    {
        $id = Info::get_id($id_or_url);
        $playlist_info = $this->info->api_request('playlists', $id);
        $limit = ceil($playlist_info['numberOfTracks'] / 100) * 100;
        $playlist_tracks = $this->info->api_request('playlists', $id, 'tracks', "&limit=$limit&orderDirection=ASC");
        $playlist = array_merge($playlist_info, $playlist_tracks);
        return new static::$playlist_class($playlist, $this->info, api: $this);
    }
}