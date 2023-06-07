    <?php

class HM_View_Infoblock_News extends HM_View_Infoblock_Abstract
{                                          
    
    protected $id = 'news';

    //Определяем класс отличный от других
    protected $class = 'scrollable';
    
    public function news($param = null)
    {
        $serviceContainer = Zend_Registry::get('serviceContainer');
        //$service = Zend_Registry::get('serviceContainer')->getService('Info');
        // todo: $attribs['param'] перенести в $options['news_id']
        $this->id = strtolower(substr(str_replace('HM_View_Infoblock_', '', get_class($this)), 0, 1)).substr(str_replace('HM_View_Infoblock_', '', get_class($this)), 1);
        $this->id .= "_" . $param;
        
        $select = $serviceContainer->getService('Info')->getOne($serviceContainer->getService('Info')->find($param));

        if($select->resource_id) {
            $this->view->resource = $this->getService('Resource')->findOne($select->resource_id);
        }

        $this->view->news = $select;
        $content = $this->view->render('newsBlock.tpl');

        return $this->render($content);
    }

    public function getNotCachedContent()
    {
        $this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/infoblocks/news/style.css');

        if($this->view->news) {
            $this->view->inlineScript()->captureStart();
            echo <<<JS
                var resource_id = {$this->view->news->resource_id};
JS;
            $this->view->inlineScript()->captureEnd();
        }
    }
}