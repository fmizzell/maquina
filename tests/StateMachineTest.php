<?php

class StateMachineTest extends \PHPUnit\Framework\TestCase {

  /**
   * @var \Maquina\StateMachine
   */
  private $stateMachine;

  public function setUp() {
    parent::setUp();
    $states = ['Locked', "Un-locked"];
    $inputs = ['Push', 'Coin'];

    $this->stateMachine = new \Maquina\StateMachine($states, $inputs);
  }

  public function testLackOfInitialState() {
    $this->expectExceptionMessage("An initial state must be provided to start processing");
    $this->stateMachine->processInput("Push");
  }

  public function testInitialStateException() {
    $this->expectExceptionMessage("Invalid initial state Blah");
    $this->stateMachine->addInitialState("Blah");
  }

  public function testInvalidInputException() {
    $this->expectExceptionMessage("Invalid Input meh");
    $this->stateMachine->addInitialState('Locked');
    $this->stateMachine->processInput("meh");
  }

  public function testBadCallableExeption() {
    $counter = new Counter();
    $this->expectExceptionMessage('Unable to call callable [{},"decrease"]');
    $this->stateMachine->addInitialState('Locked');
    $this->stateMachine->addTransition("Locked", "Push", "Locked", [$counter, "decrease"]);
    $this->stateMachine->processInput("Push");
  }

  public function testTurnstile() {
    $sm = $this->stateMachine;

    $counter = new Counter();
    $sm->addInitialState('Locked');

    $sm->addTransition("Locked", "Push", "Locked");
    $sm->addTransition("Locked", "Coin", "Un-locked");
    $sm->addTransition("Un-locked", "Coin", "Un-locked");
    $sm->addTransition("Un-locked", "Push", "Locked", [$counter, "increment"]);

    $sm->processInput("Push");
    $this->assertEquals(0, $counter->getCounter());
    $this->assertEquals("Locked", $sm->getCurrentState());

    $sm->processInput("Coin");
    $this->assertEquals(0, $counter->getCounter());
    $this->assertEquals("Un-locked", $sm->getCurrentState());

    $sm->processInput("Coin");
    $this->assertEquals(0, $counter->getCounter());
    $this->assertEquals("Un-locked", $sm->getCurrentState());

    $sm->processInput("Push");
    $this->assertEquals(1, $counter->getCounter());
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