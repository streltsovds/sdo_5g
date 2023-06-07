<?php


class HM_View_Infoblock_LeasingBlock extends HM_View_Infoblock_Abstract
{
    protected $id = 'leasing';
    protected $session;

    const TYPE_SESSIONS	= 'sessions';
    const TYPE_HDD	= 'hdd';

    public function leasingBlock($param = null)
    {
		$this->session = new Zend_Session_Namespace('infoblock_leasing');
		$this->_setDefaults();

		$type	= $this->session->type;

        $this->view->resources = [
            ['value' => 'sessions', 'text' =>  _('количество одновременных подключений')],
            ['value' => 'hdd', 'text' => _('дисковое пространство')]
        ];

        $this->view->selected = $type == 'sessions' ? 'sessions' : 'hdd';
        $this->view->loadUrl = $this->view->url(['module' => 'infoblock', 'controller' => 'leasing', 'action' => 'get-data','format' => 'json']);

    	$content = $this->view->render('leasingBlock.tpl');
        
        return $this->render($content);
    }

    private function _setDefaults()
    {
		if (!isset($this->session->period)) {
            $config = Zend_Registry::get('config')->leasing;
			$this->session->period = $config->period;
		}
		if (!isset($this->session->type)) {
			$this->session->type = self::TYPE_SESSIONS;
		}
    }
}