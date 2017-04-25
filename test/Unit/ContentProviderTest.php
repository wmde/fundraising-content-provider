<?php

declare( strict_types=1 );

namespace WMDE\Fundraising\HtmlFilter\Test\Unit;

use Twig_Environment;
use WMDE\Fundraising\HtmlFilter\ContentProvider;
use ReflectionClass;
use PHPUnit\Framework\TestCase;

/**
 * Poor man's unit tests in the absence of clean DI on ContentProvider
 *
 * @covers \WMDE\Fundraising\HtmlFilter\ContentProvider
 */
class ContentProviderTest extends TestCase {

	public function testGetWebDelegatesToWebTwig(): void {
		$provider = new ReflectionClass( ContentProvider::class );
		/**
		 * @var ContentProvider
		 */
		$instance = $provider->newInstanceWithoutConstructor();

		$webTwig = $this->createMock( Twig_Environment::class);
		$webTwig->expects($this->once())
			->method('render')
			->with('lorem.twig', [ 'a' => 'b' ])
			->willReturn('a thing');

		$web = $provider->getProperty( 'web' );
		$web->setAccessible( true );
		$web->setValue($instance, $webTwig);

		$this->assertSame('a thing', $instance->getWeb('lorem', [ 'a' => 'b' ]));
	}

	public function testGetMailDelegatesToMailTwig(): void {
		$provider = new ReflectionClass( ContentProvider::class );
		/**
		 * @var ContentProvider
		 */
		$instance = $provider->newInstanceWithoutConstructor();

		$webTwig = $this->createMock( Twig_Environment::class);
		$webTwig->expects($this->once())
			->method('render')
			->with('lorem.twig', [ 'c' => 'd' ])
			->willReturn('more things');

		$web = $provider->getProperty( 'mail' );
		$web->setAccessible( true );
		$web->setValue($instance, $webTwig);

		$this->assertSame('more things', $instance->getMail('lorem', [ 'c' => 'd' ]));
	}
}
