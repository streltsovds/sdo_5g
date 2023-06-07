<?php
class HM_Form_SubjectActualCosts extends HM_Form {

    public function init() {
        $this->setMethod(Zend_Form::METHOD_POST);

        $this->setName('subjectActualCosts');

        $this->addElement('hidden',
            'cancelUrl',
            array(
                'Required' => false,
                'Value' => $this->getView()->url(array(
                    'module'     => 'subject-costs',
                    'controller' => 'actual-costs',
                    'action'     => 'index',
                ), null, true)
            )
        );

        $subjects  =
        $providers = array();

        $tcProviders = $this->getService('TcProvider')->fetchAll(null, array('name'));

        if ($sessionQuarterId = $this->getRequest()->getParam('session_quarter_id')) {
            $sessionQuarter = $this->getService('TcSessionQuarter')->find($sessionQuarterId)->current();

            $this->addElement('hidden', 'cycle_id', array(
                'required' => true,
                'validators' => array('Int'),
                'filters' => array('int'),
                'Value' => $sessionQuarter->cycle_id
            ));

            $select = $this->getService('Subject')->getSelect();
            $select->from(array(
                's' => 'subjects'
            ),
                array(
                    'subid'=>'s.subid',
                    'name'=>'s.name',
                    'courses' => new Zend_Db_Expr('GROUP_CONCAT(session.subid)'),
                    'count_total' => new Zend_Db_Expr('COUNT(DISTINCT tca.application_id)'),
                    'count_students' => new Zend_Db_Expr('COUNT(st.MID)'),
                    'count_graduated' => new Zend_Db_Expr('COUNT(g.MID)'),
                    'provider_id' => 'tca.provider_id'
                )
            )
                ->joinInner(array('tca' => 'tc_applications'), 'tca.subject_id = s.subid', array())
                ->joinLeft(array('session' => 'subjects'), 's.subid = session.base_id', array())
                ->joinLeft(array('st' => 'Students'), 'st.CID = session.subid AND st.application_id = tca.application_id', array())
                ->joinLeft(array('g' => 'graduated'), 'g.CID = session.subid AND g.application_id = tca.application_id', array())
                ->where('s.base != ?', HM_Subject_SubjectModel::BASETYPE_SESSION)
                ->where('tca.session_quarter_id = ?', $sessionQuarterId)
                ->group(array(
                    's.subid',
                    's.name',
                    'tca.provider_id'
                ));
            ;

            $subjectsCollection = $select->query()->fetchAll();

            foreach ($tcProviders as $item) {
                foreach ($subjectsCollection as $key => $value) {
                    if ($item->provider_id == $value['provider_id']) {
                        $subjects[$item->name][$value['subid']] = $value['name'];
                    }
                }
            }
        } else {
            $subjectsCollection = $this->getService('Subject')->fetchAll();

            foreach ($tcProviders as $item) {
                foreach ($subjectsCollection as $key => $value) {
                    if ($item->provider_id == $value->provider_id) {
                        $subjects[$item->name][$value->subid] = $value->name;
                    }
                }
            }

            $cycles = array();
            $collection = $this->getService('Cycle')->fetchAll(
                array(
                    'quarter != ?' => HM_Tc_SessionQuarter_SessionQuarterModel::WHOLE_YEAR,
                    'type = ?' => HM_Cycle_CycleModel::CYCLE_TYPE_PLANNING
                )
            );
            foreach ($collection as $item) {
                $cycles[$item->cycle_id] = $item->name;
            }

            $this->addElement($this->getDefaultSelectElementName(), 'cycle_id',
                array(
                    'Label' => _('Квартал'),
                    'Required' => true,
                    'multiOptions' => $cycles,
                    'Filters' => array('StripTags'),
                )
            );
        }

//        $this->addElement($this->getDefaultSelectElementName(), 'provider_id',
//            array(
//                'Label' => _('Провайдер'),
//                'Required' => true,
//                'multiOptions' => $providers,
//                'Filters' => array('int'),
//                'Validators' => array(
//                    'int',
//                    array('GreaterThan', false, array('min' => 0, 'messages' => array(Zend_Validate_GreaterThan::NOT_GREATER => _("Необходимо выбрать значение из списка"))))
//                ),
//            )
//        );

        $this->addElement($this->getDefaultSelectElementName(), 'subject_id',
            array(
                'Label' => _('Курс'),
                'Required' => true,
                'multiOptions' => $subjects,
                'Filters' => array('StripTags'),
            )
        );


        $this->addElement($this->getDefaultTextElementName(), 'document_number',
            array(
                'Label' => _('№ платежного документа'),
                'Required' => false,
                'Filters' => array('StripTags'),
            )
        );

        $this->addElement($this->getDefaultDatePickerElementName(), 'pay_date_document', array(
            'Label' => _('Дата оплаты по платежному документу'),
            'Required' => false,
            'Validators' => array(
                array(
                    'StringLength',
                false,
                array('min' => 10, 'max' => 50)
                ),
            ),
            'Filters' => array('StripTags'),
            'JQueryParams' => array(
                'showOn' => 'button',
                'buttonImage' => "/images/icons/calendar.png",
                'buttonImageOnly' => 'true'
            )
        ));

        $this->addElement($this->getDefaultDatePickerElementName(), 'pay_date_actual', array(
            'Label' => _('Дата оплаты по факту'),
            'Required' => false,
            'Validators' => array(
                array(
                    'StringLength',
                false,
                array('min' => 10, 'max' => 50)
                ),
            ),
            'Filters' => array('StripTags'),
            'JQueryParams' => array(
                'showOn' => 'button',
                'buttonImage' => "/images/icons/calendar.png",
                'buttonImageOnly' => 'true'
            )
        ));

        $this->addElement($this->getDefaultTextElementName(), 'pay_amount',
            array(
                'Label' => _('Сумма к оплате, руб'),
                'Required' => true,
                'Filters' => array('StripTags'),
                'Validator' => array('float'),
            )
        );


        $this->addDisplayGroup(array(
            'cancelUrl',
            'cycle_id',
//            'provider_id',
            'subject_id',
            'document_number',
            'pay_date_document',
            'pay_date_actual',
            'pay_amount',
        ),
            'main',
            array('legend' => _('Общие свойства'))
        );

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!
    }

}