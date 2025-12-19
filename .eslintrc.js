module.exports = {
	root: true,
	parser: 'vue-eslint-parser',
	parserOptions: {
        parser: '@typescript-eslint/parser',
		ecmaFeatures: {
			jsx: true, // Allows for the parsing of JSX
		},
        extraFileExtensions: ['.vue'],
	},
	plugins: [
		'vue',
		'@typescript-eslint',
	],
	settings: {
		react: {
			version: 'detect', // Tells eslint-plugin-react to automatically detect the version of React to use
		},
	},
	extends: [
        'plugin:vue/recommended',
		'plugin:react/recommended',
		'plugin:@typescript-eslint/eslint-recommended',
		'plugin:@typescript-eslint/recommended',
	],
	rules: {
		'@typescript-eslint/explicit-function-return-type': 'off',
		'@typescript-eslint/no-use-before-define': 'off',
		'@typescript-eslint/no-explicit-any': 'off',
		'react/prop-types': 'off',
		quotes: ['error', 'single'],
		'comma-dangle': ['error', 'always-multiline'],
		'array-bracket-newline': ['error', 'consistent'],
		'quote-props': ['error', 'as-needed'],
		indent: ['warn', 'tab'],
		semi: ['error', 'always'],
		'@typescript-eslint/ban-types': 'off',
		'vue/script-setup-uses-vars': 'error',
		'vue/html-indent': ['warn', 'tab', {
			attribute: 1,
			baseIndent: 1,
			closeBracket: 0,
			alignAttributesVertically: true,
			ignores: []
		}],
		'vue/first-attribute-linebreak': 'off',
		'vue/max-attributes-per-line': ['warn', {
			singleline: 5,
			multiline: 1
		}]
	},
}
