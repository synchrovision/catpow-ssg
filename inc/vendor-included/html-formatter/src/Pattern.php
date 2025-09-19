<?php

namespace MallardDuck\HtmlFormatter;

abstract class Pattern
{
    const MARKER = 'ᐃ';

    const PRE = '/<(%s)\b[^>]*>([\s\S]*?)<\/\1>/miu';
    const INLINE = '/<(%s)\b([^>]*)>([^<]*)<\/\1>/miu';
    const ATTRIBUTE = '/([a-z0-9_:-]+)\s*=\s*(["\'])([\s\S]*?)\2/miu';
    const CDATA = '/<!\[CDATA\[([\s\S]*?)\]\]>/miu';
    const WHITESPACE = '/(\s+)/miu';

    const IS_DOCTYPE = '/^<!([^>]*)>/u';
    const IS_BLOCK = '/^<(\w+)\b[^>]*>([^<]*?)<\/\1>/u';
    const IS_SELFCLOSING = '/^<(%s)\b[^>]*>([^<]*?<\/\1>)?/u';
    const IS_OPENING = '/^<(\w+)\b[^>]*>/u';
    const IS_CLOSING = '/^<\/([^>]*)>/u';
    const IS_TEXT = '/^[^\sᐃ<]+[^ᐃ<]*(?=\s?(?:ᐃ|<))/u';
    const IS_WHITESPACE = '/^(\s+)/u';
    const IS_MARKER = '/^ᐃ(\w+)\b:[0-9]+:\1ᐃ/u';

    const TRAILING_SPACE_IN_OPENING_TAG = '/(<[^>]*?)\h+(\/?>)/miu';
    const SPACE_BEFORE_CLOSING_TAG = '/(>[^>\v]*?)\h+(<\/)/miu';
    const SPACE_AFTER_OPENING_TAG = '/(<\w+\b[^>]*>)\h+(\S)/miu';
    const TRAILING_LINE_SPACE = '/(\S*)\h*(\v)/miu';
    const MOVE_TO_LEFT = '/^\h+(<(%s)\b[^>]*?>\v[\s\S]*?(?:<\/\2>))/miu';
    const MOVE_TO_RIGHT = '/^(\h+)(<(%s)\b[^>]*?>\v[\s\S]*?)(<\/\3>)/miu';
}
