<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/stubs/BadFacade.php';
require_once __DIR__ . '/stubs/Foo.php';
require_once __DIR__ . '/stubs/FooFacade.php';

class FacadeTest extends TestCase {
  public function testCallingFacadeMethods(): void {
    $this->assertEquals('bar', FooFacade::getBar());
    $this->assertEquals('bar', FooFacade::returnThisVar('bar'));
  }

  /**
   * @dataProvider       badBindingsProvider
   */
  public function testBadBindings($class): void {
    require_once __DIR__ . "/stubs/$class.php";

    $this->expectException(InvalidArgumentException::class);
    $class::test();
  }

  public function badBindingsProvider(): array {
    return [
      [BadFacade::class],
      [InvalidBindingFacade::class],
    ];
  }

  public function testCallingMethodThatDoesntExist(): void {
    $this->expectException(Error::class);
    FooFacade::thisMethodDoesntExist();
  }

  public function testThatInstanceIsNotCached(): void {
    $this->assertNotSame(FooFacade::returnThis(), FooFacade::returnThis());
  }
}
