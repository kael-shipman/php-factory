<?php

require_once __DIR__."/../vendor/autoload.php";

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

class InjectableTestClass implements \KS\FactoryConsumerInterface {
    public $factory;
    public $num;

    public function __construct(int $num=null) {
        $this->num = $num;
    }

    public function setFactory(\KS\FactoryInterface $f) {
        $this->factory = $f;
    }
}







class TestFactory extends \KS\Factory {
  public function getClass(string $type, string $subtype=null) {
    if ($type == 'test') {
      if ($subtype == 'singleton') return 'TestSingleton';
      if ($subtype == 'injectable') return 'InjectableTestClass';
      return 'TestClass';
    }
    return parent::getClass($type, $subtype);
  }
}

class DerivTestFactory extends TestFactory {
  public function getClass(string $type, string $subtype=null) {
    if ($type == 'test') {
      if ($subtype == 'origSingleton') return parent::getClass($type, 'singleton');
      if ($subtype == 'singleton') return 'DerivTestSingleton';
      if ($subtype != 'orig') return 'DerivTestClass';
    }
    return parent::getClass($type, $subtype);
  }
}



