<?php

declare( strict_types=1 );

namespace WMDE\Fundraising\HtmlFilter;

use Twig_LoaderInterface;
use Twig_Source;

class PurifyingLoader implements Twig_LoaderInterface {

	/**
	 * @var Twig_LoaderInterface
	 */
	private $originalLoader;

	/**
	 * @var PurifierInterface
	 */
	private $purifier;

	public function __construct( Twig_LoaderInterface $originalLoader, PurifierInterface $purifier ) {
		$this->originalLoader = $originalLoader;
		$this->purifier = $purifier;
	}

	/**
	 * @tutorial Deprecated in twig since 1.27 but still part of the interface, so we need to delegate it
	 *
	 * @deprecated Use getSourceContext instead
	 */
	public function getSource($name)
	{
		$source = $this->originalLoader->getSource( $name );

		return $this->purifier->purify( $source );
	}

	public function getSourceContext( $name ) {
		$source = $this->originalLoader->getSourceContext( $name );
		$code = $source->getCode();

		$code = $this->purifier->purify( $code );

		// @todo Where do we get the 'path' (3rd param) from?, @see Twig_Loader_Filesystem
		return new Twig_Source( $code, $name );
	}

	public function getCacheKey( $name ) {
		return $this->originalLoader->getCacheKey( $name );
	}

	public function isFresh( $name, $time ) {
		return $this->originalLoader->isFresh( $name, $time );
	}

	public function exists( $name ) {
		return $this->originalLoader->exists( $name );
	}
}