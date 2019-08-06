<?php

namespace Maquina\StateMachine;

/**
 * Class StateMachine.
 *
 * Implementation of a
 * [state machine](https://en.wikipedia.org/wiki/Finite-state_machine)
 */
class Machine implements IStateMachine, \JsonSerializable
{
    use Execution;

    private $initialStates;

    private $transitions = [];

    protected $currentStates = null;

    private $endStates = [];

    protected $halted = false;

  /**
   * Constructor.
   */
    public function __construct(array $initial_states)
    {
        $this->execution = [];
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

            $this->halted = $this->didWeHalt();
        } else {
            throw new \Exception("Invalid Input {$input}");
        }
    }

    public function reset()
    {
        $this->recordStateExecution($this->initialStates);
        $this->currentStates = $this->initialStates;
        $this->halted = false;
    }

    public function getCurrentStates(): array
    {
        return $this->currentStates;
    }

    protected function didWeHalt()
    {
        $halted = false;
        foreach ($this->currentStates as $current_state) {
            if (in_array($current_state, $this->endStates)) {
                $halted = true;
            }
        }
        return $halted;
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

    public function jsonSerialize()
    {
        return (object) ['currentStates' => $this->currentStates, 'halted' => $this->halted];
    }

    public static function hydrate(string $json, $machine)
    {
        $data = (array) json_decode($json);
        $class = new \ReflectionClass(get_class($machine));
        if (get_class($machine) != self::class) {
            $class = $class->getParentClass();
        }

        foreach ($data as $property_name => $value) {
            $property = $class->getProperty($property_name);
            $property->setAccessible(true);
            $property->setValue($machine, $value);
        }

        return $machine;
    }
}
