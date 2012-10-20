<?php

namespace Minifier\View\Helper;


/**
 * Helper for setting and retrieving bundle scripts elements for HTML head section
 *
 * @author    Wiktor Obrębski
 */
class HeadLink extends \Zend\View\Helper\HeadLink
{
    public function __call($method, $args)
    {
        if (preg_match('/^(?P<action>(ap|pre)pend)Bundle$/', $method, $matches)) {
            $action  = $matches['action'];
            $content = $args[0];
            $this->getView()->prependCssBundle()->__invoke( $content, $action );
            return $this;
        }
        else {
            return parent::__call($method, $args);
        }
    }
}