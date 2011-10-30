<?php

namespace DoxPHP\Parser;

use DoxPHP\Exception\OutOfBoundsException;

/**
 * Parser object, used to parse Tokens
 *
 * @author Bulat Shakirzyanov <mallluhuct@gmail.com> (http://avalanche123.com)
 *
 * asd
 */
class Parser
{
    /**
     * Parses given Tokens object and returns array of parsed blocks
     *
     * @param DoxPHP\Parser\Tokens $tokens - tokens to parse
     *
     * @return array
     */
    public function parse(Tokens $tokens)
    {
        $this->blocks = array();
        $tokens->rewind();

        while($tokens->valid()) {
            $block = (object) array(
                "tags"        => array(),
                "description" => '',
                "isPrivate"   => false,
                "isProtected" => false,
                "isPublic"    => false,
                "isAbstract"  => false,
                "isFinal"     => false,
                "isStatic"    => false,
                "code"        => '',
                "type"        => '',
                "name"        => '',
            );
            $this->blocks[] = $block;

            $this->skipWhitespace($tokens);
            $this->parseBlock($tokens, $block);
        }

        return $this->blocks;
    }

    private function skipWhitespace(Tokens $tokens)
    {
        do {
            $tokens->next();
            $token = $tokens->current();
        } while ($tokens->valid() && in_array($token->name, array("T_WHITESPACE", "T_OPEN_TAG")));
    }

    private function parseBlock(Tokens $tokens, stdClass $block)
    {
        $token = $tokens->current();

        if ("T_DOC_COMMENT" === $token->name) {
            // parse all comments, last one before code wins
            $this->parseComment($token, $block);
            $this->skipWhitespace($tokens);
            $token = $tokens->current();
        }
        if ("T_ABSTRACT" === $token->name) {
            $block->isAbstract = true;
            $block->code .= $token->value." ";
            $this->skipWhitespace($tokens);
            $token = $tokens->current();
        } else {
            $block->isAbstract = false;
        }
        if ("T_FINAL" === $token->name) {
            $block->isFinal = true;
            $block->code .= $token->value." ";
            $this->skipWhitespace($tokens);
            $token = $tokens->current();
        } else {
            $block->isFinal = false;
        }
        if ("T_PRIVATE" === $token->name) {
            $block->isPrivate = true;
            $block->code .= $token->value." ";
            $this->skipWhitespace($tokens);
            $token = $tokens->current();
        } else {
            $block->isPrivate = false;
        }
        if ("T_PROTECTED" === $token->name) {
            $block->isProtected = true;
            $block->code .= $token->value." ";
            $this->skipWhitespace($tokens);
            $token = $tokens->current();
        } else {
            $block->isProtected = false;
        }
        if ("T_PUBLIC" === $token->name) {
            $block->code .= $token->value." ";
            $this->skipWhitespace($tokens);
            $token = $tokens->current();
        }
        if ("T_STATIC" === $token->name) {
            $block->isStatic = true;
            $block->code .= $token->value." ";
            $this->skipWhitespace($tokens);
            $token = $tokens->current();
        } else {
            $block->isStatic = false;
        }

        // implicit public
        $block->isPublic = !($block->isPrivate || $block->isProtected);

        if ("T_CLASS" === $token->name) {
            $block->type = 'class';

            $this->parseClassOrInterface($tokens, $block);
        } elseif ("T_INTERFACE" === $token->name) {
            $block->type = 'interface';

            $this->parseClassOrInterface($tokens, $block);
        } elseif ("T_FUNCTION" === $token->name) {
            $block->type = 'function';

            $this->parseFunctionOrMethod($tokens, $block);
        } elseif ("T_NAMESPACE" === $token->name) {
            $block->type = 'namespace';

            $this->parseNamespace($tokens, $block);
        } elseif ("T_VARIABLE" === $token->name) {
            $block->type = 'variable';

            $this->parseVariable($tokens, $block);
        } elseif ("T_CONST" === $token->name) {
            $block->type = 'constant';

            $this->parseConstant($tokens, $block);
        } else {
            array_pop($this->blocks);
        }
    }

    private function parseComment($token, stdClass $block)
    {
        $lines = array_map(function($line) {
            return substr(trim($line), 2);
        }, array_slice(explode(PHP_EOL, $token->value), 1, -1));

        $tagLines = array();

        foreach ($lines as $i => $line) {
            if (strpos($line, "@") === 0) {
                $tagLines[] = $line;
                unset($lines[$i]);
            }
        }

        $block->tags = array_values(array_map(function($line) {
            $line  = substr($line, 1);
            $words = explode(" ", preg_replace('!\s+!', ' ', $line), 4);
            $count = count($words);

            $tag = array('type'  => $words[0]);

            if ($tag['type'] == 'author') {
                array_shift($words);
                $words = explode(" ", implode(" ", array_filter($words)));
                foreach ($words as $i => $word) {
                    if (0 === strpos($word, "(")) {
                        $tag['website'] = substr($word, 1, -1);
                        unset($words[$i]);
                        continue;
                    }
                    if (0 === strpos($word, "<")) {
                        $tag['email'] = substr($word, 1, -1);
                        unset($words[$i]);
                    }
                }
                $tag['name'] = implode(' ', $words);
            } elseif ($count > 3) {
                $tag['types']       = explode("|", $words[1]);
                $tag['name']        = substr($words[2], 1);
                $tag['description'] = trim($words[3]);
            } elseif ($count > 2) {
                $tag['types']       = explode("|", $words[1]);
                $tag['name']        = substr($words[2], 1);
            } elseif ($count > 1) {
                $tag['types']       = explode("|", $words[1]);
            }

            return $tag;
        }, $tagLines));

        $block->description = trim(str_replace("\n\n\n", "\n\n", implode(PHP_EOL, $lines)), "\n");
    }

    private function parseClassOrInterface(Tokens $tokens, stdClass $block)
    {
        $token   = $tokens->current();
        $gotName = false;

        while ("{" !== $token->value) {
            $block->code .= $token->value;
            if (!$gotName && in_array($token->name, array("T_NS_SEPARATOR", "T_STRING"))) {
                $block->name .= $token->value;
            }
            if (!empty($block->name) && !$gotName && !in_array($token->name, array("T_NS_SEPARATOR", "T_STRING", "T_WHITESPACE"))) {
                $gotName = true;
            }
            $tokens->next();
            $token = $tokens->current();
        }

        $block->code = trim($block->code);

        while ("}" !== $token->value) {
            $subBlock = (object) array(
                "tags"        => array(),
                "description" => '',
                "isPrivate"   => false,
                "isProtected" => false,
                "isPublic"    => false,
                "isAbstract"  => false,
                "isFinal"     => false,
                "isStatic"    => false,
                "code"        => '',
                "type"        => '',
                "name"        => $block->name.'::',
            );

            $this->blocks[] = $subBlock;

            $this->skipWhitespace($tokens);

            if ("}" === $token->value) {
                break;
            }

            $this->parseBlock($tokens, $subBlock);

            if ($subBlock->type === 'function') {
                $subBlock->type = 'method';
            }
            $token = $tokens->current();
        }
    }

    private function parseFunctionOrMethod(Tokens $tokens, stdClass $block)
    {
        $gotName    = false;
        $openCurlys = 0;
        $token      = $tokens->current();

        while (!in_array($token->value, array("{", ";"))) {
            $block->code .= $token->value;

            // first string after keyword is the function name
            if (!$gotName && "T_STRING" === $token->name) {
                $gotName = true;
                $block->name .= $token->value."()";
            }

            $tokens->next();
            $token = $tokens->current();
        }

        $block->code = trim($block->code);
        $block->name = trim($block->name);

        if ("{" === $token->value) {
            $openCurlys = 1;
            $tokens->next();
            $token = $tokens->current();

            while ($openCurlys !== 0) {
                if ("}" === $token->value) {
                    $openCurlys--;
                } elseif ("{" === $token->value) {
                    $openCurlys++;
                }
                $tokens->next();
                $token = $tokens->current();
            }
        }
    }

    private function parseNamespace(Tokens $tokens, stdClass $block)
    {
        $openCurlys = 0;
        $token      = $tokens->current();

        while (!in_array($token->value, array("{", ";"))) {
            $block->code .= $token->value;
            if (in_array($token->name, array("T_NS_SEPARATOR", "T_STRING"))) {
                $block->name .= $token->value;
            }
            $tokens->next();
            $token = $tokens->current();
        }

        $block->code = trim($block->code);
        $block->name = trim($block->name);

        if ("{" === $token->value) {
            $openCurlys = 1;
            $tokens->next();
            $token = $tokens->current();

            // skip to the end of namespace declaration
            while ($openCurlys !== 0) {
                if ("}" === $token->value) {
                    $openCurlys--;
                } elseif ("{" === $token->value) {
                    $openCurlys++;
                }
                $tokens->next();
                $token = $tokens->current();
            }
        }
    }

    private function parseVariable(Tokens $tokens, stdClass $block)
    {
        $token = $tokens->current();

        while (";" !== $token->value) {
            $block->code .= $token->value;
            if ("T_VARIABLE" === $token->name) {
                $block->name = $token->value;
            }
            $tokens->next();
            $token = $tokens->current();
        }

        $block->code = trim($block->code);
        $block->name = trim($block->name);
    }

    private function parseConstant(Tokens $tokens, stdClass $block)
    {
        $token = $tokens->current();

        while (";" !== $token->value) {
            $block->code .= $token->value;

            $tokens->next();
            $token = $tokens->current();

            $block->name .= $token->value;
        }

        $block->code = trim($block->code);
        $block->name = trim($block->name);
    }
}
