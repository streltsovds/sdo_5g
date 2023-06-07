<?php
class HM_Poll_Result_ResultService extends HM_Service_Abstract
{

    private $_pollsResults = array();

    /**
     * Возвращает записи с ответами пользователей на вопросы опроса
     * @param $pollId
     * @return HM_Collection
     */
    public function getPollResults($pollId)
    {
        if ( !isset($this->_pollsResults[$pollId]) ) {
            $where  = $this->quoteInto(array('quiz_id = ?'), array($pollId));
            $this->_pollsResults[$pollId] = $this->getService('PollResult')->fetchAll($where);
        }

        return $this->_pollsResults[$pollId];
    }
}