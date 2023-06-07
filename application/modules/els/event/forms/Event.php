<?php
class HM_Form_Event extends HM_Form
{
	public function init()
	{

        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('event');

        $eventId = $this->getParam('event_id', 0);
        if ($event = $this->getService('Event')->getOne($this->getService('Event')->find($eventId))) {
            $weight = $event->weight;
        } else {
            $weight = HM_Event_EventModel::WEIGHT_DEFAULT;
        }

        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => $this->getView()->url(array('action' => 'index', 'controller' => 'list', 'module' => 'event'), null, true)
        ));

        $this->addElement('hidden', 'event_id', array(
            'Required' => true,
            'Validators' => array('Int'),
            'Filters' => array('Int')
        ));

        $this->addElement($this->getDefaultTextElementName(), 'title', array(
            'Label' => _('Название'),
            'Required' => true,
            'Validators' => array(
                array('StringLength', 255, 1)
            ),
            'Filters' => array(
                'StripTags'
            ),
        ));

        $this->addElement($this->getDefaultSelectElementName(), 'tool', array(
            'Label' => _('Инструмент обучения'),
            'Required' => true,
            'multiOptions' => HM_Event_EventModel::getTypes(),
            'Validators' => array(
                array('StringLength', 255, 1)
            ),
            'Filters' => array(
                'StripTags'
            ),
        )
        );

        $this->addElement($this->getDefaultSelectElementName(), 'scale_id', array(
            'Label' => _('Шкала оценивания'),
            'Required' => true,
            'multiOptions' => $this->getService('Scale')->fetchAll(array('type<>?'=>-1), 'scale_id')->getList('scale_id', 'name'),
            'Validators' => array('Int'),
            'Filters' => array('Int')
        )
        );

        $this->addElement($this->getDefaultSliderElementName(), 'weight', array(
            'Label' => _('Вес'),
            'Description' => _('Вес типа занятия определяет относительный вклад занятий этого типа в итоговую оценку за курс. Возможные значения: от 0 до 10; при автоматическом вычислении итоговой оценки веса занятий нормализуются таким образом, чтобы сумма весов типов занятий, используемых на курсе, равнялась 1.'),
            'Required' => true,
            'min' => 0,
            'max' => 10,
            'step' => 1,
        )
        );

        $event = null;
        if ($eventId = $this->getParam('event_id', 0)) {
            $event = $this->getService('Event')->getOne(
                $this->getService('Event')->find($eventId)
            );
        }

        $this->addElement($this->getDefaultFileElementName(), 'icon', array(
            'Label' => _('Иконка'),
            'Destination' => Zend_Registry::get('config')->path->upload->tmp,
            'Required' => false,
            'Description' => _('Для загрузки использовать файлы форматов: jpg, jpeg, png, gif. Максимальный размер файла &ndash; 10 Mb'),
            'Filters' => array('StripTags'),
            'file_size_limit' => 10485760,
            'file_types' => '*.jpg;*.png;*.gif;*.jpeg',
            'file_upload_limit' => 1,
            'subject' => $event,
            'preview_url' => $event ? $event->getIcon() : ''
        ));

        $icon = $this->getElement('icon');
        $icon->addDecorator('SubjectImage')
               ->addValidator('FilesSize', true, array(
                       'max' => '10MB'
                   )
               )
                ->addValidator('Extension', true, 'jpg,png,gif,jpeg')
                ->setMaxFileSize(10485760);

        $this->addDisplayGroup(
            array(
                'cancelUrl',
                'event_id',
                'title',
                'tool',
                'scale_id',
                'weight',
                'icon'
            ),
            'eventGroup',
            array('legend' => _('Тип занятия'))
        );


		$this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));


        parent::init(); // required!
	}

    public function getElementDecorators($alias, $first = 'ViewHelper') {
        if ($alias == 'icon') {
            $decorators = parent::getElementDecorators($alias, 'SubjectImage');
            array_unshift($decorators, 'ViewHelper');
            return $decorators;
        }
        return parent::getElementDecorators($alias, $first);
    }

}