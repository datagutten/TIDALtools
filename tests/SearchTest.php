<?php
//declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use datagutten\Tidal\Search;
use datagutten\Tidal\TidalError;
class SearchTest extends TestCase
{
    public $token;
    /**
     * @var Search
     */
    public $tidal;
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->token = Search::get_token();
    }
    public function setUp(): void
    {
        $this->tidal = new Search();
        $this->tidal->token = $this->token;
    }

    public function testFeatured()
    {
        $title = Search::remove_featured('Kem Kan Eg Ringe (feat. Store P & Lars Vaular)');
        $this->assertEquals('Kem Kan Eg Ringe', $title);
    }

    /**
     * @throws TidalError
     */
    public function testSearchTrack()
    {
        $search = $this->tidal->search_track('lite og stort');
        $this->assertArrayHasKey('totalNumberOfItems', $search);
        $this->assertGreaterThan(0, $search['totalNumberOfItems']);
    }

    /**
     * @throws TidalError
     */
    public function testSearchAlbum()
    {
        $search = $this->tidal->search_album('Hva nå');
        $this->assertArrayHasKey('totalNumberOfItems', $search);
        $this->assertGreaterThan(0, $search['totalNumberOfItems']);
    }

    /**
     * @throws TidalError
     */
    public function testVerifySearch()
    {
        $search = $this->tidal->search_track('lite og stort');
        $match = false;
        foreach ($search['items'] as $result) {
            $match = $this->tidal->verify_search($result, 'Lite og stort', ['No. 4']);
            if($match!==false)
                break;
        }
        $this->assertNotFalse($match);
        $this->assertEquals('Lite og stort', $match['title']);
    }

    /**
     * @throws TidalError
     */
    public function testSearchAndVerify()
    {
        $match = $this->tidal->search_track_verify('lite og stort', ['No. 4']);
        $this->assertNotFalse($match);
        $this->assertEquals('Lite og stort', $match['title']);
    }
}