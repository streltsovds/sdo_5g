<?php
class HM_Form_StudyGroup extends HM_Form
{
	public function init()
	{

        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('study-group');

        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => $this->getView()->url(array('module' => 'study-groups', 'controller' => 'list', 'action' => 'index'), null, true)
        ));

        $this->addElement('hidden', 'group_id', array(
            'Required' => true,
            'Validators' => array('Int'),
            'Filters' => array('Int')
        ));

        $this->addElement($this->getDefaultTextElementName(), 'name', array(
            'Label' => _('Название'),
            'Required' => true,
            'Validators' => array(
                array('StringLength', 255, 1)
            ),
            'Filters' => array(
                'StripTags'
            )
        ));

        $this->addDisplayGroup(array(
                'cancelUrl',
                'group_id',
                'name'
            ),
            'studyGroup',
            array('legend' => _('Общие свойства'))
        );

        $this->addElement($this->getDefaultTagsElementName(), 'tags', array(
            'Label'       => _('Включать пользователей с метками'),
            'Description' => _('Пользователи, которым назначены данные метки, будут автоматически включены в группу'),
            'json_url'    => $this->getView()->url(array('module' => 'user', 'controller' => 'index', 'action' => 'tags')),
            'Filters'     => array()
        ));

        $this->addDisplayGroup(
            array(
                'tags'
            ),
            'studyGroup2',
            array('legend' => _('Правила автоматического назначения'))
        );

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!
	}
}