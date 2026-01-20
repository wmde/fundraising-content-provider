<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\ContentProvider;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use WMDE\Fundraising\ContentProvider\HtmlPurifier as FunHtmlPurifier;

class ContentLinter extends Command {

	public const NAME = 'lint-content';

	public const EXIT_OK = 0;
	public const EXIT_TWIG_ERROR = 1;
	public const EXIT_HTML_ERROR = 2;

	private TwigContentProvider $contentProvider;

	private OutputInterface $errOutput;

	protected function configure(): void {
		$this->setName( self::NAME )
			->setDescription( 'Check content for validity' )
			->addOption(
				'web',
				'w',
				InputOption::VALUE_NONE,
				'Validate web content instead of mail content'
			)
			->addArgument(
				'content-path',
				InputArgument::REQUIRED,
				'Path to web, mail and shared directories'
			)
			->addArgument( 'content', InputArgument::REQUIRED, 'Name of content file' );
	}

	protected function initialize( InputInterface $input, OutputInterface $output ): void {
		$this->contentProvider = TwigContentProviderFactory::createContentProvider( new TwigContentProviderConfig( $input->getArgument( 'content-path' ) ) );
		$this->errOutput = $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$contentName = $input->getArgument( 'content' );

		if ( $input->getOption( 'web' ) ) {
			$returnCode = $this->lintWeb( $contentName );
		} else {
			$returnCode = $this->lintMail( $contentName );
		}

		if ( $returnCode === self::EXIT_OK ) {
			$output->writeln( sprintf( '%-50s <info>OK</info>', $contentName ), Output::VERBOSITY_VERBOSE );
		}

		return $returnCode;
	}

	private function lintWeb( string $contentName ): int {
		try {
			$content = $this->contentProvider->getWeb( $contentName );
		} catch ( ContentException $e ) {
			$this->showTwigErrorMessage( $contentName, $e->getPrevious()->getMessage() );
			return self::EXIT_TWIG_ERROR;
		}

		$purifier = new FunHtmlPurifier();
		$purifiedContent = $purifier->purify( $content );

		if ( $this->unifyHtml( $content ) !== $this->unifyHtml( $purifiedContent ) ) {
			$this->showInvalidHtmlErrorMessage( $contentName, $content, $purifiedContent );
			return self::EXIT_HTML_ERROR;
		}

		return self::EXIT_OK;
	}

	private function lintMail( string $contentName ): int {
		try {
			$this->contentProvider->getMail( $contentName );
		} catch ( ContentException $e ) {
			$this->showTwigErrorMessage( $contentName, $e->getPrevious()->getMessage() );
			return self::EXIT_TWIG_ERROR;
		}

		return self::EXIT_OK;
	}

	private function showTwigErrorMessage( string $contentName, string $twigMessage ): void {
		$this->errOutput->writeln( "<error>[Error]</error> Could not validate Twig template for '$contentName'" );
		$this->errOutput->writeln( $twigMessage );
	}

	private function showInvalidHtmlErrorMessage( string $contentName, string $content, string $purifiedContent ): void {
		$this->errOutput->writeln( "<error>[Error]</error> Invalid HTML in '$contentName'</error>" );
		$this->errOutput->writeln( "\tOriginal: " . $content );
		$this->errOutput->writeln( "\tFiltered: " . $purifiedContent );
	}

	private function unifyHtml( string $html ): string {
		$html = str_replace( 'rel="noreferrer noopener"', '', $html );
		return preg_replace( '/(\s+|\/)/', '', $html );
	}

}
