<?php


class HM_View_Infoblock_UsersSystemCounterBlock extends HM_View_Infoblock_Abstract
{
    protected $id = 'usersSystemCounter';
    
    public function usersSystemCounterBlock($param = null)
    {

        $services = Zend_Registry::get('serviceContainer');
        
        $this->view->from = date('d.m.Y', strtotime('-7 DAY'));
        $this->view->to = date('d.m.Y');
        
        $stats = $services->getService('Session')->getUsersStats($this->view->from, $this->view->to);

        $items = [];
        if (array_key_exists('users', $stats)) $items[] = ['name' => _('Пользователей:'), 'value' => $stats['users']];
        if (array_key_exists('usersNow', $stats)) $items[] = ['name' => _('В настоящий момент:'), 'value' => $stats['usersNow']];

        $this->view->items = $items;
        
        $this->view->stats = $stats;
        
        
		$content = $this->view->render('usersSystemCounterBlock.tpl');
		
        
        return $this->render($content);
    }
}