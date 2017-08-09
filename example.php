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
//var_dump(TagParser\TagParser::parse($text));