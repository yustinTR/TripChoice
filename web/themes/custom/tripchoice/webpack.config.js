const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const StylelintPlugin = require('stylelint-webpack-plugin');
const RemoveEmptyScriptsPlugin = require('webpack-remove-empty-scripts');

const isProduction = process.env.NODE_ENV == 'production';
const stylesHandler = MiniCssExtractPlugin.loader;

const config = {
    entry: {
      'main'                              : ['./src/js/main.js', './src/scss/main.scss'],
      'libraries/fontawesome/fontawesome' : ['./src/scss/libraries/fontawesome.scss'],
      'ckeditor/ckeditor'                 : ['./src/scss/libraries/ckeditor.scss'],
      'components/button/button'          : ['./components/button/button.js', './components/button/button.scss'],
      'components/image/image'            : ['./components/image/image.scss'],
      'components/title/title'            : ['./components/title/title.scss'],
      'components/teaser/teaser'          : ['./components/teaser/teaser.scss'],
      'components/header/header'          : ['./components/header/header.scss'],
      'js/hamburger'                      : ["./src/js/hamburger.js"],
      'js/student_qoute'                  : ['./src/js/student_qoute.js'],
    },
    output: {
      path: path.resolve(__dirname, 'dist'),
      clean: true,
    },
    plugins: [
        new RemoveEmptyScriptsPlugin(),
        new MiniCssExtractPlugin(),
        new StylelintPlugin({
          fix: true,
        }),
    ],
    module: {
        rules: [
            {
                test: /\.(js)$/i,
                loader: 'babel-loader',
            },
            {
                test: /\.(s(a|c)ss)$/,
                use: [stylesHandler, 'css-loader', 'postcss-loader', 'sass-loader'],

            },
            {
                test: /\.(woff(2)?|ttf|eot|svg)(\?v=\d+\.\d+\.\d+)?$/,
                type: 'asset/resource',
                generator: {
                  filename: './fonts/[name][ext]',
                },
            },
        ],
    },
};

module.exports = () => {
    if (isProduction) {
        config.mode = 'production';
        config.performance = false;
    } else {
        config.mode = 'development';
        config.devtool = "source-map";
    }
    return config;
};
