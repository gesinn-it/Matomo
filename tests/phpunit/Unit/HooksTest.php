<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\Matomo\Tests\Unit;

use MediaWiki\Extension\Matomo\Hooks;
use MediaWikiIntegrationTestCase;

/**
 * @covers \MediaWiki\Extension\Matomo\Hooks
 */
class HooksTest extends MediaWikiIntegrationTestCase {

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
		] );
		$config = Hooks::getTrackerConfig( null );

		$this->assertSame( [
			'url' => 'matomo.example.org',
			'idSite' => '3',
			'protocol' => 'https',
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

	public function testOnBeforePageDisplayAddsTrackerModule() {
		$hooks = new Hooks();
		$skin = $this->createMock( \Skin::class );
		$out = $this->createMock( \OutputPage::class );
		$out->expects( $this->once() )
			->method( 'addModules' )
			->with( 'ext.matomo.tracker' );

		$hooks->onBeforePageDisplay( $out, $skin );
	}
}
