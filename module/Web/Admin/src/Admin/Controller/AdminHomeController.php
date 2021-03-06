<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonModule for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin\Controller;

use Core\Controller\AbstractAlcarinRestfulController;
use Zend\Mvc\Router\RouteMatch;

class AdminHomeController extends AbstractAlcarinRestfulController
{
    protected $admin_pages = [
        [
            'controller' => 'users',
            'title'      => 'Manage players',
            'icon'       => 'icon-search',
        ],
        [
            'controller' => 'orbis',
            'namespace'  => 'Admin\Controller\Orbis',
            'title'      => '"Orbis" Editor',
            'icon'       => 'icon-globe',
        ],
        [
            'controller' => 'modules',
            'title'      => 'Manage modules',
            'icon'       => 'icon-search',
        ]
    ];

    public function getList()
    {
        $result = [];
        $authService = $this->isAllowed();
        foreach( $this->admin_pages as $data ) {
            $controller = $data['controller'];
            $namespace = empty($data['namespace']) ? 'Admin\\Controller' : $data['namespace'];
            $full_controller = $namespace . '\\' . ucfirst($controller);

            if( $authService->isAllowedToController($full_controller) || true ) {
                $result[$controller] = $this->pageData( $data );
            }
        }
        return [ 'pages' => $result ];
    }

    private function pageData( $data ) {
        return [
            'href' => $this->url()->fromRoute( 'admin/default', [ 'controller' => $data['controller'] ] ),
            'icon' => empty( $data['icon'] ) ? null : $data['icon'],
            'title'  => empty( $data['title'] ) ? null : $data['title'],
        ];
    }
}
