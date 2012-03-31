<?php

namespace DoxPHP\Parser;

use DoxPHP\Exception\OutOfBoundsException;

class Tokens extends \ArrayIterator
{
    public function __construct($string)
    {
        parent::__construct(token_get_all($string));
    }

    public function current()
    {
        $token = parent::current();
        if (is_array($token)) {
            $tok = array(
                "name"  => token_name($token[0]),
                "value" => $token[1],
                "line"  => $token[2],
            );
        } else {
            $tok = array(
                "name"  => null,
                "value" => $token
            );
        }

        return (object) $tok;
    }
}
