<?php
class Profile_IndexController extends HM_Controller_Action_Profile
{
    public function cardAction()
    {
        $this->view->profile = $this->getService('AtProfile')->getOne($this->getService('AtProfile')->findDependence('Category', $this->_profileId));
    }

    public function requirementsAction()
    {
        $form      = new HM_Form_Requirements();
        $profileId = $this->_getParam('profile_id', 0);

        if ( $this->_request->isPost() ) {

            if ( $form->isValid($this->_request->getParams()) ) {

                $update = array(
                    'profile_id'           => $profileId = $form->getValue('profile_id'),
                    //'requirements' => $form->getValue('requirements')
                    'age_min'              => $form->getValue('age_min'),
                    'age_max'              => $form->getValue('age_max'),
                    'gender'               => $form->getValue('gender'),
                    'education'            => $form->getValue('education'),
                    'additional_education' => $form->getValue('additional_education'),
                    'academic_degree'      => $form->getValue('academic_degree'),
                    'experience'           => $form->getValue('experience'),
                    'comments'             => $form->getValue('comments'),
                    'trips'                => $form->getValue('trips'),
                    'trips_duration'       => $form->getValue('trips_duration'),
                    'mobility'             => $form->getValue('mobility'),
                );
                $this->getService('AtProfile')->update($update);


                $delWhere = 'item_id='.$profileId.' AND (type='.
                    HM_Classifier_Link_LinkModel::TYPE_PROFILE_EDUCATION_SPECIALITIES.
                    ' OR type='.
                    HM_Classifier_Link_LinkModel::TYPE_PROFILE_EDUCATION_UNIVERSITIES.
                    ')'
                ;
                 $this->getService('ClassifierLink')->deleteBy($delWhere);
                // специальности
                if ( $form->getValue('specialities') ) {
                    foreach ($form->getValue('specialities') as $specClassifierId) {
                        $data = array(
                            'classifier_id' => $specClassifierId,
                            'item_id'       => $profileId,
                            'type'          => HM_Classifier_Link_LinkModel::TYPE_PROFILE_EDUCATION_SPECIALITIES
                        );
                        $this->getService('ClassifierLink')->insert($data);
                    }
                }

                // ВУЗы
                if ( $form->getValue('universities') ) {
                    foreach ($form->getValue('universities') as $univerClassifierId) {
                        $data = array(
                            'classifier_id' => $univerClassifierId,
                            'item_id'       => $profileId,
                            'type'          => HM_Classifier_Link_LinkModel::TYPE_PROFILE_EDUCATION_UNIVERSITIES
                        );
                        $this->getService('ClassifierLink')->insert($data);
                    }
                }

                 // ВУЗы
                 if ( $form->getValue('universities') ) {
                     foreach ($form->getValue('universities') as $univerClassifierId) {
                         $data = array(
                             'classifier_id' => $univerClassifierId,
                             'item_id'       => $profileId,
                             'type'          => HM_Classifier_Link_LinkModel::TYPE_PROFILE_EDUCATION_UNIVERSITIES
                         );
                         $this->getService('ClassifierLink')->insert($data);
                     }
                 }

                $this->_flashMessenger->addMessage(_('Обновление формальных требований успешно выполнено.'));
                $this->_redirector->gotoSimple('index', 'report', 'profile', array('profile_id' => $profileId));

            } else {
                $form->populate($this->_request->getParams());
            }

        } else {
            $profileSpec = $this->getService('AtProfile')->getProfileSpecialities($profileId, HM_Classifier_Link_LinkModel::TYPE_PROFILE_EDUCATION_SPECIALITIES);

            if ( $profileSpec ) {
                $specElement = $form->getElement('specialities');
                $specElement->setAttrib('multiOptions', $profileSpec['all']);
                $specElement->setValue(array_keys($profileSpec['profile']));
            }

            $profileUnivers = $this->getService('AtProfile')->getProfileSpecialities($this->_getParam('profile_id', 0), HM_Classifier_Link_LinkModel::TYPE_PROFILE_EDUCATION_UNIVERSITIES);

            if ( $profileUnivers ) {
                $specElement = $form->getElement('universities');
                $specElement->setAttrib('multiOptions', $profileUnivers['all']);
                $specElement->setValue(array_keys($profileUnivers['profile']));
            }

            $form->populate(array(
                //'requirements' => $this->_subject->requirements,
                'age_min'              => $this->_subject->age_min,
                'age_max'              => $this->_subject->age_max,
                'gender'               => $this->_subject->gender,
                'education'            => $this->_subject->education,
                'additional_education' => $this->_subject->additional_education,
                'academic_degree'      => $this->_subject->academic_degree,
                'experience'           => $this->_subject->experience,
                'comments'             => $this->_subject->comments,
                'trips'                => $this->_subject->trips,
                'trips_duration'       => $this->_subject->trips_duration,
                'mobility'             => $this->_subject->mobility,
            ));

        }

        $this->view->form = $form;
    }

    public function skillsAction()
    {
        $this->view->setSubHeader(_('Требования по профстандартам'));
        $form      = new HM_Form_Skills();
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                $values = $form->getValues();
                $profileId = $values['profile_id'];
                $existFunctions = $this->getService('AtProfileFunction')->fetchAll(array('profile_id = ?'=>$values['profile_id']))->getList('function_id');
                foreach($values['functions'] as $function_id) {
                    if($function_id<0 || isset($existFunctions[$function_id])) continue;
                    $this->getService('AtProfileFunction')->insert(array('profile_id'=>$profileId, 'function_id'=>$function_id));
                }
                foreach($existFunctions as $function_id) {
                    if(in_array($function_id, $values['functions'])) continue;
                    $this->getService('AtProfileFunction')->deleteBy(array('profile_id = ?'=>$profileId, 'function_id = ?'=>$function_id));
                }
                $this->_redirector->gotoSimple('index', 'report', 'profile', array('profile_id' => $values['profile_id']));
            }
        } else {
        }
        $this->view->form = $form;
    }

    public function processAction()
    {
    }
    
    public function editAction()
    {
        $form = new HM_Form_Profiles();
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                $values = $form->getValues();
                $values['category_id'] = $this->_subject->category_id;
                if ($res = $this->getService('AtProfile')->update($values)) {
                    $this->_flashMessenger->addMessage(_('Элемент успешно обновлён'));
                }
                $this->_redirector->gotoSimple('index', 'report', 'profile', array('profile_id' => $values['profile_id']));
            }
        } else {
            $profileId = $this->_getParam('profile_id', 0);
            $profile = $this->getService('AtProfile')->find($profileId)->current();
            $data = $profile->getData();
            $form->populate($data);
        }
        $this->view->form = $form;
    }

    public function programmRecruitAction()
    {
        $this->_setParam('programm_type', HM_Programm_ProgrammModel::TYPE_RECRUIT);
        return $this->_programm();
    }

    public function programmElearningAction()
    {
        $this->_setParam('programm_type', HM_Programm_ProgrammModel::TYPE_ELEARNING);
        return $this->_programm();
    }

    public function programmAssessmentAction()
    {
        $this->_setParam('programm_type', HM_Programm_ProgrammModel::TYPE_ASSESSMENT);
        return $this->_programm();
    }

    public function programmReserveAction()
    {
        $this->_setParam('programm_type', HM_Programm_ProgrammModel::TYPE_RESERVE);
        return $this->_programm();
    }

    public function _programm()
    {
        $programmType = $this->_getParam('programm_type', HM_Programm_ProgrammModel::TYPE_RECRUIT);
        $collection = $this->getService('Programm')->fetchAll(array(
            'programm_type = ?' => $programmType,          
            'item_type = ?' => HM_Programm_ProgrammModel::ITEM_TYPE_PROFILE,          
            'item_id = ?' => $this->_profileId,          
        ));
        
        if (count($collection)) {
            $currentProgramm = $this->getOne($collection);
        } else {
            
            // это нештатная ситуация, так не должно быть 
            // авто-создание программ для старых профилей
            $programms = $this->getService('AtProfile')->assignProgramms($this->_profile);
            if (isset($programms[$programmType])) {
                $currentProgramm = $programms[$programmType];
            }
            
            $this->getService('Lesson')->beginProctoringTransaction();
            if (count($this->_profile->positions)) {
                foreach ($this->_profile->positions as $position) {
                    foreach ($programms as $programm) {
                        $this->getService('Programm')->assignToUser($position->mid, $programm->programm_id);
                    }                    
                }
            }
            $this->getService('Lesson')->commitProctoringTransaction();
        }

        if ($currentProgramm) {
            if ($programmType == HM_Programm_ProgrammModel::TYPE_ELEARNING) {
                $this->_redirector->gotoUrl($this->view->url([
                    'module' => 'programm',
                    'controller' => 'subject',
                    'action' => 'index',
                    'profile_id' => $this->_profileId,
                    'programm_id' => $currentProgramm->programm_id,
                    'programm_type' => null,
                    'baseUrl' => '',
                ]), array('prependBase' => false));
            } else {
                $this->_redirector->gotoUrl($this->view->url([
                    'module' => 'programm',
                    'controller' => 'evaluation',
                    'action' => 'index',
                    'programm_id' => $currentProgramm->programm_id,
                    'programm_type' => null,
                    'baseUrl' => '',
                ]), array('prependBase' => false));
            }
        } else {
            $this->_flashMessenger->addMessage(array(
                'type'    => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Программа не найдена')
            ));
            $this->_redirector->gotoSimple('index', 'list', 'profile', array('programm_id' => null));
        }
    }    
}
