<?php
/**
 * Детализирует методику оценки 360 - определяет логику, специфичную для оценки коллегами
 *
 */
class HM_At_Evaluation_Method_Competence_SiblingsModel extends HM_At_Evaluation_Method_CompetenceModel implements HM_At_Evaluation_Method_Interface
{
    public function getRespondents($position, $user = nulln)
    {
        $return = array();
        if (!$position->is_manager) {
            return Zend_Registry::get('serviceContainer')->getService('Orgstructure')->fetchAll(Zend_Registry::get('serviceContainer')->getService('Orgstructure')->quoteInto(array(
                'soid != ? AND ', 
                'owner_soid = ? AND ', 
                'type = ? AND ',
                'is_manager = ?',
            ), array( 
                $position->soid,
                $position->owner_soid,
                HM_Orgstructure_OrgstructureModel::TYPE_POSITION,
                HM_Orgstructure_Position_PositionModel::ROLE_EMPLOYEE,
            )));
        } else {
            // руководители того же уровня
            $collection = Zend_Registry::get('serviceContainer')->getService('Orgstructure')->fetchAllDependenceJoinInner('Sibling', Zend_Registry::get('serviceContainer')->getService('Orgstructure')->quoteInto(array(
                'self.soid = ? AND ', 
                'Sibling.type = ? AND ', // не работает 
                'Sibling.is_manager = ?', // не работает
            ), array( 
                $position->owner_soid,
                HM_Orgstructure_OrgstructureModel::TYPE_POSITION,
                HM_Orgstructure_Position_PositionModel::ROLE_MANAGER,
            )));
            if (count($collection)) {
                $department = Zend_Registry::get('serviceContainer')->getService('Orgstructure')->getOne($collection);
                // workaround
                $siblingDepartments = array(); 
                foreach ($department->siblings as $sibling) {
                    if (($sibling->type != HM_Orgstructure_OrgstructureModel::TYPE_DEPARTMENT) || ($sibling->soid == $department->soid)) continue;
                    $siblingDepartments[] = $sibling->soid;
                }
            }
            if (count($siblingDepartments)) {
                return Zend_Registry::get('serviceContainer')->getService('Orgstructure')->fetchAll(Zend_Registry::get('serviceContainer')->getService('Orgstructure')->quoteInto(array(
                    'soid != ? AND ', 
                    'owner_soid IN (?) AND ', 
                    'type = ? AND ', 
                    'is_manager = ?', 
                ), array( 
                    $position->soid,
                    $siblingDepartments,
                    HM_Orgstructure_OrgstructureModel::TYPE_POSITION,
                    HM_Orgstructure_Position_PositionModel::ROLE_MANAGER,
                )));
            }
        }
        return $return;
    }

    public function isAllowCustomRespondent() {
        return true;
    }
    
    public function isAvailableForProgramm($programmType)
    {
        switch ($programmType) {
            case HM_Programm_ProgrammModel::TYPE_ASSESSMENT:
                return true;
            break;
        }
        return false;
    }        
}