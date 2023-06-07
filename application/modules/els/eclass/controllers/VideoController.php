<?php

class Eclass_VideoController extends HM_Controller_Action {

    public function init()
    {
        $this->subjectId = $this->_getParam('subject_id');
        $this->view->setBackUrl($this->view->url([
            'module' => 'subject',
            'controller' => 'lessons',
            'action' => $this->getService('User')->isEndUser() ? 'index' : 'edit',
            'subject_id' => $this->subjectId,
        ], null, true));

        parent::init();
    }

    public function indexAction()
    {
        $lessonId = $this->_request->getParam('lesson_id', 0);

        $data = $this->getService('Eclass')->getWebinarVideo($lessonId);
        $lesson = $this->getService('Lesson')->find($lessonId)->current();

        $this->view->setHeader($lesson->title);
        $this->view->setSubHeader(_('Просмотр видеозаписей'));

        $this->_subject = $this->getOne($this->getService('Subject')->find($this->subjectId));
        $videos = array();
        foreach ($data->video as $i => $video) {
            $obj = new StdClass;
            $obj->id = ($i + 1);
            $obj->src = $video->url;
            $obj->fileName = basename($video->url);
            $obj->course = $this->_subject->name;
            $obj->stamp = $video->stamp;

            $videos[] = $obj;
        }
        $this->view->playlistData = json_encode($videos);
    }
}