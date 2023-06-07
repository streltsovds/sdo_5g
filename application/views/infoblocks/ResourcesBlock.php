<?php


class HM_View_Infoblock_ResourcesBlock extends HM_View_Infoblock_Abstract
{
    protected $id = 'resources';
    protected $session;

    public function resourcesBlock($param = null)
    {
		$this->session = new Zend_Session_Namespace('infoblock_resources');
		$classifiers = Zend_Registry::get('serviceContainer')->getService('Classifier')->getTypes(HM_Classifier_Link_LinkModel::TYPE_RESOURCE);
		//$classifiers = $this->_getClassifiers();

		if (count($classifiers)) {

			if (!isset($this->session->from)) {
    			$this->session->from = '';
    		}
    		if (!isset($this->session->to)) {
    			$this->session->to = '';
    		}
    		if (!isset($this->session->classifier)) {
    		    $keys = array_keys($classifiers);
    			$this->session->classifier = array_shift($keys);
    		}
    		$this->view->classifiers = $classifiers;
    		$this->view->session = $this->session;

		} else {
		    $collection = Zend_Registry::get('serviceContainer')->getService('Resource')->fetchAll(array(
		        'status != ?' => HM_Resource_ResourceModel::STATUS_UNPUBLISHED,
		        'parent_id = ?' => 0,
		        'location = ?' => HM_Resource_ResourceModel::LOCALE_TYPE_GLOBAL,
            ));
		    $this->view->totalResources = count($collection);
		}
		$this->view->exportUrl = $this->view->url(array(
            'module' => 'infoblock',
            'controller' => 'resources',
            'action' => 'get-data',
            'format' => 'csv',
        ));

    	$content = $this->view->render('resourcesBlock.tpl');
        
        return $this->render($content);
    }

    private function _getClassifiers()
    {
    	if ($types = Zend_Registry::get('serviceContainer')->getService('Classifier')->getTypes(HM_Classifier_Link_LinkModel::TYPE_RESOURCE)) {
    	    foreach ($types as $key => $name) {

    	        $children = array();
    	        foreach (Zend_Registry::get('serviceContainer')->getService('Classifier')->fetchAll(array('level = ?' => 0, 'type = ?' => $key)) as $classifier) {
    	            $children[$classifier->classifier_id] = $classifier->name;
    	        }

    	        $classifiers[$key] = array(
    	            'name' => $name,
    	            'children' => $children,
                );
    	    }
    	}
        return $classifiers;
    }
}