<?php
class HM_State_Form_CommentForm extends HM_State_Form_AbstractForm
{
    public function init()
    {
        $this->setName('state_comment');

        parent::init();

    }

    protected function _initGroupMain()
    {
        $elements = array();

        $this->addElement($this->getDefaultTextAreaElementName(), $elements[] = 'comment', array(
            'rows' => 5,
            'Label' => 'Комментарий',
            'Required' => false,
            'Validators' => array(
                array('StringLength', 4000, 0),
            ),
            'Filters' => array(
                'StripTags'
            )
        ));

    }



    protected function _getSaveUrl()
    {
        return $this->getView()->url(array(
            'baseUrl'    => '',
            'module'     => 'state',
            'controller' => 'edit',
            'action'     => 'edit',
            'field'      => 'comment',
            'state'      => $this->_state,
            'stateId'    => $this->_stateId
        ));
    }

    public function getIconConfig()
    {
        return array(
            'title'     => _('Редактировать комментарий'),
            'name' => 'edit'
        );
    }

}