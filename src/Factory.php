<?php
namespace KS;

abstract class Factory implements FactoryInterface {
    protected static $instance = array();

    protected function __construct() {
    }

    public static function getInstance() {
        if (!array_key_exists(static::class, static::$instance)) static::$instance[static::class] = new static();
        return static::$instance[static::class];
    }

    public function new(string $class, string $type=null) {
        $args = array();
        for($i = 2; $i < func_num_args(); $i++) $args[] = func_get_arg($i);
        return $this->instantiate($class, $type, 'new', $args);
    }

    public function create(string $class, string $type=null) {
        $args = array();
        for($i = 2; $i < func_num_args(); $i++) $args[] = func_get_arg($i);
        return $this->instantiate($class, $type, 'create', $args);
    }

    public function get(string $class, string $type=null) {
        $c = $this->getClass($class, $type);
        $args = array();
        for($i = 2; $i < func_num_args(); $i++) $args[] = func_get_arg($i);
        if (!method_exists($c, 'getInstance')) throw new \RuntimeException("Class `$c` is not a singleton class and therefore can't be accessed via `get` as a singleton. Singletons must have a method `getInstance`. To make `$c` into a singleton, simply implement the `getInstance` method on it.");
        return call_user_func_array(array($c, 'getInstance'), $args);
    }

    protected function instantiate(string $class, string $type=null, string $action, array $args) {
        $c = $this->getClass($class, $type);

        // Now either instantiate new or static create
        $instance = null;
        if ($action == 'new') {
            $c = new \ReflectionClass($c);
            $instance = $c->newInstanceArgs($args);
        } elseif ($action == 'create') {
            $instance = call_user_func_array(array($c, 'create'), $args);
        } else {
            throw new \RuntimeException("Don't know how to handle action `$action`. I only know how to handle `new` and `create`.");
        }

        // Inject factory, if possible
        if ($instance instanceof \KS\FactoryConsumerInterface) $instance->setFactory($this);

        return $instance;
    }

    public function getClass(string $class, string $subtype=null) {
        throw new UnknownClassException("Don't know how to create classes for type `$class::$subtype`");
    }
}

