<?php
class HM_Role_Graduated_Import_Manager
{

    protected $_peopleIds = array();
    protected $_peopleIdsNotFound = array();
    protected $_findPeopleSelect = null;


    protected $_subjectIds = array();
    protected $_subjectIdsNotFound = array();
    protected $_findSubjectSelect = null;



    protected $_inserts = array();
    protected $_updates = array();
    protected $_deletes = array();
    protected $_notProcessed = array();

    protected $_subjectCache = array();

    const CACHE_NAME = 'HM_Role_Graduated_Import_Manager';

    private $_restoredFromCache = false;



    protected function getSubject($subjectId)
    {
        if (! isset($this->_subjectCache[$subjectId])) {
            $this->_subjectCache[$subjectId] = $this->getService('Subject')->getOne($this->getService('Subject')->find($subjectId));
        }
        return $this->_subjectCache[$subjectId];
    }

    public function getService($name)
    {
        return Zend_Registry::get('serviceContainer')->getService($name);
    }

    public function getInsertsCount()
    {
        return count($this->_inserts);
    }

    public function getUpdatesCount()
    {
        return count($this->_updates);
    }

    public function getDeletesCount()
    {
        return count($this->_deletes);
    }


    public function getNotProcessedCount()
    {
        return count($this->_notProcessed);
    }

    public function getCount()
    {
        return $this->getInsertsCount() + $this->getUpdatesCount();
    }

    public function getInserts()
    {
        return $this->_inserts;
    }

    public function getUpdates()
    {
        return $this->_updates;
    }

    public function getDeletes()
    {
        return $this->_deletes;
    }
    public function getNotProcessed()
    {
        return $this->_notProcessed;
    }

    public function saveToCache()
    {
        return Zend_Registry::get('cache')->save(
            array(
                'peopleIds' => $this->_peopleIds,
                'peopleIdsNotFound' => $this->_peopleIdsNotFound,
                'subjectIds' => $this->_subjectIds,
                'subjectIdsNotFound' => $this->_subjectIdsNotFound,
                'inserts' => $this->_inserts,
                'updates' => $this->_updates,
                'deletes' => $this->_deletes,
                'notProcessed' => $this->_notProcessed
            ),
            self::CACHE_NAME
        );
    }

    public function clearCache()
    {
        return Zend_Registry::get('cache')->remove(self::CACHE_NAME);
    }

    public function restoreFromCache()
    {
        if ($actions = Zend_Registry::get('cache')->load(self::CACHE_NAME)) {
            $this->_peopleIds = $actions['peopleIds'];
            $this->_peopleIdsNotFound = $actions['peopleIdsNotFound'];
            $this->_subjectIds = $actions['subjectIds'];
            $this->_subjectIdsNotFound = $actions['subjectIdsNotFound'];
            $this->_inserts = $actions['inserts'];
            $this->_updates = $actions['updates'];
            $this->_deletes = $actions['deletes'];
            $this->_notProcessed = $actions['notProcessed'];
            $this->_restoredFromCache = true;
            return true;
        }

        return false;
    }



    protected function getSelectForFindPeople()
    {
        if (null === $this->_findPeopleSelect) {
            $this->_findPeopleSelect = $this->getService('Orgstructure')->getSelect()->from(
                array('p' => 'People'),
                array(
                    'MID' => 'p.MID'
                )
            )->joinLeft(
                array('so1' => 'structure_of_organ'),
                "so1.type = 1 and p.MID = so1.mid",
                array()

            )->joinLeft(
                array('so2' => 'structure_of_organ'),
                "so1.owner_soid = so2.soid",
                array()
            )->joinLeft(
                array('so3' => 'structure_of_organ'),
                "so2.owner_soid = so3.soid",
                array()
            );

        }
        return clone $this->_findPeopleSelect;
    }

    protected function getSelectForFindSubject()
    {
        if (null === $this->_findSubjectSelect) {
            $this->_findSubjectSelect = $this->getService('Orgstructure')->getSelect()->from(
                array('s' => 'subjects'),
                array(
                    'CID' => 's.subid'
                )
            );

        }
        return clone $this->_findSubjectSelect;
    }

    /**
     * поиск пользователя, однозначно соответствующего записи CSV-файла
     * @param HM_Role_Graduated_Csv_CsvModel $item
     * @return bool|mixed
     */
    protected function findPeople(HM_Role_Graduated_Csv_CsvModel $item)
    {
        // Выделяем элементы для поиска пользователя
        $userFio = trim($item->user_fio);
        $position = trim($item->position);
        $division = trim ($item->division);
        $department = trim($item->department);

        // Пытаемся найти по ФИО
        $key = $userFio;
        if (isset($this->_peopleIds[$key])) return $this->_peopleIds[$key];
        if (isset($this->_peopleIdsNotFound[$key])) return false;

        // Если не получилось по ФИО, ищем по должности и ФИО
        $key = $position . '-' . $userFio;
        if (isset($this->_peopleIds[$key])) return $this->_peopleIds[$key];
        if (isset($this->_peopleIdsNotFound[$key])) return false;

        // Если не получилось по должности и ФИО, ищем по отделу, должности и ФИО
        $key = $division . '-' . $position . '-' . $userFio;
        if (isset($this->_peopleIds[$key])) return $this->_peopleIds[$key];
        if (isset($this->_peopleIdsNotFound[$key])) return false;

        // Если не получилось по отделу, должности и ФИО, ищем по полному совпадению
        $key = $department . '-' . $division . '-' . $position . '-' . $userFio;
        if (isset($this->_peopleIds[$key])) return $this->_peopleIds[$key];
        if (isset($this->_peopleIdsNotFound[$key])) return false;



        // Не получилось найти в кэше - ищем в БД


        // 1. Ищем в БД по ФИО
        list($lastName, $firstName, $patronymic) = array_map(function($v) {return trim($v);}, explode(' ', $userFio));

        $select = $this->getSelectForFindPeople();

        if (! empty($lastName)) $select->where(
            "UPPER(p.LastName) = UPPER(?)", $lastName
        );

        if (! empty($firstName)) $select->where(
            "UPPER(p.FirstName) = UPPER(?)", $firstName
        );

        if (! empty($patronymic)) $select->where(
            "UPPER(p.Patronymic) = UPPER(?)", $patronymic
        );

        $key = $userFio;
        $rr = $select->query()->fetchAll();
        if (! count($rr)) {
            $this->_peopleIdsNotFound[$key] = 0;
            return false;
        } else {
            if (count($rr) == 1) {
                // Такой пользователь найден однозначно - запоминаем его по ФИО и ввыодим ответ
                $this->_peopleIds[$key] = $rr[0]['MID'];
                return $this->_peopleIds[$key];
            }
        }

        // 2. Ищем в БД по ФИО и должности
        $key = $position . '-' . $userFio;
        if (! empty($lastName)) $select->where(
            "UPPER(so1.name) = UPPER(?)", $position
        );
        $rr = $select->query()->fetchAll();
        if (! count($rr)) {
            $this->_peopleIdsNotFound[$key] = 0;
            return false;
        } else {
            if (count($rr) == 1) {
                // Такой пользователь найден однозначно - запоминаем его по ФИО и должность и ввыодим ответ
                $this->_peopleIds[$key] = $rr[0]['MID'];
                return $this->_peopleIds[$key];
            }
        }


        // 3. Ищем в БД по ФИО, должности и отделу
        $key = $division . '-' . $position . '-' . $userFio;
        if (! empty($lastName)) $select->where(
            "UPPER(so2.name) = UPPER(?)", $division
        );
        $rr = $select->query()->fetchAll();
        if (! count($rr)) {
            $this->_peopleIdsNotFound[$key] = 0;
            return false;
        } else {
            if (count($rr) == 1) {
                // Такой пользователь найден однозначно - запоминаем его по ФИО, должности и отделу и ввыодим ответ
                $this->_peopleIds[$key] = $rr[0]['MID'];
                return $this->_peopleIds[$key];
            }
        }



        // 3. Ищем в БД по полному совпадению
        $key = $department . '-' . $division . '-' . $position . '-' . $userFio;
        if (! empty($lastName)) $select->where(
            "UPPER(so3.name) = UPPER(?)", $department
        );
        $rr = $select->query()->fetchAll();
        if (count($rr) == 1) {
            // Такой пользователь найден однозначно - запоминаем его по ФИО, должности, отделу и подразделению и ввыодим ответ
            $this->_peopleIds[$key] = $rr[0]['MID'];
            return $this->_peopleIds[$key];
        }

        $this->_peopleIdsNotFound[$key] = 0;
        return false;
    }

    /**
     * поиск учебного курса, однозначно соответствующего записи CSV-файла
     * @param HM_Role_Graduated_Csv_CsvModel $item
     * @return bool|mixed
     */
    protected function findSubject(HM_Role_Graduated_Csv_CsvModel $item)
    {
        // Выделяем элементы для поиска учебного курса
        $subjectCode = trim($item->subject_code);

        // Пытаемся найти по ФИО
        $key = $subjectCode;
        if (isset($this->_subjectIds[$key])) return $this->_subjectIds[$key];
        if (isset($this->_subjectIdsNotFound[$key])) return false;

        // Не получилось найти в кэше - ищем в БД


        // 1. Ищем в БД по коду

        $select = $this->getSelectForFindSubject();

        if (! empty($subjectCode)) $select->where(
            "UPPER(s.code) = UPPER(?)", $subjectCode
        ); else return false;

        $key = $subjectCode;
        $rr = $select->query()->fetchAll();
        if (! count($rr)) {
            $this->_subjectIdsNotFound[$key] = 0;
            return false;
        } else {
            $item = array_shift($rr); // первый попавшийся
            $this->_subjectIds[$key] = $item['CID'];
            return $this->_subjectIds[$key];
        }

        $this->_subjectIdsNotFound[$key] = 0;
        return false;
    }


    public function init($items)
    {
        if ($this->_restoredFromCache) {
            return true;
        }

        if (count($items)) {
            foreach ($items as $item) {

                $mid = $this->findPeople($item);
                $cid = $this->findSubject($item);

                if ($mid === false || $cid === false ) {
                    $this->_notProcessed[] = $item;
                } else {
                    $testDate = $item->test_date->format('Y-m-d 00:00:00');
                    $graduated = $this->getService('Graduated')->getOne($this->getService('Graduated')->fetchAll(array("MID = ?" => $mid, "CID = ?" => $cid, "begin <= ?" => $testDate, "end >= ?" => $testDate), array('SID DESC')));
                    $item->MID = $mid;
                    $item->CID = $cid;

                    if (false === $graduated) {
                        $this->_inserts[] = $item;
                    } else {
                        $item->SID = $graduated->SID;
                        $sourceItem = clone $item;
                        $this->_updates[] = array(
                            'source' => $sourceItem,
                            'destination' => $item
                        );
                    }

                }
            }
        }

        $this->saveToCache();
    }


    protected function insert($item)
    {
        $userId     = $item->MID;
        $subjectId  = $item->CID;
        $begin      = $item->test_date->format('Y-m-d 00:00:00');
        $end        = $item->test_date->format('Y-m-d 00:00:00');

        $certificate = $this->getService('Certificates')->addCertificate($userId, $subjectId, $item->period, null, HM_Certificates_CertificatesModel::TYPE_CERTIFICATE);
        $certificateId = ($certificate)? $certificate->certificate_id : 0;

        $graduated = $this->getService('Graduated')->insert(
            array(
                'MID'            => $userId,
                'CID'            => $subjectId,
                'begin'          => $begin,
                'end'            => $end,
                'status'         => HM_Role_GraduatedModel::STATUS_SUCCESS,
                'certificate_id' => $certificateId
            ), true
        );

        $this->getService('Graduated')->update(array('SID' => $graduated->SID, 'progress' => 100));

        // Добавляем оценку за курс и период действия сертификата
        $this->saveSubjectMark($item);

    }

    protected function saveSubjectMark($item)
    {
        $userId     = $item->MID;
        $subjectId  = $item->CID;
        $mark = -1;
        $period = $item->period;

        $markItem = $this->getService('SubjectMark')->getOne($this->getService('SubjectMark')->fetchAll(array("cid = ?" => $subjectId, "mid = ?" => $userId)));
        if (false === $markItem) {
            $data = array(
                'mid' => $userId,
                'cid' => $subjectId,
                'mark' => $mark,
                'confirmed' => 0,
                'comments' => 'Добавлено при импорте истории обучения',
                'certificate_validity_period' => $period
            );

            $this->getService('SubjectMark')->insert($data);
        } else {
            $this->getService('SubjectMark')->updateWhere(array('certificate_validity_period' => $period), array("cid = ?" => $subjectId, "mid = ?" => $userId));
        }
    }

    public function update($item)
    {
        $sid = $item->SID;
        $this->getService('Graduated')->update(array('SID' => $sid, 'end' => $item->test_date->format('Y-m-d 00:00:00')));
        // Добавляем оценку за курс и период действия сертификата
        $this->saveSubjectMark($item);
    }

    public function import()
    {

        if (count($this->_inserts)) {
            foreach ($this->_inserts as $item) {
                $this->insert($item);
            }
        }

        if (count($this->_updates)) {
            foreach ($this->_updates as $item) {
                $this->update($item['destination']);
            }
        }
    }
}