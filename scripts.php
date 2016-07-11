<?php

return [

    /*
     * Installation hook.
     */

	 
    /*
     * Enable hook
     *
     */
    'enable' => function ($app) {

    },

    /*
     * Uninstall hook
     *
     */
    'uninstall' => function ($app) {

        // remove the config
        $app['config']->remove('search');

    },

    /*
     * Runs all updates that are newer than the current version.
     *
     */

];