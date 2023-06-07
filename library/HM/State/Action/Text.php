<?php
class HM_State_Action_Text extends HM_State_Action
{
    public function _render($params, $readOnly)
    {
        return  '<span>' . $params['title'] . '</span>';
    }
}
