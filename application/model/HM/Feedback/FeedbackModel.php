<?php
class HM_Feedback_FeedbackModel extends HM_Model_Abstract
{
    const STATUS_ASSIGNED   = 0;
    const STATUS_INPROGRESS = 1;
    const STATUS_FINISHED   = 2;
    
    const RESPONDENT_TYPE_USER    = 0;
    const RESPONDENT_TYPE_MANAGER = 1;
    
    const ASSIGN_NOW            = 1; //сейчас
    const ASSIGN_AFTER_COMPLETE = 2; //после завершения курса
    const ASSIGN_AFTER_DAYS     = 3; //через N дней после завершения курса

    const ASSIGN_ANONYMOUS_DISALLOW   = 0;
    const ASSIGN_ANONYMOUS_ALLOW      = 1;

    const NEWCOMER_FEEDBACK_1 = 1;  // обратная связь после welcome-training
    const NEWCOMER_FEEDBACK_2 = 2;  // обратная связь после всей адаптации

    const RESERVE_FEEDBACK = 3;
    const ROTATION_FEEDBACK = 4;


    protected $_primaryName = 'feedback_id';

    public static function getAssignTypes(){
        return array(
            self::ASSIGN_NOW            => _('Сейчас'),
            self::ASSIGN_AFTER_COMPLETE => _('По окончании курса'),
            self::ASSIGN_AFTER_DAYS     => _('Через N дней после окончания курса')
        );
    }

    public static function getRespondentTypes()
    {
        return array(
            self::RESPONDENT_TYPE_USER    => _('Пользователь'),
            self::RESPONDENT_TYPE_MANAGER => _('Руководитель')
        );
    }

    static public function getHardcodeEditIds() {
        return array(
            self::NEWCOMER_FEEDBACK_1,
            self::NEWCOMER_FEEDBACK_2,
            self::RESERVE_FEEDBACK,
        );
    }

    static public function getHardcodeDeleteIds() {

        return array(
            self::NEWCOMER_FEEDBACK_1,
            self::NEWCOMER_FEEDBACK_2,
        );
    }

    public static function getPolls($subjectId = false)
    {
        if ($subjectId) {

            $collection = Zend_Registry::get('serviceContainer')->getService('Quest')->fetchAllDependenceJoinInner(
                'SubjectAssign',
                Zend_Registry::get('serviceContainer')->getService('Quest')->quoteInto(
                    array('SubjectAssign.subject_id = ?', ' AND self.type = ?'),
                    array($subjectId, HM_Quest_QuestModel::TYPE_POLL)
                )
            );

            $testsExt = $collection->getList('quest_id', 'name');

            $collectionOwn = Zend_Registry::get('serviceContainer')->getService('Quest')->fetchAll(
                Zend_Registry::get('serviceContainer')->getService('Quest')->quoteInto(
                    array('subject_id = ?', ' AND type = ?'),
                    array($subjectId, HM_Quest_QuestModel::TYPE_POLL)));
            $testsOwn = $collectionOwn->getList('quest_id', 'name', _('Выберите опрос'));

            return $testsOwn + $testsExt;

        } else {

            $collection = Zend_Registry::get('serviceContainer')->getService('Quest')->fetchAll(
                Zend_Registry::get('serviceContainer')->getService('Quest')->quoteInto(
                    array('subject_id = 0 AND type = ?', ' AND status != ?'),
                    array(HM_Quest_QuestModel::TYPE_POLL, HM_Quest_QuestModel::STATUS_UNPUBLISHED)
                ), 'name');

            return $collection->getList('quest_id', 'name', _('Выберите опрос'));
        }
    }



}