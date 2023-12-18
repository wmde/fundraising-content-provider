<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\ContentProvider\Test\Unit;

use PHPUnit\Framework\TestCase;
use Twig\Environment;
use WMDE\Fundraising\ContentProvider\TwigContentProvider;

/**
 * @covers \WMDE\Fundraising\ContentProvider\TwigContentProvider
 */
class TwigContentProviderTest extends TestCase {

	public function testGetWebDelegatesToWebTwig(): void {
		$mailerTwig = $this->createStub( Environment::class );
		$webTwig = $this->createMock( Environment::class );
		$webTwig->expects( $this->once() )
			->method( 'render' )
			->with( 'lorem.twig', [ 'a' => 'b' ] )
			->willReturn( 'a thing' );

		$provider = new TwigContentProvider( $webTwig, $mailerTwig );

		$this->assertSame( 'a thing', $provider->getWeb( 'lorem', [ 'a' => 'b' ] ) );
	}

	public function testGetMailDelegatesToMailTwig(): void {
		$webTwig = $this->createStub( Environment::class );
		$mailerTwig = $this->createMock( Environment::class );
		$mailerTwig->expects( $this->once() )
			->method( 'render' )
			->with( 'ipsum.twig', [ 'c' => 4 ] )
			->willReturn( 'Thank you' );

		$provider = new TwigContentProvider( $webTwig, $mailerTwig );

		$this->assertSame( 'Thank you', $provider->getMail( 'ipsum', [ 'c' => 4 ] ) );
	}

	public function testGetMailConvertsHtmlEntitiesToCharacters(): void {
		$webTwig = $this->createStub( Environment::class );
		$mailerTwig = $this->createStub( Environment::class );
		$mailerTwig->method( 'render' )
			->willReturn( '&amp; &lt; &gt; &quot; &apos; &uuml;' );

		$provider = new TwigContentProvider( $webTwig, $mailerTwig );

		$this->assertSame( '& < > " \' &uuml;', $provider->getMail( 'ipsum' ) );
	}
}
