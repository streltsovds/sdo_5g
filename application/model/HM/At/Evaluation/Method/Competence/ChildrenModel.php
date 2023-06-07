<?php
/**
 * Детализирует методику оценки 360 - определяет логику, специфичную для оценки подчиненными
 *
 */
class HM_At_Evaluation_Method_Competence_ChildrenModel extends HM_At_Evaluation_Method_CompetenceModel implements HM_At_Evaluation_Method_Interface
{
    public function getRespondents($position, $user = null)
    {
        $return = array();
        if ($position->is_manager) {
            
            $directDescendants = Zend_Registry::get('serviceContainer')->getService('Orgstructure')->fetchAll(Zend_Registry::get('serviceContainer')->getService('Orgstructure')->quoteInto(array(
                'owner_soid = ? AND ', 
                'type = ? AND ',
                'is_manager = ?',
            ), array( 
                $position->owner_soid,
                HM_Orgstructure_OrgstructureModel::TYPE_POSITION,
                HM_Orgstructure_Position_PositionModel::ROLE_EMPLOYEE,
            )));
            foreach ($directDescendants as $position) {
                $return[] = $position;
            }
            
            $childDepartments = Zend_Registry::get('serviceContainer')->getService('Orgstructure')->fetchAllDependenceJoinInner('Descendant', Zend_Registry::get('serviceContainer')->getService('Orgstructure')->quoteInto(array(
                'self.owner_soid = ? AND ', 
                'self.type = ? AND ',
                'Descendant.type = ? AND ', // не работает
                'Descendant.is_manager = ?', // не работает
            ), array( 
                $position->owner_soid,
                HM_Orgstructure_OrgstructureModel::TYPE_DEPARTMENT,
                HM_Orgstructure_OrgstructureModel::TYPE_POSITION,
                HM_Orgstructure_Position_PositionModel::ROLE_MANAGER,
            )));
            foreach ($childDepartments as $department) {
                foreach ($department->descendants as $position) {
                    
                    if ($position->type != HM_Orgstructure_OrgstructureModel::TYPE_POSITION) continue;
                    if ($position->is_manager != HM_Orgstructure_Position_PositionModel::ROLE_MANAGER) continue;
                    
                    $return[] = $position;
                }
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