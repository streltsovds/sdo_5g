<?php
/**
 * Методика оценки "парные сравнения"
 *
 */
class HM_At_Evaluation_Method_RatingModel extends HM_At_Evaluation_EvaluationModel
{
    static public function getMethodId()
    {
        return HM_At_Evaluation_EvaluationModel::TYPE_RATING;
    }
        
    static public function getMethodName()
    {
        return _('Парные сравнения');
    }

    static public function getRelationTypes()
    {
        return array(
            self::RELATION_TYPE_PARENT => _('Парные сравнения'),
        );
    }
    
    // DEPRECATED??
    // должно соответствовать HM_At_Evaluation_Method_CompetenceModel::getRelationTypes
    static public function getTitle()
    {
        return _('Руководитель сравнивает попарно пользователей');
    }

    public function getDefaults($user)
    {
        return array(
            'name' => _('Парные сравнения')
        );
    }

    // есть ли у пользователя цели на этот период
    public function isValid($userId, $cycleId)
    {
        return true;
    }

    public function isMultiUserEvents()
    {
    	return true;
    }

    public function insertMultiUserEvent($event, $users)
    {
    	//$users = $users->asArrayOfObjects();
    	usort($users, array('HM_At_Evaluation_Method_RatingModel', '_sortByName'));
    	foreach ($users as $user) {
    		$mids[] = $user->MID; // preserve order
    	}
    	
    	for ($i = 0; $i < count($mids); $i++) {
    		for ($j = $i + 1; $j < count($mids); $j++) {
    			$data = array(
   					'session_event_id' => $event->session_event_id,
   					'first_user_id' => $mids[$i],
   					'second_user_id' => $mids[$j]
    			);
    			
    			Zend_Registry::get('serviceContainer')->getService('AtSessionPair')->insert($data);
    		}
    	}
    	return true;
    }
    
    public function _sortByName($first, $second) 
    {
    	return ($first->getName() < $second->getName()) ? -1 : 1; 
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