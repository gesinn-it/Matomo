'use strict';

/**
 * Resolve the Matomo tracker base URL, honouring the configured protocol.
 *
 * 'auto' mirrors the scheme of the current page so mixed-content requests
 * are never sent from an https wiki page.
 *
 * @param {Object} config
 * @return {string}
 */
const resolveBaseUrl = function ( config ) {
	let protocol = config.protocol;
	if ( protocol === 'auto' ) {
		protocol = location.protocol === 'https:' ? 'https' : 'http';
	}
	return protocol + '://' + config.url;
};

/**
 * Build and enqueue the Matomo tracking commands, then load the tracker
 * script (matomo.js) from the configured Matomo instance.
 *
 * Does nothing if the extension has not been configured with a Matomo
 * URL and site ID. Pushes a trackSiteSearch command instead of the
 * default trackPageView when config.search is set (Special:Search).
 * Appends any admin-configured customJs entries after the core commands.
 * Pushes disableCookies before the core commands when config.disableCookies
 * is set, so tracking never sets first-party cookies.
 *
 * @param {Object} config
 * @param {Object|null} [config.search]
 * @param {string} [config.search.term]
 * @param {string|null} [config.search.category]
 * @param {number} [config.search.count]
 * @param {Array[]} [config.customJs]
 * @param {boolean} [config.disableCookies]
 */
const track = function ( config ) {
	if ( !config.url || !config.idSite ) {
		return;
	}

	const baseUrl = resolveBaseUrl( config );
	// eslint-disable-next-line no-underscore-dangle
	const paq = window._paq = window._paq || [];
	if ( config.disableCookies ) {
		paq.push( [ 'disableCookies' ] );
	}
	if ( config.search ) {
		paq.push( [
			'trackSiteSearch',
			config.search.term,
			config.search.category,
			config.search.count
		] );
	} else {
		paq.push( [ 'trackPageView' ] );
	}
	paq.push( [ 'enableLinkTracking' ] );
	paq.push( [ 'setTrackerUrl', baseUrl + '/matomo.php' ] );
	paq.push( [ 'setSiteId', config.idSite ] );
	( config.customJs || [] ).forEach( ( entry ) => paq.push( entry ) );

	const script = document.createElement( 'script' );
	script.async = true;
	script.src = baseUrl + '/matomo.js';
	document.head.appendChild( script );
};

module.exports = {
	resolveBaseUrl: resolveBaseUrl,
	track: track
};
