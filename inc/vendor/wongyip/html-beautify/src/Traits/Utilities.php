<?php

namespace Wongyip\HTML\Traits;

use Wongyip\HTML\OriginalBeautifyHTML;

trait Utilities
{
    /**
     * @param string $tag
     * @return bool
     */
    private function isInline(string $tag): bool
    {
        $tag = strtolower($tag);

        // Simply no.
        if (!in_array($tag, $this->options['inline_tags'])) return false;

        /**
         * @todo The following is not working as documented in the original code.
         * @see OriginalBeautifyHTML::is_unformatted()
         */

        // Extra handling, in case of HTML5 block-level link.
        if (strtolower($tag) !== 'a' || !in_array('a', $this->options['inline_tags'])) {
            return true;
        }

        // at this point we have an <a> tag; is its first child something we
        // want to remain unformatted?
        //
        // is its first child something we want to remain unformatted?
        // test next_tag to see if it is just html tag (no external content)
        if (preg_match('/^\s*<\s*\/?([a-z]*)\s*[^>]*>\s*$/', ($this->getTag(true) ?: ""), $matches)) {
            // if next_tag comes back but is not an isolated tag, then
            // let's treat the 'a' tag as having content
            // and respect the 'inline_tags' option
            $nextTag = $matches[1];
            return !in_array($nextTag, $this->options['inline_tags']);
        }

        // So yes.
        return true;
    }

    /**
     * @param string $tag
     * @return bool
     */
    private function isSelfClosing(string $tag): bool
    {
        return in_array($tag, [
            'br', 'input', 'link', 'meta', '!doctype', 'basefont', 'base', 'area',
            'hr','wbr','param','img','isindex','?xml','embed','?php','?','?='
        ]);
    }

    /**
     * @param string $char
     * @return bool
     */
    private function isWhitespace(string $char): bool
    {
        return in_array($char, ["\n", "\r", "\t", " "]);
    }

    /**
     * @return bool
     */
    private function traverseWhitespace(): bool
    {
        $char = $this->input[$this->pos] ?? '';
        if ($char && $this->isWhitespace($char)) {
            $this->newLines = 0;
            while ($char && $this->isWhitespace($char)) {
                if ($this->options['preserve_newlines'] &&
                    $char === "\n" &&
                    $this->newLines <= $this->options['preserve_newlines_max']) {
                    $this->newLines += 1;
                }
                $this->pos++;
                $char = $this->input[$this->pos] ?? '';
            }
            return true;
        }
        return false;
    }
}