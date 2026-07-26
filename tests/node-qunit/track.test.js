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

QUnit.test( 'track pushes trackSiteSearch instead of trackPageView when search data is present', ( assert ) => {
	track( {
		url: 'matomo.example.org',
		idSite: '3',
		protocol: 'https',
		search: { term: 'wiki', count: 5, category: null }
	} );

	assert.deepEqual( window._paq, [
		[ 'trackSiteSearch', 'wiki', null, 5 ],
		[ 'enableLinkTracking' ],
		[ 'setTrackerUrl', 'https://matomo.example.org/matomo.php' ],
		[ 'setSiteId', '3' ]
	] );
} );

QUnit.test( 'track passes the search category to trackSiteSearch when present', ( assert ) => {
	track( {
		url: 'matomo.example.org',
		idSite: '3',
		protocol: 'https',
		search: { term: 'wiki', count: 0, category: 'advanced' }
	} );

	assert.deepEqual( window._paq[ 0 ], [ 'trackSiteSearch', 'wiki', 'advanced', 0 ] );
} );

QUnit.test( 'track appends customJs entries to _paq after the core commands', ( assert ) => {
	track( {
		url: 'matomo.example.org',
		idSite: '3',
		protocol: 'https',
		customJs: [
			[ 'setDocumentTitle', 'custom title' ],
			[ 'trackEvent', 'category', 'action' ]
		]
	} );

	assert.deepEqual( window._paq, [
		[ 'trackPageView' ],
		[ 'enableLinkTracking' ],
		[ 'setTrackerUrl', 'https://matomo.example.org/matomo.php' ],
		[ 'setSiteId', '3' ],
		[ 'setDocumentTitle', 'custom title' ],
		[ 'trackEvent', 'category', 'action' ]
	] );
} );

QUnit.test( 'track does not push anything extra when customJs is absent or empty', ( assert ) => {
	track( { url: 'matomo.example.org', idSite: '3', protocol: 'https', customJs: [] } );

	assert.deepEqual( window._paq, [
		[ 'trackPageView' ],
		[ 'enableLinkTracking' ],
		[ 'setTrackerUrl', 'https://matomo.example.org/matomo.php' ],
		[ 'setSiteId', '3' ]
	] );
} );
