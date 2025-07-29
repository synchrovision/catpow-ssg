<?php

namespace Wongyip\HTML\Objects;

class Token
{
    /**
     * @var string
     */
    public string $text;
    /**
     * @var string
     */
    public string $type;

    /**
     * @param string $text
     * @param string $type
     */
    public function __construct(string $text, string $type)
    {
        $this->text = $text;
        $this->type = $type;
    }
}