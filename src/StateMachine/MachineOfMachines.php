<?php

namespace Maquina\StateMachine;

class MachineOfMachines extends Machine implements IStateMachine
{
  private $machines = [];

  public function addMachine($state, IStateMachine $machine) {
    $this->machines[$state] = $machine;
  }

  public function processInput(string $input) {
    // Feed the current machine.

    try {
      /* @var $machine \Maquina\StateMachine\IStateMachine */
      $machine = $this->getCurrentMachine();
    }
    catch (\Exception $e) {
      $machine = NULL;
    }

    if (isset($machine)) {
      try {
        $machine->processInput($input);
      } catch (\Exception $e) {
        // The current machine could not handle the input... transition.
        if ($machine->isCurrentlyAtAnEndState()) {
          parent::processInput($input);
          $machine->reset();
        } // If the current machine is not in the right state.
        else {
          throw $e;
        }
      }
    }
    else {
      parent::processInput($input);
    }
  }

  public function isCurrentlyAtAnEndState(): bool
  {
    $is = parent::isCurrentlyAtAnEndState();
    if ($is === FALSE) {
      return FALSE;
    }
    else {
      // If we are at an end state, we need to check the state of the machine.
      try {
        $machine = $this->getCurrentMachine();
        return $machine->isCurrentlyAtAnEndState();
      }
      catch (\Exception $e) {
        return TRUE;
      }
    }
  }

  private function getCurrentMachine(): IStateMachine {
    if (isset($this->machines[$this->currentState])) {
      return $this->machines[$this->currentState];
    }
    throw new \Exception("State {$this->currentState} does not have a machine.");
  }

}