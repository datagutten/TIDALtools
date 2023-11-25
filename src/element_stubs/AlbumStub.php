<?php

namespace datagutten\Tidal\element_stubs;

use datagutten\Tidal\elements\Album;
use datagutten\Tidal\TidalError;

class AlbumStub extends ElementStub
{
    /**
     * @return Album
     * @throws TidalError API error
     */
    public function get(): Album
    {
        return $this->tidal->album($this->id);
    }
}