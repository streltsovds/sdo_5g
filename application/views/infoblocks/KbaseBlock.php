<?php


class HM_View_Infoblock_KbaseBlock extends HM_View_Infoblock_Abstract
{
    protected $id = 'kbase';

    public function kbaseBlock($param = null)
    {
		$this->session = new Zend_Session_Namespace('infoblock_kbase');
		$this->_setDefaults();

        $periods = $this->getService('Period')->fetchAll();

        foreach ($periods as $period) {
            if (date('Y', $period->starttime) > date('Y')) {
                unset($periods[$period]);
            }
        }

        $this->view->periods = $periods;

    	$content = $this->view->render('kbaseBlock.tpl');

        return $this->render($content);
    }

    private function _setDefaults()
    {

    }
}