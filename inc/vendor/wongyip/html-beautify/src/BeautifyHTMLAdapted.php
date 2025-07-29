<?php

namespace Wongyip\HTML;

use Closure;
use Wongyip\HTML\Objects\Token;
use Wongyip\HTML\Traits\Utilities;

/**
 * Adapted from the original Beautify_Html::class.
 *
 * @see /org/beautify-html.php
 *
 * Major differences:
 *  - Property name in camel case instead of snake case.
 *  - Replaced the token array with a Token object.
 *  - Grouped utilities methods in a separated trait.
 *  - Modified default options, with enhanced options setter.
 *
 * @todo Tasks
 * Outstanding tasks:
 *  - Optimize the parse() ange getTag() methods.
 *  - Patch the nested inline tag handling in isInline() method.
 */
class BeautifyHTMLAdapted
{
    use Utilities;

    /**
     * Default Options.
     * @var array
     */
    public static array $defaultOptions = [
        'indent_inner_html'     => false,
        'indent_char'           => " ",
        'indent_size'           => 4,
        'indent_scripts'        => 'normal',
        'preserve_newlines'     => false,
        'preserve_newlines_max' => 32768,
        'wrap_line_length'      => 32768,
        // Inline tags to be skipped from formatting, derived from the original
        // default with h1-h6 removed.
        'inline_tags'           => [
            //
            'a', 'span', 'bdo', 'em', 'strong', 'dfn', 'code', 'samp', 'kbd', 'var', 'cite', 'abbr',
            'acronym', 'q', 'sub', 'sup', 'tt', 'i', 'b', 'big', 'small', 'u', 's', 'strike', 'font',
            'ins', 'del', 'pre', 'address', 'dt', // 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'
        ],
        // Tags to be prepended with an empty new line.
        'extra_liners'          => ['head', 'body', '/html'],
    ];

    // Options and callbacks ---------------------------------------------------

    /**
     * Current options.
     * @var array
     */
    protected array $options = [];
    /**
     * @var Closure
     */
    protected Closure $cssBeautify;
    /**
     * @var Closure
     */
    protected Closure $jsBeautify;

    // Private runtime variables -----------------------------------------------

    /**
     * Reflects the current Parser mode: TAG/CONTENT
     * @todo Change to bool.
     * @var string
     */
    private string $currentMode = 'CONTENT';
    /**
     * @var bool|mixed
     * @see static::optionsChanged()
     */
    private bool $indentContent;
    /**
     * @var int
     */
    private int $indentLevel = 0;
    /**
     * @var string
     * @see static::optionsChanged()
     */
    private string $indentString;
    /**
     * Input HTML.
     *
     * @var string
     */
    private string $input =  '';
    /**
     * count to see if wrap_line_length was exceeded
     * @var int
     */
    private int $lineCharCount = 0;
    /**
     * @var int
     */
    private int $newLines = 0;
    /**
     * @var array|string[]
     */
    private array $output = [];
    /**
     * Parser position
     * @var int
     */
    private int $pos = 0;
    /**
     * @var string
     */
    private string $tagType = '';
    /**
     * @var Token
     */
    private Token $tokenLast;
    /**
     * @var string
     */
    private string $____tokenLastText = '';
    /**
     * @var string
     */
    private string $____tokenLastType = '';
    /**
     * An object to hold tags, their position, and their parent-tags, initiated with default values
     *
     * @var array
     */
    private array $tags = [
        'parent'      => 'parent1',
        'parentcount' => 1,
        'parent1'     => ''
    ];
    /**
     * @var int
     */
    private int $inputLength;

    /**
     * Instantiate a new HTML Beautifier.
     *
     * @param array|null $options
     * @param Closure|null $cssBeautify
     * @param Closure|null $jsBeautify
     */
    public function __construct(array $options = null, Closure $cssBeautify = null, Closure $jsBeautify = null)
    {
        // Apply defaults, so that only unrecognized option keys maybe updated.
        $this->options = static::$defaultOptions;
        if ($options) {
            $this->options($options);
        }
        else {
            // Run-once, in case of no options is given.
            $this->optionsChanged();
        }
        // Apply external CSS and JS formatters.
        if ($cssBeautify) $this->cssBeautify = $cssBeautify;
        if ($jsBeautify) $this->jsBeautify = $jsBeautify;
    }

    /**
     * (function to return comment content in its entirety)
     *
     * @param int $posStart
     * @return string
     */
    private function getComment(int $posStart): string
    {
        // (this will have very poor perf, but will work for now.)
        $comment = '';
        $delimiter = '>';
        $this->pos = $posStart;
        $char = $this->input[$this->pos];
        $this->pos++;
        $matched = false;
        while ($this->pos <= $this->inputLength) {

            $comment .= $char;

            // (only need to check for the delimiter if the last chars match)
            if ($comment[strlen($comment) - 1] === $delimiter[strlen($delimiter) - 1] && str_contains($comment, $delimiter)) {
                break;
            }

            // (only need to search for custom delimiter for the first few characters)
            if (!$matched && strlen($comment) < 10) {
                // Conditional comment
                if (str_starts_with($comment, '<![if')) {
                    $delimiter = '<![endif]>';
                    $matched = true;
                // <[cdata[ comment
                }
                elseif (str_starts_with($comment, '<![cdata[')) {
                    $delimiter = ']]>';
                    $matched = true;
                // (some other ![ comment? ...)
                }
                elseif (str_starts_with($comment, '<![')) {
                    $delimiter = ']>';
                    $matched = true;
                }
                // Typical <!-- comment
                elseif (str_starts_with($comment, '<!--')) {
                    $delimiter = '-->';
                    $matched = true;
                }
            }

            $char = $this->input[$this->pos];
            $this->pos++;
        }

        return $comment;
    }

    /**
     * (function to capture regular content between tags)
     *
     * @return string|Token
     */
    private function getContent(): string|Token
    {
        $content = [];
        $space = false; // if a space is needed
        while (isset($this->input[$this->pos]) && $this->input[$this->pos] !== '<') {

            if ($this->pos >= $this->inputLength) {
                // return count($content) ? implode('', $content) : array('', 'TK_EOF');
                return empty($content) ? new Token('', 'TK_EOF') : implode('', $content);
            }

            if ($this->traverseWhitespace()) {
                if (count($content)) {
                    $space = true;
                }
                continue; //don't want to insert unnecessary space
            }

            $char = $this->input[$this->pos];
            $this->pos++;

            if ($space) {
                if ($this->lineCharCount >= $this->options['wrap_line_length']) { //insert a line when the wrap_line_length is reached
                    $this->printNewLine($content);
                    $this->printIndentation($content);
                } else {
                    $this->lineCharCount++;
                    $content[] = ' ';
                }
                $space = false;
            }
            $this->lineCharCount++;
            $content[] = $char; //letter at-a-time (or string) inserted to an array
        }

        return implode('', $content);
    }

    //get the full content of a script or style to pass to js_beautify
    private function getContentsTo(string $name): string|Token
    {
        if ($this->pos === $this->inputLength) {
            return new Token('', 'TK_EOF');
        }
        $content = '';
        $reg_array = array();
        preg_match('#</' . preg_quote($name, '#') . '\\s*>#im', $this->input, $reg_array, PREG_OFFSET_CAPTURE, $this->pos);
        $end_script = $reg_array ? ($reg_array[0][1]) : $this->inputLength; //absolute end of script

        if ($this->pos < $end_script) { //get everything in between the script tags
            $content = substr($this->input, $this->pos, max($end_script-$this->pos, 0));
            $this->pos = $end_script;
        }

        return $content;
    }

    /**
     * @param int $level
     * @return string
     */
    private function getFullIndent(int $level): string
    {
        $level = $this->indentLevel + $level || 0;
        if ($level < 1) {
            return '';
        }
        return str_repeat($this->indentString, $level);
    }

    /**
     * (function to record a tag and its parent in this.tags Object)
     *
     * @param string $tag
     * @return void
     */
    private function recordTag(string $tag): void
    {
        if (isset($this->tags[$tag . 'count'])) { //check for the existence of this tag type
            $this->tags[$tag . 'count']++;
            $this->tags[$tag . $this->tags[$tag . 'count']] = $this->indentLevel; //and record the present indent level
        }
        else { //otherwise initialize this tag type
            $this->tags[$tag . 'count'] = 1;
            $this->tags[$tag . $this->tags[$tag . 'count']] = $this->indentLevel; //and record the present indent level
        }
        $this->tags[$tag . $this->tags[$tag . 'count'] . 'parent'] = $this->tags['parent']; //set the parent (i.e. in the case of a div this.tags.div1parent)
        $this->tags['parent'] = $tag . $this->tags[$tag . 'count']; //and make this the current parent (i.e. in the case of a div 'div1')
    }

    /**
     * (function to retrieve the opening tag to the corresponding closer)
     *
     * @param string $tag
     * @return void
     */
    private function retrieveTag(string $tag): void
    {
        if (isset($this->tags[$tag . 'count'])) { //if the openener is not in the Object we ignore it
            $temp_parent = $this->tags['parent']; //check to see if it's a closable tag.
            while ($temp_parent) { //till we reach '' (the initial value);
                if ($tag . $this->tags[$tag . 'count'] === $temp_parent) { //if this is it use it
                    break;
                }
                $temp_parent = isset($this->tags[$temp_parent . 'parent']) ? $this->tags[$temp_parent . 'parent'] : ''; //otherwise keep on climbing up the DOM Tree
            }
            if ($temp_parent) { //if we caught something
                $this->indentLevel = $this->tags[$tag . $this->tags[$tag . 'count']]; //set the indent_level accordingly
                $this->tags['parent'] = $this->tags[$temp_parent . 'parent']; //and set the current parent
            }
            unset($this->tags[$tag . $this->tags[$tag . 'count'] . 'parent']); //delete the closed tags parent reference...
            unset($this->tags[$tag . $this->tags[$tag . 'count']]); //...and the tag itself
            if ($this->tags[$tag . 'count'] === 1) {
                unset($this->tags[$tag . 'count']);
            } else {
                $this->tags[$tag . 'count']--;
            }
        }
    }

    /**
     * @param string $tag
     * @return void
     */
    private function indentToTag(string $tag): void
    {
        // Match the indentation level to the last use of this tag, but don't remove it.
        if (!$this->tags[$tag . 'count']) {
            return;
        }
        $temp_parent = $this->tags['parent'];
        while ($temp_parent) {
            if ($tag . $this->tags[$tag . 'count'] === $temp_parent) {
                break;
            }
            $temp_parent = $this->tags[$temp_parent . 'parent'];
        }
        if ($temp_parent) {
            $this->indentLevel = $this->tags[$tag . $this->tags[$tag . 'count']];
        }
    }

    /**
     * (function to return unformatted content in its entirety)
     *
     * @param string $delimiter
     * @param bool|string $orig_tag
     * @return string
     */
    private function getUnformatted(string $delimiter, bool|string $orig_tag = false): string
    {
        if (is_string($orig_tag) && str_contains(strtolower($orig_tag), $delimiter)) {
            return '';
        }

        $content = '';
        $min_index = 0;
        $space = true;

        do {
            if ($this->pos >= $this->inputLength) {
                return $content;
            }

            $char = $this->input[$this->pos];
            $this->pos++;

            if ($this->isWhitespace($char)) {
                if (!$space) {
                    $this->lineCharCount--;
                    continue;
                }
                if ($char === "\n" || $char === "\r") {
                    $content .= "\n";
                    /*  Don't change tab indention for unformatted blocks.  If using code for html editing, this will greatly affect <pre> tags if they are specified in the 'unformatted array'
                    for ($i = 0; $i < $this->indent_level; i++) {
                      $content .= $this->indent_string;
                    }
                    $space = false; //...and make sure other indentation is erased
                    */
                    $this->lineCharCount = 0;
                    continue;
                }
            }
            $content .= $char;
            $this->lineCharCount++;
            $space = true;

            /**
             * Assuming Base64 This method could possibly be applied to All Tags
             * but Base64 doesn't have " or ' as part of its data
             * so it is safe to look for the Next delimiter to find the end of the data
             * instead of reading Each character one at a time.
             */

            if (preg_match('/^data:image\/(bmp|gif|jpeg|png|svg\+xml|tiff|x-icon);base64$/', $content ))
            {
                $content .= substr($this->input, $this->pos, strpos($this->input, $delimiter, $this->pos) - $this->pos);
                $this->lineCharCount = strpos($this->input, $delimiter, $this->pos) - $this->pos;
                $this->pos = strpos($this->input, $delimiter, $this->pos);
                continue;
            }


        } while ( strpos(strtolower($content), $delimiter, $min_index) === false);

        return $content;
    }

    /**
     * (function to get a full tag and parse its type)
     *
     * @param bool $peek
     * @return string|Token
     */
    private function getTag(bool $peek = false): string|Token
    {
        $content = [];
        $space = false;
        $tagStart = null;
        $tagStartChar = false;
        $posOriginal = $this->pos;
        $lineCharCountOriginal = $this->lineCharCount;
        do {
            if ($this->pos >= $this->inputLength) {
                if ($peek) {
                    $this->pos = $posOriginal;
                    $this->lineCharCount = $lineCharCountOriginal;
                }
                return empty($content) ? new Token('', 'TK_EOF') : implode('', $content);
            }

            $char = $this->input[$this->pos];
            $this->pos++;

            // (don't want to insert unnecessary space)
            if ($this->isWhitespace($char)) {
                $space = true;
                continue;
            }

            if ($char === "'" || $char === '"') {
                $char .= $this->getUnformatted($char);
                $space = true;
            }

            // (no space before =)
            if ($char === '=') {
                $space = false;
            }

            if (count($content) && $content[count($content) - 1] !== '=' && $char !== '>' && $space) {
                // (no space after = or before >)
                if ($this->lineCharCount >= $this->options['wrap_line_length']) {
                    $this->printNewLine($content);
                    $this->printIndentation($content);
                } else {
                    $content[] = ' ';
                    $this->lineCharCount++;
                }
                $space = false;
            }

            if ($char === '<' && !$tagStartChar) {
                $tagStart = $this->pos - 1;
                $tagStartChar = '<';
            }

            //inserts character at-a-time (or string)
            $content[] = $char;

            // Counter
            $this->lineCharCount++;

            // (if we're in a comment, do something special)
            if (isset($content[1]) && $content[1] === '!') { //
                /**
                 * Treat all comments as literals, even more than preformatted
                 * tags, and just look for the appropriate close tag.
                 */
                $content = array($this->getComment($tagStart));
                break;
            }

        } while ($char !== '>');

        $tagComplete = implode('', $content);

        // (if there's whitespace, that's where the tag name ends)
        if (str_contains($tagComplete, ' ')) {
            $tagIndex = strpos($tagComplete, ' ');
        }
        // (otherwise go with the tag ending)
        else {
            $tagIndex = strpos($tagComplete, '>');
        }

        if ($tagComplete[0] === '<') {
            $tag_offset = 1;
        }
        else {
            $tag_offset = $tagComplete[2] === '#' ? 3 : 2;
        }
        $tag_check = strtolower(substr($tagComplete, $tag_offset, max($tagIndex-$tag_offset, 0)));

        // (if this tag name is a single tag type (either in the list or has a closing /))
        if ($tagComplete[strlen($tagComplete) - 2] === '/' || $this->isSelfClosing($tag_check)) {
            if (!$peek) {
                $this->tagType = 'SINGLE';
            }
        }
        elseif ($tag_check === 'script' /*&&
            (strpos($tag_complete, 'type') === false ||
            (strpos($tag_complete, 'type') !== false &&
            preg_match('/\b(text|application)\/(x-)?(javascript|ecmascript|jscript|livescript)/', $tag_complete)))*/)
        {
            if (!$peek) {
                $this->recordTag($tag_check);
                $this->tagType = 'SCRIPT';
            }
        }
        elseif ($tag_check === 'style' /*&&
            (strpos($tag_complete, 'type') === false ||
            (strpos($tag_complete, 'type') !==false && strpos($tag_complete, 'text/css') !== false))*/ )
        {
            if (!$peek) {
                $this->recordTag($tag_check);
                $this->tagType = 'STYLE';
            }
        }
        // (do not reformat the "unformatted" tags)
        elseif ($this->isInline($tag_check)) {
            // (...delegate to get_unformatted function)
            $comment = $this->getUnformatted('</' . $tag_check . '>', $tagComplete);
            $content[] = $comment;
            // (Preserve collapsed whitespace either before or after this tag.)
            if ($tagStart > 0 && $this->isWhitespace($this->input[$tagStart - 1])) {
                array_splice($content, 0, 0, $this->input[$tagStart - 1]);
            }
            $tag_end = $this->pos - 1;
            if ($this->isWhitespace($this->input[$tag_end + 1])) {
                $content[] = $this->input[$tag_end + 1];
            }
            $this->tagType = 'SINGLE';
        }
        // (peek for <! comment)
        elseif ($tag_check && $tag_check[0] === '!') {
            // (for comments content is already correct.)
            if (!$peek) {
                $this->tagType = 'SINGLE';
                $this->traverseWhitespace();
            }
        }
        elseif (!$peek) {
            // (this tag is a double tag so check for tag-ending)
            if ($tag_check && $tag_check[0] === '/') {
                // (remove it and all ancestors)
                $this->retrieveTag(substr($tag_check, 1));
                $this->tagType = 'END';
                $this->traverseWhitespace();
            }
            // (otherwise it's a start-tag)
            else {
                // (push it on the tag stack)
                $this->recordTag($tag_check);
                if (strtolower($tag_check) !== 'html') {
                    $this->indentContent = true;
                }
                $this->tagType = 'START';
                // (Allow preserving of newlines after a start tag)
                $this->traverseWhitespace();
            }
            // (check if this double needs an extra line)
            if (in_array($tag_check, $this->options['extra_liners'])) {
                $this->printNewLine($this->output);
                if (count($this->output) && $this->output[count($this->output) - 2] !== "\n") {
                    $this->printNewLine($this->output, true);
                }
            }
        }

        if ($peek) {
            $this->pos = $posOriginal;
            $this->lineCharCount = $lineCharCountOriginal;
        }

        // (returns fully formatted tag)
        return implode('', $content);
    }

    /**
     * (initial handler for token-retrieval)
     *
     * @return Token|null
     */
    private function getToken(): ?Token
    {
        // (check if we need to format javascript)
        if (isset($this->tokenLast) && ($this->tokenLast->type === 'TK_TAG_SCRIPT' || $this->tokenLast->type === 'TK_TAG_STYLE')) {
            $type = substr($this->tokenLast->type, 7);
            $token = $this->getContentsTo($type);
            return is_string($token) ? new Token($token, 'TK_' . $type) : $token;
        }
        if ($this->currentMode === 'CONTENT') {
            $token = $this->getContent();
            return is_string($token) ? new Token($token, 'TK_CONTENT') : $token;
        }
        if ($this->currentMode === 'TAG') {
            $token = $this->getTag();
            return is_string($token) ? new Token($token, 'TK_TAG_' . $this->tagType) : $token;
        }
        return null;
    }

    /**
     * @param array $arr
     * @param bool|null $force
     * @return void
     */
    private function printNewLine(array &$arr, bool $force = null)
    {
        $this->lineCharCount = 0;
        if (!$arr || !count($arr)) {
            return;
        }
        if ($force || ($arr[count($arr) - 1] !== "\n")) { //we might want the extra line
            $arr[] = "\n";
        }
    }

    /**
     * @param $arr
     * @return void
     */
    private function printIndentation(&$arr)
    {
        for ($i = 0; $i < $this->indentLevel; $i++) {
            $arr[] = $this->indentString;
            $this->lineCharCount += strlen($this->indentString);
        }
    }

    /**
     * @param string $text
     * @return void
     */
    private function printToken(string $text): void
    {
        if (!empty($text)) {
            if (count($this->output) && $this->output[count($this->output) - 1] === "\n") {
                $this->printIndentation($this->output);
                $text = ltrim($text);
            }
        }
        $this->printTokenRaw($text);
    }

    /**
     * @param string $text
     * @return void
     */
    private function printTokenRaw(string $text): void
    {
        if ($text && $text !== '') {
            if (strlen($text) > 1 && $text[strlen($text) - 1] === "\n") {
                // unformatted tags can grab newlines as their last character
                $this->output[] = substr($text, 0, -1);
                $this->printNewLine($this->output);
            } else {
                $this->output[] = $text;
            }
        }

        for ($n = 0; $n < $this->newLines; $n++) {
            $this->printNewLine($this->output, $n > 0);
        }
        $this->newLines = 0;
    }

    /**
     * @return void
     */
    private function indent(): void
    {
        $this->indentLevel++;
    }

    /**
     * @todo Currently unused.
     * @return void
     */
    private function outdent(): void
    {
        if ($this->indentLevel > 0) {
            $this->indentLevel--;
        }
    }

    /**
     * Get or set the options. Getter return the $options array, setter merge
     * input into existing options.
     *
     * @param array|null $options
     * @return array|$this
     */
    protected function options(array $options = null): array|static
    {
        if (is_null($options)) {
            return $this->options;
        }
        foreach ($options as $key => $val) {
            if (key_exists($key, $this->options)) {
                $this->options[$key] = $val;
            }
        }
        $this->optionsChanged();
        return $this;
    }

    /**
     * Hook on options change.
     *
     * @return void
     */
    protected function optionsChanged(): void
    {
        $this->indentContent = $this->options['indent_inner_html'];
        $this->indentString = str_repeat($this->options['indent_char'], $this->options['indent_size']);
    }

    /**
     * Main process.
     *
     * @param string $input
     * @return string
     */
    protected function parse(string $input): string
    {
        // Init.
        $this->input = $input;
        $this->inputLength = strlen($this->input);
        $this->output = [];
        // We need a token to work on.
        while($token = $this->getToken()) {
            if ($token->type === 'TK_EOF') {
                break;
            }
            switch ($token->type) {
                case 'TK_TAG_START':
                    $this->printNewLine($this->output);
                    $this->printToken($token->text);
                    if ($this->indentContent) {
                        $this->indent();
                        $this->indentContent = false;
                    }
                    $this->currentMode = 'CONTENT';
                    break;
                case 'TK_TAG_STYLE':
                case 'TK_TAG_SCRIPT':
                    $this->printNewLine($this->output);
                    $this->printToken($token->text);
                    $this->currentMode = 'CONTENT';
                    break;
                case 'TK_TAG_END':
                    //Print new line only if the tag has no content and has child
                    if ($this->tokenLast->type === 'TK_CONTENT' && $this->tokenLast->text === '') {
                        $matches = array();
                        preg_match('/\w+/', $token->text, $matches);
                        $tag_name = isset($matches[0]) ? $matches[0] : null;

                        $tag_extracted_from_last_output = null;
                        if (count($this->output)) {
                            $matches = array();
                            preg_match('/(?:<|{{#)\s*(\w+)/', $this->output[count($this->output) - 1], $matches);
                            $tag_extracted_from_last_output = isset($matches[0]) ? $matches[0] : null;
                        }
                        if ($tag_extracted_from_last_output === null || $tag_extracted_from_last_output[1] !== $tag_name) {
                            $this->printNewLine($this->output);
                        }
                    }
                    $this->printToken($token->text);
                    $this->currentMode = 'CONTENT';
                    break;
                case 'TK_TAG_SINGLE':
                    // Don't add a newline before elements that should remain unformatted.
                    $matches = array();
                    preg_match('/^\s*<([a-z]+)/i', $token->text, $matches);
                    $tag_check = $matches ? $matches : null;
                    if (!$tag_check || !in_array($tag_check[1], $this->options['inline_tags'])) {
                        $this->printNewLine($this->output);
                    }
                    $this->printToken($token->text);
                    $this->currentMode = 'CONTENT';
                    break;
                case 'TK_CONTENT':
                    $this->printToken($token->text);
                    $this->currentMode = 'TAG';
                    break;
                case 'TK_STYLE':
                case 'TK_SCRIPT':
                    if ($token->text !== '') {
                        $this->printNewLine($this->output);
                        $text = $token->text;
                        $_beautifier = false;
                        $script_indent_level = 1;

                        if ($token->type === 'TK_SCRIPT') {
                            $_beautifier = $this->jsBeautify ?? false;
                        }
                        elseif ($token->type === 'TK_STYLE') {
                            $_beautifier = $this->cssBeautify ?? false;
                        }

                        if ($this->options['indent_scripts'] === "keep") {
                            $script_indent_level = 0;
                        }
                        elseif ($this->options['indent_scripts'] === "separate") {
                            $script_indent_level = -$this->indentLevel;
                        }

                        $indentation = $this->getFullIndent($script_indent_level);
                        if ($_beautifier) {
                            // call the Beautifier if available
                            $text = $_beautifier(preg_replace('/^\s*/', $indentation, $text), $this->options);
                        }
                        else {
                            // simply indent the string otherwise

                            $matches = array();
                            preg_match('/^\s*/', $text, $matches);
                            $white = isset($matches[0]) ? $matches[0] : null;

                            $matches = array();
                            preg_match('/[^\n\r]*$/', $white, $matches);
                            $dummy = isset($matches[0]) ? $matches[0] : null;

                            $_level = count(explode($this->indentString, $dummy)) - 1;
                            $reindent = $this->getFullIndent($script_indent_level - $_level);

                            $text = preg_replace('/^\s*/', $indentation, $text);
                            $text = preg_replace('/\r\n|\r|\n/', "\n" . $reindent, $text);
                            $text = preg_replace('/\s+$/', '', $text);
                        }

                        if ($text) {
                            $this->printTokenRaw($indentation . trim($text));
                            $this->printNewLine($this->output);
                        }
                    }
                    $this->currentMode = 'TAG';
                    break;
            }
            $this->tokenLast = $token;
        }

        return implode('', $this->output);
    }
}