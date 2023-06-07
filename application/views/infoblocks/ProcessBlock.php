<?php


class HM_View_Infoblock_ProcessBlock extends HM_View_Infoblock_Abstract
{
    const MAX_ITEMS = 10;

    protected $id = 'process';

    public function processBlock($param = null)
    {
        $subject = $options['subject']; 
        $services = Zend_Registry::get('serviceContainer');
            
        if ($this->getService('Process')->initProcess($subject)) {
            $content = $this->view->workflowBlock($subject);
        } elseif (
            is_a($subject, 'HM_Recruit_Newcomer_NewcomerModel') && 
            $this->getService('Acl')->isCurrentAllowed('mca:newcomer:list:start')    
        ){
            $str = _('Стартовать процесс');
            $url = $this->view->url(array(
                'module' => 'newcomer',
                'controller' => 'list',
                'action' => 'start',
                'newcomer_id' => $subject->newcomer_id,
                'baseUrl' => 'recruit',
            ));
            $content = "<div style='padding: 15px; text-align: center;'><button onClick='javascript:document.location.href=\"{$url}\"'>{$str}</button></div>";
        }

        
        return $this->render($content);
    }
}