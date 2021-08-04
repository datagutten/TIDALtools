<?php


use datagutten\Tidal;
use datagutten\Tidal\TidalError;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;


class RenameTest extends TestCase
{
    public $config;
    /**
     * @var Tidal\Rename
     */
    public $tidal;
    public $sample_dir;
    /**
     * @var Filesystem;
     */
    public $filesystem;

    public function setUp(): void
    {
        $this->config = require 'test_config.php';
        //$this->tidal = new Tidal\Rename();
        $this->sample_dir = __DIR__.'/sample_data';
        $this->filesystem = new Filesystem();
        if(!file_exists($this->sample_dir.'/test.flac'))
            Requests::get('http://techslides.com/demos/samples/sample.flac', [],
                ['filename'=>$this->sample_dir.'/test.flac']);
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

    /**
     * @throws TidalError
     */
    public function testRename()
    {
        $tidal= new Tidal\Rename($this->config);
        $file = $tidal->rename($this->sample_dir.'/test.flac', 'https://tidal.com/browse/track/19226925');
        $this->assertFileExists($file);
    }

    /**
     * @throws TidalError
     */
    public function testRenameTidalData()
    {
        $tidal= new Tidal\Rename($this->config);
        $track = $tidal->track('https://tidal.com/browse/track/19226925');
        $file = $tidal->rename($this->sample_dir.'/test.flac', $track);
        $this->assertFileExists($file);
    }

    public function tearDown(): void
    {
        $this->filesystem->remove($this->config['output_path']);
    }
}