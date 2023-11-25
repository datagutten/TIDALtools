<?php

namespace datagutten\Tidal\element_stubs;

use datagutten\Tidal\elements\Element;
use datagutten\Tidal\Tidal;

abstract class ElementStub
{
    public string $id;
    protected Tidal $tidal;

    public function __construct($id, Tidal $tidal)
    {
        $this->id = $id;
        $this->tidal = $tidal;
    }

    abstract public function get(): Element;
}