<?php

class HM_Form_Responsibility extends HM_Form
{

    public function init()
    {

        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('assign');

        $userId = $this->getParam('user_id', 0);

        $this->addElement(
            'hidden',
            'cancelUrl',
            array(
                'Required' => false,
                'Value' => $this->getView()->url(array(
                    'module' => 'user',
                    'controller' => 'edit',
                    'action' => 'card'
                ))
            ));

        $this->addElement('RadioGroup', 'useResponsibility', array(
            'MultiOptions' => array(
                0 => _('Не задана'),
                1 => _('Ограничить область ответственности'),
            ),
            'form' => $this,
            'dependences' => array(
                0 => array(),
                1 => array('soid'),
            )
        ));

        $positionIdJQueryParams = array(
            'remoteUrl' => $this->getView()->url(array('module' => 'orgstructure', 'controller' => 'ajax', 'action' => 'tree', 'only-departments' => 1))
        );

        if ($userId) {
            if (count($responsibility = $this->getService('Responsibility')->get($userId, HM_Responsibility_ResponsibilityModel::TYPE_STRUCTURE))) {
                $soid = array_shift($responsibility);
                if ($collection = $this->getService('Orgstructure')->find($soid)) {
                    $department = $collection->current();
                    $positionIdJQueryParams['selected'][] = [
                        "id" => $department->soid,
                        "value" => htmlspecialchars($department->name),
                        "leaf" => !(isset($department->descendants) && count($department->descendants))
                    ];
                    $positionIdJQueryParams['ownerId'] = $department->owner_soid;
                }
            }
        }

        $this->addElement($this->getDefaultTreeSelectElementName(), 'soid', array(
            'Label' => _('Подразделение'),
            'Required' => false,
            'validators' => array(
                'int',
                array('GreaterThan', false, array(-1))
            ),
            'filters' => array('int'),
            'params' => $positionIdJQueryParams
        ));


        $this->addDisplayGroup(
            array('useResponsibility', 'soid'),
            'group',
            array('legend' => _('Оргструктура'))
        );

        $ot = $this->getRequest()->getParam('ot');
        $dean = $this->getRequest()->getParam('dean');
        $supervisor = $this->getRequest()->getParam('supervisor');

        if ($ot || $dean || $supervisor) {
            $subjectsType = $ot ? HM_Responsibility_ResponsibilityModel::TYPE_SUBJECT_OT : HM_Responsibility_ResponsibilityModel::TYPE_SUBJECT;
            $radioGroupMulti = array(
                0 => _('Не ограничен'),
                $subjectsType => _('Ограничение по курсам'),
                HM_Responsibility_ResponsibilityModel::TYPE_GROUP => _('Ограничение по учебным группам'),
                HM_Responsibility_ResponsibilityModel::TYPE_PROGRAMM => _('Ограничение по учебным программам')
            );

            $radioDependence = array(
                $subjectsType => array('subjects'),
                HM_Responsibility_ResponsibilityModel::TYPE_GROUP => array('groups'),
                HM_Responsibility_ResponsibilityModel::TYPE_PROGRAMM => array('programms')
            );

            $this->addElement('RadioGroup', 'limited', array(
                'Label' => '',
                'MultiOptions' => $radioGroupMulti,
                'form' => $this,
                'dependences' => $radioDependence
            ));

            $role = '';
            $where = array(
                'type <> ?' => HM_Tc_Subject_SubjectModel::TYPE_FULLTIME
            );

            if ($ot) {
                $role = 'ot';
                $where['is_labor_safety =  ?'] = 1;
            } elseif ($dean) {
                $role = 'dean';
                $where['is_labor_safety != ?'] = 1;
            } elseif ($supervisor) {
                $role = 'supervisor';
                $where['is_labor_safety != ?'] = 1;
            }

            $this->addElement(
                'hidden',
                'role',
                array(
                    'Required' => false,
                    'value' => $role
                )
            );

            $this->addElement($this->getDefaultMultiSelectElementName(), 'subjects', array(
                    'Label' => '',
                    'Required' => false,
                    'remoteUrl' => $this->getView()->url(array('module' => 'subject', 'controller' => 'index', 'action' => 'subjects-list', 'ot' => $ot, 'dean' => $dean)),
                    'multiOptions' => [],
                )
            );

            $this->addElement(
                $this->getDefaultMultiSelectElementName(), 'groups',
                array(
                    'Label' => '',
                    'Required' => false,
                    'remoteUrl' => $this->getView()->url(array('module' => 'subject', 'controller' => 'index', 'action' => 'groups-list')),
                    'multiOptions' => [],
                ));

            $this->addElement(
                $this->getDefaultMultiSelectElementName(), 'programms',
                array(
                    'Label' => '',
                    'Required' => false,
                    'remoteUrl' => $this->getView()->url(array('module' => 'subject', 'controller' => 'index', 'action' => 'programms-list')),
                    'multiOptions' => [],
                ));

            $fieldsGroup = array(
                'limited',
                'cancelUrl',
                'user_id',
                'subjects',
                'role',
                'groups',
                'programms'
            );

            $this->addDisplayGroup($fieldsGroup,
                'groupSets',
                array(
                    'legend' => _('Доступ к учебным курсам')
                )
            );
        }

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!

    }
}