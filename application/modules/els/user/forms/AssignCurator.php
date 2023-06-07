<?php
class HM_Form_AssignCurator extends HM_Form{

	public function init(){

        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('assign');

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

        $this->addElement(
            'hidden',
            'user_id',
            array(
                'Required' => false,
                'value' => $this->getParam('user_id', 0)
            ));

        $this->addElement('RadioGroup', 'unlimited_projects', array(
               'Label' => '',
               'MultiOptions' => array(1 => _('Без ограничений'), 0 => _('Ограничение области ответственности')),
               'form' => $this,
               'dependences' => array(0 => array('projects'))
        ));


        $projects = $this->getService('Project')->fetchAll()->getList('projid', 'name');

        $this->addElement(
            'UiMultiSelect',
            'projects',
            array(
                 'Label' => '',
                 'Required' => false,
                 'multiOptions' => $projects,
                 'class' => 'multiselect'
            ));

        $this->addElement(
            'checkbox',
            'assign_new_projects',
            array(
                 'Label' => _('Автоматически включать новые конкурсы'),
                 'Required' => false,
                 'Validators' => array(),
                 'Filters' => array('StripTags')
            ));

        $this->addDisplayGroup(array(
            'unlimited_projects',
            'cancelUrl',
            'user_id',
            'projects',
            'assign_new_projects'
        ),
        'groupCourses',
        array(
            'legend' => _('Доступ к конкурсам')
        ));

        $classifierElementsPeople = $this->addClassifierElements(
            HM_Classifier_Link_LinkModel::TYPE_PEOPLE,
            $this->getParam('user_id', 0),
            'curator_responsibilities'
        );

        $classifierElements = $this->addClassifierElements(
            HM_Classifier_Link_LinkModel::TYPE_STRUCTURE,
            $this->getParam('user_id', 0),
            'curator_responsibilities'
        );

        $elementsDependency = array_merge($classifierElements, $classifierElementsPeople);


        if (is_array($elementsDependency) && count($elementsDependency)) {
            
            $this->addElement('RadioGroup', 'unlimited_classifiers', array(
                  'Label' => '',
                  'MultiOptions' => array(1 => _('Без ограничений'), 0 => _('Ограничение области ответственности')),
                  'form' => $this,
                  'dependences' => array(0 => $elementsDependency)
             ));
    
            array_unshift($elementsDependency, 'unlimited_classifiers');
            
            $this->addDisplayGroup(
                $elementsDependency,
                'classifiers',
                array('legend' => _('Доступ к учетным записям пользователей'))
            );

        }


        $this->addElement(
            'Submit',
            'submit',
            array(
                 'Label' => _('Сохранить')
            ));

        parent::init(); // required!

    }

}