<?php

use Wongyip\HTML\Beautify;

require_once __DIR__ . '/../vendor/autoload.php';

$html = file_get_contents(__DIR__ . '/sample.html');

$options = [
    'indent_inner_html'     => false,
    'indent_char'           => " ",
    'indent_size'           => 4,
    'wrap_line_length'      => 32768,
    'inline_tags'           => [
        // Derived from the original default, with h1-h6 removed.
        'a',
        'span', 'bdo', 'em', 'strong', 'dfn', 'code', 'samp', 'kbd', 'var', 'cite', 'abbr',
        'acronym', 'q', 'sub', 'sup', 'tt', 'i', 'b', 'big', 'small', 'u', 's', 'strike', 'font',
        'ins', 'del', 'pre', 'address', 'dt', // 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'
    ],
    'preserve_newlines'     => false,
    'preserve_newlines_max' => 32768,
    'indent_scripts'        => 'normal', // keep|separate|normal
];

$output = Beautify::html($html, $options);

$line = str_repeat('-', 80);

echo "\nInput:\n$line\n\n$html\n\nOutput:\n$line\n\n$output\n\n";