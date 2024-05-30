const defaultConfig = require("@wordpress/scripts/config/.eslintrc.js");

module.exports = {
	...defaultConfig,
	settings: {
		...defaultConfig.settings,
		'import/resolver': {
			node: {
				extensions: ['.js', '.jsx', '.ts', '.tsx', '.d.ts', '.mjs'],
			},
		},
	},
	rules: {
		...defaultConfig.rules,
		'jsx-a11y/label-has-associated-control': 'off',
		'import/no-extraneous-dependencies': 'off',
		'@wordpress/no-unsafe-wp-apis': 'off',
		'eqeqeq': ['error', 'allow-null'],
	},
};
