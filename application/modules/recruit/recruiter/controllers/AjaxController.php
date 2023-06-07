<?php

class Recruiter_AjaxController extends HM_Controller_Action
{
    public function recruitersListAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            header('HTTP/1.0 404 Not Found');
            exit;
        }

        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();
        $this->getResponse()->setHeader('Content-type', 'application/json; charset=UTF-8');

        $q = strtolower(trim($this->_request->getParam('tag')));
        $res = array();
        if(!empty($q)) {
            $q = '%'.$q.'%';
            
            $select = $this->getService('User')->getSelect();
            $select->from(array('u' => 'People'), array('mid' => 'u.MID', 'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(u.LastName, ' ') , u.FirstName), ' '), u.Patronymic)")))
                ->join(array('r' => 'recruiters'), 'u.MID = r.user_id', array())
                ->joinLeft(array('sto' => 'structure_of_organ'), 'u.MID = sto.mid', array('position' => 'sto.name'))
                ->where('u.blocked = ?', 0)
                ->where('sto.blocked IS NULL OR sto.blocked = ?', 0)
                ->where($this->getService('User')->quoteInto(array(
                    '(LOWER(LastName) LIKE LOWER(?) OR ',  
                    'LOWER(FirstName) LIKE LOWER(?) OR ',                 
                    'LOWER(Patronymic) LIKE LOWER(?) OR ',                 
                    'LOWER(Login) LIKE LOWER(?) OR ',                 
                    'LOWER(sto.name) LIKE LOWER(?) OR ',
                    "LOWER(CONCAT(CONCAT(CONCAT(CONCAT(LastName, ' '), FirstName), ' '), Patronymic)) LIKE LOWER(?))",
                ), array($q,$q,$q,$q,$q,$q)));

            $currentUser = $this->getService('User')->getCurrentUser();

            $stmt = $select->query();
            $stmt->execute();
            $rows = $stmt->fetchAll();
            
            foreach($rows as $row) {
                $o = new stdClass();
                $o->key = $row['position'] ? sprintf('%s (%s)', $row['fio'], $row['position']) : $row['fio'];
                $o->value = $row['mid'];
                $res[] = $o;
            }
        }

        echo HM_Json::encodeErrorSkip($res);
    }
}