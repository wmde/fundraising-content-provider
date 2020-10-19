<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\ContentProvider\Test\Unit;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Twig\Environment;
use WMDE\Fundraising\ContentProvider\ContentProvider;

/**
 * Poor man's unit tests in the absence of clean DI on ContentProvider
 *
 * @covers \WMDE\Fundraising\ContentProvider\ContentProvider
 */
class ContentProviderTest extends TestCase {

	public function testParsePluralizeReturnsCorrectValue(): void {
		$contentProvider = new ContentProvider( [ 'content_path' => __DIR__ . '/../data' ] );

		$this->assertEquals( 'None', $contentProvider->getWeb( 'PluralizeFile', [ 'count' => 0 ] ) );
		$this->assertEquals( 'One', $contentProvider->getWeb( 'PluralizeFile', [ 'count' => 1 ] ) );
		$this->assertEquals( 'Many', $contentProvider->getWeb( 'PluralizeFile', [ 'count' => 9 ] ) );
		$this->assertEquals( 'None', $contentProvider->getWeb( 'PluralizeFile', [ 'count' => null ] ) );
		$this->assertEquals( 'None', $contentProvider->getWeb( 'PluralizeFile', [ 'count' => false ] ) );
	}

	public function testGetWebDelegatesToWebTwig(): void {
		$provider = new ReflectionClass( ContentProvider::class );
		/**
		 * @var ContentProvider
		 */
		$instance = $provider->newInstanceWithoutConstructor();

		$webTwig = $this->createMock( Environment::class );
		$webTwig->expects( $this->once() )
			->method( 'render' )
			->with( 'lorem.twig', [ 'a' => 'b' ] )
			->willReturn( 'a thing' );

		$web = $provider->getProperty( 'web' );
		$web->setAccessible( true );
		$web->setValue( $instance, $webTwig );

		$this->assertSame( 'a thing', $instance->getWeb( 'lorem', [ 'a' => 'b' ] ) );
	}

	public function testGetMailDelegatesToMailTwig(): void {
		$provider = new ReflectionClass( ContentProvider::class );
		/**
		 * @var ContentProvider
		 */
		$instance = $provider->newInstanceWithoutConstructor();

		$webTwig = $this->createMock( Environment::class );
		$webTwig->expects( $this->once() )
			->method( 'render' )
			->with( 'lorem.twig', [ 'c' => 'd' ] )
			->willReturn( 'more things' );

		$web = $provider->getProperty( 'mail' );
		$web->setAccessible( true );
		$web->setValue( $instance, $webTwig );

		$this->assertSame( 'more things', $instance->getMail( 'lorem', [ 'c' => 'd' ] ) );
	}
}
