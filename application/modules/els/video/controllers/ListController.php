<?php
// @todo: Добавить ACL!!!!!!!!!!!!

class Video_ListController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;

	public function getVideoAction()
	{
        //$this->_helper->getHelper('layout')->disableLayout();
        //$this->getHelper('viewRenderer')->setNoRender();
        $html='<p>'._('Файл не найден').'</p>';
        $file_id = $this->_getParam('screencast',null);
        $filepath = $this->getService('FilesVideoblock')->getfilepath($file_id);
        $mediaelementPath=$this->view->serverUrl()."/js/tiny_mce/plugins/media/moxieplayer.swf";
        if (file_exists($filepath)){
            $filepath=$this->view->serverUrl().'/upload/files/'.basename($filepath);
            $html='<object width="320" height="240" type="application/x-shockwave-flash" data="'.$mediaelementPath.'">
                <param value="'.$mediaelementPath.'" name="src">
                <param value="url='.$filepath.'&amp;autoplay=1" name="flashvars">
                <param value="true" name="allowfullscreen">
                <param value="true" name="allowscriptaccess">

                </object>';
        }
        $this->view->html= $html;
    }

    public function indexAction()
    {

        if (!$this->getService('Activity')->isUserActivityPotentialModerator(
                $this->getService('User')->getCurrentUserId()
        )) {
            $this->_flashMessenger->addMessage(_('Нет прав на редактирование'));
            $this->_redirector->gotoSimple('index', 'index', 'default');
        }
        
        $this->view->setSubHeader(_('Видеоролики'));
        $select=$this->getService('FilesVideoblock')->getSelect();
        $select->from(array('v'=>'videoblock'), array('videoblock_id', 'name'))
            ->joinLeft(array('f' => 'files'),
                'v.file_id = f.file_id',array(
                    'file_id' => 'v.file_id',
                    'file_size' => 'f.file_size'
            ));
            
        $grid=$this->getGrid($select,
            array(
                'videoblock_id' => array('hidden' => true),
                'file_id' => array('hidden' => true),
                'name' => array('title' => _('Название')),
                'file_size' => array('hidden' => true), //array('title' => _('Размер')),
            ),
            array(
                'name' => null
            )
        );
        
        $grid->addAction(array(
                'module' => 'video',
                'controller' => 'list',
                'action' => 'edit'
            ),
            array('videoblock_id'),
            $this->view->svgIcon('edit', 'Редактировать')
        );
        
        $grid->addAction(array(
                'module' => 'video',
                'controller' => 'list',
                'action' => 'delete'
            ),
            array('videoblock_id'),
            $this->view->svgIcon('delete', 'Удалить')
        );

//        $grid->addMassAction(array('action' => 'delete-by'), _('Удалить'), _('Вы подтверждаете удаление отмеченных видеороликов?'));
        
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;
    }

    public function newAction()
    {
        $form=new HM_Form_Video();
        if ($this->_request->isPost()) {
            if ($form->isValid($this->_request->getPost())) {
                
                if ($form->file && $form->file->isUploaded() && $form->file->receive() && $form->file->isReceived()) {
    
                    $ret = array('error' => 0, 'msg' => _('Файл успешно загружен.'));
    
                    $file = $form->file->getFileName();
                    $fileName = basename($file);
                    $fileData = $this->getService('Files')->addFile(realpath($file), $fileName);
    
                    if($fileData){
                        $filePath = HM_Files_FilesService::getPath($fileData->file_id);
                        $this->getService('Files')->update(
                            array(
                                'file_id' => $fileData->file_id,
                                'path'	  => realpath($filePath),
                                'file_size'    => filesize($filePath)
                            )
                        );
    //                     $this->_flashMessenger->addMessage(_('Видеоролик успешно загружен'));
                    }
                }
                
                $embeddedCode = $this->_getParam('embedded_code');
                $embeddedCode = preg_replace('/width="(\d+)"/', 'width="100%"', $embeddedCode);

                // reset
                if ($this->_getParam('is_default')) {
                    $this->getService('FilesVideoblock')->update(array('is_default' => 0));
                }

                $this->getService('FilesVideoblock')->insert(array(
                    'file_id' => $fileData ? $fileData->file_id : 0,
                    'name' => $this->_getParam('name'),
                    'is_default' => $this->_getParam('is_default'),
                    'embedded_code' => $embeddedCode
                ));
                
                $this->_flashMessenger->addMessage(_('Видеоролик успешно создан'));
            $this->_redirector->gotoSimple('index', $this->_controller, $this->_module);
                
            } else {
//                $this->_flashMessenger->addMessage(_('Ошибка при создании видеоролика'));                
            }
            
//            $this->_redirector->gotoSimple('index', $this->_controller, $this->_module);
        }

        $this->view->form  = $form;
    }

    public function deleteAction()
    {
        $videoblockId = (int) $this->_getParam('videoblock_id', 0);
        
        $videoblock = $this->getService('Videoblock')->getOne($this->getService('Videoblock')->find($videoblockId));
        
        if ($videoblock->file_id) {
            $res = $this->getService('FilesVideoblock')->delete($videoblock->file_id);
            if($res > 0){
                $this->getService('Files')->delete($videoblock->file_id);
            }
        }
        
        $this->getService('Videoblock')->delete($videoblockId);
        
        $this->_flashMessenger->addMessage(_('Видеоролик успешно удален'));
        $this->_redirector->gotoSimple('index', $this->_controller, $this->_module);
    }

    public function deleteByAction()
    {
        $ids = explode(',', $this->_request->getParam('postMassIds_grid'));
        $service = $this->getService('FilesVideoblock');
        foreach ($ids as $value) {
            $file=$this->getService('FilesVideoblock')->getFilePath($value);
            $res = $service->delete(intval($value));
            if($res > 0){
                $this->getService('Files')->delete($value);
                unlink($file);
            }
        }
        $this->_flashMessenger->addMessage(_('Видеоролики успешно удалены'));
        $this->_redirector->gotoSimple('index', $this->_controller, $this->_module);
    }

    public function editAction()
    {
        $form = new HM_Form_Video();
        $videoblockId = (int) $this->_getParam('videoblock_id', 0);
        $this->view->setHeader(_('Редактирование видеоролика'));
        
        $videoblock = $this->getService('Videoblock')->getOne($this->getService('Videoblock')->find($videoblockId));

        if($this->_request->isPost() && $form->isValid($this->_request->getPost())) {

            if ($form->file && $form->file->isUploaded() && $form->file->receive() && $form->file->isReceived()) {


                if ($videoblock->file_id) {
                    $this->getService('Files')->delete($videoblock->file_id);
                }


                $file = $form->file->getFileName();
                $fileName = basename($file);
                $fileData = $this->getService('Files')->addFile(realpath($file), $fileName);

                if($fileData){
                    $filePath = HM_Files_FilesService::getPath($fileData->file_id);
                    $this->getService('Files')->update(
                        array(
                            'file_id' => $fileData->file_id,
                            'path'	  => realpath($filePath),
                            'file_size'    => filesize($filePath)
                        )
                    );
                    //                     $this->_flashMessenger->addMessage(_('Видеоролик успешно загружен'));
                }
            }


            $data = $form->getValues();

            // reset
            if ($data['is_default']) {
                $this->getService('FilesVideoblock')->update(array('is_default' => 0));
            }

            //$data['embedded_code'] = preg_replace('/width="(\d+)"/', 'width="100%"', $data['embedded_code']);
            $data['file_id'] = $fileData->file_id;

            unset($data['file']);
            $res = $this->getService('Videoblock')->update($data);

            if ($res){
                $this->_flashMessenger->addMessage(_('Видеоролик успешно обновлен'));
                $this->_redirector->gotoSimple('index', $this->_controller, $this->_module);
            }
        } else {
            $form->populate($videoblock->getValues());
        }
        $this->view->form=$form;
    }
    
    public function getEmbeddedAction()
    {
        $videoblockId = (int) $this->_getParam('videoblock_id', 0);
        if ($videoblock = $this->getService('Videoblock')->getOne($this->getService('Videoblock')->find($videoblockId))) {
            if ($videoblock->embedded_code)
            {
                exit($videoblock->embedded_code);
            }
            else
            {
                if ($videoblock->file_id)
                {
                    $file = $this->getService('Files')->getOne($this->getService('Files')->find($videoblock->file_id));

                    $temp = explode('.',$file->name);
                    $ext = $temp[count($temp) - 1];

                    $filename =  Zend_Registry::get('config')->src->upload->files . $videoblock->file_id.'.'.$ext;


                    exit('<video width="100%" controls style="background: #ffffff;">
                        <source src="/' . $filename . '" />
                        Video tag not supported. Download the video <a href="<?php echo $video->filename; ?>">here</a>.
                        <video>');


                }
            }
        }

        return '';
                
    }
}