// const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const OptimizeCssAssetsPlugin = require('optimize-css-assets-webpack-plugin');
const incstr = require('incstr');
// const nextId = incstr.idGenerator({alphabet:'abcefghijklmnopqrstuvwxyz0123456789', prefix:'l', suffix:''});

var config = {
    mode: 'production',
    plugins: [
        new MiniCssExtractPlugin({
            // Options similar to the same options in webpackOptions.output
            // both options are optional
            // filename: '[name].[hash].css',
            filename: 'liliom.css',
            chunkFilename: '[id].[hash].css',
        }),
        new OptimizeCssAssetsPlugin()
    ],

    module: {
        rules: [{
            test: /\.(sa|sc|c)ss$/,
            use: [
                MiniCssExtractPlugin.loader,
                // "style-loader", // creates style nodes from JS strings
                // "css-loader", // translates CSS into CommonJS
                {
                    loader: "css-loader",
                    options: {
                        // modules: true,
                        // getLocalIdent: (context, localIdentName, localName, options) => {
                        //     return nextId();
                        // }
                    }
                },
                "sass-loader" // compiles Sass to CSS, using Node Sass by default
            ]
        }, {
            test: /\.(png|jpg|gif)$/,
            loader: 'file-loader',
            options: {
                name: 'img/[name].[ext]',
            },
        }]
    }
};

module.exports = (env, argv) => {
    if (argv.mode === 'development') {
        config.mode = argv.mode;
        config.watch = true;
    }

    return config;
};