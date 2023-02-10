<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\ContentProvider;

use Exception;
use Twig\Environment;
use Twig\Extension\SandboxExtension;
use Twig\Lexer;
use Twig\Loader\FilesystemLoader;
use Twig\Sandbox\SecurityPolicy;
use Twig\TwigFunction;

class TwigContentProviderFactory {

	private const LEXER_CONFIG = [
		'tag_comment' => [ '{#', '#}' ],
		'tag_block' => [ '{%', '%}' ],
		'tag_variable' => [ '{$', '$}' ]
	];

	public static function createContentProvider( TwigContentProviderConfig $config ): TwigContentProvider {
		$contentDir = $config->contentDir;

		$envConfig = [
			'cache' => $config->cacheDir ?? false,
			'debug' => $config->debug,
		];

		try {
			$web = new Environment(
				new FilesystemLoader( [ $contentDir . '/web', $contentDir . '/shared' ] ),
				$envConfig
			);
			self::configureEnvironment( $web, $config->globals );

			$mail = new Environment(
				new FilesystemLoader( [ $contentDir . '/mail', $contentDir . '/shared' ] ),
				array_merge( $envConfig, [ 'autoescape' => false ] )
			);
			self::configureEnvironment( $mail, $config->globals );
		} catch ( Exception $exception ) {
			throw new SetupException( 'An exception occurred setting up the ContentProvider.', 0, $exception );
		}

		return new TwigContentProvider( $web, $mail );
	}

	private static function configureEnvironment( Environment $environment, array $globals ): void {
		foreach ( $globals as $name => $value ) {
			$environment->addGlobal( $name, $value );
		}

		$policy = new SecurityPolicy(
			[ 'filter', 'include' ],
			[ 'nl2br', 'escape', 'length', 'date' ],
			[],
			[],
			[ 'pluralize' ]
		);

		$environment->addExtension( new SandboxExtension( $policy, true ) );

		$environment->addFunction(
			new TwigFunction(
				'pluralize',
				static function ( $count, $one, $many, $none = null ): string {
					if ( !$count ) {
						$count = 0;
					}

					$none = $none ?? $many;

					switch ( $count ) {
						case 0:
							return $none;
						case 1:
							return $one;
						default:
							return $many;
					}
				}
			)
		);

		$environment->setLexer( new Lexer( $environment, self::LEXER_CONFIG ) );
	}
}
