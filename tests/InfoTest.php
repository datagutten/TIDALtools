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
}