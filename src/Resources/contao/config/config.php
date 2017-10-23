<?php

/**
 * CSS
 */
$GLOBALS['TL_STYLESHEET_MANAGER_CSS'] = [
    'qz' => [
        'core'    => [
            'files/themes/qz/scss/_variables.scss',
            'files/themes/common/scss/_variables.scss',
            'files/themes/qz/scss/_core.scss',
        ],
        'project' => [
            // Common theme
            'files/themes/common/scss/_common.scss',

            // Core variables and mixins

            // Project mixins

            // Project regions
            'files/themes/qz/scss/regions/_layout.scss',
            'files/themes/qz/scss/regions/_header.scss',
            'files/themes/qz/scss/regions/_stage.scss',
            'files/themes/qz/scss/regions/_page.scss',
            'files/themes/qz/scss/regions/_main.scss',
            'files/themes/qz/scss/regions/_pre-footer.scss',
            'files/themes/qz/scss/regions/_footer.scss',

            // Project components styles
            'files/themes/qz/scss/components/misc/_type.scss',
            'files/themes/qz/scss/components/_nav.scss',

            // Project pages
            'files/themes/qz/scss/pages/_home.scss',
        ],
    ]
];
