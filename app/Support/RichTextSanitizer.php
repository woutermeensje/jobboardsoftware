<?php

namespace App\Support;

class RichTextSanitizer
{
    public static function sanitize(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '' || in_array($value, ['<p><br></p>', '<p></p>'], true)) {
            return null;
        }

        if (! class_exists(\DOMDocument::class)) {
            $fallback = trim(strip_tags($value));

            return $fallback === '' ? null : e($fallback);
        }

        $document = new \DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);

        $document->loadHTML(
            '<?xml encoding="utf-8" ?><div id="rich-text-root">'.$value.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD,
        );

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $document->getElementById('rich-text-root');

        if (! $root) {
            return null;
        }

        self::cleanNode($root);

        $html = '';

        foreach ($root->childNodes as $child) {
            $html .= $document->saveHTML($child);
        }

        $html = trim($html);

        return $html === '' ? null : $html;
    }

    private static function cleanNode(\DOMNode $node): void
    {
        $allowedTags = [
            'a',
            'blockquote',
            'br',
            'code',
            'em',
            'h1',
            'h2',
            'h3',
            'h4',
            'h5',
            'h6',
            'i',
            'li',
            'ol',
            'p',
            'pre',
            's',
            'strong',
            'u',
            'ul',
        ];

        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof \DOMText) {
                continue;
            }

            if (! $child instanceof \DOMElement) {
                $node->removeChild($child);

                continue;
            }

            $tagName = mb_strtolower($child->tagName);

            if (in_array($tagName, ['script', 'style', 'iframe', 'object', 'embed'], true)) {
                $node->removeChild($child);

                continue;
            }

            self::cleanNode($child);

            if (! in_array($tagName, $allowedTags, true)) {
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }

                $node->removeChild($child);

                continue;
            }

            self::cleanAttributes($child);
        }
    }

    private static function cleanAttributes(\DOMElement $element): void
    {
        $href = $element->getAttribute('href');
        $classes = collect(explode(' ', $element->getAttribute('class')))
            ->filter(fn (string $class): bool => (bool) preg_match('/^ql-(align-(center|right|justify)|indent-[1-8])$/', $class))
            ->values()
            ->all();

        foreach (iterator_to_array($element->attributes) as $attribute) {
            $element->removeAttribute($attribute->name);
        }

        if ($classes !== []) {
            $element->setAttribute('class', implode(' ', $classes));
        }

        if (mb_strtolower($element->tagName) !== 'a' || $href === '') {
            return;
        }

        $scheme = parse_url($href, PHP_URL_SCHEME);
        $isRelative = $scheme === null && ! str_starts_with($href, '//');
        $isAllowedScheme = is_string($scheme) && in_array(mb_strtolower($scheme), ['http', 'https', 'mailto', 'tel'], true);

        if (! $isRelative && ! $isAllowedScheme) {
            return;
        }

        $element->setAttribute('href', $href);
        $element->setAttribute('rel', 'noopener noreferrer');
    }
}
