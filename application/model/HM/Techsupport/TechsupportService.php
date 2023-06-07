<?php
class HM_Techsupport_TechsupportService extends HM_Service_Abstract
{
    public function getIndexSelect() {
        $select = $this->getSelect();
        $select->from(
            array(
                'sr' => 'support_requests'
            ),
            array(
                //для совместимости с методом updateRole, псевдоним поля будет MID
                'MID'                => 'sr.user_id',
                'support_request_id' => 'sr.support_request_id',
                'user_name'          => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
                'roles'              => new Zend_Db_Expr('1'),
                'date_'              => 'sr.date_',
                'theme'              => 'sr.theme',
                'status'             => 'sr.status',
                'file_id'             => 'sr.file_id',
            )
        );
        $select->joinLeft(array('p' => 'People'), 'p.MID = sr.user_id', array());
        $select->group(array(
                'sr.support_request_id',
                'sr.theme',
                'sr.user_id',
                'p.LastName',
                'p.FirstName', 
                'p.Patronymic',
                'sr.date_',
                'sr.status',
                'sr.file_id'   
        ));
        
        return $select;
    }

    public function delete($id)
    {
        $support = $this->getOne($this->find($id));
        if($support->file_id) {
            $this->getService('Files')->delete($support->file_id);
        }
        return parent::delete($id);
    }
}