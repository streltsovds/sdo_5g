<?php

class HM_Subject_Resource_ResourceService extends HM_Service_Abstract
{
	/**
	 * Чистим теги в сервисе, а не в каждом контроллере
	 *
	 * @param $id
	 * @return int
	 */
	public function delete($id)
	{
		$delete = parent::delete($id);
		if ($delete) {
			$this->getService('Tag')->deleteTags($id, HM_Tag_Ref_RefModel::TYPE_RESOURCE);
		}
		return $delete;

	}

	public function link($resourceId, $subjectId, $subject = 'subject')
    {
        $result = $this->fetchRow([
            'resource_id= ?' => $resourceId,
            'subject_id = ?'  => $subjectId,
            'subject = ?' => $subject,
        ]);

        if(!$result) {
            $result = $this->insert([
                'subject_id' => $subjectId,
                'resource_id' => $resourceId,
                'subject' => $subject
            ]);

            $this->getService('Subject')->setLastUpdated($subjectId);
        }

        return $result;
    }

    public function unlink($resourceId, $subjectId, $subject = 'subject')
    {
        $this->getService('Subject')->setLastUpdated($subjectId);

        return $this->deleteBy([
            'subject_id = ?' => $subjectId,
            'resource_id = ?' => $resourceId,
            'subject = ?' => $subject,
        ]);
    }
}