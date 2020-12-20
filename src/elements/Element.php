<?php


namespace datagutten\Tidal\elements;


use datagutten\Tidal\Info;
use RuntimeException;

abstract class Element
{
    /**
     * @var Info
     */
    public $tidal;
    protected $fields = [];

    abstract public static function from_tidal(string $id_or_url);

    public function __construct(array $data, Info $tidal = null)
    {
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
}