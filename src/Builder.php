<?php

namespace Maquina;

use Maquina\StateMachine\IStateMachine;
use Maquina\StateMachine\Machine;

class Builder
{
    const ZERO_OR_MORE = 0;
    const ONE_OR_MORE = 1;

    const EMPTY = 2;
    const NOT_EMPTY = 3;

  /**
   * String machine.
   *
   * Checks that a string is present in the input exactly.
   */
    public static function s(string $string, $mode = self::NOT_EMPTY): IStateMachine
    {
        $chars = str_split($string);
        $end_state = count($chars);

        $machine = new Machine([0]);
        $machine->addEndState($end_state);

        if ($mode == self::EMPTY) {
            $machine->addEndState(0);
        }

        for ($i = 1; $i <= $end_state; $i++) {
            $current = $i - 1;
            $machine->addTransition($current, [$chars[$current]], $i);
        }

        return $machine;
    }

  /**
   * Loop machine.
   *
   * loops indefinitely on a pattern.
   */
    public static function l(string $string, $capture = true): Machine
    {
        $chars = str_split($string);
        $end_state = count($chars) - 1;

        $machine = new Machine([0]);
        $machine->addEndState($end_state);

        for ($i = 1; $i <= $end_state + 1; $i++) {
            $current = $i - 1;
            if ($current == $end_state) {
                $machine->addTransition($current, [$chars[$current]], 0);
            } else {
                $machine->addTransition($current, [$chars[$current]], $i);
            }
        }

        return $machine;
    }

  /**
   * Black hole state machine.
   *
   * It swallows all instances of the characters in the given string.
   */
    public static function bh(string $string, $mode = self::ZERO_OR_MORE)
    {
        $chars = str_split($string);
        $chars = array_unique($chars);

        $machine = new Machine([0]);

        if ($mode == self::ONE_OR_MORE) {
            $machine->addEndState(1);
            $machine->addTransition(0, $chars, 1);
            $machine->addTransition(1, $chars, 1);
        } else {
            $machine->addEndState(0);
            $machine->addTransition(0, $chars, 0);
        }

        return $machine;
    }
}
