<?php


namespace Maquina\StateMachine;

trait Execution
{
    public $execution;

    private function recordStateExecution($next_states)
    {
        if (json_encode($this->currentStates) != json_encode($next_states)) {
            $this->execution->push($next_states);
            $this->execution->push("");
        }
    }

    private function recordInputExecution($input)
    {
        $inputs = $this->execution->pop();
        $inputs .= $input;
        $this->execution->push($inputs);
    }
}
