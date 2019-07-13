<?php
namespace datagutten\tidal\tests;

use datagutten\Tidal\Info;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class GetIdTest extends TestCase
{
    function testEmptyArgument()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Empty argument');
        Info::get_id('');
    }

    function testId()
    {
        $id = Info::get_id('19226924');
        $this->assertEquals(19226924, $id);
    }
    function testAlbumURL()
    {
        $id = Info::get_id('https://tidal.com/browse/album/19226924', 'album');
        $this->assertEquals(19226924, $id);
        $id = Info::get_id('https://tidal.com/browse/album/19226924');
        $this->assertEquals(19226924, $id);
    }
    function testArtistURL()
    {
        $id = Info::get_id('https://tidal.com/browse/artist/3963574', 'artist');
        $this->assertEquals(3963574, $id);
        $id = Info::get_id('https://tidal.com/browse/artist/3963574');
        $this->assertEquals(3963574, $id);
    }
    function testTrackURL()
    {
        $id = Info::get_id('https://tidal.com/browse/track/19226925', 'track');
        $this->assertEquals(19226925, $id);
        $id = Info::get_id('https://tidal.com/browse/track/19226925');
        $this->assertEquals(19226925, $id);
    }

    function testPlaylistURL()
    {
        $id = Info::get_id('https://tidal.com/browse/playlist/5944f841-c9e2-4dc3-8928-7ecf6ec167b3', 'playlist');
        $this->assertEquals('5944f841-c9e2-4dc3-8928-7ecf6ec167b3', $id);
        $id = Info::get_id('https://tidal.com/browse/playlist/5944f841-c9e2-4dc3-8928-7ecf6ec167b3');
        $this->assertEquals('5944f841-c9e2-4dc3-8928-7ecf6ec167b3', $id);
    }

    function testMismatchURL()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid album URL: https://tidal.com/browse/track/19226925');
        Info::get_id('https://tidal.com/browse/track/19226925', 'album');
    }
    function testInvalidURL()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to find id from URL: https://tidal.com');
        Info::get_id('https://tidal.com');
    }
}