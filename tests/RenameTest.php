<?php


use datagutten\Tidal;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;


class RenameTest extends TestCase
{
    public $config;


    public function setUp(): void
    {
        $this->config = require 'test_config.php';
    }
    /**
     * @throws tidal\TidalError
     */
    public function testFileName()
    {
        $tidal= new Tidal\Rename($this->config);
        $tidal->token = Tidal\Info::get_token();
        list($file) = $tidal->track_file('https://tidal.com/browse/track/80219173', 'flac');
        $pathinfo = pathinfo($file);

        $this->assertEquals('09 Det finnes bare vi.flac', $pathinfo['basename']);
        $this->assertStringContainsString('No. 4 - Hva nå (2017) FLAC', $pathinfo['dirname']);
    }

    public function tearDown(): void
    {
        if(file_exists($this->config['output_path']))
            rmdir($this->config['output_path']);
    }
}