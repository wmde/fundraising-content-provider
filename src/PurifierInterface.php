<?php

declare( strict_types=1 );

namespace WMDE\Fundraising\ContentProvider;

interface PurifierInterface {

	public function purify( string $html ): string;
}
