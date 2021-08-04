<?php
//declare(strict_types=1);

use datagutten\Tidal\Search;
use datagutten\Tidal\TidalError;
use PHPUnit\Framework\TestCase;

class SearchTest extends TestCase
{
    public $token;
    /**
     * @var Search
     */
    public $tidal;

    /**
     * @throws TidalError
     */
    public function setUp(): void
    {
        if(empty($this->token))
            $this->token = Search::get_token();
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
        $this->assertEquals("Don't Check On Me (feat. Justin Bieber & Ink)", $match['title']);
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
        $this->assertEquals('TIX', $match['artist']['name']);
        $this->assertEquals('Neste Sommer', $match['title']);
    }

    /**
     * @throws TidalError
     */
    public function testLevenshtein()
    {
        $match = $this->tidal->search_track_verify('Det finnes bare vi', ['No.4']);
        $this->assertNotFalse($match);
        $this->assertEquals('No. 4', $match['artist']['name']);
        $this->assertEquals('Det finnes bare vi', $match['title']);
    }

    /**
     * @throws TidalError
     */
    public function testPartialArtist()
    {
        $match = $this->tidal->search_track_verify('NM i drittsekk', ['Karpe Diem']);
        $this->assertNotFalse($match);
        $this->assertEquals('karpe', $match['artist']['name']);
        $this->assertEquals('NM i drittsekk', $match['title']);
    }

    /**
     * When a original track is requested, a remix is not a match
     * @throws TidalError
     */
    public function testNotRemix()
    {
        $track_remix = $this->tidal->track('https://tidal.com/browse/track/87518814');
        $match = $this->tidal->verify_search($track_remix, 'I Owe You', ['Joe Hertz', 'Wolfie']);
        $this->assertEquals(false, $match);
    }

    /**
     * The argument to verify_search should be a single track, not a search result
     */
    public function testSearchResult()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument should be single track, not multiple search results');
        $this->tidal->verify_search(['items'=>[]], '', []);
    }
}