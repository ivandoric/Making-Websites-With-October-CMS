var elixir = require('laravel-elixir');
require('laravel-elixir-livereload');

elixir.config.assetsPath = 'themes/olympos/assets/';
elixir.config.publicPath = 'themes/olympos/assets/compiled/';

elixir(function(mix){

    mix.sass('style.scss');

    mix.scripts([
        'jquery.js',
        'app.js'
    ]);

    mix.livereload([
        'themes/olympos/assets/compiled/css/style.css',
        'themes/olympos/**/*.htm',
        'themes/olympos/assets/compiled/js/*.js'
    ])

})