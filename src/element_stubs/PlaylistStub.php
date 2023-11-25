<?php

namespace datagutten\Tidal\element_stubs;

use datagutten\Tidal\elements\Playlist;
use datagutten\Tidal\TidalError;

class PlaylistStub extends ElementStub
{
    /**
     * @return Playlist
     * @throws TidalError API error
     */
    public function get(): Playlist
    {
        return $this->tidal->playlist($this->id);
    }
}