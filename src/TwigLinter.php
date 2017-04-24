<?php

declare( strict_types=1 );

namespace WMDE\Fundraising\HtmlFilter;

use WMDE\Fundraising\HtmlFilter\HtmlPurifier as FunHtmlPurifier;

class TwigLinter {

	private $exitWithError;

	public function __construct( callable $exitWithError = null ) {
		$this->exitWithError = $exitWithError ?? function( $handle, string $errorMessage ) {
			fwrite( $handle, $errorMessage );
			exit( 1 );
		};
	}

	public function lint( ?string $fileName ): int {
		if ( $fileName === null || trim( $fileName ) === '' ) {
			$this->outputError( 'Required file name argument not provided' );
			return 1;
		}

		echo "Validating $fileName... ";

		$fileContent = @file_get_contents( $fileName );

		if ( !is_string( $fileContent ) ) {
			$this->outputError( 'Could not read file' );
			return 1;
		}

		return $this->lintContent( $fileContent );
	}

	private function outputError( string $errorMessage ) {
		call_user_func( $this->exitWithError, STDERR, 'ERROR: ' . $errorMessage . PHP_EOL );
	}

	private function lintContent( string $fileContent ): int {
		$purifier = new FunHtmlPurifier();

		$purifiedContent = $purifier->purify( $fileContent );

		if ( $this->unifyHtml( $fileContent ) !== $this->unifyHtml( $purifiedContent ) ) {
			$this->outputError(
				'Impure HTML:' . PHP_EOL
				. "\tOriginal: " .  $this->unifyHtml( $fileContent ) . PHP_EOL
				. "\tPurified: " .  $this->unifyHtml( $purifiedContent ) . PHP_EOL
			);
			return 1;
		}

		echo 'OK' . PHP_EOL;
		return 0;
	}

	private function unifyHtml( string $html ): string {
		return preg_replace( '/(\s+|\/)/', '', $html );
	}

}