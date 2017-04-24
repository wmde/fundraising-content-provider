<?php

declare( strict_types=1 );

namespace WMDE\Fundraising\HtmlFilter\Test\Integration;

use WMDE\Fundraising\HtmlFilter\HtmlPurifier;
use WMDE\Fundraising\HtmlFilter\PurifyingLoader;
use PHPUnit\Framework\TestCase;
use Twig_Loader_Array;
use Twig_Source;

class PurifyingLoaderTest extends TestCase {

	public function testIntegrateConcreteLoaderWithHtmlPurifier(): void {
		$originalLoader = new Twig_Loader_Array( [
			'lorem' => '<div>ipsum</div>'
		] );

		$purifier = new HtmlPurifier();

		$loader = new PurifyingLoader( $originalLoader, $purifier );

		$source = $loader->getSourceContext( 'lorem' );
		$this->assertInstanceOf( Twig_Source::class, $source );
		$this->assertSame( 'ipsum', $source->getCode() );
	}
}
