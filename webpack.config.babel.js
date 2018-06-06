import UglifyJsPlugin from 'uglifyjs-webpack-plugin';
import MiniCssExtractPlugin from 'mini-css-extract-plugin';
import OptimizeCSSAssetsPlugin from 'optimize-css-assets-webpack-plugin';
import path from 'path';

const config = {
    entry: [
        './node_modules/prismjs/plugins/line-numbers/prism-line-numbers.js',
        './node_modules/prismjs/themes/prism-okaidia.css',
        './assets-src/js/accordion.js',
        './assets-src/js/prism.js',
        './assets-src/sass/main.sass'
    ],
    optimization: {
        minimizer: [
            new UglifyJsPlugin({
                cache: true,
                parallel: true,
                sourceMap: true // set to true if you want JS source maps
            }),
            new OptimizeCSSAssetsPlugin({})
        ]
    },
    output: {
        path: path.resolve(__dirname, 'src/Resources/views/js'),
        filename: 'guzzle.min.js'
    },
    mode: 'production',
    plugins: [
        new MiniCssExtractPlugin({
            // Options similar to the same options in webpackOptions.output
            // both options are optional
            filename: '../css/screen.min.css'
        })
    ],
    module: {
        rules: [{
            test: /\.s?[ac]ss$/,
            use: [
                MiniCssExtractPlugin.loader,
                'css-loader',
                'sass-loader'
            ]
        }]
    }
}

export default config;