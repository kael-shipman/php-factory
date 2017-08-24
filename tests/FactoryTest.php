<?php

use PHPUnit\Framework\TestCase;

class FactoryTests extends TestCase {
  public function testFactoryCanOptionallyAutoInjectSelfIntoChildren() {
    $f = TestFactory::getInstance();
    $i = $f->newTest(100, 200);

    $this->assertTrue(true, "Didn't throw an error. Good.");

    $i = $f->newInjectableTest();
    $this->assertTrue($i->factory === $f, "Factory should be passed implicitly");
    $this->assertNull($i->num, "The second parameter should still be unset");

    $i = $f->newInjectableTest(100);
    $this->assertTrue($i->factory === $f, "Factory should be passed implicitly with other params");
    $this->assertEquals(100, $i->num, "The second parameter should be set correctly");
  }

  public function testThrowsExceptionOnInvalidInstatiationParams() {
    $f = TestFactory::getInstance();

    try {
      $f->newTest();
      $this->fail('Should have thrown an exception');
    } catch(ArgumentCountError $e) {
      $this->assertTrue(true, 'This is the expected behavior');
    }
  }

  public function testCorrectlyInstantiatesClass() {
    $f = TestFactory::getInstance();

    $test = $f->newTest(1,2);
    $this->assertTrue($test instanceof TestClass);
  }

  public function testCorrectlyInstantiatesDerivativeClass() {
    $f = DerivTestFactory::getInstance();

    $test = $f->newTest(1,2);
    $this->assertTrue($test instanceof DerivTestClass);
  }

  public function testThrowsErrorOnNonexistentStaticMethod() {
    $f = TestFactory::getInstance();

    try {
        $test = $f->createBadTest();
        $this->fail("Should have thrown an exception here");
    } catch (RuntimeException $e) {
        $this->assertTrue(true, 'This is the expected behavoir');
    }
  }

  public function testCorrectlyUsesStaticMethod() {
    $f = DerivTestFactory::getInstance();

    $test = $f->createTest();
    $this->assertTrue($test instanceof DerivTestClass);
  }

  public function testSingletons() {
    $f = TestFactory::getInstance();
    $test = $f->getSingleton(1,2);
    $this->assertEquals(1, $test->getOne());

    $test->setOne(3);
    $this->assertEquals(3, $test->getOne());

    $newTest = $f->getSingleton(7,8);
    $this->assertEquals(3, $newTest->getOne());

    $df = DerivTestFactory::getInstance();
    $dTest = $df->getSingleton(10, 11);
    $this->assertEquals(10, $dTest->getOne());

    $dNewTest = $df->getOrigSingleton(12, 13);
    $this->assertEquals(3, $dNewTest->getOne());
  }
}

