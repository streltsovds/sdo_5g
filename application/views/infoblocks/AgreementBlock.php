<?php

class HM_View_Infoblock_AgreementBlock extends HM_View_Infoblock_Abstract
{
    protected $id = 'agreement';

    public function agreementBlock($param = null)
    {
         $this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/infoblocks/agreement/style.css');

        $agreements = $processStates = array();
        $prefix = HM_Process_Type_Programm_AgrerementClaimantsModel::getStatePrefix();
        $user = $this->getService('User')->getCurrentUser();
        $userPositions = $this->getService('Orgstructure')->fetchAll(array('mid = ?' => $user->MID, 'blocked = ?' => 0));
        
        if ($isDean = $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_DEAN)) {
            
            $agreements = $this->getService('Agreement')->fetchAllDependence('ProgrammEvent', array('agreement_type = ?' => HM_Agreement_AgreementModel::AGREEMENT_TYPE_DEAN));
            
        } elseif ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR)) {
             
            $agreements = $this->getService('Agreement')->fetchAllDependence('ProgrammEvent', array(
                'agreement_type IN (?)' => array(
                    HM_Agreement_AgreementModel::AGREEMENT_TYPE_SUPERVISOR,
                    HM_Agreement_AgreementModel::AGREEMENT_TYPE_SUPERSUPERVISOR,
            )));
            
            $directDescendants = $subDescendants = array();
            if (count($userPositions)) {
                foreach ($userPositions as $position) {
                    if (count($collection = $this->getService('Orgstructure')->fetchAllDependence('Descendant', array(
                        'owner_soid = ?' => $position->owner_soid,
                        'soid != ?' => $position->soid   
                    )))) {
                        foreach ($collection as $item) {
                            if ($item->type == HM_Orgstructure_OrgstructureModel::TYPE_POSITION) {
                                $directDescendants[$item->soid] = $item->soid;
                            } else {
                                if (count($item->descendants)) {
                                    foreach ($item->descendants as $subitem) {
                                        if ($subitem->type == HM_Orgstructure_OrgstructureModel::TYPE_POSITION) {
                                            $subDescendants[$subitem->soid] = $subitem->soid;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            
        } 
        if (count($userPositions)) {
            $positionIds = $userPositions->getList('soid');
            $agreementsCustom = $this->getService('Agreement')->fetchAllDependence('ProgrammEvent', array(
                'agreement_type >= 10', // hacky..(
                'position_id IN (?)' => $positionIds,
            ));
            if (count($agreementsCustom)) {
                if (count($agreements)) {
                    $agreements = array_merge($agreements->asArrayOfObjects(), $agreementsCustom->asArrayOfObjects());
                } else {
                    $agreements = $agreementsCustom;
                }
            }
        }
        
        $order = false;
        if (count($agreements)) {
            
            foreach ($agreements as $agreement) {
                if (count($agreement->programmEvent)) {
                    $event = $agreement->programmEvent->current();
                    if ($event->type != HM_Programm_Event_EventModel::EVENT_TYPE_AGREEMENT) continue;
                    $processStates[$prefix . $event->programm_event_id] = $agreement->agreement_type;
                }
            }
            
            $select = $this->getService('Claimant')->getSelect();
            $select->from(array('c' => 'claimants'), array('claimant_id' => 'c.SID'))
                ->joinInner(array('p' => 'People'), 'p.MID = c.MID', array(
                      'user_id' => 'p.MID', 
                      'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)")
                ))
                ->joinInner(array('s' => 'subjects'), 's.subid = c.CID', array(
                    'subject_name' => 's.name', 
                    'subject_begin' => 's.begin', 
                    'subject_end' => 's.end', 
                    'subject_price' => new Zend_Db_Expr("CONCAT(CONCAT(s.price, ' ') , s.price_currency)")
                ))
                ->joinInner(array('sop' => 'state_of_process'), 'sop.process_id = c.process_id and sop.process_type = ' . HM_Process_ProcessModel::PROCESS_PROGRAMM_AGREEMENT_CLAIMANTS, array('current_state'))
                ->joinLeft(array('soo' => 'structure_of_organ'), 'p.MID = soo.mid', array('position_id' => 'soo.soid', 'position_name' => 'soo.name'))
                ->where('c.status = ?', HM_Role_ClaimantModel::STATUS_NEW)
                ->where('sop.current_state IN (?)', array_keys($processStates))
                ->order('p.LastName');
            
            if ($rowset = $select->query()->fetchAll()) {
                foreach ($rowset as $row) {
                    
                    $row['programm_event_id'] = str_replace($prefix, '', $row['current_state']);
                    $dateObject = new Zend_Date($row['subject_begin'], 'yyyy-MM-dd');
                    $row['subject_begin'] = $dateObject->toString(HM_Locale_Format::getDateFormat());
                    $dateObject = new Zend_Date($row['subject_end'], 'yyyy-MM-dd');
                    $row['subject_end'] = $dateObject->toString(HM_Locale_Format::getDateFormat());
                    
                    // для руководителя нужна еще доп. проверка его ли это подчиненный
                    if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR)) {
                        if ($processStates[$row['current_state']] == HM_Agreement_AgreementModel::AGREEMENT_TYPE_SUPERVISOR) {
                            if (!in_array($row['position_id'], $directDescendants)) continue;
                        } elseif ($processStates[$row['current_state']] == HM_Agreement_AgreementModel::AGREEMENT_TYPE_SUPERSUPERVISOR) {
                            if (!in_array($row['position_id'], $subDescendants)) continue;
                        }
                    } 
                    // берём только первую для отображания в виджете
                    $order = $row;
                    $user = $this->getService('User')->getOne($this->getService('User')->find($row['user_id']));
                    break;
                }
            }
        }
        
        $this->view->order = $order;
        $this->view->user = $user;
        $this->view->isDean = $isDean;
        $content = $this->view->render('agreementBlock.tpl');

        return $this->render($content);        
    }
    
    public function getService($service)
    {
        return Zend_Registry::get('serviceContainer')->getService($service);
    }
}