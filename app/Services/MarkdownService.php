<?php

namespace App\Services;

class MarkdownService
{
    public function toHtml(?string $markdown): string
    {
        if (empty($markdown)) {
            return '';
        }

        // Minimal safe markdown-to-HTML transformation for MVP.
        // In production, replace with a properly configured markdown-it/highlighter pipeline.
        $html = htmlspecialchars($markdown, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $html = $this->applyInline($html, '**', 'strong');
        $html = $this->applyInline($html, '__', 'strong');
        $html = $this->applyInline($html, '*', 'em');
        $html = $this->applyInline($html, '_', 'em');
        $html = $this->applyInline($html, '`', 'code');
        $html = $this->applyHeadings($html);
        $html = $this->applyLists($html);
        $html = $this->applyBlockquotes($html);
        $html = $this->applyCodeBlocks($html);
        $html = $this->applyLinks($html);
        $html = $this->applyImages($html);
        $html = $this->applyParagraphs($html);

        return $html;
    }

    private function applyInline(string $text, string $marker, string $tag): string
    {
        $pattern = '/'.preg_quote($marker, '/').'(.+?)'.preg_quote($marker, '/').'/';

        return preg_replace($pattern, "<$tag>\$1</$tag>", $text);
    }

    private function applyHeadings(string $text): string
    {
        return preg_replace_callback('/^(#{1,6})\s+(.+)$/m', function ($matches) {
            $level = strlen($matches[1]);

            return "<h$level>".trim($matches[2])."</h$level>";
        }, $text);
    }

    private function applyLists(string $text): string
    {
        return preg_replace('/^([\*\-])\s+(.+)$/m', '<li>$2</li>', $text);
    }

    private function applyBlockquotes(string $text): string
    {
        return preg_replace('/^>\s*(.+)$/m', '<blockquote>$1</blockquote>', $text);
    }

    private function applyCodeBlocks(string $text): string
    {
        return preg_replace('/```[\s\S]*?```/', '<pre><code>code</code></pre>', $text);
    }

    private function applyLinks(string $text): string
    {
        return preg_replace('/\[([^\]]+)\]\(([^\)]+)\)/', '<a href="$2" target="_blank" rel="noopener noreferrer">$1</a>', $text);
    }

    private function applyImages(string $text): string
    {
        return preg_replace('/!\[([^\]]*)\]\(([^\)]+)\)/', '<img src="$2" alt="$1" class="rounded-lg max-w-full">', $text);
    }

    private function applyParagraphs(string $text): string
    {
        $blocks = preg_split('/\n\s*\n/', $text);
        $out = [];

        foreach ($blocks as $block) {
            $trimmed = trim($block);
            if ($trimmed === '') {
                continue;
            }
            if (preg_match('/^<(h[1-6]|pre|blockquote|li)/', $trimmed)) {
                $out[] = $trimmed;
            } else {
                $out[] = '<p>'.str_replace("\n", '<br>', $trimmed).'</p>';
            }
        }

        return implode("\n", $out);
    }
}
