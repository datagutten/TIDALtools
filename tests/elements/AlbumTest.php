<?php

namespace elements;

use datagutten\Tidal\elements\Album;
use PHPUnit\Framework\TestCase;

class AlbumTest extends TestCase
{
    public function test__construct()
    {
        $album = Album::from_tidal('https://tidal.com/browse/album/13607041');
        $this->assertInstanceOf(Album::class, $album);
    }
}
