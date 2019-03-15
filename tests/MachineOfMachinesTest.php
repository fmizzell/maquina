<?php
declare(strict_types=1);

use Maquina\StateMachine\MachineOfMachines;
use Maquina\Builder as mb;
use Maquina\Feeder;

class MachineOfMachinesTest extends \PHPUnit\Framework\TestCase
{

  private function getSelectMeMachine() {
    $machine = new MachineOfMachines('select');
    $machine->addMachine('select', mb::s("SELECT"));
    $machine->addMachine('me', mb::s("ME"));
    $machine->addTransition('select', [" "], 'me');
    $machine->addEndState('me');
    return $machine;
  }

  public function testGoodInput() {
    $machine = $this->getSelectMeMachine();
    Feeder::feed("SELECT ME", $machine);
    $this->assertTrue($machine->isCurrentlyAtAnEndState());
  }

  public function testBadInput() {
    $machine = $this->getSelectMeMachine();
    $this->expectExceptionMessage("Invalid Input X");
    Feeder::feed("SELECT MEX", $machine);
  }

  public function testSqlString()
  {
    $valid_sql_strings = [];
    $valid_sql_strings[] = '[SELECT * FROM abc];';
    $valid_sql_strings[] = '[SELECT * FROM abc][WHERE def = "hij"];';
    $valid_sql_strings[] = '[SELECT * FROM abc][WHERE def = "hij" AND klm = "nop"];';
    $valid_sql_strings[] = '[SELECT * FROM abc][WHERE def = "hij" AND klm = "nop"][ORDER BY qrs];';
    $valid_sql_strings[] = '[SELECT * FROM abc][WHERE def = "hij" AND klm = "nop"][ORDER BY qrs, tuv];';
    $valid_sql_strings[] = '[SELECT * FROM abc][WHERE def = "hij" AND klm = "nop"][ORDER BY qrs, tuv][LIMIT 1];';
    $valid_sql_strings[] = '[SELECT * FROM abc][WHERE def = "hij" AND klm = "nop"][ORDER BY qrs, tuv][LIMIT 1 OFFSET 2];';

    foreach ($valid_sql_strings as $string) {
      $machine = $this->getSqlMachine();
      Feeder::feed($string, $machine);
      $this->assertTrue($machine->isCurrentlyAtAnEndState());
    }
  }

  private function getSqlMachine() {
    $machine = new MachineOfMachines('select_start');
    $machine->addEndState("end");

    $machine->addMachine('select', mb::s('SELECT * FROM'));
    $machine->addMachine('select_var', mb::bh('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ$_',  mb::ONE_OR_MORE));
    $machine->addMachine("where", $this->getWhereMachine());
    $machine->addMachine("order_by", $this->getOrderByMachine());
    $machine->addMachine("limit", $this->getLimitMachine());


    $machine->addTransition('select_start', ["["], "select");
    $machine->addTransition('select', [" "], "select_var");
    $machine->addTransition('select_var', ["]"], "select_end");
    $machine->addTransition('select_end', [";"], "end");

    $machine->addTransition('select_end', ["["], "where");
    $machine->addTransition('where', ["]"], "where_end");
    $machine->addTransition('where_end', [";"], "end");

    $machine->addTransition('where_end', ["["], "order_by");
    $machine->addTransition('order_by', ["]"], "order_by_end");
    $machine->addTransition('order_by_end', [";"], "end");

    $machine->addTransition('order_by_end', ["["], "limit");
    $machine->addTransition('limit', ["]"], "limit_end");
    $machine->addTransition('limit_end', [";"], "end");

    return $machine;
  }

  private function getWhereMachine() {
    $machine = new MachineOfMachines('where');
    $machine->addEndState("quoted_string");

    $machine->addMachine('where', mb::s('WHERE'));
    $machine->addMachine('where_var', mb::bh('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ$_',  mb::ONE_OR_MORE));
    $machine->addMachine("equal", mb::s("="));
    $machine->addMachine("quoted_string", $this->getQuotedStringMachine());
    $machine->addMachine("and", mb::s("AND"));


    $machine->addTransition('where', [" "], "where_var");
    $machine->addTransition('where_var', [" "], "equal");
    $machine->addTransition('equal', [" "], "quoted_string");
    $machine->addTransition('quoted_string', [" "], "and");
    $machine->addTransition('and', [" "], "where_var");

    return $machine;
  }

  private function getQuotedStringMachine() {
    $machine = new MachineOfMachines('1');
    $machine->addEndState("end");

    $machine->addMachine('string', mb::bh('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ ',  mb::ONE_OR_MORE));

    $machine->addTransition('1', ['"'], "string");
    $machine->addTransition('string', ['"'], "end");

    return $machine;
  }

  private function getOrderByMachine() {
    $machine = new MachineOfMachines('order');
    $machine->addEndState("order_var");

    $machine->addMachine('order', mb::s('ORDER BY'));
    $machine->addMachine('order_var', mb::bh('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ$_',  mb::ONE_OR_MORE));

    $machine->addTransition('order', [" "], "order_var");
    $machine->addTransition('order_var', [","], "space");
    $machine->addTransition('space', [" "], "order_var");

    return $machine;
  }

  private function getLimitMachine() {
    $machine = new MachineOfMachines('limit');
    $machine->addEndState("numeric1");
    $machine->addEndState("numeric2");

    $machine->addMachine('limit', mb::s('LIMIT'));
    $machine->addMachine('offset', mb::s('OFFSET'));
    $machine->addMachine('numeric1', mb::bh('0123456789'));
    $machine->addMachine('numeric2', mb::bh('0123456789'));

    $machine->addTransition('limit', [" "], "numeric1");
    $machine->addTransition('numeric1', [" "], "offset");
    $machine->addTransition('offset', [" "], "numeric2");

    return $machine;
  }

}