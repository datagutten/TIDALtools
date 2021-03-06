<?php


namespace datagutten\Tidal\elements;


use ArrayAccess;
use datagutten\Tidal\Info;
use RuntimeException;

abstract class Element implements ArrayAccess
{
    /**
     * @var Info
     */
    public $tidal;
    protected $fields = [];
    /**
     * @var array Original array from TIDAL
     */
    public $data;
    protected static $track_class = Track::class;
    protected static $album_class = Album::class;
    protected static $artist_class = Artist::class;

    abstract public static function from_tidal(string $id_or_url);

    public function __construct(array $data, Info $tidal = null)
    {
        $this->data = $data;
        if (!empty($tidal))
            $this->tidal = $tidal;
        else
            $this->tidal = new Info();

        foreach ($this->fields as $field)
        {
            if (!isset($data[$field]))
                throw new RuntimeException(sprintf('Field %s not found in data', $field));
            $this->$field = $data[$field];
        }
    }

    public function offsetExists($offset)
    {
        return empty($this->$offset);
    }

    public function offsetGet($offset)
    {
        return $this->$offset;
    }

    public function offsetSet($offset, $value)
    {
        $this->$offset = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->$offset);
    }
}