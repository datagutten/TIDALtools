<?php

use datagutten\Tidal\elements;
use datagutten\Tidal\Tidal;
use datagutten\Tidal\TidalError;
use PHPUnit\Framework\TestCase;

class TidalTest extends TestCase
{
    private Tidal $tidal;

    public function setUp(): void
    {
        $this->tidal = new datagutten\Tidal\Tidal();
    }

    public function testAlbum()
    {
        $album = $this->tidal->album('https://tidal.com/browse/album/36107300');
        $this->assertInstanceOf(elements\Album::class, $album);
        $this->assertInstanceOf(elements\Track::class, $album->tracks[0]);
        $this->assertInstanceOf(elements\Artist::class, $album->artists[0]);
        $this->assertEquals('We\'re Just Ok', $album->title);
    }

    public function testArtist()
    {
        $artist = $this->tidal->artist('https://tidal.com/browse/artist/7679202');
        $this->assertInstanceOf(elements\Artist::class, $artist);
        $this->assertEquals('Sløtface', $artist->name);
    }

    public function testTrack()
    {
        $track = $this->tidal->track('https://tidal.com/browse/track/36107303');
        $this->assertInstanceOf(elements\Track::class, $track);
        $this->assertEquals('Bad Party', $track->title);
    }

    public function testArtistAlbums()
    {
        $artist = $this->tidal->artist('https://tidal.com/browse/artist/7679202');
        $albums = $artist->albums();
        $this->assertInstanceOf(elements\Album::class, $albums[0]);
        $this->assertInstanceOf(elements\Track::class, $albums[0]->tracks[0]);
    }

    /**
     * Test fetching of ISRC for album
     * @throws TidalError
     */
    public function testAlbumISRC()
    {
        $isrc = $this->tidal->album('https://tidal.com/browse/album/266677242')->isrc_list();
        $this->assertIsArray($isrc);
        $this->assertEquals('NO4DI1706010', $isrc['1-1']);
    }
}
