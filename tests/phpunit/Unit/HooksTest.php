<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\Matomo\Tests\Unit;

use MediaWiki\Extension\Matomo\Hooks;
use MediaWikiIntegrationTestCase;

/**
 * @covers \MediaWiki\Extension\Matomo\Hooks
 */
class HooksTest extends MediaWikiIntegrationTestCase {

	protected function tearDown(): void {
		Hooks::resetSearchState();
		parent::tearDown();
	}

	public function testOnSkinAfterBottomScriptsDoesNotThrow() {
		$hooks = new Hooks();
		$skin = $this->createMock( \Skin::class );
		$text = '';

		$hooks->onSkinAfterBottomScripts( $skin, $text );

		$this->assertSame( '', $text );
	}

	public function testGetTrackerConfigReturnsConfiguredValues() {
		$this->setMwGlobals( [
			'wgMatomoURL' => 'matomo.example.org',
			'wgMatomoIDSite' => '3',
			'wgMatomoProtocol' => 'https',
			'wgMatomoDisableCookies' => true,
		] );
		$config = Hooks::getTrackerConfig( null );

		$this->assertSame( [
			'url' => 'matomo.example.org',
			'idSite' => '3',
			'protocol' => 'https',
			'search' => null,
			'customJs' => [],
			'disableCookies' => true,
		], $config );
	}

	public function testGetTrackerConfigDefaultsProtocolToAuto() {
		$this->setMwGlobals( [
			'wgMatomoURL' => 'matomo.example.org',
			'wgMatomoIDSite' => '3',
			'wgMatomoProtocol' => 'auto',
		] );
		$config = Hooks::getTrackerConfig( null );

		$this->assertSame( 'auto', $config['protocol'] );
	}

	public function testGetTrackerConfigDefaultsCustomJsToEmptyArray() {
		$config = Hooks::getTrackerConfig( null );

		$this->assertSame( [], $config['customJs'] );
	}

	public function testGetTrackerConfigReturnsConfiguredCustomJs() {
		$customJs = [
			[ 'setDocumentTitle', 'custom title' ],
			[ 'trackEvent', 'category', 'action' ],
		];
		$this->setMwGlobals( 'wgMatomoCustomJS', $customJs );

		$config = Hooks::getTrackerConfig( null );

		$this->assertSame( $customJs, $config['customJs'] );
	}

	public function testGetTrackerConfigDefaultsDisableCookiesToTrue() {
		$config = Hooks::getTrackerConfig( null );

		$this->assertTrue( $config['disableCookies'] );
	}

	public function testGetTrackerConfigReturnsConfiguredDisableCookies() {
		$this->setMwGlobals( 'wgMatomoDisableCookies', false );

		$config = Hooks::getTrackerConfig( null );

		$this->assertFalse( $config['disableCookies'] );
	}

	public function testOnBeforePageDisplayAddsTrackerModule() {
		$this->setMwGlobals( 'wgMatomoIgnoreGroups', [ 'bot', 'sysop' ] );
		$this->setService( 'UserGroupManager', $this->newUserGroupManagerReturning( [] ) );

		$hooks = new Hooks();
		$skin = $this->createMock( \Skin::class );
		$out = $this->createMock( \OutputPage::class );
		$out->method( 'getUser' )->willReturn( $this->createMock( \MediaWiki\User\UserIdentity::class ) );
		$out->expects( $this->once() )
			->method( 'addModules' )
			->with( 'ext.matomo.tracker' );

		$hooks->onBeforePageDisplay( $out, $skin );
	}

	public function testOnBeforePageDisplaySkipsTrackerModuleForIgnoredGroup() {
		$this->setMwGlobals( 'wgMatomoIgnoreGroups', [ 'bot', 'sysop' ] );
		$this->setService( 'UserGroupManager', $this->newUserGroupManagerReturning( [ 'bot' ] ) );

		$hooks = new Hooks();
		$skin = $this->createMock( \Skin::class );
		$out = $this->createMock( \OutputPage::class );
		$out->method( 'getUser' )->willReturn( $this->createMock( \MediaWiki\User\UserIdentity::class ) );
		$out->expects( $this->never() )->method( 'addModules' );

		$hooks->onBeforePageDisplay( $out, $skin );
	}

	public function testOnBeforePageDisplayAddsTrackerModuleWhenIgnoreGroupsIsEmpty() {
		$this->setMwGlobals( 'wgMatomoIgnoreGroups', [] );
		$this->setService( 'UserGroupManager', $this->newUserGroupManagerReturning( [ 'bot' ] ) );

		$hooks = new Hooks();
		$skin = $this->createMock( \Skin::class );
		$out = $this->createMock( \OutputPage::class );
		$out->method( 'getUser' )->willReturn( $this->createMock( \MediaWiki\User\UserIdentity::class ) );
		$out->expects( $this->once() )
			->method( 'addModules' )
			->with( 'ext.matomo.tracker' );

		$hooks->onBeforePageDisplay( $out, $skin );
	}

	/**
	 * @param string[] $effectiveGroups
	 * @return \MediaWiki\User\UserGroupManager
	 */
	private function newUserGroupManagerReturning( array $effectiveGroups ) {
		$userGroupManager = $this->createMock( \MediaWiki\User\UserGroupManager::class );
		$userGroupManager->method( 'getUserEffectiveGroups' )->willReturn( $effectiveGroups );
		return $userGroupManager;
	}

	public function testOnSpecialSearchResultsCountsTitleAndTextMatches() {
		$hooks = new Hooks();
		$titleMatches = $this->createMock( \ISearchResultSet::class );
		$titleMatches->method( 'numRows' )->willReturn( 2 );
		$textMatches = $this->createMock( \ISearchResultSet::class );
		$textMatches->method( 'numRows' )->willReturn( 3 );

		$hooks->onSpecialSearchResults( 'wiki', $titleMatches, $textMatches );

		$config = Hooks::getTrackerConfig( null );
		$this->assertSame( [
			'term' => 'wiki',
			'count' => 5,
			'category' => null,
		], $config['search'] );
	}

	public function testOnSpecialSearchResultsHandlesNullResultSets() {
		$hooks = new Hooks();
		$titleMatches = null;
		$textMatches = null;

		$hooks->onSpecialSearchResults( 'wiki', $titleMatches, $textMatches );

		$config = Hooks::getTrackerConfig( null );
		$this->assertSame( [
			'term' => 'wiki',
			'count' => 0,
			'category' => null,
		], $config['search'] );
	}

	public function testOnSpecialSearchSetupEngineStoresProfileAsCategory() {
		$hooks = new Hooks();
		$search = $this->createMock( \SpecialSearch::class );
		$engine = $this->createMock( \SearchEngine::class );
		$titleMatches = null;
		$textMatches = null;

		$hooks->onSpecialSearchResults( 'wiki', $titleMatches, $textMatches );
		$hooks->onSpecialSearchSetupEngine( $search, 'advanced', $engine );

		$config = Hooks::getTrackerConfig( null );
		$this->assertSame( 'advanced', $config['search']['category'] );
	}

	public function testGetTrackerConfigSearchIsNullWithoutASearch() {
		$config = Hooks::getTrackerConfig( null );

		$this->assertNull( $config['search'] );
	}
}
