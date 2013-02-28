<?php

namespace Core\Mvc\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\View\Model\JsonModel;

/**
 * simple plugin to avoid adding Zend\View\Model\JsonModel to use all the times.
 */
class Json extends AbstractPlugin
{
    public function fail($key = 'success')
    {
        return new JsonModel([$key => false]);
    }

    public function success($key = 'success')
    {
        return new JsonModel([$key => true]);
    }


    public function __invoke( $array = null )
    {
        if($array == null) return $this;

        return new JsonModel($array);
    }
}