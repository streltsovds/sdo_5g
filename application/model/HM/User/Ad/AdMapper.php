<?php
class HM_User_Ad_AdMapper extends HM_Mapper_Abstract
{
    protected function _createModel($rows, &$dependences = array())
    {
        $return = array();
        $ldapNames = HM_Integration_Abstract_Model::getLdapNames();

        foreach ($ldapNames as $ldapName => $rows)
            $return = array_merge($return, $this->createModelByLdap($ldapName, $rows));

        return $return;
    }

    public function createModelByLdap($ldapName, $rows)
    {
	$collectionClass = $this->getCollectionClass();
        $models = new $collectionClass(array(), $this->getModelClass());
        if (count($rows) < 1) {
            return $models;
        }

        if (count($rows) > 0) {
            $dependences = array();
            foreach($rows as $index => $row) {
                                          
                //if (!isset($row[Zend_Registry::get('config')->$ldapName->ldap->mapping->user->uniqueIdField][0])) continue;

                //if (strtolower(Zend_Registry::get('config')->$ldapName->ldap->mapping->user->uniqueIdField) == 'objectguid') {
                //    $row['objectguid'][0] = bin2hex($row['objectguid'][0]);
                //}

                $model = array('mid_external' => trim(iconv('UTF-8', Zend_Registry::get('config')->charset, $row[Zend_Registry::get('config')->$ldapName->ldap->mapping->user->extensionattribute10][0])));

                //if (strtolower(Zend_Registry::get('config')->$ldapName->ldap->mapping->user->uniqueIdField) == 'objectguid') {
                //    $model['mid_external'] = ''; //md5($model['mid_external']);
                //}

                $mapping = Zend_Registry::get('config')->$ldapName->ldap->mapping->user->toArray();
		
                foreach($mapping as $field => $value) {
                    if (!isset($row[$field][0])) continue;
                    $model[$value] = ($value != 'photo') ? trim(iconv('UTF-8', Zend_Registry::get('config')->charset, $row[$field][0])) : $row[$field][0];
                    if ($value == 'mid_external') $model[$value] = sprintf('%s-%s', $ldapName, $model[$value]);
                }

                $model['isAD'] = 1;   //pr($model);die();
                $models[count($models)] = $model;
                unset($rows[$index]);
            }


            $models->setDependences($dependences);
        }

        return $models;
    }

    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        $rows = $this->getAdapter()->fetchAll($where, $order, $count, $offset);
        return $this->_createModel($rows);
    }

    public function fetchAllByLdap($ldap)
    {
        $rows = $this->getAdapter()->fetchAllByLdap($ldap);
        return $this->createModelByLdap($ldap, $rows);
    }

}