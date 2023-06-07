<?php
class HM_Role_ParticipantService extends HM_Service_Abstract
{

    public function insert($data, $unsetNull = true)
    {
        if (!isset($data['time_registered'])) {
            $data['time_registered'] = $this->getDateTime();
        }

        return parent::insert($data);
    }

    public function isUserExists($projectId, $userId)
    {
        $collection = $this->fetchAll(array('CID = ?' => $projectId, 'MID = ?' => $userId)
            
            //$this->quoteInto(array('CID = ?', 'MID = ?'), array($projectId, $userId))
        );
        return count($collection);
    }
    
    /**
     * Получить список ID всех пользователей предмета по ID предмета
     * 
     * @param numeric $projectId
     * @return array
     */
    public function getUsersIds($projectId)
    {
        $collection = $this->fetchAll(array('CID = ?' => $projectId));
        return $collection->getList('MID');
    }

    public function getProjects($userId = null)
    {
        if (!$userId) $userId = $this->getService('User')->getCurrentUserId();
        $collection = $this->fetchAll(array('MID = ?' => $userId));
        $list = $collection->getList('CID','MID');
        if (!count($list)) {
            $list = array(0 => 0);
        }
        return $this->getService('Project')->fetchAll(array('projid IN (?)' => array_keys($list)));
        
        /*return $this->getService('Project')->fetchAllDependenceJoinInner(
            'Participant',
            $this->quoteInto('Participant.MID = ?', $userId),
            'self.name'
        );*/
        
    }
    
    /**
     *  метод принимает mid дубликата и mid уникальной записи
     *  проверяем совпадают ли курсы у mid дубликат в таблице
     *  participant-если да то просто удаляем эту запись, не нарушая 
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
}