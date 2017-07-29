<?php

use PHPUnit\Framework\TestCase;

class FactoryTests extends TestCase {
  public function testNewFactory() {
    $f = TestFactory::getInstance();
    $this->assertEquals('TestClass', $f->getClass('test'));
  }
  
  public function testFactoryCanOptionallyAutoInjectSelfIntoChildren() {
    $f = TestFactory::getInstance();
    $i = $f->new('test', 'injectable');

    $this->assertNull($i->factory, 'Auto-insert of factory should default to "off"');

    $i = $f->new('test', 'injectable', $f);
    $this->assertTrue($i->factory === $f, "Should be able to pass factory explicitly");
    $this->assertNull($i->num, "The second parameter should still be unset");

    $i = $f->new('test', 'injectable', $f, 100);
    $this->assertTrue($i->factory === $f, "Should be able to pass factory explicitly with other params");
    $this->assertEquals(100, $i->num, "The second parameter should be set correctly");

    // Now switch on autoinject
    $f->autoinject = true;

    $i = $f->new('test', 'injectable');
    $this->assertTrue($i->factory === $f, "Factory should be set even when not passed");
    $this->assertNull($i->num, "The second parameter should not be set");

    $i = $f->new('test', 'injectable', 200);
    $this->assertTrue($i->factory === $f, "Factory should be set implicitly");
    $this->assertEquals(200, $i->num, "The second parameter should be set correctly along with factory");

    $i = $f->new('test', 'injectable', $f, 200);
    $this->assertTrue($i->factory === $f, "Factory should be set when passed explicitly, even though autoinject is on");
    $this->assertEquals(200, $i->num, "The second parameter should be set correctly even though double inject of factory");

    // Return to default
    $f->autoinject = false;
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
      $f->new('test');
      $this->fail('Should have thrown an exception');
    } catch(ArgumentCountError $e) {
      $this->assertTrue(true, 'This is the expected behavior');
    }
  }

  public function testThrowsExceptionOnUnknownClass() {
    $f = TestFactory::getInstance();

    try {
      $f->new('invalid');
      $this->fail('Should have thrown an exception');
    } catch(\KS\UnknownClassException $e) {
      $this->assertTrue(true, 'This is the expected behavior');
    }
  }

  public function testCorrectlyInstantiatesClass() {
    $f = TestFactory::getInstance();

    $test = $f->new('test', null, 1,2);
    $this->assertTrue($test instanceof TestClass);
  }

  public function testCorrectlyInstantiatesDerivativeClass() {
    $f = DerivTestFactory::getInstance();

    $test = $f->new('test', null, 1,2);
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

