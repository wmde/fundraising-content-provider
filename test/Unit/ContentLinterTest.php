<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\ContentProvider\Test\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use WMDE\Fundraising\ContentProvider\ContentLinter;

/**
 * @covers ContentLinter
 */
class ContentLinterTest extends TestCase {

	/**
	 * @var CommandTester
	 */
	protected $commandTester;

	protected function setUp(): void {
		$linter = new ContentLinter();
		$app = new Application();
		$app->add( $linter );
		$this->commandTester = new CommandTester( $linter );
	}

	public function testGivenNonexistingContent_errorIsReturned() {
		$this->commandTester->execute( [ 'content-path' => __DIR__ . '/../data', 'content' => 'dummy' ] );
		$this->assertSame( ContentLinter::EXIT_TWIG_ERROR, $this->commandTester->getStatusCode() );
		$this->assertRegExp( '/Error.*Unable to find/s', $this->commandTester->getDisplay() );
	}

	public function testGivenValidContent_commandExitsWithoutStatusCode() {
		$this->commandTester->execute( [ 'content-path' => __DIR__ . '/../data', 'content' => 'ValidHtmlFile', '--web' => true ] );
		$this->assertSame( ContentLinter::EXIT_OK, $this->commandTester->getStatusCode() );
	}

	public function testGivenValidContent_noOutputIsGeneratedAtDefaultVerbosity() {
		$this->commandTester->execute(
			[ 'content-path' => __DIR__ . '/../data', 'content' => 'ValidHtmlFile', '--web' => true ],
			[ 'verbosity' => OutputInterface::VERBOSITY_NORMAL ]
		);
		$this->assertSame( '', $this->commandTester->getDisplay() );
	}

	public function testGivenValidContent_outputIsGeneratedAtHighVerbosity() {
		$this->commandTester->execute(
			[ 'content-path' => __DIR__ . '/../data', 'content' => 'ValidHtmlFile', '--web' => true ],
			[ 'verbosity' => OutputInterface::VERBOSITY_VERBOSE ]
		);
		$this->assertRegExp( '/ValidHtmlFile.*OK/', $this->commandTester->getDisplay() );
	}

	public function testGivenTwigFileWithSandboxedInstructions_errorIsReturned() {
		$this->commandTester->execute(
			[ 'content-path' => __DIR__ . '/../data', 'content' => 'InvalidTwigInstructions' ]
		);
		$this->assertSame( ContentLinter::EXIT_TWIG_ERROR, $this->commandTester->getStatusCode() );
		$this->assertRegExp( '/Error.*Tag "if" is not allowed/s', $this->commandTester->getDisplay() );
	}

	public function testGivenInvalidHtmlOutput_errorIsReturned() {
		$this->commandTester->execute(
			[ 'content-path' => __DIR__ . '/../data', 'content' => 'InvalidHtmlFile', '--web' => true ]
		);
		$this->assertSame( ContentLinter::EXIT_HTML_ERROR, $this->commandTester->getStatusCode() );
		$this->assertRegExp( '/Error.* Invalid HTML/s', $this->commandTester->getDisplay() );
	}

	public function testGivenValidHtmlOutput_okIsReturned() {
		$this->commandTester->execute(
			[ 'content-path' => __DIR__ . '/../data', 'content' => 'ValidHtmlFile', '--web' => true ]
		);
		$this->assertSame( ContentLinter::EXIT_OK, $this->commandTester->getStatusCode() );
	}
}
