<?php
/**
 * Старый код из pm
 * Этот интерфейс нужно имплементить если вид оценки достаточно простой, 
 * состоит только из оценки неких критериев и запускается через event/index/run
 */
interface HM_At_Session_Event_Method_Interface
{
    public function init();

    static public function getMemos();

    public function isValid();

    public function getMethodValue($value);

    public function getWeight($value);

    public function savesubmethod($values);
}