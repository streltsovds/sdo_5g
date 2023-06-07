<?php
class HM_At_Session_Pair_Result_ResultService extends HM_Service_Abstract
{
	public function saveResults($event, $allCriteria, $allPairs, $results)
	{
		$return = false;
		if (count($results)) {
			
			$return = (count($allCriteria) == count($results));
				
			$this->deleteBy($this->quoteInto(array(
				'session_event_id = ? AND ',
				'criterion_id IN (?)'
			), array(
				$event->session_event_id,
				$allCriteria
			)));
			
			foreach ($results as $criterionId => $pairs) {
				
				$return = $return && (count($allPairs) == count($pairs)); 
				
				foreach ($pairs as $pairId => $userId) {
					$this->insert(array(
						'session_pair_id' => $pairId, 
						'session_event_id' => $event->session_event_id, 
						'criterion_id' => $criterionId, 
						'user_id' => $userId, 
					));
				}
			}
		}
		
		return $return;
	}
}