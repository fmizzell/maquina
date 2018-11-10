<?php

class StateMachineTest extends \PHPUnit\Framework\TestCase {

  /**
   * https://upload.wikimedia.org/wikipedia/commons/thumb/9/9e/Turnstile_state_machine_colored.svg/330px-Turnstile_state_machine_colored.svg.png
   */
  public function testTurstile() {
    $counter = new Counter();

    $states = ['Locked', "Un-locked"];
    $inputs = ['Push', 'Coin'];

    $sm = new \Maquina\StateMachine($states, $inputs);

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

  public function getCounter() {
    return $this->counter;
  }
}