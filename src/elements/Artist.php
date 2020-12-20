<?php


namespace datagutten\Tidal\elements;


use datagutten\Tidal\Info;
use datagutten\Tidal\TidalError;

class Artist extends Element
{
    protected $fields = ['id', 'name', 'type'];
    /**
     * @var int Artist ID
     */
    public $id;
    /**
     * @var string Artist name
     */
    public $name;
    /**
     * @var string Artist relation type
     */
    public $type;

    public static function from_tidal(string $id_or_url)
    {
        $tidal = new Info();
        $artist = $tidal->artist($id_or_url);
        return new static($artist, $tidal);
    }

    /**
     * @return array
     * @throws TidalError
     */
    public function albums()
    {
        return $this->tidal->artist_albums($this->id);
    }
}