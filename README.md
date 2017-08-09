# TagParser
_Author_: **Dominik "c1nema" Pohl**

Easy to use PHP class for parsing BBCode-like Tags


> TagParser allows you to easily parse any given text for BBCode-like tags and handles the processing/replacement
> on the fly.

Documentation: Look at the source code _(it includes proper PHPDoc headers)_

Example:
```php
<?php

require_once __DIR__ . '/TagParser.php';

$text = <<<TEXT
[YouTube NoControls ID="ws3gMD8AecQ"]
[YouTube AutoPlay NoControls ID="C0DPdy98e4c"]

[Random]
[Random]
[Random]
TEXT;

TagParser\TagParser::addTag([
    'Name' => 'YouTube',
    'Handler' => function($flags, $attributes) {
        return '<iframe width="560" height="315" src="https://www.youtube.com/embed/' .
            $attributes['ID'] . '?' .

            (isset($flags['AutoPlay'])   ? '&autoplay=1' : '') .
            (isset($flags['NoControls']) ? '&controls=0' : '') .

            '" frameborder="0" allowfullscreen></iframe>';
    }
]);

\TagParser\TagParser::addTag([
    'Name' => 'Random',
    'Handler' => function($flags, $attributes) {
        return rand(0, 100);
    }
]);

echo TagParser\TagParser::process($text);
```

Output:
```html

<iframe width="560" height="315" src="https://www.youtube.com/embed/ws3gMD8AecQ?&controls=0" frameborder="0" allowfullscreen></iframe>
<iframe width="560" height="315" src="https://www.youtube.com/embed/C0DPdy98e4c?&autoplay=1&controls=0" frameborder="0" allowfullscreen></iframe>

77
94
70
```
