<?php


namespace datagutten\Tidal\elements;


use datagutten\Tidal\Info;
use datagutten\Tidal\Tidal;
use JsonSerializable;
use RuntimeException;

abstract class Element extends SimpleArrayAccess implements JsonSerializable
{
    public Info $tidal;
    public Tidal $tidal_api;
    protected array $fields = [];
    protected static array $optional_fields = [];
    protected static array $json_extra_fields = [];

    /**
     * @var array Original array from TIDAL
     */
    public $data;
    protected static string $track_class = Track::class;
    protected static string $album_class = Album::class;
    protected static string $artist_class = Artist::class;

    public function __construct(array $data, Info $tidal = null, Tidal $api = null)
    {
        $this->data = $data;

        if (!empty($api))
            $this->tidal_api = $api;

        if (!empty($tidal))
            $this->tidal = $tidal;
        elseif (!empty($this->tidal_api))
            $this->tidal = $api->info;
        else
            $this->tidal = new Info();

        foreach ($this->fields as $field)
        {
            if (!isset($data[$field]))
            {
                if (array_search($field, static::$optional_fields) === false)
                    throw new RuntimeException(sprintf('Field %s not found in data', $field));
                else
                    continue;
            }
            $this->$field = $data[$field];
        }
    }

    /**
     * Convert an array of artists to a single string
     * @param Artist[] $artists
     * @return string
     */
    public static function artistString(array $artists): string
    {
        if (count($artists) == 1)
            return $artists[0]->name;

        $artist_string = '';
        $first_featured = true;
        foreach ($artists as $artist)
        {
            if ($artist->type == 'MAIN')
                $artist_string .= $artist->name;
            elseif ($artist->type == 'FEATURED')
            {
                if ($first_featured)
                {
                    $artist_string .= ' feat. ';
                    $first_featured = false;
                }
                else
                    $artist_string .= ' & ';
                $artist_string .= $artist->name;
            }
        }
        return $artist_string;
    }

    public function jsonSerialize(): array
    {
        $data = [];
        foreach (array_merge($this->fields, static::$json_extra_fields) as $field)
        {
            if (is_callable([$this, $field]))
            {
                $data[$field] = $this->$field();
            }
            elseif (!empty($this->$field))
                $data[$field] = $this->$field;

        }
        return $data;
    }
}