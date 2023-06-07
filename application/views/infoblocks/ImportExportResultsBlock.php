<?php


class HM_View_Infoblock_ImportExportResultsBlock extends HM_View_Infoblock_Abstract
{
    protected $id = 'importExportResults';
    
    public function importExportResultsBlock($param = null)
    {   
        $this->view->isOffline = Zend_Registry::get('config')->offline;
        
        
        $content = $this->view->render('importExportResultsBlock.tpl');
        return $this->render($content);
    }
    
}

?>