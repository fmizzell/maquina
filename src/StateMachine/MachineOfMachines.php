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
        $machines = $this->getCurrentMachines();
        $errors = [];
        if (!empty($machines)) {
            foreach ($machines as $state => $machine) {
                if ($this->feedMachine($state, $machine, $input)) {
                    $errors[] = true;
                }
            }
        }

        $all_machines_errored_out = ((!empty($machines)) && (count($errors) == count($machines)));
        if ($all_machines_errored_out || empty($machines)) {
            if ($all_machines_errored_out && !$this->machineHalted()) {
                throw new \Exception("We had machines for state(s) " .
                implode(", ", $this->currentStates) . " but no machine finished");
            }

            $this->resetCurrentMachines();
            parent::processInput($input);
        }

      /*$got_machine = false;
        $machine_finished = false;

        $machines = [];
        $errors = [];

        foreach ($this->currentStates as $key => $current_state) {
            $machine = $this->getStateMachine($current_state);
            if ($machine) {
                $machines[] = $machine;
                $got_machine = true;
                try {
                    $machine->processInput($input);
                    $this->halted = $this->didWeHalt();
                } catch (\Exception $e) {
                    $errors[] = true;

                    if ($machine->isCurrentlyAtAnEndState()) {
                        $machine_finished = true;
                    } else {
                        if (count($this->currentStates) > 1) {
                            unset($this->currentStates[$key]);
                        }
                    }
                    $this->halted = $this->didWeHalt();
                    $machine->reset();
                }
            }
        }

        if ($got_machine) {
            if (count($machines) === count($errors)) {
                if ($machine_finished) {
                    $real_halted = $this->halted;
                    parent::processInput($input);
                    $this->halted = $real_halted;
                } else {
                    throw new \Exception("We had machines for state(s) " .
                      implode(", ", $this->currentStates) . " but no machine finished");
                }
            }
        } else {
            $real_halted = $this->halted;
            parent::processInput($input);
            $this->halted = $real_halted;
        }*/
    }

    private function machineHalted()
    {
        $halted = false;
        foreach ($this->getCurrentMachines() as $machine) {
            if ($machine->isCurrentlyAtAnEndState()) {
                $halted = true;
            }
        }
        return $halted;
    }

    private function getCurrentMachines()
    {
        $machines = [];
        foreach ($this->currentStates as $key => $current_state) {
            $machine = $this->getStateMachine($current_state);
            if ($machine) {
                $machines[$current_state] = $machine;
            }
        }
        return $machines;
    }

    private function feedMachine($state, $machine, $input)
    {
        $error = false;
        try {
            $machine->processInput($input);
            $this->halted = $this->didWeHalt();
        } catch (\Exception $e) {
            if (count($this->currentStates) > 1) {
                $key = array_search($state, $this->currentStates);
                if ($key !== false) {
                    $machine->reset();
                    unset($this->currentStates[$key]);
                }
            }
            $error = true;
        }
        return $error;
    }

    public function resetCurrentMachines()
    {
        foreach ($this->getCurrentMachines() as $machine) {
            $machine->reset();
        }
    }

    /*public function isCurrentlyAtAnEndState(): bool
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
    }*/

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

    protected function didWeHalt()
    {
        $parent_halt = parent::didWeHalt();
        $machines = $this->getCurrentMachines();

        if (!$parent_halt) {
            return false;
        }

        if ($parent_halt && empty($machines)) {
            return true;
        } else {
            $halted = false;
            foreach ($machines as $machine) {
                if ($machine->isCurrentlyAtAnEndState()) {
                    $halted = true;
                }
            }
            return $halted;
        }
    }
}
