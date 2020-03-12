<?php

use Maquina\StateMachine\Machine;

// @codingStandardsIgnoreStart
class NonDeterministicStateMachineTest extends \PHPUnit\Framework\TestCase
// @codingStandardsIgnoreEnd
{

  /**
   * @var Machine
   */
    private $stateMachine;

    public function setUp()
    {
        parent::setUp();

        $machine = new Machine(["A"]);
        $machine->addTransition("A", ["a"], "B");
        $machine->addTransition("A", ["a"], "C");
        $machine->addTransition("B", ["b"], "D");
        $machine->addTransition("C", ["c"], "D");
        $machine->addTransition("D", ["d"], "END");
        $machine->addEndState("END");

        $this->stateMachine = $machine;
    }

    public function testFirstPath()
    {
        $machine = $this->stateMachine;
        \Maquina\Feeder::feed("abd", $machine);
        $this->assertTrue($machine->isCurrentlyAtAnEndState());
    }

    public function testSecondPath()
    {
        $machine = $this->stateMachine;
        \Maquina\Feeder::feed("acd", $machine);
        $this->assertTrue($machine->isCurrentlyAtAnEndState());
    }

    public function testIncompletePath()
    {
        $machine = $this->stateMachine;
        \Maquina\Feeder::feed("ac", $machine);
        $this->assertFalse($machine->isCurrentlyAtAnEndState());
    }

    public function testBadPath()
    {
        $this->expectExceptionMessage("Invalid Input d");
        $machine = $this->stateMachine;
        \Maquina\Feeder::feed("acdd", $machine);
    }

    public function testStoppingAndStartingExecutionRecording()
    {
        $machine = $this->stateMachine;
        $machine->stopRecording();
        \Maquina\Feeder::feed("abd", $machine);
        $machine->startRecording();
        $this->assertTrue($machine->isCurrentlyAtAnEndState());
    }
}
