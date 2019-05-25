<?php

namespace Maquina\StateMachine;

class MachineOfMachines extends Machine implements IStateMachine
{
    private $machines = [];

    public function addMachine($state, IStateMachine $machine)
    {
        $this->machines[$state] = $machine;
    }

    public function processInput(string $input)
    {
        $got_machine = false;
        $machine_error = false;
        $machine_finished = false;

        $machines = [];
        $errors = [];

        foreach ($this->currentStates as $current_state) {
            $machine = $this->getStateMachine($current_state);
            if ($machine) {
                $machines[] = $machine;
                $got_machine = true;
                try {
                    $machine->processInput($input);
                } catch (\Exception $e) {
                    $errors[] = true;
                  // The current machine could not handle the input... transition.
                    if ($machine->isCurrentlyAtAnEndState()) {
                        $machine_finished = true;
                    }
                    $machine->reset();
                }
            }
        }

        if ($got_machine) {
            if (count($machines) === count($errors)) {
                if ($machine_finished) {
                    parent::processInput($input);
                } else {
                    throw new \Exception("We had machines for state(s) " . implode(", ", $this->currentStates) . " but no machine finished");
                }
            }
        } else {
            parent::processInput($input);
        }
    }

    public function isCurrentlyAtAnEndState(): bool
    {
        $is = parent::isCurrentlyAtAnEndState();
        if ($is === false) {
            return false;
        } else {
          // If we are at an end state, we need to check the state of the machine.
            try {
                $finished = false;
                $machines = [];
                foreach ($this->getCurrentStates() as $current_state) {
                    $machine = $this->getStateMachine($current_state);
                    if ($machine) {
                        $machines[] = $machine;
                    }
                }

                if (empty($machines)) {
                    $finished = true;
                } else {
                    foreach ($machines as $machine) {
                        if ($machine->isCurrentlyAtAnEndState()) {
                            $finished = true;
                        }
                    }
                }

                return $finished;
            } catch (\Exception $e) {
                return true;
            }
        }
    }

    public function getStateMachine($state): ?IStateMachine
    {
        if (isset($this->machines[$state])) {
            return $this->machines[$state];
        }

        return null;
    }

  /**
   * Abbreviation for getStateMachine.
   */
    public function gsm($state): ?IStateMachine
    {
        return $this->getStateMachine($state);
    }
}
