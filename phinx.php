<?php

$config = parse_ini_file(__DIR__ . '/application/settings/config.ini', true);

$production = $config['production'];
$development = $config['development : production'] + $production;
$testing = $config['testing : development'] + $development;

if('pdo_mysql' == $production['resources.db.adapter']) {
    $production['resources.db.adapter'] = 'mysql';
    if(!strcasecmp($production['resources.db.params.driver_options.CharacterSet'], 'utf-8')) {
        $production['resources.db.params.driver_options.CharacterSet'] = 'utf8';
    }
}

if('pdo_mysql' == $development['resources.db.adapter']) {
    $development['resources.db.adapter'] = 'mysql';
    if(!strcasecmp($development['resources.db.params.driver_options.CharacterSet'], 'utf-8')) {
        $development['resources.db.params.driver_options.CharacterSet'] = 'utf8';
    }
}

if('pdo_mysql' == $testing['resources.db.adapter']) {
    $testing['resources.db.adapter'] = 'mysql';
    if(!strcasecmp($testing['resources.db.params.driver_options.CharacterSet'], 'utf-8')) {
        $testing['resources.db.params.driver_options.CharacterSet'] = 'utf8';
    }
}

return
[
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/db/migrations',
        'seeds' => '%%PHINX_CONFIG_DIR%%/db/seeds'
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_database' => 'development',
        'production' => [
            'adapter' => $production['resources.db.adapter'],
            'host' => $production['resources.db.params.host'],
            'name' => $production['resources.db.params.dbname'],
            'user' => $production['resources.db.params.username'],
            'pass' => $production['resources.db.params.password'],
            'port' => $production['resources.db.params.port'],
            'charset' => $production['resources.db.params.driver_options.CharacterSet'],
        ],
        'development' => [
            'adapter' => $development['resources.db.adapter'],
            'host' => $development['resources.db.params.host'],
            'name' => $development['resources.db.params.dbname'],
            'user' => $development['resources.db.params.username'],
            'pass' => $development['resources.db.params.password'],
            'port' => $development['resources.db.params.port'],
            'charset' => $development['resources.db.params.driver_options.CharacterSet'],
        ],
        'testing' => [
            'adapter' => $testing['resources.db.adapter'],
            'host' => $testing['resources.db.params.host'],
            'name' => $testing['resources.db.params.dbname'],
            'user' => $testing['resources.db.params.username'],
            'pass' => $testing['resources.db.params.password'],
            'port' => $testing['resources.db.params.port'],
            'charset' => $testing['resources.db.params.driver_options.CharacterSet'],
        ],
    ],
    'version_order' => 'creation'
];