<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\Matomo;

use MediaWiki\Hook\SkinAfterBottomScriptsHook;
use Skin;

/**
 * Hooks for the Matomo extension
 */
class Hooks implements SkinAfterBottomScriptsHook {

	/**
	 * Placeholder hook implementation. Tracking output is added in a later phase.
	 *
	 * @param Skin $skin
	 * @param string &$text
	 */
	public function onSkinAfterBottomScripts( $skin, &$text ): void {
	}
}
