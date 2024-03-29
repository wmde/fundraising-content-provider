<?php

declare( strict_types=1 );

namespace WMDE\Fundraising\ContentProvider;

use HTMLPurifier as OriginalHTMLPurifier;
use HTMLPurifier_Config;

class HtmlPurifier {

	/**
	 * @var OriginalHTMLPurifier
	 */
	private $purifier;

	/**
	 * Tags and attributes passed to HtmlPurifier - optimized for maximum readability.
	 */
	private const ALLOWED_TAGS = '
		h1,h2,h3,h4,h5,h6,
		p,
		br,hr,
		ul,ol,li,
		span,b,i,u,strong,em,
		a[href|target],
		img[src|alt],
		table[class],thead,tbody,tr,th[scope],td[scope]
	';

	public function __construct() {
		$config = HTMLPurifier_Config::createDefault();
		$config->set( 'HTML.Allowed', self::ALLOWED_TAGS );
		$config->set( 'Attr.AllowedFrameTargets', [ '_blank' ] );
		/** Allow comments for JS placeholders ("placeholder_SOMEKEYWORD") */
		$config->set( 'HTML.AllowedCommentsRegexp', '/^(placeholder_)(\w+)+/' );

		$this->purifier = new OriginalHTMLPurifier( $config );
	}

	public function purify( string $html ): string {
		return $this->purifier->purify( $html );
	}
}
