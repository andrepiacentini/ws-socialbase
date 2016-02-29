<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Microblog\Controller\Microblog' => 'Microblog\Controller\MicroblogController',
            
        ),
    ),

    // The following section is new and should be added to your file
    'router' => array(
        'routes' => array(
            'microblog' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/Microblog[/][:action][/:id]',	// Indica que a chamada de ação será direto na raiz da URL
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[a-zA-Z0-9_-]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Microblog\Controller\Microblog',
                        'action'     => 'index',
                    ),
                ),
            ),
        ),
    ),
    
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'template_map' => array(
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),


    'translator' => array(
        'locale' => 'pt_BR',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),
);