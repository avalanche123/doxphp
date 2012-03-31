# Dox PHP

Dox for PHP is a documentation engine for PHP inspired by the [Dox](https://github.com/visionmedia/dox) for JavaScript.

# Installation

## Pear

```console
pear channel-discover pear.avalanche123.com
pear install avalanche123/doxphp-beta
```

## Github

Clone this repository and put the `doxphp` under bin directory in your executable path.

# Usage

Dox PHP operates over stdio:

```shell
$ doxphp < test.php
...JSON...
```

test.php

```php
<?php

/**
 * Greets the world
 *
 * @author Bulat Shakirzyanov <mallluhuct@gmail.com> (http://avalanche123.com)
 *
 * @param string $world - the world to greet
 *
 * @return void
 */
function hello($world) {
  echo "Hello ${world}";
}
```

output

```js
[
    {
        "tags": [
            {
                "type"   : "author"
              , "email"  : "mallluhuct@gmail.com"
              , "website": "http:\/\/avalanche123.com"
              , "name"   : "Bulat Shakirzyanov"
            }
          , {
                "type"       : "param"
              , "types"      : [ "string" ]
              , "name"       : "world"
              , "description": "- the world to greet"
            }
          , {
                "type" : "return"
              , "types": [ "void" ]
            }
        ]
      , "description": "Greets the world"
      , "isPrivate"  : false
      , "isProtected": false
      , "isPublic"   : true
      , "isAbstract" : false
      , "isFinal"    : false
      , "isStatic"   : false
      , "code"       : "function hello($world)"
      , "type"       : "function"
      , "name"       : "hello()"
      , "line"       : 12
    }
]
```

# Supports

* classes and interfaces
* functions and methods (produces slightly different results)
* namespaces (who phpdocs them really?)
* class variables and constants (sweet!)

# Installation

Use pear to install

```console
pear channel-discover pear.avalanche123.com
pear install avalanche123/doxphp-alpha
```

# Renderers

* doxphp2sphinx

```console
doxphp < test.php | doxphp2sphinx  > test.rst
```

test.rst:

```rst
.. php:function:: hello

   Greets the world

   :param string $world: - the world to greet

   :returns void:
```

* doxphp2docco

```console
doxphp2docco *.php
```

this creates `docs` directory in the current directory and populates it with html files.

test.html:

```html
<!DOCTYPE html>

<html>
<head>
  <title>test.php</title>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8">
  <link rel="stylesheet" media="all" href="resources/doxphp.css" />
</head>
<body>
  <div id="container">
    <div id="background"></div>
        <table cellpadding="0" cellspacing="0">
      <thead>
        <tr>
          <th class="docs">
            <h1>test.php</h1>
          </th>
          <th class="code">
          </th>
        </tr>
      </thead>
      <tbody>
        <tr id="section-1">
          <td class="docs">
            <div class="pilwrap">
              <a class="pilcrow" href="#section-1">&#182;</a>
            </div>
            <p>Greets the world</p>
          </td>
          <td class="code">
            <div class="highlight">
              <pre>
                <span class="cp">&lt;?php</span>
                <span class="k">function</span>
                <span class="nf">hello</span>
                <span class="p">(</span>
                <span class="nv">$world</span>
                <span class="p">)</span>
              </pre>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</body>
</html>
```

# TODO

implement more renderers
