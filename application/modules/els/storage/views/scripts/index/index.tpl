<?php echo $this->vueServerFile('main', array(
        'connectorUrl' => $this->url(array(
            'module' => 'storage',
            'controller' => 'index',
            'action' => 'elfinder',
            'subject' => $this->subjectName,
            'subject_id' => $this->subjectId
        )),
        'isModal' => false,
        'lang' => Zend_Registry::get('config')->wysiwyg->params->language
));