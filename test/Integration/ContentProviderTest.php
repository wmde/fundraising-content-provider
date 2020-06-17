<?php

declare( strict_types=1 );

namespace WMDE\Fundraising\ContentProvider\Test\Integration;

use WMDE\Fundraising\ContentProvider\ContentException;
use WMDE\Fundraising\ContentProvider\ContentProvider;
use WMDE\Fundraising\ContentProvider\SetupException;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

/**
 * @covers \WMDE\Fundraising\ContentProvider\ContentProvider
 */
class ContentProviderTest extends TestCase {

	public function testTwigInstancesUseLexerConfig() {
		$content = vfsStream::setup( 'content', null, [
			'web' => [
				'a.twig' => 'a {$ variable $}{# comment not rendered #}'
			],
			'mail' => [
				'b.twig' => 'b {$ variable $}{# comment not rendered #}'
			],
			'shared' => [
				'c.twig' => 'c {$ variable $}{# comment not rendered #}'
			],
		] );

		$provider = new ContentProvider( [
			'content_path' => $content->url(),
		] );

		$this->assertSame( 'a lorem', $provider->getWeb( 'a', ['variable' => 'lorem'] ) );
		$this->assertSame( 'c ipsum', $provider->getWeb( 'c', ['variable' => 'ipsum'] ) );

		$this->assertSame( 'b lorem', $provider->getMail( 'b', ['variable' => 'lorem'] ) );
		$this->assertSame( 'c amet', $provider->getMail( 'c', ['variable' => 'amet'] ) );
	}

	public function testFoldersLoadAndInRightOrder(): void {
		$content = vfsStream::setup( 'content', null, [
			'web' => [
				'some_html.twig' => '<p>lorem {$ variable $}.</p>',
				'conflicting_name.twig' => 'from web'
			],
			'mail' => [
				'some_plaintext.twig' => '{$ variable $} <http://wikipedia.de>',
				'conflicting_name.twig' => 'from mail'
			],
			'shared' => [
				'conflicting_name.twig' => 'i should never be loaded',
				'nonconflicting_name.twig' => 'from shared'
			],
		] );

		$provider = new ContentProvider( [
			'content_path' => $content->url(),
		] );

		$this->assertSame(
			'<p>lorem one.</p>',
			$provider->getWeb( 'some_html', ['variable' => 'one'] )
		);
		$this->assertSame(
			'from web',
			$provider->getWeb( 'conflicting_name' ),
			'Templates with same name should be loaded from web directory first!'
		);
		$this->assertSame(
			'from shared',
			$provider->getWeb( 'nonconflicting_name' )
		);

		$this->assertSame(
			'two <http://wikipedia.de>',
			$provider->getMail( 'some_plaintext', ['variable' => 'two'] )
		);
		$this->assertSame(
			'from mail',
			$provider->getMail( 'conflicting_name' ),
			'Templates with same name should be loaded from mail directory first!'
		);
		$this->assertSame(
			'from shared',
			$provider->getMail( 'nonconflicting_name' )
		);
	}

	public function testGlobalVariablesAvailableButLocalPrevails(): void {
		$content = vfsStream::setup( 'content', null, [
			'web' => [
				'myhtml.twig' => '{$ variable $}',
			],
			'mail' => [
				'myplaintext.twig' => '{$ variable $}',
			],
			'shared' => [],
		] );

		$provider = new ContentProvider( [
			'content_path' => $content->url(),
			'globals' => [
				'variable' => 'globalvalue'
			]
		] );

		$this->assertSame( 'globalvalue', $provider->getWeb( 'myhtml' ) );
		$this->assertSame( 'local', $provider->getWeb( 'myhtml', ['variable' => 'local'] ) );

		$this->assertSame( 'globalvalue', $provider->getMail( 'myplaintext' ) );
		$this->assertSame( 'local', $provider->getMail( 'myplaintext', ['variable' => 'local'] ) );
	}

	public function testMissingContentPathSetupCausesNotice(): void {
		$this->expectNotice();
		$this->expectExceptionMessageMatches( '/Undefined index: content_path/' );

		new ContentProvider( [] );
	}

	public function testBadSetupCausesSetupException(): void {
		$this->expectException( SetupException::class );
		$this->expectExceptionMessageMatches( '/An exception occurred setting up the ContentProvider./' );

		new ContentProvider( ['content_path' => '/missing/link'] );
	}

	public function testMissingWebTemplateCausesContentException(): void {
		$this->expectException( ContentException::class );

		$content = vfsStream::setup( 'content', null, [
			'web' => [],
			'mail' => [],
			'shared' => [],
		] );

		$provider = new ContentProvider( [
			'content_path' => $content->url()
		] );

		$provider->getWeb( 'not_there' );
	}

	public function testMissingMailTemplateCausesContentException(): void {
		$this->expectException( ContentException::class );

		$content = vfsStream::setup( 'content', null, [
			'web' => [],
			'mail' => [],
			'shared' => [],
		] );

		$provider = new ContentProvider( [
			'content_path' => $content->url()
		] );

		$provider->getMail( 'not_there' );
	}

	public function testUnspecifiedVariableRendersBlank(): void {
		$content = vfsStream::setup( 'content', null, [
			'web' => [
				'template_with_variable.twig' => 'prefix{$ variable $}suffix'
			],
			'mail' => [],
			'shared' => [],
		] );

		$provider = new ContentProvider( [
			'content_path' => $content->url()
		] );

		$this->assertSame(
			'prefixsuffix',
			$provider->getWeb( 'template_with_variable', ['some_other' => 'value'] )
		);
	}

	public function testInWebTemplatesPurificationIsNotPerformed(): void {
		$content = vfsStream::setup( 'content', null, [
			'web' => [
				'fancy_html.twig' => '<section><div>not purified!<brokentag></div></section>',
			],
			'mail' => [],
			'shared' => [],
		] );

		$provider = new ContentProvider( [
			'content_path' => $content->url(),
		] );

		$this->assertSame(
			'<section><div>not purified!<brokentag></div></section>',
			$provider->getWeb( 'fancy_html' )
		);
	}
}
