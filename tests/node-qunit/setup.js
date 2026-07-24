const { JSDOM } = require( 'jsdom' );

QUnit.hooks.beforeEach( () => {
	const dom = new JSDOM( '', { url: 'https://wiki.example.org/' } );
	global.window = dom.window;
	global.document = dom.window.document;
	global.location = dom.window.location;
} );
