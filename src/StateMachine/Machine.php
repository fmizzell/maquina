<?php

namespace Maquina\StateMachine;

use Maquina\Capture;

/**
 * Class StateMachine.
 *
 * Implementation of a
 * [state machine](https://en.wikipedia.org/wiki/Finite-state_machine)
 */
class Machine implements IStateMachine
{
    use Capture;

    private $initialStates;

    private $transitions = [];

    protected $currentStates = null;

    private $endStates = [];

    private $halted = false;

  /**
   * Constructor.
   */
    public function __construct(array $initial_states)
    {
        $this->initialStates = $initial_states;
        $this->currentStates = $this->initialStates;
    }

  /**
   * Note transitions, and an action if relevant.
   */
    public function addTransition(string $current_state, array $inputs, string $next_state)
    {
        foreach ($inputs as $input) {
            $this->transitions[$current_state][$input][$next_state] = true;
        }
    }

  /**
   * Set an end state.
   */
    public function addEndState(string $state)
    {
        $this->endStates[] = $state;
    }

    public function isCurrentlyAtAnEndState(): bool
    {
        return $this->halted;
    }

  /**
   * Give the state machine an input for it to work.
   */
    public function processInput(string $input)
    {
        if ($this->transitionIsValid($input)) {
            $next_states = $this->getNextStates($input);
            $this->currentStates = $next_states;
            $this->handleMatch($input);

            $this->halted = false;
            foreach ($this->currentStates as $current_state) {
                if (in_array($current_state, $this->endStates)) {
                    $this->halted = true;
                }
            }
        } else {
            throw new \Exception("Invalid Input {$input}");
        }
    }

    public function reset()
    {
        $this->currentStates = $this->initialStates;
        $this->resetMatch();
    }

    public function getCurrentStates(): array
    {
        return $this->currentStates;
    }

  /**
   * Private.
   */
    private function transitionIsValid($input)
    {
        foreach ($this->currentStates as $current_state) {
            if (isset($this->transitions[$current_state][$input])) {
                return true;
            }
        }
        return false;
    }

  /**
   * Private.
   */
    private function getNextStates($input)
    {
        $next_states = [];

        foreach ($this->currentStates as $current_state) {
            if (isset($this->transitions[$current_state][$input])) {
                $keys = array_keys($this->transitions[$current_state][$input]);
                $next_states = array_merge($next_states, $keys);
            }
        }

        return $next_states;
    }
}
