<?php

namespace Core;

use Core\Service\GameServiceAwareInterface;
use Core\Service\GameServiceAwareTrait;

class GameObject implements GameServiceAwareInterface
{
    use GameServiceAwareTrait;

    protected $parent;
    protected $extManager;
    protected $plugins   = [];

    public function __construct($parent = null)
    {
        $this->parent = $parent;
    }

    public function parent()
    {
        return $this->parent;
    }

    protected function getExtManager()
    {
        if( $this->extManager == null ) {
            $this->extManager = $this->getServicesContainer()->get('ext-manager');
        }
        return $this->extManager;
    }

    /**
     * checking than this object has plugin with specific name
     */
    public function has($plugin_name)
    {
        if( isset($this->plugins[$plugin_name]) ) return true;
        return $this->getExtManager()->hasFactory($this, $plugin_name);
    }

    public function __call($plugin_name, $args)
    {
        $plugin = null;
        if( !isset($this->plugins[$plugin_name]) ) {
            $factory = $this->getExtManager()->getFactory($this, $plugin_name);
            if(is_string($factory)) {
                $plugin = new $factory($this);
            }
            else {
                $plugin = $factory($this->getServicesContainer(), $this);
            }

            if( $plugin instanceof GameServiceAwareInterface ) {
                $plugin->setServicesContainer($this->getServicesContainer());
            }
            $this->plugins[$plugin_name] = $plugin;
        }
        else {
            $plugin = $this->plugins[$plugin_name];
        }

        return $plugin;
    }

}