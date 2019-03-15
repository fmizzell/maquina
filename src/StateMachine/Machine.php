<?php

namespace Maquina\StateMachine;

/**
 * Class StateMachine.
 *
 * Implementation of a
 * [state machine](https://en.wikipedia.org/wiki/Finite-state_machine)
 */
class Machine implements IStateMachine {

  private $initialState;

  private $transitions = [];

  protected $currentState = NULL;

  private $endStates = [];

  /**
   * Constructor.
   */
  public function __construct(string $initial_state) {
    $this->initialState = $initial_state;
    $this->currentState = $initial_state;
  }

  /**
   * Note transitions, and an action if relevant.
   */
  public function addTransition(string $current_state, array $inputs, string $next_state)
  {
    foreach ($inputs as $input) {
      $this->transitions[$current_state][$input][$next_state] = TRUE;
    }
  }

  /**
   * Set an end state.
   */
  public function addEndState(string $state)
  {
    $this->endStates[] = $state;
  }

  public function isCurrentlyAtAnEndState(): bool {
    if (empty($this->endStates)) {
      throw new \Exception("This is an infinite machine");
    }

    return in_array($this->currentState, $this->endStates);
  }

  /**
   * Give the state machine an input for it to work.
   */
  public function processInput(string $input) {
    if ($this->transitionIsValid($input)) {
      $next_state = $this->getNextState($input);
      $this->currentState = $next_state;
    }
    else {
      throw new \Exception("Invalid Input {$input}");
    }
  }

  public function reset()
  {
    $this->currentState = $this->initialState;
  }

  public function getCurrentState(): string
  {
    return $this->currentState;
  }

  /**
   * Private.
   */
  private function transitionIsValid($input) {
    return isset($this->transitions[$this->currentState][$input]);
  }

  /**
   * Private.
   */
  private function getNextState($input) {
    $keys = array_keys($this->transitions[$this->currentState][$input]);
    return $keys[0];
  }

}
