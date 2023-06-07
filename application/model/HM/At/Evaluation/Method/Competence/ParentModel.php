<?php
/**
 * Детализирует методику оценки 360 - определяет логику, специфичную для оценки руководителем
 *
 */
class HM_At_Evaluation_Method_Competence_ParentModel extends HM_At_Evaluation_Method_CompetenceModel implements HM_At_Evaluation_Method_Interface
{
    public function getRespondents($position, $user = null)
    {
        return HM_At_Evaluation_Method_Competence_ParentModel::_getRespondents($position, $user);
    }

    public static function _getRespondents($position, $user = null)
    {
        $return = array();
        if (!$position->is_manager) {
            return Zend_Registry::get('serviceContainer')->getService('Orgstructure')->fetchAll(Zend_Registry::get('serviceContainer')->getService('Orgstructure')->quoteInto(array(
                'owner_soid = ? AND ', 
                'type = ? AND ',
                'is_manager = ?',
            ), array( 
                $position->owner_soid,
                HM_Orgstructure_OrgstructureModel::TYPE_POSITION,
                HM_Orgstructure_Position_PositionModel::ROLE_MANAGER,
            )));
        } else {
            // ищем руководителей родительского подразделения
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
                foreach ($department->siblings as $sibling) {
                    if (($sibling->type != HM_Orgstructure_OrgstructureModel::TYPE_POSITION) || ($sibling->is_manager != HM_Orgstructure_Position_PositionModel::ROLE_MANAGER)) continue;
                    $return[] = $sibling;
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