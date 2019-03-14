<?php

use Maquina\StateMachine\Machine;

class StateMachineTest extends \PHPUnit\Framework\TestCase {

  /**
   * @var \Maquina\Machine
   */
  private $stateMachine;

  public function setUp() {
    parent::setUp();

    $machine = new Machine("Locked");
    $machine->addTransition("Locked", ["Push"], "Locked");
    $machine->addTransition("Locked", ["Coin"], "Un-locked");
    $machine->addTransition("Un-locked", ["Coin"], "Un-locked");
    $machine->addTransition("Un-locked", ["Push"], "Locked");
    $this->stateMachine = $machine;
  }

  public function testInvalidInputException() {
    $this->expectExceptionMessage("Invalid Input meh");
    $this->stateMachine->processInput("meh");
  }

  public function testTurnstile() {
    $sm = $this->stateMachine;

    $sm->processInput("Push");
    $this->assertEquals("Locked", $sm->getCurrentState());

    $sm->processInput("Coin");
    $this->assertEquals("Un-locked", $sm->getCurrentState());

    $sm->processInput("Coin");
    $this->assertEquals("Un-locked", $sm->getCurrentState());

    $sm->processInput("Push");
    $this->assertEquals("Locked", $sm->getCurrentState());
  }
}

class Counter {
  private $counter = 0;

  public function increment() {
    $this->counter += 1;
  }

  private function decrease() {
    $this->counter -= 1;
  }

  public function getCounter() {
    return $this->counter;
  }
}