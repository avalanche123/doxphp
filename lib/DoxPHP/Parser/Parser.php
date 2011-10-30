<?php

namespace DoxPHP\Parser;

use DoxPHP\Exception\OutOfBoundsException;

class Parser
{
    public function parse(Tokens $tokens)
    {
        $this->blocks = array();
        $tokens->rewind();

        try {
            while(true) {
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

                $tokens->next();
                $this->skipWhitespace($tokens);

                $this->parseCode($tokens, $block);
            }
        } catch (OutOfBoundsException $e) {            
        }

        return $this->blocks;
    }

    private function skipWhitespace(Tokens $tokens)
    {
        $token = $tokens->current();
        while (in_array($token->name, array("T_WHITESPACE", "T_OPEN_TAG"))) {
            $token = $tokens->next();
        }
    }

    private function parseCode(Tokens $tokens, stdClass $block)
    {
        $this->skipWhitespace($tokens);
        $token = $tokens->current();

        if ("T_DOC_COMMENT" === $token->name) {
            // parse all comments, last one before code wins
            while (true) {
                $this->parseComment($token, $block);

                $tokens->next();
                $this->skipWhitespace($tokens);
                $token = $tokens->current();

                if ("T_DOC_COMMENT" === $token->name) {
                    continue;
                }

                break;
            }
        }
        
        while (!in_array($token->name, array("T_CLASS", "T_INTERFACE", "T_FUNCTION", "T_NAMESPACE", "T_VARIABLE", "T_CONST"))) {
            if ("T_ABSTRACT" === $token->name) {
                $block->isAbstract = true;
                $block->code .= $token->value." ";
            } else {
                $block->isAbstract = false;
            }
            if ("T_FINAL" === $token->name) {
                $block->isFinal = true;
                $block->code .= $token->value." ";
            } else {
                $block->isFinal = false;
            }
            if ("T_PRIVATE" === $token->name) {
                $block->isPrivate = true;
                $block->code .= $token->value." ";
            } else {
                $block->isPrivate = false;
            }
            if ("T_PROTECTED" === $token->name) {
                $block->isProtected = true;
                $block->code .= $token->value." ";
            } else {
                $block->isProtected = false;
            }
            if ("T_PUBLIC" === $token->name) {
                $block->code .= $token->value." ";
            }
            if ("T_STATIC" === $token->name) {
                $block->isStatic = true;
                $block->code .= $token->value." ";
            } else {
                $block->isStatic = false;
            }

            $tokens->next();
            $this->skipWhitespace($tokens);
            $token = $tokens->current();
        }

        // implicit public
        $block->isPublic = !($block->isPrivate || $block->isProtected);

        if ("T_CLASS" === $token->name) {
            $block->type = 'class';
            $this->blocks[] = $block;

            $this->parseClassOrInterface($tokens, $block);
        } elseif ("T_INTERFACE" === $token->name) {
            $block->type = 'interface';
            $this->blocks[] = $block;

            $this->parseClassOrInterface($tokens, $block);
        } elseif ("T_FUNCTION" === $token->name) {
            $block->type = 'function';
            $this->parseFunctionOrMethod($tokens, $block);

            $this->blocks[] = $block;
        } elseif ("T_NAMESPACE" === $token->name) {
            $block->type = 'namespace';
            $this->parseNamespace($tokens, $block);

            $this->blocks[] = $block;
        } elseif ("T_VARIABLE" === $token->name) {
            $block->type = 'variable';
            $this->parseVariable($tokens, $block);

            $this->blocks[] = $block;
        } elseif ("T_CONST" === $token->name) {
            $block->type = 'constant';
            $this->parseConstant($tokens, $block);

            $this->blocks[] = $block;
        } else {
            return false;
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
        $token = $tokens->current();

        $gotName = false;

        while ("{" !== $token->value) {
            $block->code .= $token->value;
            if (!$gotName && in_array($token->name, array("T_NS_SEPARATOR", "T_STRING"))) {
                $block->name .= $token->value;
            }
            if (!empty($block->name) && !$gotName && !in_array($token->name, array("T_NS_SEPARATOR", "T_STRING", "T_WHITESPACE"))) {
                $gotName = true;
            }
            $token = $tokens->next();
        }

        $block->code = trim($block->code);

        $tokens->next();

        $this->skipWhitespace($tokens);

        $token = $tokens->current();

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
                "name"        => '',
            );

            $this->parseCode($tokens, $subBlock);

            if ($subBlock->type === 'function') {
                $subBlock->type = 'method';
            }
            $subBlock->name = $block->name.'::'.$subBlock->name;

            $this->skipWhitespace($tokens);
            $token = $tokens->next();
        }
    }

    private function parseFunctionOrMethod(Tokens $tokens, stdClass $block)
    {
        $token = $tokens->current();

        while (!in_array($token->value, array("{", ";"))) {
            $block->code .= $token->value;

            // first string after keyword is the function name
            if (empty($block->name) && "T_STRING" === $token->name) {
                $block->name = $token->value."()";
            }

            $token = $tokens->next();
        }

        $block->code = trim($block->code);
        $block->name = trim($block->name);

        if ("{" === $token->value) {
            $openCurlys = 1;
            while ($openCurlys !== 0) {
                $token = $tokens->next();
                if ("}" === $token->value) {
                    $openCurlys--;
                } elseif ("{" === $token->value) {
                    $openCurlys++;
                }
            }
        }
    }

    private function parseNamespace(Tokens $tokens, stdClass $block)
    {
        $token = $tokens->current();

        while (!in_array($token->value, array("{", ";"))) {
            $block->code .= $token->value;
            if (in_array($token->name, array("T_NS_SEPARATOR", "T_STRING"))) {
                $block->name .= $token->value;
            }
            $token = $tokens->next();
        }

        $block->code = trim($block->code);
        $block->name = trim($block->name);

        // skip to the end of namespace declaration
        if ("{" === $token->value) {
            $openCurlys = 1;
            while ($openCurlys > 0) {
                $token = $tokens->next();
                if ("}" === $token->value) {
                    $openCurlys--;
                } elseif ("{" === $token->value) {
                    $openCurlys++;
                }
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
            $token = $tokens->next();
        }

        $block->code = trim($block->code);
        $block->name = trim($block->name);

        $this->skipWhitespace($tokens);
    }

    private function parseConstant(Tokens $tokens, stdClass $block)
    {
        $token = $tokens->current();

        while (";" !== $token->value) {
            $block->code .= $token->value;
            $this->skipWhitespace($tokens);
            $token = $tokens->next();
            $block->name .= $token->value;
        }

        $block->code = trim($block->code);
        $block->name = trim($block->name);

        $this->skipWhitespace($tokens);
    }
}
