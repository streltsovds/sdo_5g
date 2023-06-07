<?php
class HM_Form_Kpi extends HM_Form {

    public function init()
    {
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('criteria');

        $this->addElement('hidden',
            'cancelUrl',
            array(
                'Required' => false,
                'Value' => $this->getView()->url(array('action' => 'index'))
            )
        );

        $this->addElement('hidden',
            'criterion_id',
            array(
                'Required' => true,
                'Validators' => array('Int'),
                'Filters' => array('Int')
            )
        );

        $this->addElement($this->getDefaultTextElementName(), 'name', array(
            'Label' => _('Критерий оценки'),
            'Required' => true,
            'Validators' => array(
                array('StringLength',
                    false,
                    array('min' => 1, 'max' => 255)
                )
            ),
            'Filters' => array('StripTags'),
            'class' => 'wide'
        )
        );

        $this->addElement($this->getDefaultTextAreaElementName(), 'description', array(
            'Label' => _('Описание'),
            'Required' => false,
            'Validators' => array(
                array('StringLength',
                    false,
                    array('max' => 255)
                )
            ),
            'Filters' => array('StripTags'),
            'class' => 'wide'
        )
        );

        $scaleValueDescriptions = array();
        $scaleId = Zend_Registry::get('serviceContainer')->getService('Option')->getOption('kpiScaleId'); // шкала оценки способов достижения в AT (в подборе не используется)
        // @todo: сортировать по 'ScaleValue.value'; не работает в MSSQL
        $scale = Zend_Registry::get('serviceContainer')->getService('Scale')->fetchAllDependenceJoinInner('ScaleValue', Zend_Registry::get('serviceContainer')->getService('Scale')->quoteInto('self.scale_id = ?', $scaleId))->current();

        if (count($scale->scaleValues)) {
            foreach ($scale->scaleValues as $value) {
                $this->addElement($this->getDefaultTextAreaElementName(), $scaleValueDescriptions[] = 'scale_value_' . $value->value_id, array(
                    'Label' => $value->text,
                    'Required' => false,
                ));
            }
        }

        $this->addDisplayGroup(array(
            'cancelUrl',
            'name',
            'description',
            'cluster_id',
        ),
            'criteria',
            array('legend' => _('Критерий оценки выполнения задач'))
        );

        if (count($scaleValueDescriptions)) {
            $this->addDisplayGroup(
                $scaleValueDescriptions,
                'descriptions',
                array('legend' => _('Описание уровней развития'))
            );
        }

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!
    }
}