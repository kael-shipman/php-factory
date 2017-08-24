<?php
namespace KS;

abstract class Factory {
    protected static $instance = array();

    protected function __construct() {
    }

    public static function getInstance() {
        $classname = __NAMESPACE__."\\".get_called_class();
        if (!array_key_exists($classname, static::$instance)) static::$instance[$classname] = new static();
        return static::$instance[$classname];
    }

    protected function instantiate($c, $args, $action='new') {
        $instance = null;
        if ($action == 'new') {
            $c = new \ReflectionClass($c);
            $instance = $c->newInstanceArgs($args);
        } else {
            try {
                $instance = call_user_func_array(array($c, $action), $args);
            } catch (\Exception $e) {
                throw new \RuntimeException("Don't know how to handle action `$action`. You can only pass actions that are static methods on the class you're creating, or `new` to instantiate a new object via its constructor.");
            }
        }

        // Inject self and other services, if applicable
        $this->injectServices($instance);

        return $instance;
    }

    /**
     * Should check for specific `*Consumer` interfaces and automatically inject them, dependencies it controls.
     * For example:
     *
     * ```
     * if ($obj instanceof FactoryConsumerInterface) $obj->setFactory($this);
     * if ($obj instanceof DatasourceConsumerInterface && $this->datasource) $obj->setDatasource($this->datasource);
     * //...
     * ```
     * 
     * @param Object $obj The object into which to inject services
     * @return null
     */
    abstract protected function injectServices($obj);
}

