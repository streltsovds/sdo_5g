<?php

class HM_Poll_PollModel extends HM_Test_Abstract_AbstractModel
{
    //Статусы
    const STATUS_UNPUBLISHED = 0;
    const STATUS_STUDYONLY   = 1;
    const STATUS_CONTENTONLY = 2;

    static public function getStatuses()
    {
        return array(
            self::STATUS_UNPUBLISHED    => _('Не опубликован'),
            self::STATUS_STUDYONLY      => _('Ограниченное использование'),
            self::STATUS_CONTENTONLY    => _('Оценка, рейтинг контента'),
        );
    }
    
    public function getTestType()
    {
        return HM_Test_TestModel::TYPE_POLL;
    }

    /**
     * Проверка прав на голосование
     * @return bool
     */
    public function accessRead()
    {
        return $this->getService()->accessRead($this->quiz_id);
    }

    /**
     * Проверка прав на просмотр результата
     * @return bool
     */
    public function accessViewResult()
    {
        return $this->getService()->accessViewResult($this->quiz_id);
    }

    /**
     * Проверка прав на просмотр результата
     * @param $result
     * @return bool
     */
    public function accessEditResult($result)
    {
        return $this->getService()->accessEditResult($this->quiz_id, $result);
    }
}