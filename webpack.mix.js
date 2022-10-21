const mix = require('laravel-mix');

mix.disableSuccessNotifications();
mix.options({
    terser: {
        extractComments: false
    }
});
mix.setPublicPath('dist');
mix.version();

mix.js('resources/js/monet.js', 'dist/js');

mix.postCss('resources/css/monet.css', 'dist/css', [
    require('tailwindcss')
]);
