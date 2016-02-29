<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
		'router' => array(
				'routes' => array(
				    'home' => array(
						    'type' => 'Module\Router\Content',
								'type' => 'segment',
								'options' => array(
										'route'    => '/[:action][/:id]',
										'defaults' => array(
												'controller' => 'Application\Controller\Index',
												'action'     => 'index',
										),
								),
						),
				),
		),
        'service_manager' => array(
        'factories' => array(
            'translator' => 'Zend\I18n\Translator\TranslatorServiceFactory',
        ),
    ),
    'translator' => array(
        'locale' => 'en_US',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Application\Controller\Index' => 'Application\Controller\IndexController',
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'layout/main-template'    => __DIR__ . '/../view/layout/main-template.phtml',
            'layout/no-menu'          => __DIR__ . '/../view/layout/main-template-no-menu.phtml',
            'layout/no-session'       => __DIR__ . '/../view/layout/main-template-no-session.phtml',
            'app/index' => __DIR__ . '/../view/application/index/index.phtml',
            'app/facebook' => __DIR__ . '/../view/application/index/login_fb.phtml',
            'app/sim' => __DIR__ . '/../view/application/index/sim.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
            'main/busca'                => __DIR__ . '/../view/application/index/busca.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
);
