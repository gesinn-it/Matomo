const { resolveBaseUrl, track } = require( '../../resources/ext.matomo.tracker/track.js' );

QUnit.module( 'ext.matomo.tracker/track' );

QUnit.test( 'resolveBaseUrl uses the configured protocol', ( assert ) => {
	assert.strictEqual(
		resolveBaseUrl( { url: 'matomo.example.org', protocol: 'https' } ),
		'https://matomo.example.org'
	);
	assert.strictEqual(
		resolveBaseUrl( { url: 'matomo.example.org', protocol: 'http' } ),
		'http://matomo.example.org'
	);
} );

QUnit.test( 'resolveBaseUrl with protocol "auto" mirrors the page protocol', ( assert ) => {
	global.location = { protocol: 'https:' };
	assert.strictEqual(
		resolveBaseUrl( { url: 'matomo.example.org', protocol: 'auto' } ),
		'https://matomo.example.org'
	);

	global.location = { protocol: 'http:' };
	assert.strictEqual(
		resolveBaseUrl( { url: 'matomo.example.org', protocol: 'auto' } ),
		'http://matomo.example.org'
	);
} );

QUnit.test( 'track does nothing when the Matomo URL is not configured', ( assert ) => {
	track( { url: '', idSite: '3', protocol: 'auto' } );

	assert.strictEqual( window._paq, undefined );
	assert.strictEqual( document.head.querySelector( 'script' ), null );
} );

QUnit.test( 'track does nothing when the site ID is not configured', ( assert ) => {
	track( { url: 'matomo.example.org', idSite: '', protocol: 'auto' } );

	assert.strictEqual( window._paq, undefined );
	assert.strictEqual( document.head.querySelector( 'script' ), null );
} );

QUnit.test( 'track enqueues the _paq commands and appends the tracker script', ( assert ) => {
	track( { url: 'matomo.example.org', idSite: '3', protocol: 'https' } );

	assert.deepEqual( window._paq, [
		[ 'trackPageView' ],
		[ 'enableLinkTracking' ],
		[ 'setTrackerUrl', 'https://matomo.example.org/matomo.php' ],
		[ 'setSiteId', '3' ]
	] );

	const script = document.head.querySelector( 'script' );
	assert.strictEqual( script.src, 'https://matomo.example.org/matomo.js' );
	assert.true( script.async );
} );

QUnit.test( 'track reuses an existing window._paq array', ( assert ) => {
	window._paq = [ [ 'setDocumentTitle', 'custom' ] ];

	track( { url: 'matomo.example.org', idSite: '3', protocol: 'https' } );

	assert.deepEqual( window._paq, [
		[ 'setDocumentTitle', 'custom' ],
		[ 'trackPageView' ],
		[ 'enableLinkTracking' ],
		[ 'setTrackerUrl', 'https://matomo.example.org/matomo.php' ],
		[ 'setSiteId', '3' ]
	] );
} );
