<?php

namespace Maquina\StateMachine;

/**
 * Class StateMachine.
 *
 * Implementation of a
 * [state machine](https://en.wikipedia.org/wiki/Finite-state_machine)
 */
class Machine implements IStateMachine
{
    use Execution;

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
        $this->execution = new \SplStack();
        $this->initialStates = $initial_states;

        $this->recordStateExecution($this->initialStates);

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
            $this->recordInputExecution($input);

            $next_states = $this->getNextStates($input);

            $this->recordStateExecution($next_states);

            $this->currentStates = $next_states;

            $this->didWeHalt();
        } else {
            throw new \Exception("Invalid Input {$input}");
        }
    }

    public function reset()
    {
        $this->recordStateExecution($this->initialStates);
        $this->currentStates = $this->initialStates;
    }

    public function getCurrentStates(): array
    {
        return $this->currentStates;
    }

    private function didWeHalt()
    {
        $this->halted = false;
        foreach ($this->currentStates as $current_state) {
            if (in_array($current_state, $this->endStates)) {
                $this->halted = true;
            }
        }
    }

    private function transitionIsValid($input)
    {
        foreach ($this->currentStates as $current_state) {
            if (isset($this->transitions[$current_state][$input])) {
                return true;
            }
        }
        return false;
    }

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
