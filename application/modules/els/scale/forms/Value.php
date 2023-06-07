<?php
class HM_Form_Value extends HM_Form {

    public function init() {
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('value');

        $scaleId = $this->getParam('scaleId');
        $scale = $this->getService('Scale')->findOne($scaleId);

        $this->addElement('hidden',
            'cancelUrl',
            array(
                'Required' => false,
                'Value' => $this->getView()->url(array('controller' => 'list', 'action' => 'index'))
            )
        );

        $this->addElement('hidden',
            'scale_id',
            array(
                'Required' => true,
                'Validators' => array('Int'),
                'Filters' => array('Int')
            )
        );

        $this->addElement('hidden',
            'value_id',
            array(
                'Required' => true,
                'Validators' => array('Int'),
                'Filters' => array('Int')
            )
        );

        $this->addElement($this->getDefaultTextElementName(), 'value', array(
            'Label' => _('Значение'),
            'Description' => _('Допустимы только целочисленные значения. Можно использовать отрицательные значения, они не учитывыаются в подсчете итоговых оценок'),
            'Required' => true,
            'Validators' => array('Int'),
            'Filters' => array('StripTags'),
        )
        );

        $this->addElement($this->getDefaultTextElementName(), 'text', array(
            'Label' => _('Текстовое значение'),
            'Required' => false,
            'Filters' => array('StripTags'),
        )
        );

        if ($scale->mode == HM_Scale_ScaleModel::MODE_COMPETENCE) { // Когда область применения шкалы = "Оценка персонала"
            $this->addElement($this->getDefaultTextAreaElementName(), 'description', array(
                'Label' => _('Значение для оценки индикаторов'),
                'Required' => false,
                'Filters' => array('StripTags'),
            ));
        }

        $this->addDisplayGroup(array(
            'cancelUrl',
            'value',
            'text',
            'description',
        ),
            'value_group',
            array('legend' => _('Общие свойства'))
        );

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!
    }
}