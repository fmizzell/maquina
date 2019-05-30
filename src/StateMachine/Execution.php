<?php


namespace Maquina\StateMachine;

trait Execution
{
    public $execution;

    private function recordStateExecution($next_states)
    {
        if (json_encode($this->currentStates) != json_encode($next_states)) {
            array_push($this->execution, $next_states);
            array_push($this->execution, "");
        }
    }

    private function recordInputExecution($input)
    {
        $inputs = array_pop($this->execution);
        $inputs .= $input;
        array_push($this->execution, $inputs);
    }
}
