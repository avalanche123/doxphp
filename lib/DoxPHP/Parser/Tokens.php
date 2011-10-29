<?php

namespace DoxPHP\Parser;

use DoxPHP\Exception\OutOfBoundsException;

class Tokens implements \Iterator
{
    private $tokens;
    private $count;
    private $index;

    public function __construct($string)
    {
        $this->tokens = token_get_all($string);
        $this->count  = count($this->tokens);
        $this->index  = 0;
    }

    public function rewind()
    {
        $this->index = 0;
    }

    public function key()
    {
        return $this->index;
    }

    public function current()
    {
        if (!$this->valid()) {
            throw new OutOfBoundsException();
        }
        return $this->getToken($this->tokens[$this->index]);
    }

    public function valid()
    {
        return $this->index >= 0 && $this->index < $this->count;
    }

    public function next()
    {
        $this->index++;
        return $this->current();
    }

    public function prev()
    {
        $this->index--;
        return $this->current();
    }

    private function getToken($token)
    {
        if (is_array($token)) {
            $tok = array(
                "name"  => token_name($token[0]),
                "value" => $token[1]
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
