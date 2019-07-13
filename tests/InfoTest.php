<?php

use datagutten\Tidal;
use PHPUnit\Framework\TestCase;

class InfoTest extends TestCase
{
    /**
     * @var Tidal\Info
     */
    public $tidal;
    public function setUp(): void
    {
        $this->tidal = new Tidal\Info();
    }

    /**
     * @throws Tidal\TidalError
     */
    public function testTokenNotFound()
    {
        $this->expectException(Tidal\TidalError::class);
        $this->expectExceptionMessage('Token not found in response string');
        Tidal\Info::get_token('https://httpbin.org');
    }

    /**
     * @throws Tidal\TidalError
     */
    public function testTokenBadCode()
    {
        $this->expectException(Tidal\TidalError::class);
        $this->expectExceptionMessage('500 Internal Server Error');
        Tidal\Info::get_token('https://httpbin.org/status/500');
    }

    /**
     * @throws Tidal\TidalError
     */
    public function testApiRequestBadToken()
    {
        $this->expectException(Tidal\TidalError::class);
        $this->expectExceptionMessage('Unable to get token from https://tidal.com/browse/foo/bar');
        $this->tidal->api_request('foo', 'bar');
    }

    /**
     * @throws Tidal\TidalError
     */
    public function testQueryEmptyURL()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->tidal->query('');
    }

    /**
     * @throws Tidal\TidalError
     */
    public function testQuery()
    {
        $this->tidal->token = 'test_token';
        $this->tidal->sessionId = 'test_session';

        $response = $this->tidal->query('https://httpbin.org/post', array('post_test'=>'value'));
        $response = json_decode($response, true);

        $this->assertEquals('test_token', $response['headers']['X-Tidal-Token']);
        $this->assertEquals('test_session', $response['headers']['X-Tidal-Sessionid']);
    }

    /**
     * @throws Tidal\TidalError
     */
    public function testQueryError()
    {
        $this->expectException(Tidal\TidalError::class);
        $this->tidal->query('https://httpbin.org/status/codes/500');
    }

    /**
     * @throws Tidal\TidalError
     */
    public function testResponseNotString()
    {
        $this->expectException(InvalidArgumentException::class);
        /** @noinspection PhpParamsInspection */
        $this->tidal->parse_response(['foo']);
    }

    /**
     * @throws Tidal\TidalError
     */
    public function testExceptionMessage()
    {
        $this->expectException(Tidal\TidalError::class);
        $this->expectExceptionMessage('test_message');
        $this->tidal->parse_response('{"userMessage": "test_message"}');
    }

    /**
     * @throws Tidal\TidalError
     */
    public function testQueryWithoutToken()
    {
        $this->expectException(Tidal\TidalError::class);
        $this->expectExceptionMessage('Missing token');
        $this->tidal->artist('https://tidal.com/browse/artist/5496411');
    }

    /**
     * @throws Tidal\TidalError
     */
    public function testGetTokenFromRequest()
    {
        $track = $this->tidal->track('https://tidal.com/browse/track/82348963');
        $this->assertIsArray($track);
        $this->assertEquals('Lite og stort', $track['title']);
    }

    public function testArtistImage()
    {
        $image = Tidal\Info::resolve_image(['picture'=>'37b52f9d-eb8e-41e4-8a53-e6a5af704ec3']);
        $this->assertArrayHasKey('picture', $image);
        $this->assertEquals('https://resources.wimpmusic.com/images/37b52f9d/eb8e/41e4/8a53/e6a5af704ec3/320x320.jpg', $image['picture']);
        $response = Requests::head($image['picture']);
        $this->assertEquals(200, $response->status_code);
    }

    /**
     * @throws Tidal\TidalError
     */
    public function testAlbumCover()
    {
        $album = $this->tidal->album('https://tidal.com/browse/album/80219164');
        $this->assertIsArray($album);
        $this->assertEquals('Hva nå', $album['title']);
        $response = Requests::head($album['cover']);
        $this->assertEquals(200, $response->status_code);
    }

    /**
     * @throws Tidal\TidalError
     */
    public function testPlaylistImage()
    {
        $playlist = $this->tidal->playlist('http://www.tidal.com/playlist/5944f841-c9e2-4dc3-8928-7ecf6ec167b3');
        $response = Requests::head($playlist['image']);
        $this->assertEquals(200, $response->status_code);
    }

    /**
     * @throws Tidal\TidalError
     */
    public function testArtist()
    {
        $this->tidal->token = Tidal\Info::get_token();
        $artist = $this->tidal->artist('https://tidal.com/browse/artist/5496411');
        $this->assertIsArray($artist);
        $this->assertEquals('No. 4', $artist['title']);
    }

    public function testPrepareMetadataInvalidArgument()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Track info or album info not array');
        Tidal\Info::prepare_metadata('','');
    }

    /**
     * @throws Tidal\TidalError
     */
    public function testCompilation()
    {
        $album = $this->tidal->album('https://tidal.com/browse/album/112868751');
        $track = $this->tidal->track('https://tidal.com/browse/track/112868755');
        $metadata = Tidal\Info::prepare_metadata($track, $album);
        $this->assertIsArray($metadata);
        $this->assertEquals(4, $metadata['tracknumber']);
        $this->assertEquals('I Just Can\'t Wait to Be King', $metadata['title']);
    }

    /**
     * @throws Tidal\TidalError
     */
    public function testPlaylist()
    {
        $this->tidal->token = Tidal\Info::get_token();
        $playlist = $this->tidal->playlist('https://tidal.com/browse/playlist/5944f841-c9e2-4dc3-8928-7ecf6ec167b3');
        $this->assertIsArray($playlist);
        $this->assertEquals('Jeg Er Så Oslo Du Kan Kalle Meg...', $playlist['title']);
        $this->assertEquals(21, $playlist['numberOfTracks']);
    }

    public function testPlaylistMetadata()
    {
        $this->tidal->token = Tidal\Info::get_token();
        $playlist = $this->tidal->playlist('https://tidal.com/browse/playlist/5944f841-c9e2-4dc3-8928-7ecf6ec167b3');
        $track = $this->tidal->track('https://tidal.com/browse/track/19226925');
        $metadata = Tidal\Info::prepare_metadata($track, $playlist, true);
        $this->assertIsArray($metadata);
        $this->assertEquals(true, $metadata['compilation']);
        $this->assertEquals(21, $metadata['totaltracks']);
    }
}