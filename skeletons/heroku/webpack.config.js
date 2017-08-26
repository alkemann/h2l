var webpack = require('webpack');
var path = require("path");

var DIST_DIR = path.resolve(__dirname, "webroot/js");
var SRC_DIR = path.resolve(__dirname, "frontend");

var config = {
    entry: SRC_DIR + "/index.js",
    output: {
        path: DIST_DIR,
        filename: "bundle.js",
        publicPath: "/js/"
    },
    debug: true,
    module: {
        loaders: [
            {
                test: /\.js?/,
                include: SRC_DIR,
                loader: "babel-loader",
                query: {
                    presets: ["react", "es2015", "stage-2"]
                }
            }
        ]
    },
    plugins: [
      new webpack.ProvidePlugin({
        'fetch': 'imports?this=>global!exports?global.fetch!whatwg-fetch'
      })
    ]
};

module.exports = config;
