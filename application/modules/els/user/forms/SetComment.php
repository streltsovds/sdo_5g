<?php
/**
 * Created by PhpStorm.
 * User: k1p
 * Date: 12/6/18
 * Time: 1:45 PM
 */

class HM_Form_SetComment extends HM_Form
{
    private $_sessionQuarterId;

    public function __construct($options = null)
    {
        $this->_sessionQuarterId = $options['sessionQuarterId'];
        parent::__construct($options);
    }

    public function init()
    {
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('set-comment');

        $this->addElement('hidden', 'cancelUrl', array(
            'required' => false,
            'value' => $this->getView()->url(array(
                'baseUrl' => 'tc',
                'module' => 'session-quarter',
                'controller' => 'student',
                'action' => 'index',
                'session_quarter_id' => $this->_sessionQuarterId,
            ), null, true)
        ));


        $this->addElement('textarea', 'comment', array(
            'Label'    => _('Комментарий'),
            'Required' => true,
            'class'    => 'wide',
            'Filters'  => array('HtmlSanitize'),
        ));

        $this->addDisplayGroup(
            array(
                'cancelUrl',
                'comment',
                'submit'
            ),
            'messageGroup',
            array('legend' => _('Ввести комментарий'))
        );

        $this->addElement('Submit', 'submit', array('Label' => _('Сохранить')));

        parent::init();
    }

}