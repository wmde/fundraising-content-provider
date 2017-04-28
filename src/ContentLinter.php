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

	const NAME = 'lint-content';

	const EXIT_OK = 0;
	const EXIT_TWIG_ERROR = 1;
	const EXIT_HTML_ERROR = 2;

	protected function configure() {
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

	protected function execute( InputInterface $input, OutputInterface $output ) {

		$contentPath = $input->getArgument( 'content-path' );
		$contentProvider = new ContentProvider( ['content_path' => $contentPath] );
		$contentName = $input->getArgument( 'content' );

		$errOutput = $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;

		$method = $input->getOption( 'web' ) ? 'getWeb' : 'getMail';
		try {
			$content = $contentProvider->$method( $contentName );
		} catch ( ContentException $e ) {
			$errOutput->writeln( "<error>[Error]</error> Could not validate Twig template for '$contentName'" );
			$errOutput->writeln( $e->getPrevious()->getMessage() );
			return self::EXIT_TWIG_ERROR;
		}

		if ( $method !== 'getWeb' ) {
			$output->writeln( sprintf( '%-50s <info>OK</info>', $contentName ), Output::VERBOSITY_VERBOSE );
			return self::EXIT_OK;
		}

		$purifier = new FunHtmlPurifier();

		$purifiedContent = $purifier->purify( $content );

		if ( $this->unifyHtml( $content ) !== $this->unifyHtml( $purifiedContent ) ) {
			$errOutput->writeln( "<error>[Error]</error> Invalid HTML in '$contentName'</error>" );
			$errOutput->writeln( "\tOriginal: " . $content );
			$errOutput->writeln( "\tPurified: " . $purifiedContent );
			return self::EXIT_HTML_ERROR;
		}

		$output->writeln( sprintf( '%-50s <info>[OK]</info>', $contentName ), Output::VERBOSITY_VERBOSE );
		return self::EXIT_OK;
	}

	private function unifyHtml( string $html ): string {
		$html = str_replace( 'rel="noreferrer noopener"', '', $html );
		return preg_replace( '/(\s+|\/)/', '', $html );
	}

}
