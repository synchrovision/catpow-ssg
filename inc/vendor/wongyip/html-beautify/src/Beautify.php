<?php

namespace Wongyip\HTML;

use Closure;

/**
 * A wrapper class to the original Beautify_Html class, bringing proper type-hint
 * to IDE, added init() method as static constructor and the options() get-setter
 * method.
 */
class Beautify extends BeautifyHTMLAdapted
{
    /**
     * State-less method.
     *
     * @param string $input
     * @param array|null $options
     * @param Closure|null $cssBeautify
     * @param Closure|null $jsBeautify
     * @return string
     */
    public static function html(string $input, array $options = null, Closure $cssBeautify = null, Closure $jsBeautify = null): string
    {
        return Beautify::init($options, $cssBeautify, $jsBeautify)->beautify($input);
    }

    /**
     * Instantiate a HTML Beautifier.
     *
     * @param array|null $options
     * @param Closure|null $cssBeautify
     * @param Closure|null $jsBeautify
     * @return static
     */
    public static function init(array $options = null, Closure $cssBeautify = null, Closure $jsBeautify = null): static
    {
        return new static($options, $cssBeautify, $jsBeautify);
    }

    /**
     * Beautify the input HTML.
     *
     * @param string $html
     * @return string
     */
    public function beautify(string $html): string
    {
        return parent::parse($html);
    }
}