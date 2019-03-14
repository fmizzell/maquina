<?php

namespace Maquina;

trait Capture
{
  private $capture = TRUE;

  private $match = "";
  private $matches = [];

  public function noCapture() {
    $this->capture = FALSE;
  }

  public function getMatches() {
    return $this->matches;
  }

  public function getMatch() {
    return $this->match;
  }

  /**
   * Private.
   */
  private function handleMatch($input) {
    $this->match .= $input;

    /* @var $this \Maquina\StateMachine\IStateMachine */
    if ($this->isCurrentlyAtAnEndState()) {
      $this->matches[] = $this->match;
      $this->match = "";
    }
  }

}