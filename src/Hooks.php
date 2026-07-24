<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\Matomo;

use MediaWiki\Hook\SkinAfterBottomScriptsHook;
use MediaWiki\MediaWikiServices;
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

	/**
	 * ResourceLoader packageFiles callback providing tracker config to ext.matomo.tracker.
	 *
	 * @param mixed $context ResourceLoaderContext (MW < 1.36) or MediaWiki\ResourceLoader\Context (MW >= 1.36)
	 * @return array
	 */
	public static function getTrackerConfig( $context ): array {
		$config = MediaWikiServices::getInstance()->getMainConfig();

		return [
			'url' => $config->get( 'MatomoURL' ),
			'idSite' => $config->get( 'MatomoIDSite' ),
			'protocol' => $config->get( 'MatomoProtocol' ),
		];
	}
}
