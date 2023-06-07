<?php
interface HM_At_Evaluation_Method_Interface
{
    static public function getMethodName();    

    public function getRespondents($position, $user = null);
}