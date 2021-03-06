const mix = require('laravel-mix');
const webpack = require('webpack');
const path = require('path');
const tailwindcss = require('tailwindcss');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/js')
    .vue({
        version: 3,
        options: {
            compilerOptions: {
                whitespace: 'preserve',
            },
        },
    })
    .sass('resources/sass/app.scss', 'public/css', {
        sassOptions: {
            includePaths: [
                path.resolve(__dirname, './node_modules/bootstrap/scss/')
            ]
        }
    })
    .options({
        postCss: [ tailwindcss('./tailwind.config.js') ],
    })
    .version();