const path = require('path')
const webpackConfig = require('@nextcloud/webpack-vue-config')

webpackConfig.entry = {
	'admin-settings': {
		import: path.resolve(__dirname, 'src/admin-settings.js'),
		filename: 'linkvertise-admin-settings.js',
	},
	'files-action': {
		import: path.resolve(__dirname, 'src/files-action.js'),
		filename: 'linkvertise-files-action.js',
	},
}

module.exports = webpackConfig
