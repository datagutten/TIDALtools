<?php


namespace datagutten\Tidal\elements;


use datagutten\Tidal\Info;
use datagutten\Tidal\TidalError;

class Artist extends Element
{
    protected $fields = ['id', 'name', 'type'];
    protected static array $optional_fields = ['type'];
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

    /**
     * @return array
     * @throws TidalError
     */
    public function albums()
    {
        return $this->tidal->artist_albums($this->id);
    }
}