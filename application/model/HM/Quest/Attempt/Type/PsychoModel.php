<?php
class HM_Quest_Attempt_Type_PsychoModel extends HM_Quest_Attempt_Type_Abstract
{
    const DEFAULT_GENDER = HM_User_Metadata_MetadataModel::GENDER_MALE;
    const DEFAULT_AGE = 20;

    /**
     * @return bool|void
     * @throws Zend_Exception
     */
    public function updateByType()
    {
        $categoryResults = [];

        /** @var HM_Quest_Category_Result_ResultService $qcrService */
        $qcrService = Zend_Registry::get('serviceContainer')->getService('QuestCategoryResult');

        $qcrService->deleteBy(['attempt_id = ?' => $this->attempt_id]);
        if (count($questionResults = Zend_Registry::get('serviceContainer')->getService('QuestQuestionResult')->fetchAll(['attempt_id = ?' => $this->attempt_id]))) {//fetchAllDependence('Category', 
            foreach ($questionResults as $questionResult) {

                $categories = array();
                if(intval($questionResult->category_id).''===$questionResult->category_id) {
                    $categories = array($questionResult->category_id);
                } else {
                    $categories = @unserialize($questionResult->category_id);
                }

                foreach($categories as $categoryId) {
                    if (!isset($categoryResults[$categoryId])) {
                        $categoryResults[$categoryId] = 0;                    
                    }
                    $categoryResults[$categoryId] += $questionResult->score_raw;
                }


            }
        }

        if(count($categoryResults)) {
            $categories = Zend_Registry::get('serviceContainer')->getService('QuestCategory')->fetchAll(['category_id IN (?)' => array_keys($categoryResults)]);
            $categories = $categories->getList('category_id', 'formula');

            foreach ($categoryResults as $categoryId => $categoryResult) {
                $result = $qcrService->getResultByScore($categories[$categoryId], $categoryResult);
                $qcrService->insert([
                    'attempt_id' => $this->attempt_id,
                    'category_id' => $categoryId,
                    'score_raw' => $categoryResult,
                    'result' => $result ? $result : '',
                ]);
            }
        }
    }
    
    // почти полностью совпадает с HM_Quest_Attempt_Type_TestModel::getReportContext()
    public function getReportContext()
    {
        $contextList = array();
        $context = Zend_Registry::get('serviceContainer')->getService('AtSessionEvent')->getEventContext($this->context_event_id);
        if (count($context)) {
                
            if (count($context['event']->criterionPersonal)) {
                $criterionPersonal = $context['event']->criterionPersonal->current();
            }         
            
            $contextList = array(
                _('Оценочная сессия') => $context['session']->name,
                _('Методика оценки') => $context['evaluation']->name,
                _('Критерий оценки') => $criterionPersonal->name,
            );
        }
        return $contextList;
    }
}