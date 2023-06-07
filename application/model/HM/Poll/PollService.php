<?php
class HM_Poll_PollService extends HM_Test_Abstract_AbstractService
{

    private $_pollsCache   = array();
    private $_pollsResults = array();
    public function delete($id)
    {
        //удаляем метки
        $this->getService('TagRef')->deleteBy($this->quoteInto(array('item_id=?',' AND item_type=?'),
                                                               array($id,HM_Tag_Ref_RefModel::TYPE_POLL)));
        return parent::delete($id);
    }

    protected function _updateData($test)
    {
        return $this->getService('Test')->updateWhere(
            array('data' => $test->data),
            $this->quoteInto(array('test_id = ?', ' AND type = ?'), array($test->test_id, HM_Test_TestModel::TYPE_POLL))
        );
    }

    public function publish($id)
    {
        $this->update(array(
            'quiz_id' => $id,
            'status' => HM_Poll_PollModel::STATUS_STUDYONLY,
        ));
    }
    
    public function unpublish($id)
    {
        $this->update(array(
            'quiz_id' => $id,
            'status' => HM_Poll_PollModel::STATUS_UNPUBLISHED,
        ));
    }

    public function getDefaults()
    {
        $user = $this->getService('User')->getCurrentUser();
        return array(
            'created' => $this->getDateTime(),
            'updated' => $this->getDateTime(),
            'created_by' => $user->MID,
            'status' => 0, //public
        );
    }

    public function copy($test, $subjectId = null)
    {
        $newTest = parent::copy($test, $subjectId);

        if ($newTest) {
            $this->getService('TagRef')->copy(HM_Tag_Ref_RefModel::TYPE_POLL, $test->quiz_id, $newTest->quiz_id);
        }
        return $newTest;
    }

    /**
     * @param $poll
     * @return bool|HM_Model_Abstract|HM_Poll_PollModel
     */
    public function getPollObject($poll)
    {
        if ($poll instanceof HM_Poll_PollModel) {
            if ( !isset($this->_pollsCache[$poll->quiz_id]) ) {
                $this->_pollsCache[$poll->quiz_id] = $poll;
            }
            $pollId = $poll->quiz_id;
        } else {
            $pollId = (int) $poll;
            if ( !isset($this->_pollsCache[$pollId]) ) {
                $this->_pollsCache[$pollId] = $this->getOne($this->find($pollId));
            }
        }

        return $this->_pollsCache[$pollId];
    }
    /**
     * Могут ли пользователю отображаться вопросы опроса для голосования
     * для этого пользователь должен иметь права, чтобы отвечать на вопросы, а опрос должен быть активен
     * @param $poll
     * @return bool
     */
    public function canViewPageRateQuestions($poll)
    {
        return $this->isActivePoll($poll) && $this->canAnswerPageRate($poll);
    }

    /**
     * Проверка активности опроса
     * @param $poll
     * @todo проверка дат активности опроса
     */
    public function isActivePoll($poll)
    {
        $poll = $this->getPollObject($poll);

        return $poll->status != HM_Poll_PollModel::STATUS_UNPUBLISHED;
    }


    /**
     * Проверка прав пользователя отвечать на опрос
     * У пользователя должна быть необходимая роль
     * и опрос не должен быть завершенным для этого пользователя
     * @param $poll
     * @return bool
     */
    public function canAnswerPageRate($poll)
    {
        $poll = $this->getPollObject($poll);

        return (!$this->isDonePageRate($poll)
                && $this->getService('Acl')
                        ->inheritsRole(
                            $this->getService('User')->getCurrentUserRole(),
                            array(HM_Role_Abstract_RoleModel::ROLE_DEAN, HM_Role_Abstract_RoleModel::ROLE_ADMIN)
                ));

    }

    /**
     * Проверка ответил ли пользователь на все вопросы опроса для объекта.
     * @param $poll
     * @param null $userId - ИД опльзователя
     * @return bool
     */
    public function isDonePageRate($poll, $userId = null)
    {
        if ( !$userId ) {
            $userId = $this->getService('User')->getCurrentUserId();
        }
        $poll = $this->getPollObject($poll);
        if ( !$poll ) {
            return false;
        }

        $pollId = $poll->quiz_id;
        $linkId = array_shift($this->getService('PollLink')->getCurrentLinksIds($pollId));

        $where             = $this->quoteInto(array('quiz_id = ?', ' AND link_id = ?', ' AND user_id = ?'), array($pollId, $linkId, $userId));
        $doneQuestionCount = count($this->getService('PollResult')->fetchAll($where)->getList('question_id'));
        $allQuestionCount  = (int) $poll->questions;

        return ($doneQuestionCount >= $allQuestionCount);
    }

    /**
     * Проверка прав на просмотр результата
     * @param $poll
     * @return bool
     */
    public function canViewResultPageRate($poll)
    {
        $poll = $this->getPollObject($poll);
        return  (!$this->canAnswerPageRate($poll)
                || $this->getService('Acl')
                        ->inheritsRole(
                            $this->getService('User')->getCurrentUserRole(),
                            array(HM_Role_Abstract_RoleModel::ROLE_ENDUSER)
                ));
    }
    public function canViewDetailResultPageRate($poll)
    {
        $poll = $this->getPollObject($poll);
        return  ($poll && $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(),
                    array(HM_Role_Abstract_RoleModel::ROLE_DEAN, HM_Role_Abstract_RoleModel::ROLE_ADMIN)
            ));
    }
    /**
     * Проверка прав на редактирование результата
     * @param $poll
     * @return bool
     */
    public function canEditResultPageRate($poll, $result)
    {
        $poll = $this->getPollObject($poll);
        return $this->getService('Acl')
                    ->inheritsRole(
                        $this->getService('User')->getCurrentUserRole(),
                        array(HM_Role_Abstract_RoleModel::ROLE_DEAN, HM_Role_Abstract_RoleModel::ROLE_ADMIN)
            );
    }

    /**
     * Возвращает количество пользователей принявших участие в опросе
     * @param $pollId
     * @return int
     */
    public function getRespondentsCount($pollId)
    {
        $results = $this->getService('PollResult')->getPollResults($pollId);
        return (count($results))? count($results->getList('user_id')) : 0;
    }

    /**
     * Калькулирует и возвращает итоговый балл объекта опроса
     * @param $pollId
     * @param null $linkId
     * @return int
     */
    public function getPageRank($pollId, $linkId = null)
    {
        $balMaxSum = 0;
        $weightSum = 0;
        $poll      = $this->getPollObject($pollId);

        if ( !$poll ) return 0;

        $pollResults  = $this->getService('PollResult')->getPollResults($pollId);
        if ( !count($pollResults) ) return 0;

        if ( !$linkId ) {
            $linkId = array_shift($this->getService('PollLink')->getCurrentLinksIds($pollId));
        }

        if ( !$linkId ) return 0;


        $questionList  = explode(HM_Question_QuestionModel::SEPARATOR, $poll->data);
        $answersWeight = $this->getService('Question')->getWeight($questionList);


        foreach ($pollResults as $resultItem) {

            if ( $resultItem->link_id != $linkId) continue;
            $weightSum += (isset($answersWeight[$resultItem->question_id][$resultItem->answer_id]))? $answersWeight[$resultItem->question_id][$resultItem->answer_id] : 0;
        }

        foreach ($questionList as $questionId) {
            $balMaxSum += (isset($answersWeight[$questionId]['balmax']))? $answersWeight[$questionId]['balmax'] : 0;
        }

        return ($balMaxSum > 0)? $weightSum/$balMaxSum : 0;
    }

    /**
     * То же что getPageRank, только результат нормализует по $maxValue
     * @param $pollId
     * @param null $linkId
     * @param int $maxValue
     */
    public function getNormalizePageRank($pollId, $linkId = null, $maxValue = 100)
    {
        $rank = $this->getPageRank($pollId, $linkId);
        if ( $rank < 0 ) $rank = 0;
        if ( $rank > 1)  $rank = 1;

        return round($rank * $maxValue);
    }


    /**
     * Определяет местоположение линка в таблице рейтингов по опросу
     * и возвращает его.
     * @param $pollId
     * @param $link
     * @return int
     */
    public function getPageRatePosition($linkId)
    {
        $link = $this->getService('PollLink')->getLinkObject($linkId);

        if ( !$link ) { return 0; }

        $pollId    = $link->quiz_id;
        $pollLinks = $this->getService('PollLink')->getLinksByQuizId($pollId);

        $linkRanks = array();
        foreach ( $pollLinks as $linkItem ) {
            $linkRanks[$linkItem->link_id] = $this->getNormalizePageRank($pollId, $linkItem->link_id);
        }

        natsort($linkRanks);

        $i = 0;
        foreach ($linkRanks as $rankId => $rankItem) {
            if ( $rankId == $linkId ) { return (count($linkRanks) - $i); }
            $i++;
        }

        return 0;
    }
    
    public function clearLesson($subjectId, $pollId)
    {
        $test = $this->getService('Test')->getOne(
            $this->getService('Test')->fetchAll(
                $this->getService('Test')->quoteInto(
                    array('type = ?', " AND test_id = ?", ' AND cid = ?'),
                    array(HM_Test_TestModel::TYPE_POLL, $pollId, $subjectId)
                )
            )
        );
    
        if ($test) {
            $this->getService('Lesson')->deleteBy(
                $this->getService('Lesson')->quoteInto(
                    array('typeID = ?', " AND params LIKE ?", ' AND CID = ?'),
                    array(HM_Event_EventModel::TYPE_POLL, '%module_id=' . $test->tid . ';%', $subjectId)
                ));
        }
        $this->getService('Test')->delete($test->tid);
    }
}