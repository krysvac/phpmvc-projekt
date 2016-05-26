<?php
/**
 * Config-file for Anax, theme related settings, return it all as array.
 *
 */

return [

    /**
     * Settings for Which theme to use, theme directory is found by path and name.
     *
     * path: where is the base path to the theme directory, end with a slash.
     * name: name of the theme is mapped to a directory right below the path.
     */
    'settings' => [
        'path' => ANAX_INSTALL_PATH . 'theme/',
        'name' => 'anax-grid',
    ],

    /**
     * Add default views.
     */
    'views' => [
        [
            'region' => 'header', 
            'template' => 'me/header', 
            'data' => [
                'siteTitle' => "Allt om spel, ett forum för spelare",
                'siteTagline' => "Have you ever had a dreams, that, that you um, you had, you'd, you would, you could, you'd do you will, you want, you, you could, do so you you'd, do you could, you, you wanted, you want him to do you so much you could do anything?",
            ],
            'sort' => -1
        ],
        [
            'region' => 'footer', 
            'template' => 'me/footer', 
            'data' => [], 
            'sort' => -1
        ],
        [
            'region' => 'navbar', 
            'template' => [
                'callback' => function () {
                    return $this->di->navbar->create();
                },
            ], 
            'data' => [], 
            'sort' => -1
        ],
    ],


    /**
     * Data to extract and send as variables to the main template file.
     */
    'data' => [

        // Language for this page.
        'lang' => 'sv',

        //Title
        'title' => "Tartarus",

        // Append this value to each <title>
        'title_append' => ' | En sån där mejmej sida',

        // Stylesheets
        'stylesheets' => ['css/anax-grid/style.php'],

        // Inline style
        'style' => null,

        // Favicon
        'favicon' => 'img/favicon.png',

        // Path to modernizr or null to disable
        'modernizr' => 'js/modernizr.js',

        // Path to jquery or null to disable
        'jquery' => '//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js',

        // Array with javscript-files to include
        'javascript_include' => [],

        // Use google analytics for tracking, set key or null to disable
        'google_analytics' => null,
    ],
];
