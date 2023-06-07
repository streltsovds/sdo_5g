<?php
class HM_Session_SessionService extends HM_Service_Abstract
{
    public function generateKey()
    {
        $user = $this->getService('User')->getCurrentUser();
        if ($user) {
            $key = md5(md5(sprintf('%s|%s', $user->MID, $user->Login)).time());
            return $key;
        }
        return false;
    }


    public function toLog($data = array(), $userId = null)
    {
        if (!isset($userId)) {
            $userId = $this->getService('User')->getCurrentUserId();
        }

        $sessionData = array(
            'mid'   => $userId,
            'start' => $this->getDateTime(),
            'stop'  => $this->getDateTime(),
            'ip'    => $_SERVER["REMOTE_ADDR"]
        );

        $sessionData = array_merge($sessionData, $data);

        $session = $this->insert($sessionData);
    }


    public function setAuthorizerKey()
    {
        $s = new Zend_Session_Namespace('s');
        if ($s->sessid) {
            $key = $this->generateKey();
           
            if ($key) {
                $this->updateWhere(
                    array(
                        'sesskey' => ''
                    ),
                    $this->quoteInto('mid = ?', $this->getService('User')->getCurrentUserId())
                );

                $this->update(
                    array(
                        'sessid' => $s->sessid,
                        'sesskey' => $key
                    )
                );
                setcookie('hmkey', $key, time() + 3600*24*30*6, '/');
            }
        }
    }
    
    
    public function getUsersStats($from, $to)
    {
        $select = $this->getSelect();
        
        $from = date('Y-m-d', strtotime($from));
        $to = date('Y-m-d', strtotime($to));
        
        $select->from('sessions', array('amount' => new Zend_Db_Expr("COUNT(DISTINCT mid)")) )
               //->where()
               ->where('start >= ?',  $from . ' 00:00')
               ->where('stop <= ?',  $to . ' 23:59:59')
               /*->where('start >= \'' . $from . ' 00:00\'' . ' AND start <= \''. $to . ' 23:59:59\'')
               ->orwhere('stop >= \'' . $from . ' 00:00\'' . ' AND stop <= \''. $to . ' 23:59:59\'')*/
               //->group(array('mid'))
               ;
        $query = $select->query();
        $fetch = $query->fetchAll();
        
        //pr($fetch);
        
        $countUsers = intval($fetch[0]['amount']);
//        $countGuests = $this->getService('Guest')->getStat($from, $to);

        $config = Zend_Registry::get('config');
        $lifetime = ($seconds = ini_get('session.gc_maxlifetime')) ? $seconds : (int)$config->user->onlinetimeout;
        $select = $this->getSelect();
        $select->from('sessions', array('amount' => new Zend_Db_Expr("COUNT(DISTINCT mid)")) )
            ->where('stop >= ?',  date('Y-m-d H:i:s', time() - $lifetime))
        ;
        // echo $select;
        $query = $select->query();
        $fetch = $query->fetchAll();
        $countUsersNow = intval($fetch[0]['amount']);

        return array('users' => $countUsers, 'usersNow' => $countUsersNow, /*'guests' => $countGuests*/);
        
    }

    public function insert($data, $unsetNull = true)
    {
        $currentPhpSessId = session_id();
        $data['sesskey'] = $currentPhpSessId;

        if ($this->getService('Option')->getOption('disable_multiple_authentication')) {

            $loggedUserSession = $this->getService('Session')->fetchAll(
                $this->quoteInto(
                    'mid = ? AND logout = 0', $data['mid']
                )
            );
            $loggedUserSessionId = '';
            foreach ($loggedUserSession as $session) {
                $loggedUserSessionId = $session->sesskey; break;
            }

            if ($loggedUserSessionId != $currentPhpSessId) {
                $this->updateWhere(
                    array('logout' => 1),
                    $this->quoteInto(
                        array('mid = ?'), $data['mid'])
                );
            }
        }
        return parent::insert($data, $unsetNull);
    }
}