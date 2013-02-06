<?php

return array(
    'minifier'    => array(
        'bundles'   => array(
            'js'    => array(
                'output_dir'     => '/js/bundles',
                'list'              => array(
                    'external'  => array(
                        'sources'   => array(
                            '/js/jquery.js',
                            '/js/bootstrap.min.js',
                            '/js/jquery.cookie.js',
                            '/js/jquery.ba-bbq.js',
                        ),
                    ),
                    //files enough for guest users
                    'guest' => array(
                        'sources' => array(
                            '/js/compiled_coffee/core.js',
                            '/js/compiled_coffee/Alcarin/active-view.js',
                            '/js/compiled_coffee/Alcarin/active-list.js',
                            '/js/compiled_coffee/Alcarin/Errors/errors-core.js',
                            '/js/compiled_coffee/Alcarin/game-events-proxy.js',
                            '/js/compiled_coffee/test.js',
                            '/js/compiled_coffee/Alcarin/JQueryPlugins/RESTful.js',
                        ),
                    ),
                    //files enough for admins
                    'admin' => array(
                        'sources' => array(
                            '/js/compiled_coffee/core.js',
                            '/js/compiled_coffee/test.js',
                        ),
                    ),
                    //files for regular players
                    'player' => array(
                        'sources' => array(
                            '/js/compiled_coffee/core.js',
                            '/js/compiled_coffee/test.js',
                        ),
                    ),
                ),
            ),
            'css'    => array(
                'output_dir' => '/css/bundles',
                'list'       => array(
                    'external'  => array(
                        'sources'   => array(
                            '/css/style.css',
                            '/css/bootstrap.min.css',
                            '/css/bootstrap-responsive.min.css',
                        ),
                    ),
                    'guest' => array(
                        'sources' => array(
                            '/css/compiled_less/player-panel.css',
                            '/css/compiled_less/user-bar.css',
                            '/css/compiled_less/middle-nav.css',
                            '/css/compiled_less/admin-index.css',
                        ),
                    ),
                    //files enough for admins
                    'admin' => array(
                        'sources' => '/css/compiled_less/test.css',
                    ),
                    //files for regular players
                    'player' => array(
                        'sources' => '/css/compiled_less/test.css',
                    ),
                ),
            ),
        ),
    ),
);