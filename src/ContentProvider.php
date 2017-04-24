<?php

declare( strict_types=1 );

namespace WMDE\Fundraising\HtmlFilter;

use Exception;
use Twig_Environment;
use Twig_Error;
use Twig_Extension_Sandbox;
use Twig_Lexer;
use Twig_Loader_Filesystem;
use Twig_Sandbox_SecurityPolicy;

class ContentProvider {

	private const LEXER_CONFIG = [
		'tag_comment' => ['{#', '#}'],
		'tag_block' => ['{%', '%}'],
		'tag_variable' => ['{$', '$}']
	];

	private const TEMPLATE_FILE_EXTENSION = '.twig';

	/**
	 * @var Twig_Environment
	 */
	private $web;

	/**
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
	 * @var Twig_Environment
	 */
	private $mail;

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

			$this->web = new Twig_Environment(
				new PurifyingLoader(
					new Twig_Loader_Filesystem( [$contentDir . '/web', $contentDir . '/shared'] ),
					new HtmlPurifier()
				),
				$envConfig
			);
			$this->configureEnvironment( $this->web, $config );

			$this->mail = new Twig_Environment(
				new Twig_Loader_Filesystem( [$contentDir . '/mail', $contentDir . '/shared'] ),
				array_merge( $envConfig, ['autoescape' => false] )
			);
			$this->configureEnvironment( $this->mail, $config );
		} catch ( Exception $exception ) {
			throw new SetupException( 'An exception occurred setting up the ContentProvider.', 0, $exception );
		}
	}

	/**
	 * Get a rendered web content.
	 *
	 * @param string $name The template name
	 * @param array $context An array of parameters to pass to the template
	 *
	 * @return string
	 */
	public function getWeb( string $name, array $context = [] ): string {
		return $this->render( $this->web, $name, $context );
	}

	/**
	 * Get a rendered mail content.
	 *
	 * @param string $name The template name
	 * @param array $context An array of parameters to pass to the template
	 *
	 * @return string
	 */
	public function getMail( string $name, array $context = [] ): string {
		return $this->render( $this->mail, $name, $context );
	}

	private function render( Twig_Environment $environment, string $name, array $context = [] ): string {
		try {
			$content = $environment->render( $name . self::TEMPLATE_FILE_EXTENSION, $context );
		} catch ( Twig_Error $exception ) {
			throw new ContentException( "An exception occurred rendering '$name'", 0, $exception );
		}

		return $content;
	}

	private function configureEnvironment( Twig_Environment $environment, array $config ): void {
		foreach ( $config['globals'] as $name => $value ) {
			$environment->addGlobal( $name, $value );
		}

		$policy = new Twig_Sandbox_SecurityPolicy(
			['filter', 'include'],
			['nl2br', 'escape'],
			[],
			[],
			[]
		);
		$environment->addExtension( new Twig_Extension_Sandbox( $policy, true ) );

		$environment->setLexer( new Twig_Lexer( $environment, self::LEXER_CONFIG ) );
	}
}
