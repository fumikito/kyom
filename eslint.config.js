/**
 * ESLint flat config.
 *
 * @wordpress/scripts 32（ESLint 9）から .eslintrc / .eslintignore は
 * 読まれなくなったため、従来の .eslintrc の内容をこちらへ移行している。
 * ignores も flat config 側で持つ必要がある。
 */
const globals = require( 'globals' );
const wordpress = require( '@wordpress/eslint-plugin' );

module.exports = [
	{
		ignores: [
			'assets/**',
			'build/**',
			'node_modules/**',
			'vendor/**',
			'wordpress/**',
		],
	},
	...wordpress.configs[ 'recommended-with-formatting' ],
	{
		languageOptions: {
			globals: {
				...globals.browser,
				...globals.jquery,
				UIkit: 'readonly',
				Netabare: 'readonly',
				wp: 'readonly',
			},
		},
	},
];
