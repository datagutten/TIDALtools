<?php

namespace elements;

use datagutten\Tidal\elements;
use datagutten\Tidal\Tidal;
use PHPUnit\Framework\TestCase;

class PlaylistTest extends TestCase
{
    public function testGet_tracks()
    {
        $tidal = new Tidal();
        $playlist_obj = $tidal->playlist('f1aae2f6-b820-4028-b56c-6e8c4e9e9551');
        $playlist_obj->tracks = [];
        $this->assertEmpty($playlist_obj->tracks);
        $playlist_obj->get_tracks();
        $this->assertInstanceOf(elements\Track::class, $playlist_obj->tracks[0]);
    }
}
