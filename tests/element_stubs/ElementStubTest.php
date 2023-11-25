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
}
