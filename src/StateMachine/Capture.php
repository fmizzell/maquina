<?php

namespace Maquina;

trait Capture
{
    private $capture = true;

    private $match = "";

    public function noCapture()
    {
        $this->capture = false;
    }

    public function getMatch()
    {
        return $this->match;
    }

    private function handleMatch($input)
    {
        if ($this->capture) {
            $this->match .= $input;
        }
    }

    private function resetMatch()
    {
        $this->match = "";
    }
}
