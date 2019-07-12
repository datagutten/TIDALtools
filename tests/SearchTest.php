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

    /**
     * SearchTest constructor.
     * @param null $name
     * @param array $data
     * @param string $dataName
     * @throws TidalError
     */
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

    /**
     * @throws TidalError
     */
    public function testSearchAndVerifyFeat()
    {
        $match = $this->tidal->search_track_verify('Don\'t check on me', ['Chris brown feat. justin bieber & ink']);
        $this->assertNotFalse($match);
        $artists = implode(', ', array_column($match['artists'], 'name'));
        $this->assertEquals('Chris Brown, Justin Bieber, Ink', $artists);
        $this->assertEquals('Don\'t Check On Me', $match['title']);
    }

    /**
     * @throws TidalError
     */
    public function testSearchAndVerifyFeat2()
    {
        $match = $this->tidal->search_track_verify('Summer days', ['Martin garrix feat. macklemore, patric stump of fall out boy']);
        $this->assertNotFalse($match);
        $artists = implode(', ', array_column($match['artists'], 'name'));
        $this->assertEquals('Martin Garrix, Macklemore, Fall Out Boy', $artists);
        $this->assertEquals('Summer Days (feat. Macklemore & Patrick Stump of Fall Out Boy)', $match['title']);
    }

    /**
     * @throws TidalError
     */
    public function testCorrectArtist()
    {
        $match = $this->tidal->search_track_verify('Neste Sommer', ['TIX']);
        $this->assertNotFalse($match);
        $this->assertEquals('Tix', $match['artist']['name']);
        $this->assertEquals('Neste Sommer', $match['title']);
    }
}