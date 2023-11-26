<?php

namespace datagutten\Tidal\elements;

use datagutten\Tidal\element_stubs;
use datagutten\Tidal\elements;
use datagutten\Tidal\TidalError;
use InvalidArgumentException;

class User extends Element
{
    protected array $fields = ['userId', 'name', 'color', 'numberOfFollowers', 'numberOfFollows', 'profileType'];
    protected static array $optional_fields = ['name', 'color', 'numberOfFollowers', 'numberOfFollows', 'profileType'];

    public int $userId;
    public string $name;
    public array $color;
    public int $numberOfFollowers;
    public int $numberOfFollows;
    public string $profileType;

    /**
     * Get user playlists
     * @return Playlist[]
     * @throws TidalError API error
     */
    function playlists(string $order = 'DATE', string $orderDirection = 'DESC', int $offset = 0, int $limit = 50): array
    {
        $data = $this->tidal_api->api_request('my-collection/playlists/folders/flattened', prefix: 'api', get: ['includeOnly' => 'PLAYLIST'], offset: $offset, limit: $limit, order: $order, orderDirection: $orderDirection);

        $playlists = [];
        foreach ($data['items'] as $item)
        {
            $playlists[] = new Playlist($item['data'], $this->tidal_api);
        }
        return $playlists;
    }

    /**
     * Get raw favorite tracks from API
     * @param string $order Track order (ALBUM, NAME, ARTIST, LENGTH or DATE)
     * @param string $orderDirection Order direction (ASC or DESC)
     * @param int $offset Track offset
     * @param int $limit Track count limit
     * @return array
     * @throws TidalError API error
     */
    protected function favoriteTracksAPI(string $order = 'DATE', string $orderDirection = 'DESC', int $offset = 0, int $limit = 50): array
    {
        if (!in_array($order, ['ALBUM', 'NAME', 'ARTIST', 'LENGTH', 'DATE']))
            throw new InvalidArgumentException('Invalid order');

        return $this->tidal_api->api_request(sprintf('users/%d/favorites/tracks', $this->userId), version: 'v1', offset: $offset, limit: $limit, order: $order, orderDirection: $orderDirection);
    }

    /**
     * Get the users favorite tracks
     * @param string $order Track order (ALBUM, NAME, ARTIST, LENGTH or DATE)
     * @param string $orderDirection Order direction (ASC or DESC)
     * @return Track[] Array of track objects
     * @throws TidalError API error
     */
    public function favoriteTracks(string $order = 'DATE', string $orderDirection = 'DESC'): array
    {
        $limit = 50;
        $data = $this->favoriteTracksAPI($order, $orderDirection, limit: $limit);

        if ($data['totalNumberOfItems'] > $limit)
        {
            $data2 = $this->favoriteTracksAPI($order, $orderDirection, $limit, $data['totalNumberOfItems'] - $limit);
            $items = array_merge($data['items'], $data2['items']);
        }
        else
            $items = $data['items'];
        return array_map(fn($track) => new elements\Track($track['item'], $this->tidal), $items);
    }

    /**
     * Get the users favorites
     * @return array
     * @throws TidalError API error
     */
    public function favorites(): array
    {
        $data = $this->tidal_api->api_request(sprintf('users/%d/favorites/ids', $this->userId), version: 'v1');

        return [
            'Albums' => array_map(fn($id) => new element_stubs\AlbumStub($id, $this->tidal_api), $data['ALBUM']),
            'Tracks' => array_map(fn($id) => new element_stubs\TrackStub($id, $this->tidal_api), $data['TRACK']),
            'Artists' => array_map(fn($id) => new element_stubs\ArtistStub($id, $this->tidal_api), $data['ARTIST']),
            //'Video' => array_map(function ($id){return new element_stubs\VideoStub($id, $this->tidal);}, $data['TRACK']),
            'Playlists' => array_map(fn($id) => new element_stubs\PlaylistStub($id, $this->tidal_api), $data['PLAYLIST']),
        ];
    }
}