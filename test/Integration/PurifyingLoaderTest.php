<?php

declare( strict_types=1 );

namespace WMDE\Fundraising\ContentProvider\Test\Integration;

use WMDE\Fundraising\ContentProvider\HtmlPurifier;
use WMDE\Fundraising\ContentProvider\PurifyingLoader;
use PHPUnit\Framework\TestCase;
use Twig_Loader_Array;
use Twig_Source;

/**
 * @covers \WMDE\Fundraising\ContentProvider\PurifyingLoader
 */
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
