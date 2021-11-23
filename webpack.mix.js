const mix = require('laravel-mix');
require('laravel-mix-polyfill');

mix.webpackConfig(webpack => {
    return {
        optimization: {
            sideEffects:false,
        },
        plugins: [
            new webpack.ProvidePlugin({
                $: 'jquery',
                jQuery: 'jquery',
                'window.jQuery': 'jquery'
            })
        ]
    }
})

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app-laravel-table.js', 'resources/assets')
    .postCss('resources/css/laravel-table.css', 'resources/assets', [
        require('postcss-import'),
        require('tailwindcss'),
    ])
    .polyfill({
        enabled: true,
        useBuiltIns: 'usage',
        targets: '> 5%, not dead'
    });

if (mix.inProduction()) {
    mix.version();
}