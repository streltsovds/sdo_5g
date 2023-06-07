<?php
class HM_Role_StudentService extends HM_Service_Abstract
{

    public function insert($data, $unsetNull = true)
    {
        if (!isset($data['time_registered'])) {
            $data['time_registered'] = $this->getDateTime();
        }

        return parent::insert($data, $unsetNull);
    }

    public function isUserExists($subjectId, $userId)
    {
        $collection = $this->fetchAll(array('CID = ?' => $subjectId, 'MID = ?' => $userId));
        return count($collection);
    }
    
    /**
     * Получить список ID всех пользователей предмета по ID предмета
     * 
     * @param numeric $subjectId
     * @return array
     */
    public function getUsersIds($subjectId)
    {
        $collection = $this->fetchAll(array('CID = ?' => $subjectId));
        return $collection->getList('MID');
    }

    public function getSubjects($userId = null)
    {
        if (!$userId) $userId = $this->getService('User')->getCurrentUserId();
        $collection = $this->fetchAll(array('MID = ?' => $userId));
        $list = $collection->getList('CID','MID');
        if (!count($list)) {
            $list = array(0 => 0);
        }
        return $this->getService('Subject')->fetchAll(array('subid IN (?)' => array_keys($list)), 'name');
        
        /*return $this->getService('Subject')->fetchAllDependenceJoinInner(
            'Student',
            $this->quoteInto('Student.MID = ?', $userId),
            'self.name'
        );*/
        
    }
    
    /**
     *  метод принимает mid дубликата и mid уникальной записи
     *  проверяем совпадают ли курсы у mid дубликат в таблице
     *  student-если да то просто удаляем эту запись, не нарушая 
     *  целостность данных в таблтитце.Затем добавляем все
     *  курсы дубликата-dublicMid уникальному-unicMid
     *  @param integer unicMid - MID уникального пользователя
     *  @param integer dublicMid - MID пользователя-дубликата
     *  @return boolean type 
     *  @author GlazyrinAE 
     */
    public function updateUnic($unicMid, $dublicMid)
    {
        //объявляем пустоц массив    
        $arrayCid = array();
        //делаем запрос на все записи у дубликата 
        $rowCid = $this->fetchAll(array('MID = ?' => $dublicMid));              
        //получаем список всех курсов у дубликата
        $arrayCid = $rowCid->getList('CID');  
        //проверяем кол-во курсов у дубликата
        if (count($arrayCid)>0)
        {    
            //проходим по массиву полученных курсов
            foreach($arrayCid as $valCid)
            {
                //если курс существует и не пустое значение
                if (!empty($valCid))
                {
                    //делаем запрос, а у уникального пользователя есть ли такие же курсы?
                    $result = $this->fetchRow(array('MID = ?' => $unicMid, 'CID = ?' => $valCid));  
                    //если есть
                    if (null !== $result)
                    //удаляем такие курсы    
                        $resultDel = $this->deleteBy(array('MID = ?' => $dublicMid , 'CID = ?' => $valCid));
                    else 
                    {                    
                        //обновляем запись у дубликата - изменяем его MID на MID уникального пользователя
                        $data  = array('MID' => $unicMid);
                        $where = array('MID = ?' => $dublicMid, 'CID = ?' => $valCid);
                        $resultUpdate = $this->updateWhere($data , $where);                    
                    }
                }                
            }
            //как только цикл отработал и все значения проанализированы
            //проверим выполнение действий по обновлению и удалению записей,
            //чтобы быть увереным в том, что операции в БД прошли успешно
            if (!empty($resultDel) or !empty($resultUpdate))
                return true;
            else 
                return false;            
        }
        else            
            return false;            
    }

    public function isSubjectUnaccessible($student, $subject)
    {
        $begin = $student->begin_personal;
        $end   = $student->end_personal;

        if (
            // если "Без ограничений" + доп. проверка на end_personal - на случай назначения через программу (там могут быть свои даты)
            (($subject->period == HM_Subject_SubjectModel::PERIOD_FREE)/* && empty($end)*/) ||
            // если нестрогое ограничение - всегда доступен
            ($subject->period_restriction_type == HM_Subject_SubjectModel::PERIOD_RESTRICTION_DECENT)
        ) return false;

        // во всех остальных случаях проверяем, попадает ли в даты
        return (time() < strtotime($end)) && (time() > strtotime($begin))
            ? false
            : _('Курс назначен на другие даты');
    }

    public function getSubjectDates($student, $subject)
    {
        $return = array();

        if ($student->begin_personal) {
            $begin = new HM_Date($student->begin_personal);
            $return['begin'] = $begin->toString(Zend_Date::DATES);
        }

        if ($student->end_personal) {
            $end = new HM_Date($student->end_personal);
            $return['end'] = $end->toString(Zend_Date::DATES);

            if ($subject->period_restriction_type == HM_Subject_SubjectModel::PERIOD_RESTRICTION_DECENT) {
                $return['notStrict'] = true;
            }

        } else {
            $return['notLimited'] = true;
        }

        return $return;
    }

    public function getCachedSubjectProgramm($student, $subject)
    {
        $programmEventUserId = $student->programm_event_user_id;
        if (!$programmEventUserId) {
            $select = $this->getSelect();
            $select->from(['peu' => 'programm_events_users'], [
                'p.name'
            ])
            ->joinInner(['pe' => 'programm_events'], 'pe.programm_event_id = peu.programm_event_id', [])
            ->joinInner(['p' => 'programm'], 'p.programm_id = pe.programm_id', [])
            ->where('peu.user_id = ?', $student->MID)
            ->where('pe.type = 1 AND pe.item_id = ?', $student->CID);

            $result = $select->query()->fetchAll();
            return $result[0]['name'];
        } else {
            if ($collection = $student->getCachedValue('programmEventUserId2Programm', $programmEventUserId)) {
                if (count($collection)) {
                    $programm = $collection->current();
                    return $programm->name;
                }
            }
        }
        return false;
    }

    public function getCachedSubjectMark($student, $subject)
    {
        $subjectId = $student->CID;
        if ($subjectId) {
            $mark = $student->getCachedValue('subjectId2Mark', $subjectId);
            if ($mark !== false) {
                return $mark;
//                return HM_Scale_Converter::getInstance()->id2value($mark, $subject->scale_id);
            }
        }
        return false;
    }

    public function getCachedSubjectProgress($student, $subject)
    {
        $subjectId = $student->CID;
        if ($subjectId) {
            if ($progress = $student->getCachedValue('subjectId2Progress', $subjectId)) {
                return sprintf("%s%%", $progress);
            }
        }
        return false;
    }
}