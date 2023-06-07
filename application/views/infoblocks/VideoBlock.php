<?php

class HM_View_Infoblock_VideoBlock extends HM_View_Infoblock_Abstract
{
    protected $id = 'video';
    public $disableCache = true;

    public function videoBlock($param = null)
    {
        //$url = $this->view->url(array('module'=> 'resource', 'controller' => 'index', 'action' => 'data', 'resource_id' => $resource->resource_id), null, true);
        $this->view->headScript()->appendFile($this->view->serverUrl('/js/lib/mediaelement/mediaelement-and-player.min.js'));
        $this->view->headLink()->appendStylesheet($this->view->serverUrl('/js/lib/mediaelement/mediaelementplayer.css'));
        $this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/infoblocks/video/style.css');
        
        $videos = $this->getService('Videoblock')->fetchAll(null, array('is_default DESC', 'name ASC'))->asArrayOfObjects();

        foreach ($videos as &$video)
        {
            if ($video->file_id)
            {
                $file = $this->getService('Files')->getOne($this->getService('Files')->find($video->file_id));

                $temp = explode('.',$file->name);
                $ext = $temp[count($temp) - 1];

                $video->filename =  Zend_Registry::get('config')->src->upload->files . $video->file_id.'.'.$ext;
            }
        }
        $this->view->videos = $videos;

        $this->view->showEditLink = $isModerator = $this->getService('Activity')->isUserActivityPotentialModerator(
            $this->getService('User')->getCurrentUserId()
        );

        $this->view->currentRole = $this->getService('User')->getCurrentUserRole();
        
        $content = $this->view->render('videoBlock.tpl');
        
        return $this->render($content);
    }
}