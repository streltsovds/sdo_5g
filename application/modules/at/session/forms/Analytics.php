<?php
class HM_Form_Analytics extends HM_Form {

    public function init() 
    {
        $this->setMethod(Zend_Form::METHOD_POST)
            ->setName('user_analytics_form');
        
        if ($sessionUserId = $this->getParam('session_user_id', 0)) {
            $sessionUser = Zend_Registry::get('serviceContainer')->getService('AtSessionUser')->find($sessionUserId)->current();
        } elseif ($sessionId = $this->getParam('session_id', 0)) {
            $sessionUser = Zend_Registry::get('serviceContainer')->getService('AtSessionUser')->fetchAll(array(
                'session_id = ?' => $sessionId,
                'user_id = ?' => Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId(),
            ))->current();
        }
        
        $this->addElement('hidden', 'session_user_id', array(
            'Value' => $sessionUser->session_user_id,
        ));
        
        $this->addElement($this->getDefaultCheckboxElementName(), HM_At_Evaluation_Method_CompetenceModel::ANALYTICS_GRAPH_USER, array(
            'Label' => 'Профиль пользователя по итогам текущей оценочной сессии',
            'Value' => 1,
        ));

        $this->addElement($this->getDefaultCheckboxElementName(), HM_At_Evaluation_Method_CompetenceModel::ANALYTICS_GRAPH_PROFILE, array(
            'Label' => 'Профиль успешности должности пользователя',
            'Value' => 1,
        ));
        
//         $this->addElement('RadioGroup', HM_At_Evaluation_Method_CompetenceModel::ANALYTICS_GRAPH_USER, array(
//             'Label' => '',
//         	'Value' => HM_Form_Element_RadioGroup::CHECKED,
//             'form' => $this, // lowercase!
//             'InputType' => 'checkbox',
//             'MultiOptions' => array(
//                 HM_Form_Element_RadioGroup::CHECKED => _('Профиль пользователя по итогам текущей оценочной сессии'),
//             ),                
//             'dependences' => array()
//         ));        
        
//         $this->addElement('RadioGroup', HM_At_Evaluation_Method_CompetenceModel::ANALYTICS_GRAPH_PROFILE, array(
//             'Label' => '',
//         	'Value' => HM_Form_Element_RadioGroup::CHECKED,
//             'form' => $this, // lowercase!
//             'InputType' => 'checkbox',
//             'MultiOptions' => array(
//                 HM_Form_Element_RadioGroup::CHECKED => _('Профиль успешности должности пользователя'),
//             ),                
//             'dependences' => array()
//         ));        
        
        $sessions = Zend_Registry::get('serviceContainer')->getService('AtSession')->fetchAllJoinInner('SessionUser', Zend_Registry::get('serviceContainer')->getService('AtSessionUser')->quoteInto(array(
            'SessionUser.user_id = ? AND ',
            'SessionUser.status = ? AND ',
            'self.session_id != ?'
        ), array(
            $sessionUser->user_id,
            HM_At_Session_User_UserModel::STATUS_COMPLETED,
            $sessionUser->session_id,
        )), 'self.begin_date ASC')->getList('session_id', 'name');
        
        if (count($sessions)) {
            $sessions = array(-1 => _('Выберите элемент') . ':') + $sessions;
            // @todo: для UIMultiOption перестал работать .change()
            $this->addElement($this->getDefaultSelectElementName(), HM_At_Evaluation_Method_CompetenceModel::ANALYTICS_GRAPH_SESSIONS,
                array(
                    'Label' => _('Профили пользователя по итогам прошлых оценочных сессий'),
                    'Required' => false,
                    'multiple' => true,
//                     'jQueryParams' => array(
//                         'height' => 50
//                         //'remoteUrl' => $this->getView()->url(array('module' => 'session', 'controller' => 'report', 'action' => 'past-sessions'))
//                     ),
                    'multiOptions' => $sessions,
                    'class' => 'multiselect'
                )
            );
        }        
        
        // start preparing position_id element
        $positionIdJQueryParams = array(
//             'remoteUrl' => $this->getView()->url(array('module' => 'orgstructure', 'controller' => 'ajax', 'action' => 'tree', 'only-departments' => 1)),
            'remoteUrl' => '/orgstructure/ajax/tree',
            'onlyLeaves' => true,
        );

        if ($userId = $sessionUser->user_id) {
            $units = $this->getService('Orgstructure')->fetchAll(array('mid = ?' => $userId));
            if (count($units)) {
                $department = $units->current();
                $positionIdJQueryParams['selected'][] = [
                    "id" => $department->soid,
                    "value" => htmlspecialchars($department->name),
                    "leaf" => !(isset($department->descendants) && count($department->descendants))
                ];
                $positionIdJQueryParams['ownerId'] = $department->owner_soid;
            }
        }

         $this->addElement($this->getDefaultTreeSelectElementName(), HM_At_Evaluation_Method_CompetenceModel::ANALYTICS_GRAPH_POSITION, array(
            'Label' => _('Профили успешности других должностей'),
            'required' => false,
            'params' => $positionIdJQueryParams
        ));
        
        $this->addDisplayGroup(
            array(HM_At_Evaluation_Method_CompetenceModel::ANALYTICS_GRAPH_USER, HM_At_Evaluation_Method_CompetenceModel::ANALYTICS_GRAPH_PROFILE),
            'profiles',
            array('legend' => _('Профили для сравнения'))
        );
        $this->addDisplayGroup(
            array(HM_At_Evaluation_Method_CompetenceModel::ANALYTICS_GRAPH_SESSIONS, HM_At_Evaluation_Method_CompetenceModel::ANALYTICS_GRAPH_POSITION),
            'more_profiles',
            array('legend' => _('Дополнительно'))
        );
        
        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Просмотр')));

        parent::init(); // required!
    }
}