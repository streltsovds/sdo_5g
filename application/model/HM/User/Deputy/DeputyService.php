<?php
class HM_User_Deputy_DeputyService extends HM_Service_Abstract
{

    protected $_currDateBeg = null;
    protected $_currDateEnd = null;

    protected function setCurrDate()
    {
        $f = 'Ymd H:i:s';
        $this->_currDateBeg = new DateTime();
        $this->_currDateEnd = clone $this->_currDateBeg;
        $this->_currDateEnd->sub(new DateInterval('P1D'));

        $this->_currDateBeg = $this->_currDateBeg->format($f);
        $this->_currDateEnd = $this->_currDateEnd->format($f);
    }


    /**
     * Получить всех активный заместителей/замещаемых за указанный период для указанного пользователя
     * @param $dateBegin - Начальная дата периода замещения
     * @param $dateEnd - Конечная дата периода замещения
     * @param $userId - ID пользователя
     * @return array
     */
    public function getAllDeputy($dateBegin, $dateEnd, $userId)
    {

        if ($dateBegin instanceof DateTime) $begin = $dateBegin; else $begin = new DateTime($dateBegin);
        if ($dateEnd instanceof DateTime) $end = $dateEnd; else $end = new DateTime($dateEnd);

        $begin = $begin->format('Ymd 00:00:00');
        $end = $end->format('Ymd 00:00:00');

        $select = $this->getSelect()->from(
            array('a' => 'deputy_assign'),
            array(
                'MID' => new Zend_Db_Expr(sprintf("CASE WHEN a.user_id = %d THEN pd.MID ELSE p.MID END", $userId)),
                'I_Am_a_Deputy' => new Zend_Db_Expr(sprintf("CASE WHEN a.user_id = %d THEN 0 ELSE 0 END", $userId)),
                'FIO' => new Zend_Db_Expr(sprintf("CASE WHEN a.user_id = %d THEN CONCAT(pd.LastName, CONCAT(' ', CONCAT(pd.FirstName, CONCAT(' ', pd.Patronymic)))) ELSE  CONCAT(p.LastName, CONCAT(' ', CONCAT(p.FirstName, CONCAT(' ', p.Patronymic)))) END", $userId))
            )
        );


        $select->joinInner(
            array('pd'=> 'People'),
            "a.deputy_user_id = pd.MID",
            array()
        );

        $select->joinInner(
            array('p'=> 'People'),
            "a.user_id = p.MID",
            array()
        );

        // $userId замещаемый ИЛИ заместитель
        $select->where(
            "(a.user_id = ?", $userId
        )->orWhere(
            "a.deputy_user_id = ?)", $userId
        );

        // запись активна
        $select->where(
            "(a.not_active IS NULL"
        )->orWhere(
            "a.not_active = 0)"
        );


        if ($begin === $end ) {
            // Указана одна дата - она попадает в интервал замещения
            $select->where(
                "(a.begin_date <= ?", $begin
            )->where(
                "a.end_date >= ?)", $begin
            );
        } else {
            // диапазон дат попадает в интервал замещения
            $select->where(
                "((a.begin_date >= ?", $begin
            )->where(
                "a.begin_date <= ?)", $end
            )->orWhere(
                "(a.begin_date <= ?", $begin
            )->where(
                "a.end_date >= ?)", $end
            )->orWhere(
                "(a.end_date >= ?", $begin
            )->where(
                "a.end_date <= ?)", $end
            )->orWhere(
                "(a.begin_date >= ?", $begin
            )->where(
                "a.end_date <= ?))", $end
            );
        }



        $r = $select->query()->fetchAll();
        return $r;
    }


    /**
     * Получить всех активных заместителей/замещаемых для указанного пользователя на текущую дату
     * @param $userId - ID пользователя
     * @return array
     */
    public function getDeputyCurrDate($userId)
    {
        $date = new DateTime();
        return $this->getAllDeputy($date, $date, $userId);
    }


    protected function getCurrDateBeg()
    {
        if ($this->_currDateBeg === null) {
            $this->setCurrDate();
        }
        return $this->_currDateBeg;
    }


    protected function getCurrDateEnd()
    {
        if ($this->_currDateEnd === null) {
            $this->setCurrDate();
        }
        return $this->_currDateEnd;
    }


    // Проверяем возможность назначить указанного пользователя заместителем на указанный период времени
    public function validDeputyUser($deputyUserId, $dateBegin, $dateEnd)
    {
        $result = true;
        $where = "(not_active IS  NULL OR not_active = 0) AND deputy_user_id = " . (int) $deputyUserId . " AND  ( begin_date BETWEEN '" . $dateBegin . "' AND '" . $dateEnd . "' OR end_date BETWEEN '" . $dateBegin . "' AND '" . $dateEnd . "' OR (begin_date <= '" . $dateBegin . "' AND end_date >= '" . $dateEnd . "'))";
        $r = $this->fetchAll($where);
        if (count($r)) $result = false;
        return $result;
    }


    // Проверяем возможность назначить заместителя на указанный период времени
    public function validDeputy($dateBegin, $dateEnd)
    {
        $result = true;
        $where = "(not_active IS  NULL OR not_active = 0) AND user_id = " . $this->getService('User')->getCurrentUserId() . " AND  ( begin_date BETWEEN '" . $dateBegin . "' AND '" . $dateEnd . "' OR end_date BETWEEN '" . $dateBegin . "' AND '" . $dateEnd . "' OR (begin_date <= '" . $dateBegin . "' AND end_date >= '" . $dateEnd . "'))";
        $r = $this->fetchAll($where);
        if (count($r)) $result = false;
        return $result;
    }

    // Тест заместителя на текущую дату
    public function testDeputy()
    {
        $result = false;

        if ($userId = $this->getService('User')->getCurrentUserId()) {
            //        $where = "(not_active IS  NULL OR not_active = 0) AND (user_id = " . $this->getService('User')->getCurrentUserId() . " OR deputy_user_id = " . $this->getService('User')->getCurrentUserId() . ") AND (begin_date < '" . $this->getCurrDateBeg(). "' AND end_date > '" . $this->getCurrDateEnd() . "')";
            $where = "(not_active IS  NULL OR not_active = 0) AND (user_id = " . $userId . " OR deputy_user_id = " . $userId . ") AND (end_date > '" . $this->getCurrDateEnd() . "')";
            $r = $this->fetchAll($where);
            if (count($r)) $result = true;
            return $result;
        }
        return false;
    }


    // Кто мой заместитель (на текущую дату)?
    public function whoIsMyDeputy()
    {
//        $where = "(not_active IS  NULL OR not_active = 0) AND user_id = " . $this->getService('User')->getCurrentUserId() . " AND (begin_date < '" . $this->getCurrDateBeg(). "' AND end_date > '" . $this->getCurrDateEnd() . "')";
        $where = "(not_active IS  NULL OR not_active = 0) AND user_id = " . $this->getService('User')->getCurrentUserId() . " AND (end_date > '" . $this->getCurrDateEnd() . "')";
        $result = $this->getOne($this->fetchAllDependence('Deputy', $where));

        $r = null;
        if ($result !== false) {
            if (count($result->deputy)) {
                $r = array(
                    'user' => $this->getOne($result->deputy),
                    'dateBegin' => date_format(new DateTime($result->begin_date), 'd.m.Y'),
                    'dateEnd' => date_format(new DateTime($result->end_date), 'd.m.Y')
                );
            }
        }

        return $r;
    }

    // Кого я замещаю (на текущую дату)?
    public function whoseDeputyIam()
    {
        $currentUser = $this->getService('User')->getCurrentUserId() ? : 0;
//        $where = "(not_active IS  NULL OR not_active = 0) AND deputy_user_id = " . $this->getService('User')->getCurrentUserId() . " AND (begin_date < '" . $this->getCurrDateBeg(). "' AND end_date > '" . $this->getCurrDateEnd() . "')";
        $where = "(not_active IS  NULL OR not_active = 0) AND deputy_user_id = " . $currentUser . " AND (end_date > '" . $this->getCurrDateEnd() . "')";
        $result = $this->getOne($this->fetchAllDependence('User', $where));

        $r = null;
        if ($result !== false) {
            if (count($result->user)) {

                $currDate = new DateTime();
                $beginDate = new DateTime($result->begin_date);

                $diff = $currDate->diff($beginDate);
                $active = $diff->invert;

                $r = array(
                    'user' => $this->getOne($result->user),
                    'dateBegin' => date_format(new DateTime($result->begin_date), 'd.m.Y'),
                    'dateEnd' => date_format(new DateTime($result->end_date), 'd.m.Y'),
                    'active' => $active
                );
            }
        }

        return $r;

    }


}