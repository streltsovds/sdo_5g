<?php

/**
 * Просто обётрка для отличия обычных строк от строк, содержащих js-выражение,
 * которое планируется передать в js-объект.
 *
 */
class HM_JsExpression
{
    public $string;

    public function __construct($string)
    {
        $this->string = $string;
    }
}