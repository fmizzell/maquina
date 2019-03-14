<?php

namespace Maquina;


class AmbiguousMachine extends Machine
{
  public $ambiguousInputHandler;
  public $ambiguousInputs = [];
  public $disambiguated = FALSE;
  public $buffer = [];

  /**
   * Set an ambiguous input handler.
   */
  public function setAmbiguousInputHandler($callable) {
    $this->ambiguousInputHandler = $callable;
  }

  /**
   * Mark inputs as ambiguous in the machine.
   */
  public function addAmbiguousInput($input) {
    if (isset($this->ambiguousInputHandler)) {
      if (in_array($input, $this->getInputs())) {
        $this->ambiguousInputs[] = $input;
      }
      else {
        throw new \Exception("Invalid input: {$input}");
      }
    }
    else {
      throw new \Exception("Declare and ambiguous input handler before adding ambiguous inputs");
    }
  }

  /**
   * Give the state machine an input for it to work.
   */
  public function processInput($input) {

    $this->setCurrentStateIfNotSet();

    if ($this->processPreviouslyFoundAmbiguousInputs($input)) {
      return;
    }

    if ($this->checkAndCaptureAmbiguousInputs($input)) {
      return;
    }

    // Handle input after the end state.
    if ($this->getCurrentState() === $this->getEndState()) {
      throw new \Exception("Already at end state {$this->getEndState()}, no more processing can be done.");
    }

    $this->letTheStateMachineWork($input);
  }

  /**
   * Private.
   */
  private function checkAndCaptureAmbiguousInputs($input) {
    if (in_array($input, $this->ambiguousInputs) && !$this->disambiguated) {
      $this->buffer[] = $input;
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Private.
   */
  private function processPreviouslyFoundAmbiguousInputs($input) {
    if (!empty($this->buffer)) {
      $this->buffer[] = $input;

      $buffer = $this->buffer;
      $this->buffer = [];
      $final_input = call_user_func($this->ambiguousInputHandler, $buffer, $this->getCurrentState());
      $buffer[0] = $final_input;

      $this->disambiguated = TRUE;
      $counter = 0;
      foreach ($buffer as $input) {
        $this->processInput($input);
        if ($counter == 0) {
          $this->disambiguated = FALSE;
        }
        $counter++;
      }

      return TRUE;
    }
    return FALSE;
  }

}