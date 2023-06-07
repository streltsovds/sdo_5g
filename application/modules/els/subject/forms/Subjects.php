<?php
class HM_Form_Subjects extends HM_Form {

    public function init() {
        $model = new HM_Subject_SubjectModel(null);

        $this->setMethod(Zend_Form::METHOD_POST);
        //$this->setAttrib('enctype', 'multipart/form-data');
        $this->setName('subjects');

        if (!($subjectId = $this->getParam('subject_id', 0))) {
            $subjectId = $this->getParam('subid', 0);
        }

        $base = (int) $this->getParam('base', HM_Subject_SubjectModel::BASETYPE_PRACTICE);

        /** @var HM_Subject_SubjectModel $subj */
        $subj = $subjectId ? $this->getService('Subject')->getById($subjectId) : false;

        $this->addElement($this->getDefaultStepperElementName(), 'stepper', [
            "steps" => array(
                _('Общие свойства') => ['subjectSubjects1'],
                _('Организация обучения') => ['subjectSubjects2'],
                _('Ограничение времени обучения') => ['subjectPeriodGroup'],
                _('Итоговая оценка за курс') => ['subjectResultsGroup'],
                _('Классификация') => ['classifiers'],
                _('Что развивает') => ['criteriaGroup'],
            ),
            "form" => $this
        ]);

        /*$front      = Zend_Controller_Front::getInstance();
        $request    = $front->getRequest();
        $action     = $request->getActionName();

        if ( $action == 'edit' ) {
            $this->setAttrib('onSubmit', 'if (confirm("'._('При изменении времени обучения автоматически меняются все даты занятий, которые выходят за дату окончания курса. Продолжить?').'")) return true; return false;');
        }*/

        $this->addElement('hidden',
            'cancelUrl',
            array(
                'Required' => false,
                'Value' => $this->getView()->url(
                    array(
                        'module'     => 'subject',
                        'controller' => 'list',
                        'action'     => 'index',
                        'base'       => $base
                    ),
                    NULL,
                    TRUE)
            )
        );

        $this->addElement('hidden',
            'banner_url'
        );

        $this->addElement('hidden',
            'icon_delete'
        );

        $this->addElement('hidden',
            'subid',
            array(
                'Required' => true,
                'Validators' => array('Int'),
                'Filters' => array('Int')
            )
        );

        $this->addElement('hidden',
            'base',
            array(
                'Required'   => true,
                'Validators' => array('Int'),
                'Filters'    => array('Int'),
                'Value'      => $subj ? $subj->getBaseType() : $base
            )
        );

        $this->addElement('hidden',
            'base_id',
            array(
                'Required'   => true,
                'Validators' => array('Int'),
                'Filters'    => array('Int'),
                'Value'      => $subjectId
            )
        );




// #4379 - в master'е не должно быть упоминаний SAP
//        $this->addElement($this->getDefaultTextElementName(), 'external_id', array(
//            'Label' => _('ID курса в SAP'),
//            'Required' => false,
//            'Validators' => array(
//                array('StringLength',
//                    45,
//                    1
//                )
//            ),
//            'Filters' => array('StripTags')
//        )
//        );

        $this->addElement($this->getDefaultTextElementName(), 'name', array(
            'Label' => _('Название'),
            'Description' => _('Название курса, максимальная длина - 255 символов'),
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

        $this->addElement($this->getDefaultTextElementName(), 'shortname', array(
            'Label' => _('Краткое название'),
            // #16815
			'Description' => _('Краткое название курса, максимальная длина - 24 символа'),
            'Validators' => array(
                array('StringLength',
                    false,
                    array('min' => 1, 'max' => 24)
                )
            ),
            'Filters' => array('StripTags'),
        )
        );

        $this->addElement($this->getDefaultTextElementName(), 'code', array(
            'Label' => _('Код'),
            'Required' => false,
            'Validators' => array(
                array('StringLength',
                    false,
                    array('min' => 1, 'max' => 255)
                )
            ),
            'Filters' => array('StripTags')
        )
        );
        //
        //        $collection = $this->getService('Supplier')->fetchAll(null, 'title');
        //        $providers = ($collection) ? $collection->getList('supplier_id', 'title') : array();
        //        $providers[0] = _('не указан');
        //        $this->addElement($this->getDefaultSelectElementName(), 'supplier_id', array(
        //            'Label' => _('Провайдер'),
        //            'Required' => false,
        //            'multiOptions' => $providers,
        //            'Validators' => array('Int'),
        //            'Filters' => array('Int')
        //        )
        //        );

        if (
            ($subj && !($subj->base === HM_Subject_SubjectModel::BASETYPE_SESSION) && !($base === HM_Subject_SubjectModel::BASETYPE_SESSION))
            || (!$subj && !($base === HM_Subject_SubjectModel::BASETYPE_SESSION))
        ) {
            $this->addElement('RadioGroup', 'is_fulltime', array(
                'Required'    => true,
                'form' => $this,
                'Filters' => array('Int'),
                'Label' => _('Тип'),
                'MultiOptions' => $model->getTypes(),
                'Separator' => '&nbsp;',
                'Validators' => array('Int'),
            ));
        }

        // по дефолту делаем курсы TYPE_DISTANCE
        // иначе они попадут в меню внешнее обучение, но без провайдера
        // $this->addElement('hidden', 'type', array(
        //     'Required' => false,
        //     'Value' => HM_Subject_SubjectModel::TYPE_DISTANCE
        // ));

        $this->addElement($this->getDefaultTextAreaElementName(), 'short_description', array(
            'Label' => _('Краткое описание'),
            'Required' => false,
            'class' => 'wide',
            'Validators' => array(
                array('StringLength',
                    false,
                    array('min' => 0, 'max' => 255)
                )
            ),
//            'Filters' => array('HtmlSanitizeRich'),
        ));



        $this->addElement($this->getDefaultWysiwygElementName(), 'description', array(
            'Label' => _('Описание'),
            'Required' => false,
            'class' => 'wide',
//            'Filters' => array('HtmlSanitizeRich'),
            //'toolbar' => 'hmToolbarMidi',
        ));


        $this->addElement($this->getDefaultCheckboxElementName(), 'in_banner', array(
                'Required' => false,
                'Label' => _('Отображать в виджете "Информационный слайдер"')
        )
        );

        $this->addElement($this->getDefaultFileElementName(), 'icon_banner', array(
                'Label' => _('Загрузить изображение для информационного слайдера'),
                'Description' => _('Для использования в виджете "Информационный слайдер".'),
                'Destination' => Zend_Registry::get('config')->path->upload->tmp,
                'Required' => false,
                'Filters' => array('StripTags'),
                'file_size_limit' => 10485760,
                'file_types' => '*.jpg;*.png;*.gif;*.jpeg',
                'file_upload_limit' => 1,
//                'delete_button' => true,
//                'preview_url' => '/upload/info-icons/0/15.jpg',
                'subject' => null,
            )
        );


        $regTypes = HM_Subject_SubjectModel::getRegTypes();
        $this->addElement($this->getDefaultSelectElementName(), 'reg_type',
            array(
                'Label'     => _('Тип регистрации'),
                'Required'     => false,
                'multiOptions' => $regTypes,
                'Validators'   => array('Int'),
                'Filters'      => array('Int'),
                'value'        => HM_Subject_SubjectModel::REGTYPE_SELF_ASSIGN,
            )
        );



/*        if(in_array($isBase, array(HM_Subject_SubjectModel::BASETYPE_BASE, HM_Subject_SubjectModel::BASETYPE_PRACTICE))) {
            $regTypes = HM_Subject_SubjectModel::getRegTypes();

           if(in_array($isBase, array(HM_Subject_SubjectModel::BASETYPE_BASE))){
                unset($regTypes[HM_Subject_SubjectModel::REGTYPE_FREE]);
            }

            $this->addElement($this->getDefaultSelectElementName(), 'reg_type',
                array(
                    'Label'        => _('Тип регистрации'),
                    'Required'     => false,
                    'multiOptions' => $regTypes,
                    'Validators'   => array('Int'),
                    'Filters'      => array('Int')
                )
            );

        }else{
            $this->addElement('hidden',
                'reg_type',
                array(
                    'Required' => true,
                    'Validators' => array('Int'),
                    'Filters' => array('Int'),
                    'Value' => HM_Subject_SubjectModel::REGTYPE_ASSIGN_ONLY
                )
            );
        }*/



        /*if(in_array($isBase, array(HM_Subject_SubjectModel::BASETYPE_PRACTICE, HM_Subject_SubjectModel::BASETYPE_SESSION))){
            $processes = $this->getService('Process')->fetchAll(array('type = ?' => HM_Process_ProcessModel::PROCESS_ORDER, 'process_id IN (?)' => HM_Subject_SubjectModel::getSessionProcessIds()));
        }else{
            $processes = $this->getService('Process')->fetchAll(array('type = ?' => HM_Process_ProcessModel::PROCESS_ORDER, 'process_id IN (?)' => HM_Subject_SubjectModel::getTrainingProcessIds()));
        }

        $processList = $processes->getList('process_id', 'name');

        if($isBase != HM_Subject_SubjectModel::BASETYPE_BASE){
            $processList = array(0 => _('Без согласования')) + $processList;
        }*/

        $this->addElement($this->getDefaultSelectElementName(), 'claimant_process_id',
            array(
                'Label'        => _('Тип согласования'),
                'Required'     => false,
                'multiOptions' => HM_Subject_SubjectModel::getClaimantProcessTitles(),
                'Validators'   => array('Int'),
                'Filters'      => array('Int')
            )
        );


        //if(in_array($isBase, array(HM_Subject_SubjectModel::BASETYPE_PRACTICE, HM_Subject_SubjectModel::BASETYPE_SESSION))){
        //Для сессий
        $this->addElement('RadioGroup', 'period', array(
            'Description' => 'При изменении времени обучения автоматически изменятся все даты занятий, которые вышли за окончание курса.',
            'Value' => HM_Subject_SubjectModel::PERIOD_FREE,
            //'Required' => true,
            'MultiOptions' => HM_Subject_SubjectModel::getPeriodTypes(),
            'form' => $this,
            'dependences' => array(
                                 HM_Subject_SubjectModel::PERIOD_FREE => array(),
                                 HM_Subject_SubjectModel::PERIOD_DATES => array('begin', 'end', /*'period_restriction_type'*/),
                                 HM_Subject_SubjectModel::PERIOD_FIXED => array('longtime'),
                             )
        ));

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
            'Filters' => array('StripTags')
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
            'Filters' => array('StripTags')
        )
        );

        $this->addElement($this->getDefaultTextElementName(), 'longtime', array(
            'Label' => _('Количество дней'),
            'Required' => false,
            'Validators' => array('Int'),
            'Filters' => array('Int')
            )
        );

        $this->addElement($this->getDefaultCheckboxElementName(), 'period_restriction_type', array(
            'Label' => _('Нестрогое ограничение'),
            'Description' => _('При установке флажка все фиксированные значения времени приобретают статус рекомендуемых'),
            'Required' => false,
            'Filters' => array('Int')
        ));

        $this->addElement($this->getDefaultTextElementName(), 'price', array(
            'Label' => _('Стоимость'),
            'Required' => false
            )
        );

        $this->addElement($this->getDefaultTextElementName(), 'volume', array(
            'Label' => _('Объём, час.'),
            'Required' => false
            )
        );

//        $this->addElement($this->getDefaultSelectElementName(), 'price_currency', array(
//            'Label' => _('Валюта'),
//            'Required' => false,
//            'multiOptions' => HM_Currency_CurrencyModel::getFullNameList(),
//            'Validators' => array('Alpha'),
//            'Filters' => array('Alpha'),
//            'Value'   =>HM_Currency_CurrencyModel::getDefaultCurrency()
//            )
//        );

        $this->addElement($this->getDefaultTextElementName(), 'plan_users', array(
            'Label' => _('Планируемое количество слушателей'),
            'Required' => false,
            'Validators' => array('Int'),
            'Filters' => array('Int')
        )
        );

        $icon = '';

        if (!empty($subj)) {
            $icon = $subj->getUserIcon();
        }

        $this->addElement($this->getDefaultFileElementName(), 'icon', array(
            'Label' => _('Загрузить иконку из файла'),
            'Destination' => Zend_Registry::get('config')->path->upload->tmp,
            'Required' => false,
            'Description' => _('Для использования в виджете "Витрина учебных курсов"'),
            'Filters' => array('StripTags'),
            'file_size_limit' => 10485760,
            'file_types' => '*.jpg;*.png;*.gif;*.jpeg',
            'file_upload_limit' => 1,
            'subject' => null,
            'crop' => [
                'ratio' => HM_Subject_SubjectModel::THUMB_WIDTH / HM_Subject_SubjectModel::THUMB_HEIGHT
            ],
            'preview_url' => $icon,
//            'delete_button' => true
        )
        );


        $price = $this->getElement('price');
        $price -> addValidator('Float',false,array('locale' => 'en'));

        $collection = $this->getService('Room')->fetchAll(null, 'name');
        $rooms = array('Не задано');
        if ($collection) {
            foreach ($collection->getList('rid', 'name') as $rid => $name) {
            	$rooms[$rid] = $name; // preserve keys
            }
        }

        $this->addElement($this->getDefaultSelectElementName(), 'rooms',
            array(
                'Label' => _('Место проведения'),
                'Required' => false,
                'Filters' => array(
                    'Int'
                ),
                'multiOptions' => $rooms,
//                'class' => 'multiselect',
            )
        );

        $this->addElement($this->getDefaultSelectElementName(), 'scale_id', array(
            'Label' => _('Шкала оценивания'),
            'multiOptions' => $this->getService('Scale')->fetchAll(array('scale_id IN (?)' => HM_Scale_ScaleModel::getBuiltInTypes()), 'scale_id')->getList('scale_id', 'name'),
            'Validators' => array('Int'),
            'Filters' => array('Int'),
            'value' => $subj->scale_id,
        )
        );

        $where = $this->getService('Classifier')->quoteInto(
            array(
                ' level = ?',
                ' AND type = ?'
            ),
            array(
                0,
                HM_Classifier_Type_TypeModel::BUILTIN_TYPE_STUDY_DIRECTIONS,
            )
        );

        $optGroups = array(
            0 => 'Не задано'
        );
        $classifierElements = $this->getService('Classifier')->fetchAll($where);

        foreach ($classifierElements as $classifierElement) {
            $where = $this->getService('Classifier')->quoteInto(
                array(
                    ' level = ?',
                    ' AND type = ?',
                    ' AND lft > ?',
                    ' AND rgt < ?',
                ),
                array(
                    1,
                    HM_Classifier_Type_TypeModel::BUILTIN_TYPE_STUDY_DIRECTIONS,
                    $classifierElement->lft,
                    $classifierElement->rgt,

                )
            );
            $subElements = $this->getService('Classifier')->fetchAll($where, 'name')->getList('classifier_id', 'name');

            // optgroup не нужны, классификатор может быть и одноуровневый
//            $optGroups[$classifierElement->name] = $subElements;
            $optGroups[$classifierElement->classifier_id] = $classifierElement->name;
            foreach ($subElements as $key => $value) {
                $optGroups[$key] = '- ' . $value;
            }
        }

        $this->addElement($this->getDefaultSelectElementName(), 'direction_id', array(
                'Label' => _('Направления обучения'),
                'multiOptions' => $optGroups,
                'Validators' => array('Int'),
                'Filters' => array('Int')
            )
        );

        $this->addElement($this->getDefaultCheckboxElementName(), 'auto_mark', array(
            'Label' => _('Автоматически выставлять итоговую оценку за курс'),
            'Description' => '',
            'required' => false,
            'validators' => array('Int'),
            'filters' => array('int'),
            'value' => $subj->auto_mark
        ));

        $this->addElement($this->getDefaultTextElementName(), 'threshold', array(
            'Label' => _('Порог прохождения'),
            'Description' => _('Пороговое значение - это итоговый процент за все занятия курса, при достижении которого автоматически выставляется оценка «Пройдено успешно».'),
            'validators' => array(
                'Int',
                array('GreaterThan', false, array(-1)),
                array('LessThan', false, array(101))
            ),
            'filters' => array('int'),
            'class' => 'indent',
        ));

        $collection = $this->getService('Formula')->fetchAll(
            $this->getService('Formula')->quoteInto(
                array('type = ?', ' AND  cid = 0'),
                array(HM_Formula_FormulaModel::TYPE_SUBJECT)
            ),
            'name'
        );
        $formulas = $collection->getList('id', 'name', _('Нет'));

        $this->addElement($this->getDefaultSelectElementName(), 'formula_id', array(
            'Label' => _('Формула для выставления итоговой оценки'),
            'required' => false,
            'validators' => array(
                'int',
                array('GreaterThan', false, array(-1))
            ),
            'filters' => array('int'),
            'multiOptions' => $formulas,
            'class' => 'indent',
        ));

        $this->addElement($this->getDefaultCheckboxElementName(), 'auto_graduate', array(
            'Label' => _('Автоматически переводить в прошедшие обучение'),
            'Description' => '',
            'required' => false,
            'validators' => array('Int'),
            'filters' => array('int'),
            'value' => 0
        ));


        $this->addDisplayGroup(array(
            'cancelUrl',
            'subid',
            'banner_url',
// #4379 - в master'е не должно быть упоминаний SAP
//            'external_id',
            'name',
            'shortname',
            'code',
        	'supplier_id',
            //'type',
            'icon',
            'server_icon',
            'direction_id',
            'short_description',
            'description',
            'in_banner',
            'icon_banner',
            //'price',
        ),
            'subjectSubjects1',
            array('legend' => _('Общие свойства'))
        );

// в 4.2 отказываемся от переключателя режимов; останется один хороший режим
//        $this->addDisplayGroup(
//            array(
//                'access_mode',
//            	'access_elements'
//            ),
//            'subjectModeGroup',
//            array('legend' => _('Режим прохождения курса'))
//        );

        $this->addDisplayGroup(array(
            /*'begin',
            'end',*/
            'is_fulltime',
            'reg_type',
            'claimant_process_id',
            'rooms',
            'plan_users',
            'price',
            'volume'
        ),
            'subjectSubjects2',
            array('legend' => _('Организация обучения'))
        );

        //if(in_array($isBase, array(HM_Subject_SubjectModel::BASETYPE_PRACTICE, HM_Subject_SubjectModel::BASETYPE_SESSION))){
        $this->addDisplayGroup(
            array(
                'period',
                'begin',
                'end',
                'longtime',
                'period_restriction_type',
                'auto_done',
            ),
            'subjectPeriodGroup',
            array('legend' => _('Ограничение времени обучения'))
        );

        $this->addDisplayGroup(
            array(
                'scale_id',
                'auto_mark',
                'formula_id',
                'threshold',
                'auto_graduate',
            ),
            'subjectResultsGroup',
            array('legend' => _('Итоговая оценка за курс'))
        );
        //}

        $classifierElements = $this->addClassifierElements(HM_Classifier_Link_LinkModel::TYPE_SUBJECT, $subjectId);
        $this->addClassifierDisplayGroup($classifierElements);

        /** Что развивает */

        $this->addElement($this->getDefaultMultiSelectElementName(), 'criteria', array(
                'Label' => _('Компетенции'),
                'Required' => false,
                'Validators' => array(
                    'Int'
                ),
                'Filters' => array(
                    'Int'
                ),
                'class' => 'multiselect',
                'idName' => 'criterion_id',
                'remoteUrl' => $this->getView()->url([
                    'module' => 'subject',
                    'controller' => 'list',
                    'action' => 'criteria',
                    'criterion_type' => HM_At_Criterion_CriterionModel::TYPE_CORPORATE,
                    'subject_id' => $subjectId,
                ]))
        );

        $this->addDisplayGroup(
            array(
                'criteria',
            ),
            'criteriaGroup',
            array('legend' => _('Что развивает'))
        );

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!
    }


    public function saveBannerIcon($subid = null)
    {
        $banner = $this->getElement('icon_banner');
        if (! $subid ) $subid = $this->getValue('subid');
        if (! $subid) return;


        if ($banner->isDeleted() && !$banner->isUploaded()) {
            $this->getElement('banner_url')->setValue(null);
            $this->getElement('icon_banner')->setPreviewUrl(null);
            return;
        }

        $session = new Zend_Session_Namespace('upload');
        $uploadId = $this->getRequest()->getParam('icon_banner');

        if ($uploadId == '') return;

        if (isset($session->{$uploadId})) {
            $upload = $session->{$uploadId};
            if (count($upload)) {
                $fileInfo = $upload[0];
                $src = $fileInfo['tmp_name'];

                $extension = '';
                if (preg_match('/\.([^\.]+?)$/', $src, $m) ) {
                    $extension = '.' . $m[1];
                }

                $dst = HM_Subject_SubjectModel::getIconBannerFolder($subid) . $subid . $extension;
                copy($src, $dst);
                unlink($src);
                $icon = Zend_Registry::get('config')->url->base . preg_replace('/^.+?public\//', '', $dst);
                $this->getElement('banner_url')->setValue($icon);
                $this->getElement('icon_banner')->setPreviewUrl($icon);
            }
        }



    }



    public function setDefaults_(array $defaults)
    {
        parent::setDefaults($defaults); // TODO: Change the autogenerated stub
        $this->getElement('icon_banner')->setPreviewUrl($defaults['banner_url']);
        return $this;
    }
}
