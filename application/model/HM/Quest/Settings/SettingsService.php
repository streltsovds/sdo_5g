<?php
class HM_Quest_Settings_SettingsService extends HM_Service_Abstract
{
    /**
     * @param HM_Quest_QuestModel $quest
     * @param int $scopeType
     * @param int $scopeId
     * @param bool $overwrite
     * @return HM_Quest_Settings_SettingsModel
     */
    public function copyToScope($quest, $scopeType, $scopeId, $overwrite = true)
    {
        if (!$overwrite && $quest->hasScopeSettings($scopeType, $scopeId)) {
            $quest->setScope($scopeType, $scopeId);
            return $quest->getSettings();
        }

        if ($quest->getSettings()) {
            $dataSettings = $quest->getSettings()->getValues();
        }
        else {
            $dataSettings = array();
        }

        $this->delete($quest->quest_id, $scopeType, $scopeId); // на всякий случай
    
        $settings = $this->fetchRow(array(
            'quest_id = ?' => $quest->quest_id,
            'scope_type = ?' => $scopeType,
            'scope_id = ?' => $scopeId,
        ));
    
        if (!$settings) {
            $dataSettings = array_merge($dataSettings, array(
                'quest_id' => $quest->quest_id,
                'scope_type' => $scopeType,
                'scope_id' => $scopeId,
            ));
            $settings = $this->insert($dataSettings);
        }
        return $settings;
    }

    public function copy($fromQuestId, $fromScopeType, $fromScopeId, $toQuestId, $toScopeType = null, $toScopeId = null)
    {
        $settings = $this->fetchAll(array(
            'quest_id = ?' => $fromQuestId,
            'scope_type = ?' => $fromScopeType,
            'scope_id = ?' => $fromScopeId,
        ));

        $this->deleteBy(array(
            'quest_id = ?' => $toQuestId,
            'scope_type = ?' => $toScopeType,
            'scope_id = ?' => $toScopeId,
        ));

        if (!$settings->count()) {
            return false;
        }
        $settings = $settings->current()->getData();

        $toScopeType = is_null($toScopeType) ? $fromScopeType : $toScopeType;
        $toScopeId = is_null($toScopeId) ? $fromScopeId : $toScopeId;

        $this->delete($toQuestId, $toScopeType, $toScopeId);

        $settings['quest_id'] = $toQuestId;
        $settings['scope_type'] = $toScopeType;
        $settings['scope_id'] = $toScopeId;

        $this->insert($settings);

        return true;
    }
    
    public function deleteByScope($questId, $scopeType, $scopeId)
    {
        $this->deleteBy(array(
            'quest_id = ?' => $questId,
            'scope_type = ?' => $scopeType,
            'scope_id = ?' => $scopeId,
        ));        
    }
    
    static public function detectScope(HM_Quest_QuestModel $quest)
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $lessonId = (int) $request->getParam('lesson_id');
        $meetingId = (int) $request->getParam('meeting_id');
        $subjectId = (int) $request->getParam('subject_id');

        if ($lessonId) {
            $quest->setScope(HM_Quest_QuestModel::SETTINGS_SCOPE_LESSON, $lessonId);
        } elseif ($meetingId) {
            $quest->setScope(HM_Quest_QuestModel::SETTINGS_SCOPE_MEETING, $meetingId);
        } elseif ($subjectId) {
            $quest->setScope(HM_Quest_QuestModel::SETTINGS_SCOPE_SUBJECT, $subjectId);
        } else {
            $quest->setScope(HM_Quest_QuestModel::SETTINGS_SCOPE_GLOBAL);
        }        
    }
}