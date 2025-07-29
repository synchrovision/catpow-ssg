# HTML Beautify

Based on the [Beautify HTML](https://github.com/ivanweiler/beautify-html) by [Ivan Weiler](https://github.com/ivanweiler), I'm here to bring it to the [Packagist](https://packagist.org/) only, no feature is added. Please find the original [`README`](org/README.md) for details.

## Installation
```sh
composer require wongyip/html-beautify
```

## Usage
The same usage with the original [Beautify HTML](https://github.com/ivanweiler/beautify-html)
is maintained, except of the namespaced classname. As in most cases (at least in most of my
cases), `Beautify::class` will be used once only within the whole request/command life cycle,
so a static `Beautify::html()` method is added to format HTML in a state-less manner.

```php
use \Wongyip\HTML\Beautify;

$html = <<<HTML
    <!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Title</title></head>
    <body><div class="row col-sm12"><ol><li></li><li></li><li></li></ol></div></body></html>
    HTML;

# State-less
echo Beautify::html($html);

# Reusable
$beautifier = new Beautify();
echo $beautifier->beautify($html);

# Alternative static constructor
echo Beautify::init()->beautify($html);
```
_All the above `echo` statements output the same HTML below:_
```html
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Title</title>
</head>

<body>
<div class="row" style="color: brown;">
    <ul>
        <li>Hello</li>
        <li>World!</li>
    </ul>
</div>
</body>

</html>
```

## Options
```php
use \Wongyip\HTML\Beautify;

# All options are optional.
$options = [
    'indent_inner_html'     => false,
    'indent_char'           => " ",
    'indent_size'           => 4,
    'wrap_line_length'      => 32768,
    'unformatted'           => ['code', 'pre'],
    'preserve_newlines'     => false,
    'preserve_newlines_max' => 32768,
    'indent_scripts'        => 'normal',
];

# Set on instantiate.
$beautifier = new Beautify($options);

# Update option(s)
$beautifier->options(['indent_size' => 2]);

# Get options
$options = $beautifier->options();
```

## Try it out
```bash
composer create-project wongyip/html-beautify
cd html-beautify
composer install
php demo/demo.php
```