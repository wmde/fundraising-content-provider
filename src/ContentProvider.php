<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\ContentProvider;

use Exception;
use Twig\Environment;
use Twig\Error\Error;
use Twig\Extension\SandboxExtension;
use Twig\Lexer;
use Twig\Loader\FilesystemLoader;
use Twig\Sandbox\SecurityPolicy;
use Twig\TwigFunction;

class ContentProvider {

	private const LEXER_CONFIG = [
		'tag_comment' => [ '{#', '#}' ],
		'tag_block' => [ '{%', '%}' ],
		'tag_variable' => [ '{$', '$}' ]
	];

	private const TEMPLATE_FILE_EXTENSION = '.twig';
	private Environment $web;
	private Environment $mail;

	/**
	 * Create a new instance
	 *
	 * Requires a configuration array in the following form
	 *
	 * new ContentProvider( [
	 *   'content_path' => '/path/to/my/contentrepo',  // place of wmde/fundraising-frontend-content in filesystem
	 *   'cache' => '/tmp/mycache',  // place in filesystem to use as template cache
	 *   'debug' => false,  // use debug mode for template engine
	 *   'globals' => [
	 *     'basepath' => '',  // used for url prefixing
	 *   ]
	 * ] );
	 *
	 * @param array $config Configuration settings
	 */
	public function __construct( array $config ) {
		$contentDir = $config['content_path'];

		if ( empty( $config['globals'] ) ) {
			$config['globals'] = [];
		}

		$envConfig = [
			'cache' => $config['cache'] ?? false,
			'debug' => $config['debug'] ?? false,
		];

		try {

			$this->web = new Environment(
				new FilesystemLoader( [ $contentDir . '/web', $contentDir . '/shared' ] ),
				$envConfig
			);
			$this->configureEnvironment( $this->web, $config );

			$this->mail = new Environment(
				new FilesystemLoader( [ $contentDir . '/mail', $contentDir . '/shared' ] ),
				array_merge( $envConfig, [ 'autoescape' => false ] )
			);
			$this->configureEnvironment( $this->mail, $config );
		}
		catch ( Exception $exception ) {
			throw new SetupException( 'An exception occurred setting up the ContentProvider.', 0, $exception );
		}
	}

	private function configureEnvironment( Environment $environment, array $config ): void {
		foreach ( $config['globals'] as $name => $value ) {
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
				function ( $count, $one, $many, $none = null ): string {
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

	/**
	 * Get a rendered web content.
	 *
	 * @param string $name The template name
	 * @param array $context An array of parameters to pass to the template
	 *
	 * @return string
	 * @throws ContentException
	 */
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

	/**
	 * Get a rendered mail content.
	 *
	 * @param string $name The template name
	 * @param array $context An array of parameters to pass to the template
	 *
	 * @return string
	 * @throws ContentException
	 */
	public function getMail( string $name, array $context = [] ): string {
		return $this->render( $this->mail, $name, $context );
	}
}
