<?php

use Maquina\StateMachine\Machine;

// @codingStandardsIgnoreStart
class StateMachineTest extends \PHPUnit\Framework\TestCase
// @codingStandardsIgnoreEnd
{

  /**
   * @var Machine
   */
    private $stateMachine;

    public function setUp()
    {
        parent::setUp();

        $machine = new Machine(["Locked"]);
        $machine->addTransition("Locked", ["Push"], "Locked");
        $machine->addTransition("Locked", ["Coin"], "Un-locked");
        $machine->addTransition("Un-locked", ["Coin"], "Un-locked");
        $machine->addTransition("Un-locked", ["Push"], "Locked");

        $this->stateMachine = $machine;
    }

    public function testInvalidInputException()
    {
        $this->expectExceptionMessage("Invalid Input meh");
        $this->stateMachine->processInput("meh");
    }

    public function testTurnstile()
    {
        $sm = $this->stateMachine;

        $sm->processInput("Push");
        $this->assertEquals("Locked", $sm->getCurrentStates()[0]);

        $sm->processInput("Coin");
        $this->assertEquals("Un-locked", $sm->getCurrentStates()[0]);

        $sm->processInput("Coin");
        $this->assertEquals("Un-locked", $sm->getCurrentStates()[0]);

        $sm->processInput("Push");
        $this->assertEquals("Locked", $sm->getCurrentStates()[0]);
    }

    public function testSerialization()
    {
        $sm = $this->stateMachine;
        $virgin = clone $sm;

        $sm->processInput("Push");
        $this->assertEquals("Locked", $sm->getCurrentStates()[0]);

        $sm->processInput("Coin");
        $this->assertEquals("Un-locked", $sm->getCurrentStates()[0]);

        $json = json_encode($sm);
        $sm2 = Machine::hydrate($json, clone $virgin);

        $sm2->processInput("Coin");
        $this->assertEquals("Un-locked", $sm2->getCurrentStates()[0]);

        $sm2->processInput("Push");
        $this->assertEquals("Locked", $sm2->getCurrentStates()[0]);
    }
}

// @codingStandardsIgnoreStart
class Counter
{
    private $counter = 0;

    public function increment()
    {
        $this->counter += 1;
    }

    private function decrease()
    {
        $this->counter -= 1;
    }

    public function getCounter()
    {
        return $this->counter;
    }
}
// @codingStandardsIgnoreEnd
