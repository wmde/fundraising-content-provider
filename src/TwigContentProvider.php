<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\ContentProvider;

use Twig\Environment;
use Twig\Error\Error;

class TwigContentProvider implements ContentProvider {

	private const TEMPLATE_FILE_EXTENSION = '.twig';

	public function __construct( private readonly Environment $web,	private readonly  Environment $mail ) {
	}

	public function getWeb( string $name, array $context = [] ): string {
		return $this->render( $this->web, $name, $context );
	}

	private function render( Environment $environment, string $name, array $context = [] ): string {
		try {
			$content = $environment->render( $name . self::TEMPLATE_FILE_EXTENSION, $context );
		}
		catch ( Error $exception ) {
			throw new ContentException( "An exception occurred rendering '$name'", 0, $exception );
		}

		return $content;
	}

	public function getMail( string $name, array $context = [] ): string {
		return htmlspecialchars_decode(
			$this->render( $this->mail, $name, $context ),
			ENT_QUOTES | ENT_HTML5
		);
	}
}
