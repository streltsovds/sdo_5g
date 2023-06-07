<?php
class Programm_ClaimantsController extends HM_Controller_Action
{
    private $_programm = null;
    private $_subject = null;

    public function init()
    {
        $subjectId = $this->_getParam('subject_id', 0);
        
        if ($subjectId && count($collection = $this->getService('Subject')->findDependence('Programm', $subjectId))) {
            $this->_subject = $collection->current();

            if (count($this->_subject->programm)) {
                $this->_programm = $this->getOne($this->_subject->programm);
            } else {
                
                // авто-создание программы
                $this->_programm = $this->getService('Programm')->insert(array(
                    'programm_type' => HM_Programm_ProgrammModel::TYPE_AGREEMENT_CLAIMANTS,
                    'item_type' => HM_Programm_ProgrammModel::ITEM_TYPE_SUBJECT,
                    'item_id' => $this->_subject->subid,
                    'name' => HM_Programm_ProgrammModel::getProgrammTitle(HM_Programm_ProgrammModel::TYPE_AGREEMENT_CLAIMANTS, HM_Programm_ProgrammModel::ITEM_TYPE_SUBJECT, $this->_subject->name),
                ));
            }
            
            
            if (!$this->isAjaxRequest()) {
                $this->view->setExtended(
                    array(
                        'subjectName' => 'Subject',
                        'subjectId' => $this->_subject->subid,
                        'subjectIdParamName' => 'subject_id',
                        'subjectIdFieldName' => 'subject_id',
                        'subject' => $this->_subject
                    )
                );
                $this->view->setHeader($this->_programm->name);
            }                   
        }   

        parent::init();
    }

    public function indexAction()
    {
        $processes = $this->getService('Programm')->getActiveProcesses($this->_programm);
        $this->view->processes = $processes;
        $this->view->editable = !count($processes);
        
        // существующие элементы программы
        $events = array();
        $collection = $this->getService('ProgrammEvent')->fetchAllDependence('Agreement',
            $this->quoteInto(
                array('programm_id = ?', ' AND type = ?'),
                array($this->_programm->programm_id, HM_Programm_Event_EventModel::EVENT_TYPE_AGREEMENT)
            ), 
            'ordr'
        );
        foreach ($collection as $event) {
            if (count($event->agreement)) {
                $agreement = $event->agreement->current();
                $events[$agreement->agreement_type] = $agreement; //$this->getService('AtEvaluation')->getProgrammTitle($agreement);
            }
        }        
        
        // потенциально возможные элементы
        foreach (range(1, 6) as $i) {
            $customAgreements[10 * $i] = array(
            'name' => HM_Agreement_AgreementModel::getAgreementTitle(HM_Agreement_AgreementModel::AGREEMENT_TYPE_CUSTOM) . ' ' . $i
            );
        }

        $agreements = HM_Agreement_AgreementModel::getAgreementTitles();
        $agreementsSubitems = array();

        foreach ($agreements as $key => $agreement) {
            $agreementsSubitems[$key] = array('name' => $agreement, 'editable' => false);
        }

        $items = array(
            array(
                'name' => _('Роли'),
                'subitems' => $agreementsSubitems,
                'isCustom' => false,
            ),
            array(
                'name' => _('Должности'),
                'subitems' => $customAgreements,
                'isCustom' => true,
            ),
        );
        
//        $this->view->modeCheckbox = new Zend_Form_Element_Checkbox('mode_strict', array(
//            'Label' => _('Последовательный режим прохождения'),
//            'Description' => _('Последовательный режим прохождения'), // @todo: tooltip не отображается
//            'Value' => 1,// принудительно $this->_programm->mode_strict,
//            'disabled' => true,
//            'Decorators' => array(
//                array('ViewHelper'),
//                //array('Description', array('tag' => 'p', 'class' => 'description')),
//                array('Label', array('tag' => 'span', 'placement' => Zend_Form_Decorator_Abstract::APPEND, 'separator' => '&nbsp;')),
//                array(array('data' => 'HtmlTag'), array('tag' => 'dd', 'class'  => 'element'))
//            )
//        ));

        $this->view->options = array(
            'mode_strict' => array(
               'label' => _('Последовательный режим прохождения'),
               'value' => 1
            )
        );
        
        $this->view->page = 0;
        $this->view->items = $items;
        $this->view->events = $events;
        $this->view->programm = $this->_programm;
    }
    
    public function assignAction()
    {
        if ($this->isAjaxRequest()) {
            $this->getHelper('viewRenderer')->setNoRender();

            $keys = $this->_getParam('item_id', array());
            
            $collection = $this->getService('Agreement')->fetchAll(array(
                'item_type = ?' => HM_Agreement_AgreementModel::ITEM_TYPE_CLAIMANT,
                'item_id = ?' => $this->_subject->subid,
            ));
            $agreements = $collection->asArrayOfObjects(); 
            $agreementKeys = $agreementsIdsToDel = count($collection) ? $collection->getList('agreement_type', 'agreement_id') : array();

            if (count($agreementKeys)) {
                // на всякий случай проверяем чтобы не было лишних programmEvents
                if (count($collection = $this->getService('ProgrammEvent')->fetchAllDependence('ProgrammEventUser', $this->quoteInto(
                        array('programm_id = ?', ' AND type = ?', ' AND item_id NOT IN (?)'),
                        array($this->_programm->programm_id, HM_Programm_Event_EventModel::EVENT_TYPE_AGREEMENT, $agreementKeys)
                    )
                ))) {
                    foreach ($collection as $programmEvent) {
                        $this->getService('ProgrammEvent')->deleteEvent($programmEvent);
                    }
                }
            }
            
            if (count($keys)) {
                foreach($keys as $ordr => $agreementType) {

                    if (!array_key_exists($agreementType, $agreementKeys)) {
                        $agreement = $this->getService('Agreement')->insert(array(
                            'agreement_type' => $agreementType,
                            'item_type' => HM_Agreement_AgreementModel::ITEM_TYPE_CLAIMANT,
                            'item_id' => $this->_subject->subid,
                            'name' => HM_Agreement_AgreementModel::getAgreementTitle($agreementType),
                        ));
                    } else {
                        unset($agreementsIdsToDel[$agreementType]);
                        $agreement = $agreements[$agreementKeys[$agreementType]];
                    }                    
                    $this->getService('Programm')->assignItem(array(
                        'programm_id' => $this->_programm->programm_id, 
                        'item_id' => $agreement->agreement_id, 
                        'type' => HM_Programm_Event_EventModel::EVENT_TYPE_AGREEMENT, 
                        'name' => HM_Agreement_AgreementModel::getAgreementTitle($agreementType),
                        'ordr' => $ordr,
                    ));
                }
            }

            if (count($agreementsIdsToDel)) {
                $this->getService('Agreement')->deleteBy(
                    $this->quoteInto(
                        array('agreement_id IN (?)'),
                        array($agreementsIdsToDel)
                    )
                );
                
                if (count($collection = $this->getService('ProgrammEvent')->fetchAllDependence('ProgrammEventUser', $this->quoteInto(
                        array('programm_id = ?', ' AND type = ?', ' AND item_id IN (?)'),
                        array($this->_programm->programm_id, HM_Programm_Event_EventModel::EVENT_TYPE_AGREEMENT, $agreementsIdsToDel)
                    )
                ))) {
                    foreach ($collection as $programmEvent) {
                        $this->getService('ProgrammEvent')->deleteEvent($programmEvent);
                    }                    
                }
            }
            
            $needUpdate = false;
            $modeStrict = $this->_getParam('mode_strict');
            if ($this->_programm->mode_strict != $modeStrict) {
                $this->_programm->mode_strict = $modeStrict;
                $needUpdate = true;
            }
            // @todo: реализовать и протестировать mode_strict = 0 
            $this->_programm->mode_strict = 1;

            $this->getService('Programm')->update($this->_programm->getValues());

        } else {
            $this->_redirector->gotoSimple('index');
        }
    }
    
    public function editAction()
    {
        $this->view->headScript()->appendFile($this->view->serverUrl('/js/content-modules/fieldset.js'));
        
        $request = $this->getRequest();
        $agreementType = $this->_getParam('agreement_type');
        
        if ($this->_programm && $agreementType) {
            
            if (count($collection = $this->getService('Agreement')->fetchAllDependenceJoinInner('ProgrammEvent', $this->getService('Agreement')->quoteInto(array(
                'self.agreement_type = ? AND ', 'ProgrammEvent.programm_id = ?'
            ), array(
                $agreementType, $this->_programm->programm_id
            ))))) {
                $agreement = $collection->current();
            }

            $form = new HM_Form_Agreement();
            if ($request->isPost()) {
                if ($form->isValid($request->getParams())) {
                    
                    $agreement->name = $form->getValue('name');
                    $agreement->position_id = $form->getValue('position_id');
                    $this->getService('Agreement')->update($agreement->getValues());
                    
                    if (count($agreement->programmEvent)) {
                        $event = $agreement->programmEvent->current();
                        $event->name = $agreement->name;
                        $this->getService('ProgrammEvent')->update($event->getValues());
                    }
                    
                    $this->_flashMessenger->addMessage(_('Настройки успешно сохранены'));
                    $this->_redirector->gotoSimple('index', 'claimants', 'programm', array('subject_id' => $this->_subject->subid));
                }
            } else {
                $form->populate($agreement->getValues());
            }            
            
            $this->view->form = $form;
        }
    }
}