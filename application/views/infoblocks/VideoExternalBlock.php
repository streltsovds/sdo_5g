<?php

class HM_View_Infoblock_VideoExternalBlock extends HM_View_Infoblock_Abstract
{
    protected $id = 'video';

    public function videoExternalBlock($param = null)
    {
        //$url = $this->view->url(array('module'=> 'resource', 'controller' => 'index', 'action' => 'data', 'resource_id' => $resource->resource_id), null, true);
        $this->view->headScript()->appendFile($this->view->serverUrl('/js/lib/mediaelement/mediaelement-and-player.min.js'));
        $this->view->headLink()->appendStylesheet($this->view->serverUrl('/js/lib/mediaelement/mediaelementplayer.css'));
        $this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/infoblocks/video/style.css');
        
        $videos = $this->getService('Videoblock')->fetchAll()->asArrayOfObjects();
        usort($videos, array('HM_View_Infoblock_VideoBlock', '_sortByName'));
        $this->view->videos = $videos;
        $this->view->showEditLink= $isModerator = $this->getService('Activity')->isUserActivityPotentialModerator(
            $this->getService('User')->getCurrentUserId()
        );
        
        $content = $this->view->render('videoExternalBlock.tpl');
        
        return $this->render($content);
    }
    
    public function _sortByName($video1, $video2)
    {
        return ($video1->name < $video2->name) ? -1 : 1;
    }
}