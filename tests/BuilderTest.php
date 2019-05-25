<?php

use Maquina\Builder as mb;
use Maquina\Feeder as mf;

// @codingStandardsIgnoreStart
class BuilderTest extends \PHPUnit\Framework\TestCase
// @codingStandardsIgnoreEnd
{
    public function testStringMachineBadInput()
    {
        $this->expectExceptionMessage("Invalid Input L");
        $machine = mb::s("SELECT");
        mf::feed("SELECL", $machine);
    }

    public function testStringMachineGoodInput()
    {
        $machine = mb::s("SELECT");
        mf::feed("SELECT", $machine);
        $this->assertTrue($machine->isCurrentlyAtAnEndState());
        $this->assertEquals("SELECT", $machine->getMatch());
    }

    public function testStringMachineEndState()
    {
        $this->expectExceptionMessage("Invalid Input");
        $machine = mb::s("SELECT");
        mf::feed("SELECT ", $machine);
    }

    public function testBlackHole()
    {
        $this->expectExceptionMessage("Invalid Input L");
        $machine = mb::bh("OX");
        mf::feed("OXXXOXOLXOO", $machine);
    }

    public function testLoop()
    {
        $this->expectExceptionMessage("Invalid Input B");
        $machine = mb::l("AB");
        mf::feed("ABABABBA", $machine);
    }
}
