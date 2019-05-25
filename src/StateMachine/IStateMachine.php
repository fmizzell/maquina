<?php

namespace Maquina\StateMachine;

interface IStateMachine
{
    public function addTransition(string $current_state, array $inputs, string $next_state);
    public function addEndState(string $state);

    public function processInput(string $input);

    public function isCurrentlyAtAnEndState(): bool;

    public function getCurrentStates():array;

    public function reset();
}
