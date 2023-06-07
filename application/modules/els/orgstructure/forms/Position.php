<?php
class HM_Form_Position extends HM_Form
{
    public function init()
    {
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('deparment');

        /** @var Zend_Session_Namespace $session */
        $session = new Zend_Session_Namespace('default');

        $sessOrgId = $session->orgstructure_id;

        $parent = (int) $this->getParam('parent', $sessOrgId);
        $orgId = (int) $this->getParam('org_id', 0);

        if ($orgId) {
            $item = $this->getService('Orgstructure')->getOne(
                $this->getService('Orgstructure')->find($orgId)
            );

            if ($item) {
                $parent = $item->owner_soid;
            }
        }


        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => $this->getView()->url(array(
                                                'module' => 'orgstructure',
                                                'controller' => 'list',
                                                'action' => 'index',
                                                'key' => $parent
                                           ), null, true)
        ));

        $this->addElement('hidden', 'soid', array(
            'Required' => true,
            'Filters' => array('Int'),
            'Value' => 0
        )
        );

        $this->addElement('hidden', 'owner_soid', array(
            'Required' => true,
            'Filters' => array('Int'),
            'Value' => $parent
        )
        );

        $this->addElement('hidden', 'type', array(
            'Required' => true,
            'Filters' => array('Int'),
            'Value' => HM_Orgstructure_OrgstructureModel::TYPE_POSITION
        )
        );

        $this->addElement('hidden', 'profile_id', array(
            'Required' => true,
            'Filters' => array('Int'),
        )
        );

        $this->addElement($this->getDefaultTextElementName(), 'name', array('Label' => _('Название'),
            'Required' => true,
            'Validators' => array(
                array('StringLength', 255, 1)
            ),
            'Filters' => array('StripTags')
        )
        );

        $this->addElement($this->getDefaultTextElementName(), 'code', array('Label' => _('Краткое название'),
//		'Description' => _('Краткое обозначение штатной должности'), // такие подсказки больше похожи на издевательство
            'Required' => false,
            'Validators' => array(
                array('StringLength', 255, 1)
            ),
            'Filters' => array('StripTags')
        )
        );

        $this->addElement($this->getDefaultTextAreaElementName(), 'info',
            array(
            	'Label' => _('Дополнительная информация'),
            	'Required' => false,
            	'Validators' => array(
                    array('StringLength', 1000, 1),
                ),
            	'Filters' => array('StripTags')
            )
        );

        $this->addElement($this->getDefaultCheckboxElementName(), 'is_manager', array(
                'Label' => _('Руководитель подразделения'),
                'Required' => false,
                //'Description' => _('Если флажок установлен, то д.'),
            )
        );

        $this->addElement($this->getDefaultTagsElementName(), 'mid', array(
            'required' => false,
            'Label' => _('Пользователь'),
            'Description' => _('Для поиска можно вводить любое сочетание букв из фамилии, имени и отчества'),
            'json_url' => $this->getView()->url(array('module' => 'user', 'controller' => 'ajax', 'action' => 'users-list'), null, true),
            'allowNewItems' => false,
            'newel' => false,
            'itemText' => 'key',
            'fullPreload' => false,
            'maxitems' => 1,
            'returnIdsNotText' => true,
        ));

        $this->addElement($this->getDefaultCheckboxElementName(), 'is_manager', array(
            'Label' => _('Руководитель'),
            'Description' => _('Руководители имеют расширенные полномочия в системе. При необходимости возможно наличие нескольких руководителей в одном подразделении.'),
            'Required' => false,
            'Value' => 0,
        ));


        $this->addDisplayGroup(array(
             'cancelUrl',
             'name',
             'code',
             'is_manager',
             'mid',
             'is_manager',
             'info'
        ),
            'Users1',
            array('legend' => _('Должность'))
        );

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        return parent::init();

    }
}
