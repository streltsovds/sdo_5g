<?php
class HM_Role_ModeratorService extends HM_Service_Abstract
{
	public function getProjects($userId = null)
	{
		if (null === $userId) {
			$userId = $this->getService('User')->getCurrentUserId();
		}
		$collection = $this->fetchAll(array('user_id = ?' => $userId));
		if (count($collection)) {
			$list = $collection->getList('projid','user_id');
			return $this->getService('Project')->fetchAll(array('projid IN(?)' => array_keys($list)), 'name');
		}
		return null;
	}
    public function isUserExists($projectId, $userId)
    {
        $collection = $this->fetchAll(array('project_id = ?' => $projectId, 'user_id = ?' => $userId)

        //$this->quoteInto(array('CID = ?', 'MID = ?'), array($projectId, $userId))
        );
        return count($collection);
    }
}