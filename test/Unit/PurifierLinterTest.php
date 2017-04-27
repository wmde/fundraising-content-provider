<?php

declare( strict_types=1 );

namespace WMDE\Fundraising\ContentProvider\Test\Unit;

use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\ContentProvider\Linter\PurifierLinter;

/**
 * @covers PurifierLinter
 */
class PurifierLinterTest extends TestCase {

	public function testGivenNullFileName_errorIsOutput() {
		$linter = new PurifierLinter( function( $handle, string $errorMessage ) {
			$this->assertSame( 'ERROR: Required file name argument not provided' . PHP_EOL, $errorMessage );
		} );

		$linter->lint( null );
	}

	public function testGivenEmptyFileName_errorIsOutput() {
		$linter = new PurifierLinter( function( $handle, string $errorMessage ) {
			$this->assertSame( 'ERROR: Required file name argument not provided' . PHP_EOL, $errorMessage );
		} );

		$linter->lint( '' );
	}

	public function testGivenNonExistingFileName_errorIsOutput() {
		$errorWasOutput = false;

		$linter = new PurifierLinter( function( $handle, string $errorMessage ) use ( &$errorWasOutput ) {
			$this->assertSame( 'ERROR: Could not read file' . PHP_EOL, $errorMessage );
			$errorWasOutput = true;
		} );

		$this->expectOutputRegex( '/Validating/' );
		$this->assertSame( 1, $linter->lint( '/tmp/pink-fluffy-unicorns-dancing-on-rainbows.kittens' ) );
		$this->assertTrue( $errorWasOutput, 'Error should be output' );
	}

	public function testGivenNameOfFileWithValidHtml_noErrorsAreOutput() {
		$linter = new PurifierLinter( function( $handle, string $errorMessage ) {
			$this->fail( 'No errors should be output, yet got: ' . $errorMessage );
		} );

		$this->expectOutputRegex( '/OK/' );
		$this->assertSame( 0, $linter->lint( __DIR__ . '/../data/ValidHtmlFile.twig' ) );
	}

	public function testGivenNameOfFileWithInvalidHtml_errorIsOutput() {
		$errorWasOutput = false;

		$linter = new PurifierLinter( function( $handle, string $errorMessage ) use ( &$errorWasOutput ) {
			$this->assertContains( 'Impure HTML', $errorMessage );
			$errorWasOutput = true;
		} );

		$this->expectOutputRegex( '/Validating/' );
		$linter->lint( __DIR__ . '/../data/InvalidHtmlFile.twig' );
		$this->assertTrue( $errorWasOutput, 'Error should be output' );
	}

}
