<?php
    return array(
		/* Configuração do dbAdapter */
		'db' => array(
				'driver'         => 'PDO',
				'dsn'            => 'mysql:dbname=microblog;host=localhost',
				'driver_options' => array(
						PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
				),
		),
		'service_manager' => array(
				'factories' => array(
						'Zend\Db\Adapter\Adapter'
						=> 'Zend\Db\Adapter\AdapterServiceFactory',
						'Zend\Db\Adapter\AdapterInsert'
						=> 'Zend\Db\Adapter\AdapterServiceFactory',
				),
		),
        'view_manager' => array(
        		'base_path' => '/'
        ),      
    );