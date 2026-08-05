<?php

namespace App\Services;

use League\CommonMark\CommonMarkConverter;

class MarkdownRenderer
{
    public function __construct(private readonly CommonMarkConverter $converter = new CommonMarkConverter([
        'html_input' => 'strip',
        'allow_unsafe_links' => false,
    ])) {}

    public function render(string $markdown): string
    {
        return $this->converter->convert($markdown)->getContent();
    }
}
