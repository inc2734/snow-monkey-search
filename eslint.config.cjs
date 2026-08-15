const defaultConfig = require( '@wordpress/scripts/config/eslint.config.cjs' );

module.exports = [
	{
		ignores: [
			'**/dist/**',
			'**/.export/**',
			'**/snow-monkey-search/**',
		],
	},
	...defaultConfig,
	{
		settings: {
			'import/core-modules': [
				'@wordpress/api-fetch',
				'@wordpress/block-editor',
				'@wordpress/blocks',
				'@wordpress/components',
				'@wordpress/core-data',
				'@wordpress/data',
				'@wordpress/element',
				'@wordpress/i18n',
				'@wordpress/icons',
				'@wordpress/server-side-render',
			],
			'import/resolver': {
				node: {
					extensions: [
						'.js',
						'.jsx',
						'.ts',
						'.tsx',
						'.d.ts',
						'.mjs',
					],
				},
			},
		},
		rules: {
			'jsx-a11y/label-has-associated-control': 'off',
			'import/no-extraneous-dependencies': 'off',
			'@wordpress/no-unsafe-wp-apis': 'off',
			'no-unused-vars': [
				'error',
				{ caughtErrors: 'none', ignoreRestSiblings: true },
			],
			eqeqeq: [ 'error', 'allow-null' ],
		},
	},
];
