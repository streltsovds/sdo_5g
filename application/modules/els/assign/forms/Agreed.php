<?php

class HM_Form_Agreed extends HM_Form
{
    public function init()
    {
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('agreed');
        $postMassField = $this->getView()->postMassField;

        $postMassIds = $this->getView()->postMassIds;
        $courseIds = $this->getView()->courseIds;
        $filteredUsers = $this->getView()->filteredUsers;
        $subjectId = (int)$this->getRequest()->getParam('subject_id', 0);

        $this->addElement(
            'hidden',
            'agreed',
            [
                'required' => false,
                'value' => 1,
                'filters' => ['Int']
            ]
        );

        $this->addElement(
            'hidden',
            'all_users',
            [
                'required' => false,
                'Filters' => ['StripTags'],
                'Value' => is_array($postMassIds) ? implode(',', $postMassIds) : $postMassIds
            ]
        );

        $this->addElement(
            'hidden',
            'courseId',
            [
                'required' => false,
                'Filters' => ['StripTags'],
                'Value' => $courseIds
            ]
        );

        $this->addElement(
            'hidden',
            'filtered_users',
            [
                'required' => false,
                'Filters' => ['StripTags'],
                'Value' => is_array($filteredUsers) ? implode(',', $filteredUsers) : $filteredUsers
            ]
        );

        $this->addElement(
            'hidden',
            $postMassField,
            [
                'required' => false,
                'Filters' => ['StripTags'],
                'Value' => is_array($postMassIds) ? implode(',', $postMassIds) : $postMassIds
            ]
        );

        $this->addElement(
            $this->getDefaultSubmitElementName(),
            'all_submit',
            [
                'Label' => _('Да'),
            ]
        );

        $this->addElement(
            $this->getDefaultSubmitLinkElementName(),
            'filter_submit',
            [
                'Label' => _('Нет'),
                'url' => $this->getView()->url([
                    'module' => 'assign',
                    'controller' => 'student',
                    'action' => 'index',
                    'subject_id' => $subjectId
                ], null, true)
            ]
        );

        /*$this->addDisplayGroup(
            array(
                'agreed',
                'all_users',
                'filtered_users',
                $postMassField,
                'all_submit',
                'filter_submit'
            ),
            'agreedGroup',
            array('legend' => 'Действия')
        );*/

        parent::init();
    }
}
