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
}
