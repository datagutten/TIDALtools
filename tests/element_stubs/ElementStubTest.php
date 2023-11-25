<?php

namespace element_stubs;

use datagutten\Tidal\element_stubs as stubs;
use datagutten\Tidal\elements;
use datagutten\Tidal\Tidal;
use PHPUnit\Framework\TestCase;

class ElementStubTest extends TestCase
{
    public Tidal $tidal;

    public function setUp(): void
    {
        $this->tidal = new Tidal();
    }

    public function testGetTrack()
    {
        $stub = new stubs\TrackStub(77698892, $this->tidal);
        $track = $stub->get();
        $this->assertInstanceOf(elements\Track::class, $track);
        $this->assertEquals('Det finnes bare vi', $track->title);
    }

    public function testGetAlbum()
    {
        $stub = new stubs\AlbumStub(266677242, $this->tidal);
        $album = $stub->get();
        $this->assertInstanceOf(elements\Album::class, $album);
        $this->assertEquals('Hva nå', $album->title);
    }

    public function testGetPlaylist()
    {
        $stub = new stubs\PlaylistStub('f1aae2f6-b820-4028-b56c-6e8c4e9e9551', $this->tidal);
        $playlist = $stub->get();
        $this->assertInstanceOf(elements\Playlist::class, $playlist);
        $this->assertEquals('Hans Rotmo', $playlist->title);
    }
}
