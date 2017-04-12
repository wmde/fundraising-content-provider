<?php

declare( strict_types=1 );

namespace WMDE\Fundraising\HtmlFilter;

interface PurifierInterface {

	public function purify( string $html ): string;
}
