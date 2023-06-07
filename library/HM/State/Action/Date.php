<?php

class HM_State_Action_Date extends HM_State_Action
{
    public function _render($params, $readOnly)
    {
        $value = $params['value'];


        /*
         * @var HM_View
         */
        $view = Zend_Registry::get('view');
        $view->addScriptPath(dirname(__FILE__));
        $view->title = $params['title'];
        $view->id = $params['id'];

        $view->stateId = $this->getState()->getProcess()->getStateId();
        $view->forState = get_class($this->getState());
        $view->selectName = $params['id'];
        $view->value = $params['value'];

        if($readOnly == false){
            return $view->render('view/date.tpl');
        }else{
            return $view->render('view/date-read.tpl');
        }


    }







}
