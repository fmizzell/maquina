<?php

namespace Maquina;

trait Capture
{
    private $capture = true;

    private $match = "";
    private $matches = [];

    public function noCapture()
    {
        $this->capture = false;
    }

    public function getMatches()
    {
        return $this->matches;
    }

    public function getMatch()
    {
        return $this->match;
    }

  /**
   * Private.
   */
    private function handleMatch($input)
    {
        $this->match .= $input;

      /* @var $this \Maquina\StateMachine\IStateMachine */
        if ($this->isCurrentlyAtAnEndState()) {
            $this->matches[] = $this->match;
            $this->match = "";
        }
    }
}
