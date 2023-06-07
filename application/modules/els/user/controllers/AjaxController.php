<?php

class User_AjaxController extends HM_Controller_Action
{
    public function usersListAction($andWhere = '')
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            header('HTTP/1.0 404 Not Found');
            exit;
        }

        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();
        $this->getResponse()->setHeader('Content-type', 'application/json; charset=UTF-8');

        $q = strtolower(trim($this->getJsonParams()['tag']));
        $res = array();
        if(!empty($q)) {
            $q = '%'.$q.'%';
            
            $select = $this->getService('User')->getSelect();
            $select->from(array('u' => 'People'), array('mid' => 'u.MID', 'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(u.LastName, ' ') , u.FirstName), ' '), u.Patronymic)")))
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
            // в зависимости от роли пользователя показываем разные учётные записи
            switch ($currentUser->role) {
                case HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL:
                case HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL:
                    $soid = $this->getService('Responsibility')->get();
                    $responsibilityPosition = $this->getOne($this->getService('Orgstructure')->find($soid));
                    if ($responsibilityPosition) {
                        $subSelect = $this->getService('Orgstructure')->getSelect()
                            ->from('structure_of_organ', array('soid'))
                            ->where('lft > ?', $responsibilityPosition->lft)
                            ->where('rgt < ?', $responsibilityPosition->rgt);
                        $select->where("(sto.mid IS NULL) OR (sto.soid IN (?))", $subSelect);
                    } else {
                        $select->where('1 = 0');
                    }                
                    break;
            }
                    
            $stmt = $select->query();
            $stmt->execute();
            $rows = $stmt->fetchAll();
            
            foreach($rows as $row) {
                $o = new stdClass();
                $o->key = $row['position'] ? sprintf('%s (%s)', $row['fio'], $row['position']) : $row['fio'];
                $o->value = $row['mid'];
                $res[$row['mid']] = $o;
            }
        }

        echo HM_Json::encodeErrorSkip($res);
    }
    
    public function usersListForCertificateAction()
    {
        $currentUser = $this->getService('User')->getCurrentUser();

        if ($currentUser->role !== HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR) {
            return $this->usersListAction();
        }

        $select = $this->getService('User')->getSelect();

        $select->from(array('s' => 'structure_of_organ'), array(
            's1.lft',
            's1.rgt'
        ));
        $select->joinLeft(array('s1' => 'structure_of_organ'), 's1.soid = s.owner_soid', array())
               ->where('s.mid = ?', $currentUser->MID);

        $result = $select->query()->fetch();

        if ($result) {
           $where = " AND (MID IN (
               SELECT mid FROM structure_of_organ WHERE lft > {$result['lft']} AND rgt < {$result['rgt']} GROUP BY mid
           ))";
           return $this->usersListAction($where);
        }

        return $this->usersListAction();
    }

    public function currentUserDataAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            header('HTTP/1.0 404 Not Found');
            exit;
        }

        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();
        $this->getResponse()->setHeader('Content-type', 'application/json; charset=UTF-8');

        $currentUser = $this->getService('User')->getCurrentUser();
        echo isset($currentUser->MID) ? json_encode($currentUser) : json_encode(array());
    }

    public function usersListForNewcomerEvaluationAction()
    {

        return $this->usersListAction();
    }

    public function usersListForReserveEvaluationAction()
    {

        return $this->usersListAction();
    }


    /// AAAA КОПИПАСТА
    public function usersListIsManagerAction()
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
                ->joinLeft(array('sto' => 'structure_of_organ'), 'u.MID = sto.mid', array('position' => 'sto.name'))
                ->where('u.blocked = ?', 0)
                ->where('sto.is_manager = ?', HM_Orgstructure_OrgstructureModel::MANAGER)
                ->where('sto.blocked IS NULL OR sto.blocked = ?', 0)
                ->where($this->getService('User')->quoteInto(array(
                    '(LOWER(LastName) LIKE LOWER(?) OR ',
                    'LOWER(FirstName) LIKE LOWER(?) OR ',
                    'LOWER(Patronymic) LIKE LOWER(?) OR ',
                    'LOWER(Login) LIKE LOWER(?) OR ',
                    "LOWER(CONCAT(CONCAT(CONCAT(CONCAT(LastName, ' '), FirstName), ' '), Patronymic)) LIKE LOWER(?))",
                ), array($q,$q,$q,$q,$q)));

            $currentUser = $this->getService('User')->getCurrentUser();
            // в зависимости от роли пользователя показываем разные учётные записи
            switch ($currentUser->role) {
                case HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL:
                case HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL:
                    $soid = $this->getService('Responsibility')->get();
                    $responsibilityPosition = $this->getOne($this->getService('Orgstructure')->find($soid));
                    if ($responsibilityPosition) {
                        $subSelect = $this->getService('Orgstructure')->getSelect()
                            ->from('structure_of_organ', array('soid'))
                            ->where('lft > ?', $responsibilityPosition->lft)
                            ->where('rgt < ?', $responsibilityPosition->rgt);
                        $select->where("(sto.mid IS NULL) OR (sto.soid IN (?))", $subSelect);
                    } else {
                        $select->where('1 = 0');
                    }
                    break;
            }

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