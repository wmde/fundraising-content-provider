<?php

declare( strict_types=1 );

namespace WMDE\Fundraising\ContentProvider\Test\Integration;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\ContentProvider\HtmlPurifier;

#[CoversClass( HtmlPurifier::class )]
class HtmlPurifierTest extends TestCase {

	/**
	 * @var HtmlPurifier
	 */
	private $sut;

	public function setUp(): void {
		$this->sut = new HtmlPurifier();
	}

	public function testReturnsAllAllowedTags(): void {
		$this->assertSame(
			'<h1>my <u>test</u> <em>site</em></h1>
<p>lorem</p>
<ul><li>item <strong>1</strong></li>
</ul><img src="/logo.png" alt="wikimedia" />
some<br />
thing<br /><hr />
new
<table class="bobby"><tr><td>1</td></tr></table>
dolor
<a href="http://wikipedia.org" target="_blank" rel="noreferrer noopener">opening in new window, rel added by HtmlPurifier</a>
amet
<!-- placeholder_SOMEKEYWORD -->

<a href="http://wikimedia.de">ordinary link</a>',
			$this->sut->purify(
				'<h1>my <u>test</u> <em>site</em></h1>
<p>lorem</p>
<ul><li>item <strong>1</strong></li>
</ul><img src="/logo.png" alt="wikimedia" />
some<br>
thing<br/><hr />
new
<table class="bobby"><tr><td>1</td></tr></table>
dolor
<a href="http://wikipedia.org" target="_blank">opening in new window, rel added by HtmlPurifier</a>
amet
<!-- placeholder_SOMEKEYWORD -->
<!-- This comment must be stripped -->
<a href="http://wikimedia.de">ordinary link</a>'
			)
		);
	}

	public function testStripsDisallowedTags(): void {
		$this->assertSame( 'invalid div', $this->sut->purify( '<div>invalid div</div>' ) );
	}

	public function testRepairsDamagedTags(): void {
		$this->assertSame( '<p>dangling paragraph</p>', $this->sut->purify( '<p>dangling paragraph' ) );
	}

	public function testRemovesInvalidAttributes(): void {
		$this->assertSame( '<p>BIG</p>', $this->sut->purify( '<p style="font-size:100000px">BIG</p>' ) );
	}
}
