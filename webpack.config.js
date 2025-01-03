const Encore = require('@symfony/webpack-encore');

Encore
    // Le répertoire où Webpack va placer les fichiers compilés
    .setOutputPath('public/build/')
    .setPublicPath('/build')

    // Point d'entrée pour les fichiers JavaScript
    .addEntry('app', './assets/js/app.js')

    // Pour le CSS (optionnel, si tu utilises Sass ou CSS)
    .enableSassLoader()
    .enablePostCssLoader()

    // Active les versions des fichiers pour le cache
    .enableVersioning()

;

module.exports = Encore.getWebpackConfig();
