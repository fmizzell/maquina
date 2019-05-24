<?php

namespace Maquina;

use Maquina\StateMachine\IStateMachine;

class Feeder
{
    public static function feed(string $inputs, IStateMachine $machine)
    {
        foreach (str_split($inputs) as $input) {
            $machine->processInput($input);
        }
    }
}
