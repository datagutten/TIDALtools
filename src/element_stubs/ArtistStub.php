<?php

namespace datagutten\Tidal\element_stubs;

use datagutten\Tidal\elements\Artist;
use datagutten\Tidal\TidalError;

class ArtistStub extends ElementStub
{
    /**
     * @return Artist
     * @throws TidalError API error
     */
    public function get(): Artist
    {
        return $this->tidal->artist($this->id);
    }
}