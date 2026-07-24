/*
 * ResourceLoader entry point: wires the tracker config supplied by
 * MediaWiki\Extension\Matomo\Hooks::getTrackerConfig (via config.json)
 * into the tracking logic in track.js.
 */
const config = require( './config.json' );
const track = require( './track.js' ).track;

track( config );
