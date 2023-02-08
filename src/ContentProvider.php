<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\ContentProvider;

interface ContentProvider {
	/**
	 * Get a rendered web content.
	 *
	 * @param string $name The template name
	 * @param array<string,mixed> $context An array of parameters to pass to the template
	 *
	 * @return string
	 * @throws ContentException
	 */
	public function getWeb( string $name, array $context = [] ): string;

	/**
	 * Get a rendered mail content.
	 *
	 * @param string $name The template name
	 * @param array<string,mixed> $context An array of parameters to pass to the template
	 *
	 * @return string
	 * @throws ContentException
	 */
	public function getMail( string $name, array $context = [] ): string;
}
