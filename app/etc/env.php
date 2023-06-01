<?php
return [
    'backend' => [
        'frontName' => 'admin_17chqr'
    ],
    'crypt' => [
        'key' => 'd0e00edd530eb407f2890ea852b1b0da
ff1cb1b1812723edeeabb99cfb216ab3
ba9230287d69f1bd98076e4f4c95d059'
    ],
    'db' => [
        'table_prefix' => '',
        'connection' => [
            'default' => [
                'host' => 'localhost',
                'dbname' => 'durovinnew',
                'username' => 'durovin',
                'password' => 'Durovin!@3698',
                'model' => 'mysql4',
                'engine' => 'innodb',
                'initStatements' => 'SET NAMES utf8;',
                'active' => '1',
                'driver_options' => [
                    1014 => false
                ]
            ]
        ]
    ],
    'resource' => [
        'default_setup' => [
            'connection' => 'default'
        ]
    ],
    'x-frame-options' => 'SAMEORIGIN',
    'MAGE_MODE' => 'production',
    'session' => [
        'save' => 'files'
    ],
    'cache' => [
        'frontend' => [
            'default' => [
                'id_prefix' => 'a12_'
            ],
            'page_cache' => [
                'id_prefix' => 'a12_'
            ]
        ],
        'allow_parallel_generation' => false,
        'graphql' => [
            'id_salt' => '6qln2Gt73hUERdC2H9PRZ4fcO7NisDVX'
        ]
    ],
    'install' => [
        'date' => 'Wed, 16 Jan 2019 16:04:19 +0000'
    ],
    'system' => [
        'default' => [
            'dev' => [
                'debug' => [
                    'debug_logging' => '0'
                ],
                'front_end_development_workflow' => [
                    'type' => 'server_side_compilation'
                ]
            ],
            'smile_elasticsuite_core_base_settings' => [
                'es_client' => [
                    'servers' => 'localhost:9200',
                    'enable_https_mode' => '0',
                    'enable_http_auth' => false,
                    'http_auth_user' => '',
                    'http_auth_pwd' => ''
                ]
            ]
        ]
    ],
    'downloadable_domains' => [
        'www.durovinnew.com'
    ],
    'queue' => [
        'consumers_wait_for_messages' => 1
    ],
    'http_cache_hosts' => [
        [
            'host' => '127.0.0.1',
            'port' => '80'
        ]
    ],
    'lock' => [
        'provider' => 'db'
    ],
    'remote_storage' => [
        'driver' => 'file'
    ],
    'directories' => [
        'document_root_is_pub' => true
    ],
    'cache_types' => [
        'compiled_config' => 1,
        'config' => 1,
        'layout' => 1,
        'block_html' => 1,
        'collections' => 1,
        'reflection' => 1,
        'db_ddl' => 1,
        'eav' => 1,
        'customer_notification' => 1,
        'config_integration' => 1,
        'config_integration_api' => 1,
        'full_page' => 1,
        'config_webservice' => 1,
        'translate' => 1
    ]
];
