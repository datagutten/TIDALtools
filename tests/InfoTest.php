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
    public function testArtist()
    {
        $this->tidal->token = Tidal\Info::get_token();
        $artist = $this->tidal->artist('https://tidal.com/browse/artist/5496411');
        $this->assertIsArray($artist);
        $this->assertEquals('No. 4', $artist['title']);
    }
}