<?php
class User_CuratorController extends HM_Controller_Action_User 
{
    /**
     * Экшн для списка курсов
     */
    public function assignAction() 
    {

        $userId = $this->_getParam('user_id', 0);
        if(!$userId) $userId = $this->getService('User')->getCurrentUserId();
        
        $form = new HM_Form_AssignCurator();
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                
            	$values = $form->getValues();
                $res = $this->getService('Curator')->deleteProjectsResponsibilities($userId);

                if (!$values['unlimited_projects'] && is_array($values['projects']) && count($values['projects'])) {
                    foreach($values['projects'] as $projectId) {
                        $res = $this->getService('Curator')->addProjectResponsibility($userId, $projectId);
                    }
                }

                $classifiers = $form->getClassifierValues();
                $this->getService('CuratorResponsibility')->deleteResponsibilities($userId);

                if (!isset($values['unlimited_classifiers'])) {
                    $values['unlimited_classifiers'] = 1;
                }

                if (!$values['unlimited_classifiers'] && is_array($classifiers) && count($classifiers)) {
                    foreach($classifiers as $classifierId) {
                        $res = $this->getService('CuratorResponsibility')->addResponsibility($userId, $classifierId);
                    }
                }

                $this->getService('Curator')->setResponsibilityOptions(array(
                                                                         'user_id' => (int) $userId,
                                                                         'unlimited_projects' => $values['unlimited_projects'],
                                                                         'unlimited_classifiers' => $values['unlimited_classifiers'],
                                                                         'assign_new_projects' => $values['assign_new_projects']
                                                                    ));

                $this->_flashMessenger->addMessage(_('Области ответственности успешно изменены'));
        		$this->_redirector->gotoSimple('assign', 'curator', 'user', array('user_id' => $userId));

            }
        } else {

            $values = $this->getService('Curator')->getResponsibilityOptions($userId);
            $values['projects'] = $values['classifiers'] = array();

            if(!$this->getService('Curator')->userIsCurator($userId)){
                $form->addAttribs(array('onSubmit' => 'return confirm("'._('Вы уверены, что хотите добавить роль организатора обучения данному пользователю?').'")'));
            }

            if($this->getService('Curator')->getProjectsResponsibilities($userId)){
                $values['projects'] = $this->getService('Curator')->getAssignedProjectsResponsibilities($userId)->getList('projid', 'projid');
            }

            $form->populate($values);

        }
        $this->view->form = $form;

    }


    //  Функции для обработки полей в таблице


    /**
     * @param string $field Поле из таблицы
     * @return string Возвращаем статус
     */
    public function updateStatus($field) {
    	$userId = $this->_getParam('user_id', 0);
    	//pr($field);
    	$options = $this->getService('Curator')->getResponsibilityOptions($userId);
    	if($options['unlimited_projects'] == 1){
            return _('Да');        	    
    	}
        if ($field == $userId) {
            return _('Да');
        } else {
            return _('Нет');
        }
    }

    public function updateName($name, $projectId) {

        return '<a href="' .
                $this->view->url(
                    array('module' => 'project',
                        'controller' => 'index',
                        'action' => 'index',
                        'project_id' => $projectId
                    )
                ) .
                '">' . $name . '</a>';


    }


}

