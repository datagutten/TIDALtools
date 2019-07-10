<?php
//declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use datagutten\Tidal\Search;
use datagutten\Tidal\TidalError;

class SearchTest extends TestCase
{
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
        $tidal=new Search;
        $tidal->token = Search::get_token();
        $search = $tidal->search_track('lite og stort');
        $this->assertArrayHasKey('totalNumberOfItems', $search);
        $this->assertGreaterThan(0, $search['totalNumberOfItems']);
    }

    /**
     * @throws TidalError
     */
    public function testSearchAlbum()
    {
        $tidal=new Search;
        $tidal->token = Search::get_token();
        $search = $tidal->search_album('Hva nå');
        $this->assertArrayHasKey('totalNumberOfItems', $search);
        $this->assertGreaterThan(0, $search['totalNumberOfItems']);
    }

    /**
     * @throws TidalError
     */
    public function testVerifySearch()
    {
        $tidal=new Search;
        $tidal->token = Search::get_token();
        $search = $tidal->search_track('lite og stort');
        $match = false;
        foreach ($search['items'] as $result) {
            $match = $tidal->verify_search($result, 'Lite og stort', array('No. 4'));
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
        $tidal=new Search;
        $tidal->token = Search::get_token();
        $match = $tidal->search_track_verify('lite og stort', array('No. 4'));
        $this->assertNotFalse($match);
        $this->assertEquals('Lite og stort', $match['title']);
    }
}