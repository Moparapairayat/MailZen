<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SupportTicketMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'support_ticket_id',
        'sender_id',
        'sender_type',
        'body',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'support_ticket_id');
    }

    public function sender(): MorphTo
    {
        return $this->morphTo();
    }

    public function getBodyForDisplayAttribute(): string
    {
        return self::sanitizeBody($this->body);
    }

    public static function sanitizeBody(?string $html): string
    {
        $html = (string) $html;

        if (trim($html) === '') {
            return '';
        }

        $allowedTags = [
            'a',
            'b',
            'blockquote',
            'br',
            'code',
            'div',
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
            'span',
            'strong',
            'u',
            'ul',
        ];

        $allowedAttrs = [
            'a' => ['href', 'target', 'rel'],
        ];

        $doc = new \DOMDocument();

        $previous = libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $xpath = new \DOMXPath($doc);

        foreach (['script', 'style', 'iframe', 'object', 'embed'] as $tag) {
            foreach ($doc->getElementsByTagName($tag) as $node) {
                $node->parentNode?->removeChild($node);
            }
        }

        foreach ($xpath->query('//*') as $el) {
            if (!$el instanceof \DOMElement) {
                continue;
            }

            $tag = strtolower($el->tagName);

            if (!in_array($tag, $allowedTags, true)) {
                while ($el->firstChild) {
                    $el->parentNode?->insertBefore($el->firstChild, $el);
                }
                $el->parentNode?->removeChild($el);
                continue;
            }

            $allowedForTag = $allowedAttrs[$tag] ?? [];
            $attrsToRemove = [];

            foreach ($el->attributes ?? [] as $attr) {
                $name = strtolower($attr->name);

                if (str_starts_with($name, 'on') || $name === 'style') {
                    $attrsToRemove[] = $attr->name;
                    continue;
                }

                if (!in_array($name, $allowedForTag, true)) {
                    $attrsToRemove[] = $attr->name;
                }
            }

            foreach ($attrsToRemove as $attrName) {
                $el->removeAttribute($attrName);
            }

            if ($tag === 'a') {
                $href = trim((string) $el->getAttribute('href'));

                if ($href !== '') {
                    $hrefLower = strtolower($href);

                    $isSafeHref =
                        str_starts_with($hrefLower, 'http://') ||
                        str_starts_with($hrefLower, 'https://') ||
                        str_starts_with($hrefLower, 'mailto:') ||
                        str_starts_with($hrefLower, 'tel:') ||
                        str_starts_with($hrefLower, '/') ||
                        str_starts_with($hrefLower, '#');

                    if (!$isSafeHref) {
                        $el->removeAttribute('href');
                    }
                }

                if (strtolower((string) $el->getAttribute('target')) === '_blank') {
                    $rel = trim((string) $el->getAttribute('rel'));
                    $relParts = array_filter(preg_split('/\s+/', $rel) ?: []);
                    $relParts[] = 'noopener';
                    $relParts[] = 'noreferrer';
                    $el->setAttribute('rel', implode(' ', array_values(array_unique($relParts))));
                }
            }
        }

        return trim((string) $doc->saveHTML());
    }
}
