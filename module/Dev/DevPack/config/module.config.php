<?php
return array(
    'logs'  => array(
        //list of services, that will be used as
        //logs writers
        'writers'    => array()
    ),

    'nette_debug' => array(
        'template_map' => array(       // merge templates if enabled
            'error/index' => __DIR__ . '/../view/error/index.phtml',
        ),
    ),

    'view_manager' => array(
        'template_path_stack' => array(
            'dev-pack' => __DIR__ . '/../view',
        ),
    ),

    'mongo' => array(
        'collection'    => 'DevPack\MongoCollection'
    ),

    'mongo_profiler' => array(
        'class'   => 'DevPack\Profiler',
        'options' => array(
        ),
    ),

    'controller_plugins' => array(
        'invokables' => array(
            'log'   => 'DevPack\Mvc\Controller\Plugin\Logger',
        ),
    ),

    'service_manager' => array(
        'invokables' => array(
            'mongo-log-writer'        => 'DevPack\Log\Writer\MongoWriter',
            'DevPack\MongoCollector'  => 'DevPack\Collector\MongoCollector',
            'DevPack\GameTimeCollector'  => 'DevPack\Collector\GameTimeCollector',
            'DevPack\MongoCollection' => 'DevPack\MongoCollection',
        ),
        'factories' => array(
            'system-logger' => 'DevPack\Factory\SystemLoggerFactory',
        )
    ),

    'controllers' => array(
        'invokables' => array(
            'DevPack\DevConsoleController' => 'DevPack\Controller\DevConsoleController',
        ),
    ),

    'console' => array(
        'router' => array(
            'routes' => array(
                'create-su' => array(
                    'options' => array(
                        'route'    => 'create su <suemail> <supass>',
                        'defaults' => array(
                            'controller' => 'DevPack\DevConsoleController',
                            'action'     => 'createSu',
                        ),
                    ),
                ),
                'init-db' => array(
                    'options' => array(
                        'route'    => 'init db',
                        'defaults' => array(
                            'controller' => 'DevPack\DevConsoleController',
                            'action'     => 'prepareDatabase',
                        ),
                    ),
                ),
            ),
        ),
    ),
);
