<?php

/**
 * Returns the importmap for this application.
 *
 * - "path" is a path inside the asset mapper system. Use the
 *     "debug:asset-map" command to see the full list of paths.
 *
 * - "entrypoint" (JavaScript only) set to true for any module that will
 *     be used as an "entrypoint" (and passed to the importmap() Twig function).
 *
 * The "importmap:require" command can be used to add new entries to this file.
 */
return [
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
    '@symfony/stimulus-bundle' => [
        'path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js',
    ],
    '@symfony/ux-live-component' => [
        'path' => './vendor/symfony/ux-live-component/assets/dist/live_controller.js',
    ],
    'twig' => [
        'version' => '1.17.1',
    ],
    'locutus/php/strings/sprintf' => [
        'version' => '2.0.16',
    ],
    'locutus/php/strings/vsprintf' => [
        'version' => '2.0.16',
    ],
    'locutus/php/math/round' => [
        'version' => '2.0.16',
    ],
    'locutus/php/math/max' => [
        'version' => '2.0.16',
    ],
    'locutus/php/math/min' => [
        'version' => '2.0.16',
    ],
    'locutus/php/strings/strip_tags' => [
        'version' => '2.0.16',
    ],
    'locutus/php/datetime/strtotime' => [
        'version' => '2.0.16',
    ],
    'locutus/php/datetime/date' => [
        'version' => '2.0.16',
    ],
    'locutus/php/var/boolval' => [
        'version' => '2.0.16',
    ],
    'axios' => [
        'version' => '1.6.7',
    ],
    'fos-routing' => [
        'version' => '0.0.6',
    ],
    'datatables.net-plugins/i18n/en-GB.mjs' => [
        'version' => '1.13.6',
    ],
    'datatables.net-bs5' => [
        'version' => '1.13.11',
    ],
    'jquery' => [
        'version' => '3.7.1',
    ],
    'datatables.net' => [
        'version' => '1.13.10',
    ],
    'datatables.net-bs5/css/dataTables.bootstrap5.min.css' => [
        'version' => '1.13.11',
        'type' => 'css',
    ],
    'datatables.net-buttons-bs5' => [
        'version' => '2.4.3',
    ],
    'datatables.net-buttons' => [
        'version' => '2.4.3',
    ],
    'datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css' => [
        'version' => '2.4.3',
        'type' => 'css',
    ],
    'datatables.net-responsive-bs5' => [
        'version' => '2.5.1',
    ],
    'datatables.net-responsive' => [
        'version' => '2.5.1',
    ],
    'datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css' => [
        'version' => '2.5.1',
        'type' => 'css',
    ],
    'datatables.net-scroller-bs5' => [
        'version' => '2.4.0',
    ],
    'datatables.net-scroller' => [
        'version' => '2.4.0',
    ],
    'datatables.net-scroller-bs5/css/scroller.bootstrap5.min.css' => [
        'version' => '2.4.0',
        'type' => 'css',
    ],
    'datatables.net-searchpanes-bs5' => [
        'version' => '2.3.0',
    ],
    'datatables.net-searchpanes' => [
        'version' => '2.3.0',
    ],
    'datatables.net-searchpanes-bs5/css/searchPanes.bootstrap5.min.css' => [
        'version' => '2.3.0',
        'type' => 'css',
    ],
    'datatables.net-select-bs5' => [
        'version' => '1.7.1',
    ],
    'datatables.net-select' => [
        'version' => '1.7.1',
    ],
    'datatables.net-select-bs5/css/select.bootstrap5.min.css' => [
        'version' => '1.7.1',
        'type' => 'css',
    ],
    '@hotwired/stimulus' => [
        'version' => '3.2.2',
    ],
    'bootstrap' => [
        'version' => '5.3.3',
    ],
    '@popperjs/core' => [
        'version' => '2.11.8',
    ],
    'bootstrap/dist/css/bootstrap.min.css' => [
        'version' => '5.3.3',
        'type' => 'css',
    ],
    'perfect-scrollbar' => [
        'version' => '1.5.5',
    ],
    'perfect-scrollbar/css/perfect-scrollbar.min.css' => [
        'version' => '1.5.5',
        'type' => 'css',
    ],
    'chart.js/auto' => [
        'version' => '4.4.1',
    ],
    'datatables.net-searchbuilder-bs5' => [
        'version' => '1.7.0',
    ],
    'datatables.net-searchbuilder' => [
        'version' => '1.7.0',
    ],
    'datatables.net-searchbuilder-bs5/css/searchBuilder.bootstrap5.min.css' => [
        'version' => '1.7.0',
        'type' => 'css',
    ],
    'stimulus-timeago' => [
        'version' => '4.1.0',
    ],
    'date-fns' => [
        'version' => '3.3.1',
    ],
    '@babel/runtime/helpers/esm/typeof' => [
        'version' => '7.24.0',
    ],
    '@babel/runtime/helpers/esm/createForOfIteratorHelper' => [
        'version' => '7.24.0',
    ],
    '@babel/runtime/helpers/esm/assertThisInitialized' => [
        'version' => '7.24.0',
    ],
    '@babel/runtime/helpers/esm/inherits' => [
        'version' => '7.24.0',
    ],
    '@babel/runtime/helpers/esm/createSuper' => [
        'version' => '7.24.0',
    ],
    '@babel/runtime/helpers/esm/classCallCheck' => [
        'version' => '7.24.0',
    ],
    '@babel/runtime/helpers/esm/createClass' => [
        'version' => '7.24.0',
    ],
    '@babel/runtime/helpers/esm/defineProperty' => [
        'version' => '7.24.0',
    ],
    'bootswatch/dist/cerulean/bootstrap.min.css' => [
        'version' => '5.3.2',
        'type' => 'css',
    ],
    '@kurkle/color' => [
        'version' => '0.3.2',
    ],
    'bootstrap-icons/font/bootstrap-icons.min.css' => [
        'version' => '1.11.3',
        'type' => 'css',
    ],
    '@splidejs/splide' => [
        'version' => '4.1.4',
    ],
    '@splidejs/splide/dist/css/splide.min.css' => [
        'version' => '4.1.4',
        'type' => 'css',
    ],
    'htmx.org' => [
        'version' => '1.9.10',
    ],
    'htmx.org/dist/ext/preload.js' => [
        'version' => '1.9.10',
    ],
    'mmenu-light' => [
        'version' => '3.2.2',
    ],
    'mmenu-light/dist/mmenu-light.css' => [
        'version' => '3.2.2',
        'type' => 'css',
    ],
    'stimulus-dropdown' => [
        'version' => '2.1.0',
    ],
    'stimulus-use' => [
        'version' => '0.51.3',
    ],
    'hotkeys-js' => [
        'version' => '3.13.7',
    ],
];
