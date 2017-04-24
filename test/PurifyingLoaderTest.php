<?php

declare( strict_types=1 );

namespace WMDE\Fundraising\HtmlFilter\Test;

use WMDE\Fundraising\HtmlFilter\PurifierInterface;
use WMDE\Fundraising\HtmlFilter\PurifyingLoader;
use PHPUnit\Framework\TestCase;
use Twig_LoaderInterface;
use Twig_Source;

class PurifyingLoaderTest extends TestCase {

	public function testGetSourceContextCallsOriginalLoader(): void {
		$originalLoader = $this->createMock( Twig_LoaderInterface::class );
		$originalLoader
			->expects( $this->once() )
			->method( 'getSourceContext' )
			->with( 'greeting.twig' )
			->willReturn( new Twig_Source( 'hello', 'greeting.twig' ) );

		$purifier = $this->createMock( PurifierInterface::class );
		$purifier
			->expects( $this->once() )
			->method( 'purify' )
			->with( 'hello' )
			->willReturn( 'hello' );

		$loader = new PurifyingLoader( $originalLoader, $purifier );

		$this->assertEquals(
			new Twig_Source( 'hello', 'greeting.twig' ),
			$loader->getSourceContext( 'greeting.twig' )
		);
	}

	public function testgetCacheKeyCallsOriginalLoader(): void {
		$originalLoader = $this->createMock( Twig_LoaderInterface::class );
		$originalLoader
			->expects( $this->once() )
			->method( 'getCacheKey' )
			->with( 'afg' )
			->willReturn( 'jkl' );

		$purifier = $this->createMock( PurifierInterface::class );
		$purifier
			->expects( $this->never() )
			->method( $this->anything() );

		$loader = new PurifyingLoader( $originalLoader, $purifier );

		$this->assertSame( 'jkl', $loader->getCacheKey( 'afg' ) );
	}

	public function testIsFreshCallsOriginalLoader(): void {
		$originalLoader = $this->createMock( Twig_LoaderInterface::class );
		$originalLoader
			->expects( $this->once() )
			->method( 'isFresh' )
			->with( 'ezekiel', 2517 )
			->willReturn( true );

		$purifier = $this->createMock( PurifierInterface::class );
		$purifier
			->expects( $this->never() )
			->method( $this->anything() );

		$loader = new PurifyingLoader( $originalLoader, $purifier );

		$this->assertTrue( $loader->isFresh( 'ezekiel', 2517 ) );
	}

	public function testExistsCallsOriginalLoader(): void {
		$originalLoader = $this->createMock( Twig_LoaderInterface::class );
		$originalLoader
			->expects( $this->once() )
			->method( 'exists' )
			->with( 'john' )
			->willReturn( true );

		$purifier = $this->createMock( PurifierInterface::class );
		$purifier
			->expects( $this->never() )
			->method( $this->anything() );

		$loader = new PurifyingLoader( $originalLoader, $purifier );

		$this->assertTrue( $loader->exists( 'john' ) );
	}
}
