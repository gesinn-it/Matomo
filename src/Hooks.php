<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\Matomo;

use ISearchResultSet;
use MediaWiki\Hook\BeforePageDisplayHook;
use MediaWiki\Hook\SkinAfterBottomScriptsHook;
use MediaWiki\Hook\SpecialSearchResultsHook;
use MediaWiki\Hook\SpecialSearchSetupEngineHook;
use MediaWiki\MediaWikiServices;
use MediaWiki\User\UserIdentity;
use OutputPage;
use SearchEngine;
use Skin;
use SpecialSearch;

/**
 * Hooks for the Matomo extension
 */
class Hooks implements
	BeforePageDisplayHook,
	SkinAfterBottomScriptsHook,
	SpecialSearchResultsHook,
	SpecialSearchSetupEngineHook
{

	/**
	 * Searched term on Special:Search, or null if the current request is not a search.
	 *
	 * Hook handler classes registered without a "services" spec are instantiated
	 * anew per hook call, so this can't be an instance property: it must survive
	 * from onSpecialSearchResults/onSpecialSearchSetupEngine through to the
	 * ResourceLoader packageFiles callback later in the same request.
	 *
	 * @var string|null
	 */
	private static $searchTerm;

	/**
	 * Number of results for the current Special:Search request.
	 *
	 * @var int|null
	 */
	private static $searchCount;

	/**
	 * Search profile/category for the current Special:Search request.
	 *
	 * @var string|null
	 */
	private static $searchCategory;

	/**
	 * Resets the per-request search tracking state. Test-only.
	 */
	public static function resetSearchState(): void {
		self::$searchTerm = null;
		self::$searchCount = null;
		self::$searchCategory = null;
	}

	/**
	 * Placeholder hook implementation. Tracking output is added in a later phase.
	 *
	 * @param Skin $skin
	 * @param string &$text
	 */
	public function onSkinAfterBottomScripts( $skin, &$text ): void {
	}

	/**
	 * Captures the search term and result count on Special:Search.
	 *
	 * @param string $term
	 * @param ISearchResultSet|null &$titleMatches
	 * @param ISearchResultSet|null &$textMatches
	 */
	public function onSpecialSearchResults( $term, &$titleMatches, &$textMatches ): void {
		self::$searchTerm = $term;
		self::$searchCount = 0;
		if ( $titleMatches instanceof ISearchResultSet ) {
			self::$searchCount += $titleMatches->numRows();
		}
		if ( $textMatches instanceof ISearchResultSet ) {
			self::$searchCount += $textMatches->numRows();
		}
	}

	/**
	 * Captures the search profile (category) on Special:Search.
	 *
	 * @param SpecialSearch $search
	 * @param string $profile
	 * @param SearchEngine $engine
	 */
	public function onSpecialSearchSetupEngine( $search, $profile, $engine ): void {
		self::$searchCategory = $profile;
	}

	/**
	 * Loads the ext.matomo.tracker ResourceLoader module on every page,
	 * unless the current user belongs to a group listed in
	 * $wgMatomoIgnoreGroups.
	 *
	 * @param OutputPage $out
	 * @param Skin $skin
	 */
	public function onBeforePageDisplay( $out, $skin ): void {
		if ( $this->isUserIgnored( $out->getUser() ) ) {
			return;
		}
		$out->addModules( 'ext.matomo.tracker' );
	}

	/**
	 * Checks whether the given user belongs to a group listed in
	 * $wgMatomoIgnoreGroups and should therefore be excluded from tracking.
	 *
	 * @param UserIdentity $user
	 * @return bool
	 */
	private function isUserIgnored( UserIdentity $user ): bool {
		$ignoredGroups = MediaWikiServices::getInstance()->getMainConfig()->get( 'MatomoIgnoreGroups' );
		if ( !$ignoredGroups ) {
			return false;
		}

		$userGroups = MediaWikiServices::getInstance()->getUserGroupManager()->getUserEffectiveGroups( $user );
		return (bool)array_intersect( $ignoredGroups, $userGroups );
	}

	/**
	 * ResourceLoader packageFiles callback providing tracker config to ext.matomo.tracker.
	 *
	 * @param mixed $context ResourceLoaderContext (MW < 1.39) or MediaWiki\ResourceLoader\Context (MW >= 1.39);
	 *   left untyped since the callback only needs config values, not context methods, so no
	 *   version_compare() guard is required for either MW version boundary in this class
	 * @return array
	 */
	public static function getTrackerConfig( $context ): array {
		$config = MediaWikiServices::getInstance()->getMainConfig();

		return [
			'url' => $config->get( 'MatomoURL' ),
			'idSite' => $config->get( 'MatomoIDSite' ),
			'protocol' => $config->get( 'MatomoProtocol' ),
			'search' => self::getSearchConfig(),
			'customJs' => $config->get( 'MatomoCustomJS' ),
			'disableCookies' => $config->get( 'MatomoDisableCookies' ),
		];
	}

	/**
	 * Returns the site search data captured for the current request, or null
	 * if the current request is not a Special:Search request.
	 *
	 * @return array{term:string,count:int,category:string|null}|null
	 */
	private static function getSearchConfig(): ?array {
		if ( self::$searchTerm === null ) {
			return null;
		}

		return [
			'term' => self::$searchTerm,
			'count' => self::$searchCount ?? 0,
			'category' => self::$searchCategory,
		];
	}
}
