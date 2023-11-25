<?php

namespace datagutten\Tidal\element_stubs;

use datagutten\Tidal\elements\Track;
use datagutten\Tidal\TidalError;

class TrackStub extends ElementStub
{
    /**
     * @return Track
     * @throws TidalError API error
     */
    public function get(): Track
    {
        return $this->tidal->track($this->id);
    }
}