<?php

declare( strict_types=1 );

namespace WMDE\Fundraising\HtmlFilter\Test;

use WMDE\Fundraising\HtmlFilter\HtmlPurifier;
use PHPUnit\Framework\TestCase;

class HtmlPurifierTest extends TestCase {

	/**
	 * @var HtmlPurifier
	 */
	private $sut;

	public function setUp(): void {
		$this->sut = new HtmlPurifier();
	}

	public function testReturnsOnlyAllowedTags(): void {
		$this->assertSame(
			'<h1>my test <em>site</em></h1>
<p>lorem</p>
<ul><li>item <strong>1</strong></li>
</ul><img src="/logo.png" alt="wikimedia" />
some<br />
thing<br />
new',
			$this->sut->purify(
				'<h1>my test <em>site</em></h1>
<p>lorem</p>
<ul>
    <li>item <strong>1</strong></li>
</ul>

<img src="/logo.png" alt="wikimedia" />
some<br>
thing<br/>
new'
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
