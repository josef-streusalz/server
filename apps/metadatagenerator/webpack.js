const path = require('path');
const webpackConfig = require('@nextcloud/webpack-vue-config');
webpackConfig.entry = {
    main: { import: path.join(__dirname, 'src', 'main.js'), filename: 'metadatagenerator-main.js' },
};
module.exports = webpackConfig;
