<?php

namespace Tests\Unit;

use App\Support\NoticeMessage;
use Tests\TestCase;

class NoticeMessageTest extends TestCase
{
    public function test_it_renders_links_and_preserves_other_text(): void
    {
        $rendered = NoticeMessage::render("첫 줄\n[설문조사 하러가기](https://example.com/form?a=1&b=2) 그리고 [홈](http://example.com)");

        $this->assertStringContainsString("첫 줄\n", $rendered);
        $this->assertStringContainsString('href="https://example.com/form?a=1&amp;b=2"', $rendered);
        $this->assertStringContainsString('>설문조사 하러가기</a>', $rendered);
        $this->assertStringContainsString('href="http://example.com"', $rendered);
        $this->assertStringContainsString('rel="noopener noreferrer"', $rendered);
    }

    public function test_it_escapes_html_and_leaves_unsafe_or_invalid_links_as_text(): void
    {
        $rendered = NoticeMessage::render('<script>alert(1)</script> [<b>설문</b>](https://example.com) [위험](javascript:alert) [잘못된 주소](https://)');

        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $rendered);
        $this->assertStringContainsString('&lt;b&gt;설문&lt;/b&gt;</a>', $rendered);
        $this->assertStringContainsString('[위험](javascript:alert)', $rendered);
        $this->assertStringContainsString('[잘못된 주소](https://)', $rendered);
        $this->assertStringNotContainsString('<script>', $rendered);
    }
}
