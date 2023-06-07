<?php
class HM_User_Import_AdManager
{
    protected $_existingPeople = array();
    protected $_existingPeopleIds = array();

    protected $_insertsPeople = array();
    protected $_updatesPeople = array();
    protected $_deletesPeople = array();

    protected $_notProcessed = array();

    const CACHE_NAME = 'HM_User_Import_Manager';

    private $_loginCount = 0;
    private $_restoredFromCache = false;

    const DEFAULT_PASSWORD = '12345678Z*';

    public function getService($name)
    {
        return Zend_Registry::get('serviceContainer')->getService($name);
    }

    private function _init($isAD = false)
    {
        $where = array('blocked = ?' => 0);
        if($isAD){
            $where['isAD = ?'] = 1;
        }
        $persons = $this->getService('User')->fetchAll($where);

        if (count($persons)) {
            foreach($persons as $person) {
                /** @var HM_User_UserModel $person */
                $this->_existingPeople[$person->MID] = $person;
                $uniqueLdapId = $person->getUniqueLdapId();
                if (strlen($uniqueLdapId)) {
                    $this->_existingPeopleIds[$uniqueLdapId] = $person->MID;
                }
            }
        }
    }

    protected function _needPersonUpdate($uniqueLdapId)
    {
        $existingPerson = $this->_existingPeople[$this->_existingPeopleIds[$uniqueLdapId]];
        return !empty($existingPerson->mid_external); // всегда
//        && // сотрудник
//            (
//                empty($existingPerson->Login) || // логина нет
//                empty($existingPerson->EMail) || // email нет
//                !$existingPerson->getRealPhoto()
//            ); // или фотки нет
    }

    public function getInsertsCount()
    {
        return 0; //count($this->_insertsPeople);
    }

    public function getUpdatesCount()
    {
        return count($this->_updatesPeople);
    }

    public function getDeletesCount()
    {
        return 0; //count($this->_deletesPeople);
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
        return $this->_insertsPeople;
    }

    public function getUpdates()
    {
        return $this->_updatesPeople;
    }

    public function getDeletes()
    {
        return $this->_deletesPeople;
    }

    public function getNotProcessed()
    {
        return $this->_notProcessed;
    }

    public function saveToCache()
    {
        return Zend_Registry::get('cache')->save(
            array(
                 'inserts' => $this->_insertsPeople,
                 'updates' => $this->_updatesPeople,
                 'deletes' => $this->_deletesPeople,
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
            $this->_insertsPeople = $actions['inserts'];
            $this->_updatesPeople = $actions['updates'];
            $this->_deletesPeople = $actions['deletes'];
            $this->_notProcessed = $actions['notProcessed'];
            $this->_restoredFromCache = true;
            return true;
        }

        return false;
    }

    public function init($items, $isAD = false)
    {
        $this->_init($isAD);

        if ($this->_restoredFromCache) {
            return true;
        }

        if (count($items)) {
            foreach($items as $item) {

                $uniqueLdapId = $item->mid_external;

                if (empty($uniqueLdapId)) {
                    $this->_notProcessed[] = $item;
                    continue;
                }

                if (isset($this->_updatesPeople[$uniqueLdapId])) continue;
                if (!isset($this->_existingPeople[$this->_existingPeopleIds[$uniqueLdapId]])) continue;

                $existingPerson = $this->_existingPeople[$this->_existingPeopleIds[$uniqueLdapId]];

                if ($this->_needPersonUpdate($uniqueLdapId)) {
                    $item->MID = $existingPerson->MID;
                    $this->_updatesPeople[$uniqueLdapId] = array('source' => $existingPerson, 'destination' => $item);
                }
                unset($this->_existingPeople[$existingPerson->MID]);
            }
        }

        $this->saveToCache();
    }

    public function import()
    {
        echo sprintf("Загружено фото: %s;\r\n", count($this->_updatesPeople));
        echo sprintf("Не сопоставлены с 1С: %s;\r\n", count($this->_notProcessed));

        if (count($this->_updatesPeople)) {
            foreach($this->_updatesPeople as $id => $update) {

                $this->getService('User')->updateWhere(array(
                    'Login' => $update['destination']->Login ? : '',
                    'EMail' => $update['destination']->EMail ? : '',
                    'Password'=> new Zend_Db_Expr(sprintf("PASSWORD('%s')", self::DEFAULT_PASSWORD)),
                    'Domain'=> $this->getDomain($id),
                ), array(
                    'MID = ?' => $update['source']->MID
                ));

                if (!empty($update['destination']->photo)) {
                    $update['source']->setPhoto($update['destination']->photo);
                }
            }
        }

        $emptyPersons = $this->getService('User')->fetchAll(array(
            'EMail IS NOT NULL AND EMail != ?' => '',
            'Login IS NULL OR Login = ?' => '',
        ));

        foreach ($emptyPersons as $person) {
            list($login, $domain) = explode('@', $person->EMail);
            $person->Login = $login;
            $person->Domain = $domain;
            $person->Password = new Zend_Db_Expr(sprintf("PASSWORD('%s')", self::DEFAULT_PASSWORD));
            $this->getService('User')->update($person->getValues());
        }
    }

    protected function getDomain($id)
    {
        list($ldapName) = explode('-', $id);
        return Zend_Registry::get('config')->$ldapName->ldap->options->accountDomainNameShort;
    }
}