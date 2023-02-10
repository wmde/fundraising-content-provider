<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\ContentProvider;

class TwigContentProviderConfig {

	/**
	 * @param string $contentDir path to wmde/fundraising-frontend-content in filesystem
	 * @param string|null $cacheDir path tp write cache files, leave empty to avoid caching
	 * @param array $globals // Global variables injected into every template, e.g. "basepath"
	 * @param bool $debug // use debug mode for template engine
	 */
	public function __construct(
		public readonly string $contentDir,
		public readonly ?string $cacheDir = null,
		public readonly array $globals = [],
		public readonly bool $debug = false,

	) {
	}
}
