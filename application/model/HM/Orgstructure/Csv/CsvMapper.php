<?php
class HM_Orgstructure_Csv_CsvMapper extends HM_Mapper_Abstract
{
    protected $structure  = array();
    protected $classifier = array();
    protected $models;
    protected $services;
    protected $dependences=array();

    protected function _createModel($rows, &$dependences = array())
    {
        $collectionClass = $this->getCollectionClass();
        $models = new $collectionClass(array(), $this->getModelClass());
        if (count($rows) < 1) {
            return $models;
        }

        if (count($rows) > 0) {

        	$dependences = array();
            $services    = Zend_Registry::get('serviceContainer');

            foreach($rows as $index => $row) {

                if (strlen($row['mid_external'])) {
                    
                	$row['type'] = HM_Orgstructure_OrgstructureModel::TYPE_POSITION;
                    $forUser = $this->getAdapter()->getUserFields();
                    $userData = $this->_getFields($forUser, $row);
                    
                    $row['soid_external'] = implode('-', array($row['soid_external'], $row['mid_external']));

                    $dependences['soid_external'][$row['soid_external']]['user'][] = array(
                        'row' => $userData,
                        'refClass' => 'HM_User_UserModel'
                    );
                } else{
                    $row['type'] = HM_Orgstructure_OrgstructureModel::TYPE_DEPARTMENT;
                    $row['is_manager'] = 0;
                }

                $forOrg = $this->getAdapter()->getOrgFields();
                $orgData = $this->_getFields($forOrg, $row);

                $models[count($models)] = $orgData;

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

    protected function _getFields($fields, $row)
    {
    	$res = array();
    
    	foreach($fields as $field){
    		$res[$field] = $row[$field];
    	}
    	return $res;
    }    
}