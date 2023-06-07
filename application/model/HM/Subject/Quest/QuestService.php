<?php
class HM_Subject_Quest_QuestService extends HM_Service_Abstract
{
/*
    public function getCourseParent($courseId){
        $ret = array();

        $ret = $this->fetchAll(array('course_id = ?' => $courseId));
             
        return $ret;
    }
*/
    /**
     * @param HM_Quest_QuestModel $quest
     * @param $fromSubjectId
     * @param $toSubjectId
     * @return HM_Model_Abstract
     */
    public function copy($quest, $fromSubjectId, $toSubjectId)
    {
        if(!$quest->subject_id) {
                $newSubjectQuest = $this->insert(array(
                    'quest_id' => $quest->quest_id,
                    'subject_id' => $toSubjectId
                ));
        } else {
                $data = $quest->getValues(null, array('quest_id'));
                $data['subject_id'] = $toSubjectId;
                $newQuest = $this->getService('Quest')->insert($data);
                $questionsArray = $this->getService('QuestCluster')->copy($quest->quest_id, $newQuest->quest_id);
                $this->getService('QuestQuestionQuest')->copy($questionsArray, $newQuest->quest_id, $toSubjectId);
                $newSubjectQuest = $this->insert(array(
                    'quest_id' => $newQuest->quest_id,
                    'subject_id' => $toSubjectId
                ));
        }

        //копируем настройки из области видимости курса
        $subjectScope = HM_Quest_QuestModel::SETTINGS_SCOPE_SUBJECT;
        $hasSubjectSettings = $quest->hasScopeSettings(
            $subjectScope, $fromSubjectId
        );
        if ($hasSubjectSettings) {
            $this->getService('QuestSettings')->copy(
                $quest->quest_id,           $subjectScope, $fromSubjectId,
                $newSubjectQuest->quest_id, $subjectScope, $toSubjectId
            );
        }

        return $newSubjectQuest;
    }


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
			$this->getService('Tag')->deleteTags($id, HM_Tag_Ref_RefModel::TYPE_TEST);
		}
		return $delete;

	}
}