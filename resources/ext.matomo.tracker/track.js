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
 * URL and site ID.
 *
 * @param {Object} config
 */
const track = function ( config ) {
	if ( !config.url || !config.idSite ) {
		return;
	}

	const baseUrl = resolveBaseUrl( config );
	// eslint-disable-next-line no-underscore-dangle
	const paq = window._paq = window._paq || [];
	paq.push( [ 'trackPageView' ] );
	paq.push( [ 'enableLinkTracking' ] );
	paq.push( [ 'setTrackerUrl', baseUrl + '/matomo.php' ] );
	paq.push( [ 'setSiteId', config.idSite ] );

	const script = document.createElement( 'script' );
	script.async = true;
	script.src = baseUrl + '/matomo.js';
	document.head.appendChild( script );
};

module.exports = {
	resolveBaseUrl: resolveBaseUrl,
	track: track
};
