<?php
class HM_At_Session_Pair_Rating_RatingService extends HM_Service_Abstract
{
	public function profileResultsByCriterion($criteriaPairResults, $users, $pairs, $criteria)
	{
		$results = array(HM_At_Session_Pair_Rating_RatingModel::TOTAL => array());
		
		$plan = count($users) - 1;
		$planTotal = (count($users) - 1) * count($criteria);
		foreach ($users as $user) {
			foreach ($criteriaPairResults as $criterionId => $pairIds) {
				if (!isset($results[$criterionId])) $results[$criterionId] = array();
				foreach ($pairIds as $pairId => $selectedUserId) {
					if (in_array($user->MID, array($pairs[$pairId]->first_user_id, $pairs[$pairId]->second_user_id))) {

						if (!isset($results[$criterionId][$user->MID])) $results[$criterionId][$user->MID] = array('plan' => $plan, 'fact' => 0, 'selected' => 0);
						if (!isset($results[HM_At_Session_Pair_Rating_RatingModel::TOTAL][$user->MID])) $results[HM_At_Session_Pair_Rating_RatingModel::TOTAL][$user->MID] = array('plan' => $planTotal, 'fact' => 0, 'selected' => 0);
						
						if ($user->MID == $selectedUserId) {
							$results[$criterionId][$user->MID]['selected']++;
							$results[HM_At_Session_Pair_Rating_RatingModel::TOTAL][$user->MID]['selected']++;
						}
						
						$results[$criterionId][$user->MID]['fact']++;
						$results[HM_At_Session_Pair_Rating_RatingModel::TOTAL][$user->MID]['fact']++;
						
						$results[$criterionId][$user->MID]['ratio'] = $results[$criterionId][$user->MID]['selected'] / $results[$criterionId][$user->MID]['fact'];
						$results[HM_At_Session_Pair_Rating_RatingModel::TOTAL][$user->MID]['ratio'] = $results[HM_At_Session_Pair_Rating_RatingModel::TOTAL][$user->MID]['selected'] / $results[HM_At_Session_Pair_Rating_RatingModel::TOTAL][$user->MID]['fact'];
					}
				}
			}
		}
		return $results;
	}
	
	public function getRatings($results)
	{
		$rating = 0;
		$prevRatio = false;
		$return = $completed = $incomplete = array();
		uasort($results, array('HM_At_Session_Pair_Rating_RatingService', '_sortByRatio'));
		foreach ($results as $userId => $result) {
			if ($result['plan'] > $result['fact']) {
				$incomplete[] = $userId;		
			} else {
				$completed[] = $userId;
			}
		}
		foreach ($completed as $userId) {
			$return[] = array(
				'rating' => ($results[$userId]['ratio'] != $prevRatio) ? ++$rating : $rating, 
				'ratio' => ceil($results[$userId]['ratio'] * 100),
				'user_id' => $userId,
			);
			$prevRatio = $results[$userId]['ratio'];
		}
		foreach ($incomplete as $userId) {
			$return[] = array(
				'rating' => HM_At_Session_Pair_Rating_RatingModel::RATING_NA, 
				'user_id' => $userId,
			);
		}
		return $return;
	}
	
	public function _sortByRatio($result1, $result2)
	{
		return ($result1['ratio'] > $result2['ratio']) ? -1 : 1;
	}
}