<?php

declare(strict_types=1);

namespace WMDE\Fundraising\HtmlFilter;

use HTMLPurifier_Config;
use HTMLPurifier as OriginalHTMLPurifier;

class HtmlPurifier implements PurifierInterface {

	/**
	 * @var OriginalHTMLPurifier
	 */
	private $purifier;

	private static $allowedTags = 'p,b,a[href],i,strong,em,span,ul,ol,li,h1,h2,h3,h4,h5,h6,br,img[src|alt]';

	public function __construct() {
		$config = HTMLPurifier_Config::createDefault();
		$config->set( 'HTML.Allowed', self::$allowedTags );

		$this->purifier = new OriginalHTMLPurifier( $config );
	}

	public function purify( string $html ): string {
		return $this->purifier->purify( $html );
	}
}
