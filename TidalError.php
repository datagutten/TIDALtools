<?php
/**
 * Created by PhpStorm.
 * User: Anders
 * Date: 02.06.2019
 * Time: 13.52
 */
class TidalError extends Exception
{
    public function __construct($message, $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}