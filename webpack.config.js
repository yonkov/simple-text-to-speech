const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const path = require( 'path' );

module.exports = {
	...defaultConfig,
	entry: {
		editor: path.resolve( process.cwd(), 'src', 'editor.js' ),
	},
};
