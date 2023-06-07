<?php
/**
 * Форма для создания и редактирования записей в истории обучения пользователей
 *
 */
class HM_Form_StudyHistory extends HM_Form
{
    public function init()
    {
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setAttrib('enctype', 'multipart/form-data');
        $this->setName('studyHistory');

        $this->addElement('hidden',
            'cancelUrl',
            array(
                'Required' => false,
                'Value' => $_SERVER['HTTP_REFERER'],
            )
        );

        $this->addElement('hidden',
            'user_id',
            array(
                'Required' => false,
                'value' => $this->getParam('user_id', 0)
            )
        );

        $this->addElement($this->getDefaultTagsElementName(), 'subjects', array(
            'Label'       => _('Курс'),
            'Description' => _('Поиск/выбор из курсов, с указанием провайдера'),
            'json_url'    => $this->getView()->url(array('module' => 'user', 'controller' => 'index', 'action' => 'external-subjects-with-providers')),
            'Filters'     => array()
        ));

//        $this->addElement(new HM_Form_Element_FcbkComplete('subjects', array(
//                'Label'       => _('Курс'),
//                'Description' => _('Поиск/выбор из курсов, с указанием провайдера'),
//                'json_url'    => $this->getView()->url(array('module' => 'user', 'controller' => 'index', 'action' => 'external-subjects-with-providers')),
//                'Filters'     => array()
//            )
//        ));

        $this->addElement($this->getDefaultDatePickerElementName(), 'begin', array(
                'Label' => _('Дата начала'),
                'Required' => false,
                'Validators' => array(
                    array(
                        'StringLength',
                        false,
                        array('min' => 10, 'max' => 50)
                    )
                ),
                'Filters' => array('StripTags'),
                'JQueryParams' => array(
                    'showOn' => 'button',
                    'buttonImage' => "/images/icons/calendar.png",
                    'buttonImageOnly' => 'true'
                )
            )
        );

        $this->addElement($this->getDefaultDatePickerElementName(), 'end', array(
                'Label' => _('Дата окончания'),
                'Required' => false,
                'Validators' => array(
                    array(
                        'StringLength',
                        false,
                        array('min' => 10, 'max' => 50)
                    ),
                    array(
                        'DateGreaterThanFormValue',
                        false,
                        array('name' => 'begin')
                    )
                ),
                'Filters' => array('StripTags'),
                'JQueryParams' => array(
                    'showOn' => 'button',
                    'buttonImage' => "/images/icons/calendar.png",
                    'buttonImageOnly' => 'true'
                )
            )
        );

        $this->addElement($this->getDefaultDatePickerElementName(), 'certificate_date', array(
                'Label' => _('Дата выдачи сертификата'),
                'Required' => false,
                'Validators' => array(
                    array(
                        'StringLength',
                        false,
                        array('min' => 10, 'max' => 50)
                    ),
                    array(
                        'DateGreaterThanFormValue',
                        false,
                        array('name' => 'end')
                    )
                ),
                'Filters' => array('StripTags'),
                'JQueryParams' => array(
                    'showOn' => 'button',
                    'buttonImage' => "/images/icons/calendar.png",
                    'buttonImageOnly' => 'true'
                )
            )
        );

        $this->addElement($this->getDefaultTextElementName(), 'certificate_months', array(
            'Label' => _('Срок действия сертификата (число месяцев)'),
            'Required' => false,
            'Validators' => array(array('StringLength', 255, 1)),
            'Filters' => array('StripTags'))
        );

        $types = array();
        $currentId = 1;
        foreach(HM_Certificates_CertificatesModel::getCertificateTypes() as $typeId => $typeTitle) {
            $types[$typeId] = $typeTitle;
            $currentId += $typeId;
        }

        $this->addElement($this->getDefaultTextElementName(), 'certificate_number', array(
                'Label' => _('Номер документа'),
                'Required' => true,
                'Validators' => array(array('StringLength', 255, 1)),
                'Filters' => array('StripTags'))
        );

        $this->addElement($this->getDefaultSelectElementName(), 'certificate_type', array(
            'Label' => _('Вид документа'),
// @todo: исправить, сейчас 'сертификат' не проходит валидацию
//            'Validators' => array('Int', array('GreaterThan', false, array('min' => 0))),
            'Filters' => array('Int'),
            'Required' => true,
            'MultiOptions' => array($currentId => _('Выберите тип документа')) + $types,
            'Value' => $currentId
        ));

        $this->addElement('File', 'file', array(
                'Label'      => _('Загрузить сертификат'),
                'Required'   => true,
                'Filters'    => array('StripTags'),
                'Destination' => Zend_Registry::get('config')->path->upload->tmp,
                'file_size_limit' => 10485760,
                'file_types' => '*.jpg;*.png;*.gif;*.jpeg;*.svg;*.swf;*.flv;*.pdf',
                'file_upload_limit' => 1
            )
        );

        $this->addDisplayGroup(
            array(
                'cancel',
                'user_id',
                'subjects',
                'begin',
                'end',
                'certificate_date',
                'certificate_months',
                'certificate_number',
                'certificate_type',
                'file',
                'submit'
            ),
            'StudyHistory',
            array('legend' => _('Запись в истории обучения'))
        );

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!
    }
}