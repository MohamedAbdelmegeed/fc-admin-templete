<?php

declare(strict_types=1);

namespace Src\Support\Infrastructure\Security;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer as SymfonySanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * محرّر النصوص بيخزّن HTML — والعرض الخام بيشغّله. ده أخطر مصدر XSS
 * عندنا، فبننقّي **عند الحفظ وعند العرض** الاتنين. (docs/20 بند ٥)
 *
 * القائمة بيضاء: أي وسم مش في config('security.html.allowed_tags')
 * بيتشال. مفيش script ولا iframe ولا style ولا on* ولا javascript:.
 */
final class HtmlSanitizer
{
    private ?SymfonySanitizer $sanitizer = null;

    public function clean(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        return $this->sanitizer()->sanitize($html);
    }

    /** للحقول اللي المفروض تبقى نص صافي — بيشيل كل الوسوم. */
    public function stripTags(?string $html): string
    {
        return trim(strip_tags($this->clean($html)));
    }

    private function sanitizer(): SymfonySanitizer
    {
        if ($this->sanitizer instanceof SymfonySanitizer) {
            return $this->sanitizer;
        }

        $config = (new HtmlSanitizerConfig)
            ->allowLinkSchemes((array) config('security.html.allowed_link_schemes', ['http', 'https']))
            ->allowMediaSchemes(['https'])
            ->allowRelativeLinks()
            ->withMaxInputLength((int) config('security.html.max_input_length', 500_000))
            // كل رابط خارجي بياخد rel آمن — من غيره صفحة الهدف تقدر
            // توصل لنافذتنا عبر window.opener.
            ->forceAttribute('a', 'rel', 'noopener noreferrer');

        foreach ((array) config('security.html.allowed_tags', []) as $tag) {
            $config = $config->allowElement($tag, $this->attributesFor($tag));
        }

        if (($hosts = config('security.html.allowed_link_hosts')) !== null) {
            $config = $config->allowLinkHosts((array) $hosts);
        }

        $mediaHosts = (array) config('security.html.allowed_media_hosts', []);

        if ($mediaHosts !== []) {
            $config = $config->allowMediaHosts($mediaHosts);
        }

        return $this->sanitizer = new SymfonySanitizer($config);
    }

    /** @return list<string> */
    private function attributesFor(string $tag): array
    {
        return match ($tag) {
            'a' => ['href', 'title', 'target', 'rel'],
            'code', 'pre', 'span' => ['class'],
            default => [],
        };
    }
}
