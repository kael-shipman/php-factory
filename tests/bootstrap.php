<?php

require_once __DIR__."/../vendor/autoload.php";

interface FactoryConsumerInterface {
    function setFactory(TestFactory $f);
}

class TestClass {
  protected $one;
  protected $two;

  public function __construct(int $one, int $two) {
    $this->one = $one;
    $this->two = $two;
  }

  public function getOne() { return $this->one; }
  public function getTwo() { return $this->two; }

  public function setOne($val) {
    $this->one = $val;
    return $this;
  }
  public function setTwo($val) {
    $this->two = $val;
    return $this;
  }
}

class DerivTestClass extends TestClass {
  public static function create() {
    return new static(5,6);
  }
}

class TestSingleton extends TestClass {
  protected static $instance;
  public function __construct(int $one, int $two) {
    if (static::$instance !== null) return static::$instance;
    parent::__construct($one, $two);
    static::$instance = $this;
  }

  public static function getInstance(int $one, int $two) {
    if (static::$instance === null) static::$instance = new static($one, $two);
    return static::$instance;
  }
}

class DerivTestSingleton extends DerivTestClass {
  protected static $instance;
  public function __construct(int $one, int $two) {
    if (static::$instance !== null) return static::$instance;
    parent::__construct($one, $two);
    static::$instance = $this;
  }

  public static function create() {
    if (static::$instance !== null) return static::$instance;
    static::$instance = parent::create();
    return static::$instance;
  }

  public static function getInstance(int $one, int $two) {
    if (static::$instance === null) static::$instance = new static($one, $two);
    return static::$instance;
  }
}

class InjectableTestClass implements FactoryConsumerInterface {
    public $factory;
    public $num;

    public function __construct(int $num=null) {
        $this->num = $num;
    }

    public function setFactory(TestFactory $f) {
        $this->factory = $f;
    }
}







class TestFactory extends \KS\Factory {
    public function newTest() {
        return $this->instantiate('TestClass', func_get_args());
    }
    public function getSingleton() {
        return $this->instantiate('TestSingleton', func_get_args(), 'getInstance');
    }
    public function newInjectableTest() {
        return $this->instantiate('InjectableTestClass', func_get_args());
    }
    public function createBadTest() {
        return $this->instantiate('TestClass', func_get_args(), 'notAMethod');
    }




    protected function injectServices($obj) {
        if ($obj instanceof FactoryConsumerInterface) $obj->setFactory($this);
    }
}

class DerivTestFactory extends TestFactory {
    public function createTest() {
        return $this->instantiate('DerivTestClass', func_get_args(), 'create');
    }
    public function newTest() {
        return $this->instantiate('DerivTestClass', func_get_args());
    }
    public function getSingleton() {
        return $this->instantiate('DerivTestSingleton', func_get_args(), 'getInstance');
    }
    public function newOrigTest() {
        return $this->instantiate('TestClass', func_get_args());
    }
    public function getOrigSingleton() {
        return $this->instantiate('TestSingleton', func_get_args(), 'getInstance');
    }
}



