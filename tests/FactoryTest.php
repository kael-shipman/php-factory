<?php

use PHPUnit\Framework\TestCase;

class FactoryTests extends TestCase {
  public function testNewFactory() {
    $f = TestFactory::getInstance();
    $this->assertEquals('TestClass', $f->getClass('test'));
  }
  
  public function testFactoryCanOptionallyAutoInjectSelfIntoChildren() {
    $f = TestFactory::getInstance();
    $i = $f->neew('test', null, 100, 200);

    $this->assertTrue(true, "Didn't throw an error. Good.");

    $i = $f->neew('test', 'injectable');
    $this->assertTrue($i->factory === $f, "Should be able to pass factory explicitly");
    $this->assertNull($i->num, "The second parameter should still be unset");

    $i = $f->neew('test', 'injectable', 100);
    $this->assertTrue($i->factory === $f, "Should be able to pass factory explicitly with other params");
    $this->assertEquals(100, $i->num, "The second parameter should be set correctly");
  }

  public function testThrowsExceptionOnInvalidStaticCreate() {
    $f = TestFactory::getInstance();

    try {
      $f->create('test');
      $this->fail('Should have thrown an exception');
    } catch(\PHPUnit\Framework\AssertionFailedError $e) {
      throw $e;
    } catch(Exception $e) {
      $this->assertTrue(true, 'This is the expected behavior');
    }
  }

  public function testThrowsExceptionOnInvalidInstatiationParams() {
    $f = TestFactory::getInstance();

    try {
      $f->neew('test');
      $this->fail('Should have thrown an exception');
    } catch(PHPUnit_Framework_Error_Warning $e) {
      $this->assertTrue(true, 'This is the expected behavior');
    }
  }

  public function testThrowsExceptionOnUnknownClass() {
    $f = TestFactory::getInstance();

    try {
      $f->neew('invalid');
      $this->fail('Should have thrown an exception');
    } catch(\KS\UnknownClassException $e) {
      $this->assertTrue(true, 'This is the expected behavior');
    }
  }

  public function testCorrectlyInstantiatesClass() {
    $f = TestFactory::getInstance();

    $test = $f->neew('test', null, 1,2);
    $this->assertTrue($test instanceof TestClass);
  }

  public function testCorrectlyInstantiatesDerivativeClass() {
    $f = DerivTestFactory::getInstance();

    $test = $f->neew('test', null, 1,2);
    $this->assertTrue($test instanceof DerivTestClass);
  }

  public function testCorrectlyUsesStaticMethod() {
    $f = DerivTestFactory::getInstance();

    $test = $f->create('test');
    $this->assertTrue($test instanceof DerivTestClass);
  }

  public function testThrowsErrorOnSingletonGetOfNonSingletonClass() {
    $f = TestFactory::getInstance();
    try {
      $test = $f->get('test', null, 1, 2);
      $this->fail("Should have thrown an exception getting a singleton instance of a nonsingleton class");
    } catch (\RuntimeException $e) {
      $this->assertTrue(true, "This is the correct behavior");
    }
  }

  public function testSingletons() {
    $f = TestFactory::getInstance();
    $test = $f->get('test', 'singleton', 1,2);
    $this->assertEquals(1, $test->getOne());

    $test->setOne(3);
    $this->assertEquals(3, $test->getOne());

    $newTest = $f->get('test', 'singleton', 7,8);
    $this->assertEquals(3, $newTest->getOne());

    $df = DerivTestFactory::getInstance();
    $dTest = $df->get('test', 'singleton', 10, 11);
    $this->assertEquals(10, $dTest->getOne());

    $dNewTest = $df->get('test', 'origSingleton', 12, 13);
    $this->assertEquals(3, $dNewTest->getOne());
  }
}


