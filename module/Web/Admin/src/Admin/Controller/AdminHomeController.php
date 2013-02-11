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

class AdminHomeController extends AbstractAlcarinRestfulController
{
    protected $admin_pages = [
        [
            'name' => 'users',
            'icon' => 'http://img.weather.weatherbug.com/forecast/icons/localized/500x420/en/trans/cond000.png',
            'alt'  => 'Manage users.',
        ],
    ];

    public function getList()
    {
        throw new \Exception('test');
        $result = [];
        $authService = $this->isAllowed();
        foreach( $this->admin_pages as $data ) {
            $page = $data['name'];
            if( $authService->isAllowedToController( $page ) ) {
                $router = $this->getServiceLocator()->get('router');
                $url =$router->assemble( ['controller' => $page], ['name' => 'admin']);

                $result[$page] = $this->pageData( $data );
            }
        }
        return [ 'pages' => $result ];
    }

    private function pageData( $data ) {
        return [
            'href' => $this->url()->fromRoute( 'admin', [ 'controller' => $data['name'] ] ),
            'icon' => empty( $data['icon'] ) ? null : $data['icon'],
            'alt'  => empty( $data['alt'] ) ? null : $data['alt'],
        ];
    }
}
