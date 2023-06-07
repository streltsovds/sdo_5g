<?php
/*
 * ВНИМАНИЕ!!! 
 * Прим мерже с боковыми ветками может потеряться совместимость с адаптерами
 * Если интеграция в конкурсе настроена, лучше вообще не мержить этот файл 
*
*/

class HM_Orgstructure_Import_Manager
{
    const DEFAULT_PASSWORD = 'pass';

    const CACHE_NAME = 'HM_Orgstructure_Import_Manager';

    protected $_items = null;
    protected $_adapter = null;

    protected $_existingItems = array();
    protected $_existingIds = array();
    protected $_existingPeople = array();
    protected $_existingPeopleIds = array();

    protected $_insertsPeople = array();
    protected $_updatesPeople = array();

    protected $_inserts = array();
    protected $_updates = array();
    protected $_deletes = array();

    protected $_loginCount = 0;
    private $_restoredFromCache = false;

    public function saveToCache()
    {
        return Zend_Registry::get('cache')->save(
            array(
                'inserts' => $this->_inserts,
                'updates' => $this->_updates,
                'deletes' => $this->_deletes,
                'insertsPeople' => $this->_insertsPeople,
                'updatesPeople' => $this->_updatesPeople
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
            $this->_inserts = $actions['inserts'];
            $this->_updates = $actions['updates'];
            $this->_deletes = $actions['deletes'];
            $this->_insertsPeople = $actions['insertsPeople'];
            $this->_updatesPeople = $actions['updatesPeople'];
            $this->_restoredFromCache = true;
            return true;
        }

        return false;
    }

    public function getService($name)
    {
        return Zend_Registry::get('serviceContainer')->getService($name);
    }

    public function getInserts()
    {
        return $this->_inserts;
    }

    public function getInsertsPeople()
    {
        return $this->_insertsPeople;
    }

    public function getUpdates()
    {
        return $this->_updates;
    }

    public function getUpdatesPeople()
    {
        return $this->_updatesPeople;
    }

    public function getDeletes()
    {
        return $this->_deletes;
    }

    public function getInsertsCount()
    {
        return count($this->_inserts);
    }

    public function getInsertsPeopleCount()
    {
        return count($this->_insertsPeople);
    }

    public function getUpdatesPeopleCount()
    {
        return count($this->_updatesPeople);
    }

    public function getUpdatesCount()
    {
        return count($this->_updates);
    }

    public function getDeletesCount()
    {
        return count($this->_deletes);
    }

    public function getCount()
    {
        return $this->getInsertsCount() + $this->getUpdatesCount() + $this->getDeletesCount() + $this->getInsertsPeopleCount() + $this->getUpdatesPeopleCount();
    }

    protected function _init()
    {
        $items = $this->getService('Orgstructure')->fetchAll(array(
            'blocked = ?' => 0,
        ));

        if (count($items)) {
            foreach($items as $item) {
                $this->_existingItems[$item->soid] = $item;
                if (strlen($item->soid_external)) {
                    $item->soid_external = trim($item->soid_external);
                    $this->_existingIds[$item->soid_external] = $item->soid;
                }
            }
        }

        $persons = $this->getService('User')->fetchAll();

        if (count($persons)) {
            foreach($persons as $person) {
                $this->_existingPeople[$person->MID] = $person;
                if (strlen($person->mid_external)) {
                    $person->mid_external = trim($person->mid_external);
                    $this->_existingPeopleIds[$person->mid_external] = $person->MID;
                }
            }
        }

    }

    protected function _isItemExists($itemExternalId)
    {
        return isset($this->_existingIds[$itemExternalId]);
    }

    protected function _isPersonExists($personExternalId)
    {
        if (isset($this->_existingPeopleIds[$personExternalId])) {
            return $this->_existingPeopleIds[$personExternalId];
        }

        return false;
    }

    protected function _checkPerson($person)
    {
        if (!$person) return true;

        if (!$this->_isPersonExists($person->mid_external)) {
            unset($person->mid);
            $this->_insertsPeople[$person->mid_external] = $person;
        } else {
            $existingPerson = $this->_existingPeople[$this->_existingPeopleIds[$person->mid_external]];

            if ($this->_needPersonUpdate($person)) {
                $person->MID = $existingPerson->MID;
                $this->_updatesPeople[$person->mid_external] = array('source' => $existingPerson, 'destination' => $person);
            }
        }
    }

    protected function _needPersonUpdate($person)
    {
        $existingPerson = $this->_existingPeople[$this->_existingPeopleIds[$person->mid_external]];

        $values = $person->getValues(null, array('mid', 'mid_external'));

        if (count($values)) {
            foreach($values as $key => $value) {
                if (trim($existingPerson->{$key}) != trim($value)) return true;
            }
        }

        return false;
    }

    protected function _needItemUpdate($item)
    {
        $existingItem = $this->_existingItems[$this->_existingIds[$item->soid_external]];

        $values = $item->getValues(null, array('soid', 'soid_external', 'mid_external', 'owner_soid_external'));
        if (count($values)) {
            foreach($values as $key => $value) {
                if (trim($existingItem->{$key}) != trim($value)) {
                    return true;
                }
            }
        }

        $item->owner_soid = trim($item->owner_soid);

        if (strlen($item->owner_soid)) {
            if (!$this->_isItemExists($item->owner_soid)) {
                return true;
            }

            if ($existingItem->owner_soid != $this->_existingIds[$item->owner_soid]) {
                return true;
            }
        }

        $item->mid = trim($item->mid);

        if (strlen($item->mid)) {
            if (!$this->_isPersonExists($item->mid)) return true;
            if ($existingItem->mid != $this->_existingPeopleIds[$item->mid]) {
                return true;
            }
        }

        return false;
    }

    public function init($items)
    {
        $this->_adapter = $this->getService('User')->getMapper()->getAdapter()->getAdapter();
        
        if ($this->_restoredFromCache) {
            $this->_init();
            return true;
        }

        if (count($items)) {

            $this->_init();

            foreach($items as $item) {

                $item->soid_external = trim($item->soid_external);

                if (!strlen($item->soid_external)) continue;

                if (!$this->_isItemExists($item->soid_external)) {

                    // insert new item
                    $this->_inserts[$item->soid_external] = $item;
                    $this->_checkPerson($item->getUser());

                } else {

                    $existingItem = $this->_existingItems[$this->_existingIds[$item->soid_external]];

                    if ($this->_needItemUpdate($item)) {
                        $item->soid = $existingItem->soid;
                        if (isset($this->_existingPeople[$existingItem->mid])) {
                            $existingItem->setUser($this->_existingPeople[$existingItem->mid]);
                        }
                        $this->_updates[$item->soid_external] = array('source' => $existingItem, 'destination' => $item);
                    }

                    if ($item->getType() == HM_Orgstructure_OrgstructureModel::TYPE_POSITION) {
                        $this->_checkPerson($item->getUser());
                    }

                    //unset($this->_existingIds[$existingItem->soid_external]);
                    unset($this->_existingItems[$existingItem->soid]);

                }
            }

            if (count($this->_existingItems)) {
                $this->_deletes = $this->_existingItems;
            }

        }

        $this->saveToCache();
    }

    protected function _generateLogin()
    {
        while(true) {
            $login = HM_User_UserService::NEW_LOGIN_PREFIX.str_pad((string) $this->_loginCount, 4, "0", STR_PAD_LEFT);
            $collection = $this->getService('User')->fetchAll($this->getService('User')->quoteInto('Login = ?', $login));
            if (count($collection)) {
                $this->_loginCount++;
                continue;
            } else {
                $this->_loginCount++;
                return $login;
            }
        }
    }

    public function import()
    {
        if (count($this->_deletes)) {

            foreach($this->_deletes as $soid => $item){

                // блокируем подразделения и вложенные должности
            	// только те, которые пришли из 1С (непустой soid_external)
            	$this->getService('Orgstructure')->updateWhere(array('blocked' => 1), $this->getService('Orgstructure')->quoteInto(array(
            			'soid_external != ? AND ',
            			'lft >= ? AND ',
            			'rgt <= ?',
            	), array(
            			'',
            			$item->lft,
            			$item->rgt,
            	)));            	
            	
            }

        }

        if (count($this->_insertsPeople)) {

            foreach($this->_insertsPeople as $id => $insert) {
                if (!isset($insert->Login)) {
                    $insert->Login = $this->_generateLogin();
                }
                $insert->Password = new Zend_Db_Expr("PASSWORD('".self::DEFAULT_PASSWORD."')");
                $user = $this->getService('User')->insert($insert->getValues());
                if ($user) {
                    $this->_existingPeopleIds[$user->mid_external] = $user->MID;
                }
            }
        }

        if (count($this->_updatesPeople)) {
            foreach($this->_updatesPeople as $id => $update) {
                $this->getService('User')->update($update['destination']->getValues(null, array('mid')));
            }
        }

        if (count($this->_inserts)) {
            
            $insertArr = array();
            foreach($this->_inserts as $ins){
                $insertArr[$ins->soid_external] = array('insert' => $ins, 'childs' => array(), 'parent' => 0);
            }

            // ВНИМАНИЕ!! может ооочень долго работать!
            foreach($insertArr as $key => &$value){
                foreach($insertArr as $key2 => &$value2){
                    if($key == $value2['insert']->owner_soid_external){
                        $value['childs'][] = &$value2;
                        $value2['parent'] = $key;
                    }
                }
            }


            foreach($insertArr as $insert){
                if($insert['parent'] === 0){
                    $this->insertNode($insert);
                }
            }
        }

        if (count($this->_updates)) {
            foreach($this->_updates as $id => $update)
            {
                $update = $update['destination'];

                if (strlen($update->owner_soid) && isset($this->_existingIds[$update->owner_soid])) {
                    $update->owner_soid = $this->_existingIds[$update->owner_soid];
                } else {
                    $update->owner_soid = 0;
                }

                if (strlen($update->mid) && ($update->getType() == HM_Orgstructure_OrgstructureModel::TYPE_POSITION) && isset($this->_existingPeopleIds[$update->mid])) {
                    $update->mid = $this->_existingPeopleIds[$update->mid];
                } else {
                    $update->mid = 0;
                }

                $values = $update->getValues(null, array('owner_soid_external', 'mid_external'));
                $update = $this->getService('Orgstructure')->updateNode($values, $update->soid,$update->owner_soid);
            }
        }

       // update classifiers
		/*
        $result = array();
        $classifiers  = $this->getService('Classifier')->fetchAll(array('type = ?' => HM_Classifier_ClassifierService::TYPE_AREA));
        $classifiers2 = clone $classifiers;
        foreach($classifiers as $cl1){
            foreach($classifiers2 as $cl2){
                if($cl1->lft < $cl2->lft && $cl1->rgt > $cl2->rgt){
                    $result[$cl2->classifier_id][] = $cl1->classifier_id;
                }
            }
        }
		*/

        //$items = $this->getService('Orgstructure')->fetchAll(array('soid IN ( ? )' => $this->_existingIds));

        //$arrayArea = $this->getService('Classifier')->returnAreaClassifiers();

        /*foreach($this->_updates as $item){
            $classifierId = $arrayArea[$item['destination']->CostCenter];
            $classifiers = $result[$classifierId];
            $classifiers[] = $classifierId;
            $this->getService('ClassifierLink')->setClassifiers($item['destination']->soid, HM_Classifier_ClassifierService::TYPE_AREA, $classifiers);
        }*/

        
        $this->_adapter->query("DELETE FROM supervisors WHERE user_id NOT IN (SELECT DISTINCT mid FROM structure_of_organ WHERE is_manager = 1)");
        $this->_adapter->query("INSERT INTO supervisors (user_id) SELECT DISTINCT mid FROM structure_of_organ WHERE is_manager = 1 AND mid NOT IN (SELECT user_id FROM supervisors)");
                    
        return true;
    }


    protected function insertNode($node)
    {
        $insert = $node['insert'];

        if (false === $this->_setParent($insert)) return true;
        $this->_setUser($insert);
        
        $values = $insert->getValues(null, array('soid', 'owner_soid_external', 'mid_external')); // skip these attrs
        $item = $this->getService('Orgstructure')->insert(
            $values,
            $values['owner_soid']
        );

        if ($item) {
            $this->_existingIds[$insert->soid_external] = $item->soid;
            
            foreach($node['childs'] as $child){
                $this->insertNode($child);
            }            
        }
    }
    
    protected function _setParent(&$insert)
    {
    	if (empty($insert->owner_soid_external)) {
    		$insert->owner_soid = 0; // real 1st level
    	} elseif (isset($this->_existingIds[$insert->owner_soid_external])) {
    		$insert->owner_soid = $this->_existingIds[$insert->owner_soid_external];
    	} else {
    		// оторванные ветки - целиком пропускаем
            // todo Уточнить..
    		return false;
    	}
    }

    protected function _setUser(&$insert)
    {
    	if (empty($insert->mid_external)) {
    		$insert->mid = 0; // no user
    	} elseif (isset($this->_existingPeopleIds[$insert->mid_external])) {
    		$insert->mid = $this->_existingPeopleIds[$insert->mid_external];
    	} else {
    		return true;
    	}
    }
}