<?php


namespace Maquina\StateMachine;

trait Execution
{
    public $execution;

    private $recordsData = TRUE;

    private function recordStateExecution($next_states)
    {
        if (!$this->recordsData) {
            return;
        }
        if (json_encode($this->currentStates) != json_encode($next_states)) {
            array_push($this->execution, $next_states);
            array_push($this->execution, "");
        }
    }

    private function recordInputExecution($input)
    {
        if (!$this->recordsData) {
            return;
        }
        $inputs = array_pop($this->execution);
        $inputs .= $input;
        array_push($this->execution, $inputs);
    }

    public function startRecording() {
        $this->recordsData = TRUE;
    }

    public function stopRecording() {
        $this->recordsData = FALSE;
    }

}
