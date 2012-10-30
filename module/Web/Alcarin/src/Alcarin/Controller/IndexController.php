<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Alcarin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Core\Permission\Resource;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        if( $this->isAllowed()->hasAccessToAdminPanels() ) {
            \Zend\Debug\Debug::dump( 'test' );

        }
        return [ 'version' => \Zend\Version\Version::VERSION ];
    }
}
