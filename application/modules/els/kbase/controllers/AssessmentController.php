<?php
class Kbase_AssessmentController extends HM_Controller_Action{
    
    public function init(){
        $this->_helper->ContextSwitch()
                ->setAutoJsonSerialization(true)
                ->addActionContext('index', 'json')
                ->initContext('json');
        parent::init();
    }
    
    public function indexAction(){
        try{
            $resource_id = (int) $this->_getParam('resource_id', 0);
            $type        = $this->_getParam('type', 0);
            $fieldName   = $this->_getParam('field_name', 'value');
            $assessment  = (float) $this->_getParam('score', false);
            $user = $this->getService('User')->getCurrentUserId();
            
            if($assessment === false){
                throw new HM_Exception(_('Неверные параметры'));
            }
            $this->getService('KbaseAssessment')->estimate(
                $assessment,
                $user,
                $resource_id,
                $type
            );
            // Вернуть среднюю оценку по данному курсу
            $result = array(
                'status' => 'OK',
                'msg' => $this->getService('KbaseAssessment')->getAverage($resource_id, $type, $fieldName)
            );
        } catch (HM_Exception $e){
            $result = array(
                'status' => 'ERR',
                'msg' => $e->getMessage()
            );
        }
        $this->view->assign($result);
    }
}