<?php
class File_UploadController extends HM_Controller_Action
{
    public function init()
    {
        parent::init();

        $this->_helper->ContextSwitch()
            ->setAutoJsonSerialization(true)
            ->addActionContext('save', 'json')
            ->addActionContext('delete', 'json')
            ->initContext('json');
    }

    public function deleteAction()
    {
        /** @var HM_Files_FilesService $fileService */
        $fileService = $this->getService('Files');

        $fileId = $this->_getParam('file_id');

        $fileService->delete($fileId);

        return $this->responseJson(['success' => 1]);
    }

    public function saveAction()
    {
        /** @var HM_Files_FilesService $fileService */
        $fileService = $this->getService('Files');

        $view = $this->view;

        $result = array();

        if (isset($_FILES) && is_array($_FILES) && count($_FILES)) {

            foreach($_FILES as $name => $file) {

                $fileResult = $fileService->addFile($_FILES[$name]['tmp_name'], $_FILES[$name]['name']);

                $fileId = (int) $fileResult->file_id;

                $getUrl = $view->url(array(
                    'module'     => 'file',
                    'controller' => 'get',
                    'action'     => 'file',
                    'file_id'    => $fileId
                ));

                $deleteUrl = $view->url(array(
                    'module'     => 'file',
                    'controller' => 'upload',
                    'action'     => 'delete',
                    'file_id'    => $fileId
                ));

                $result[] = array(
                    'id'         => $fileId,
                    'name'       => $fileResult->name,
                    'size'       => $fileResult->file_size,
                    'url'        => $getUrl,
                    'deleteUrl' => $deleteUrl,
                    'error'      => 0,
                    'time'       => time()
                );
            }
        }

        $view->assign($result);
    }

    public function indexAction()
    {
        setlocale(LC_ALL, 'ru_RU.UTF-8'); //#18247 - если это не сделать, то по карйней мере под линуксом, при вызове basename, будет образана кирилица с начала имени файла до первого неюникодного символа

        $isHtml5Upload = $_POST['ishtml'] === 'yes';
        if ($isHtml5Upload) {
            Zend_Controller_Front::getInstance()->registerPlugin(
                new Zend_Controller_Plugin_ErrorHandler(
                    array(
                        'module' => 'default',
                        'controller' => 'error',
                        'action' => 'json'
                    )
                )
            );
        } else {
            $this->_helper->getHelper('layout')->disableLayout();
        }
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        if ($isHtml5Upload) {
            $this->_helper->ContextSwitch()->setAutoJsonSerialization(true)->addActionContext('index', 'json')->initContext('json');
            $this->_helper->ContextSwitch()->setAutoJsonSerialization(true)->addActionContext('get', 'json')->initContext('json');
            $this->_helper->ContextSwitch()->setAutoJsonSerialization(true)->addActionContext('post', 'json')->initContext('json');
            $this->_helper->ContextSwitch()->setAutoJsonSerialization(true)->addActionContext('put', 'json')->initContext('json');
            $this->_helper->ContextSwitch()->setAutoJsonSerialization(true)->addActionContext('delete', 'json')->initContext('json');
        } else {
            $this->getHelper('viewRenderer')->setNoRender();
        }

        $uploadDir = Zend_Registry::get('config')->path->upload->tmp;
        $result = array();
        if (isset($_FILES) && is_array($_FILES) && count($_FILES)) {
            foreach($_FILES as $name => $file) {
                $file = $uploadDir . basename($_FILES[$name]['name']);
                $fileNameWithExt = basename($_FILES[$name]['name']);

                if (file_exists($file)) {
                    $filenameParts = explode('.', basename($_FILES[$name]['name']));
                    $fileext = array_pop($filenameParts);
                    $filename = implode('.', $filenameParts);
                    $file = sprintf("%s%s_%s.%s", $uploadDir, $filename, time(), $fileext);
                }

                $size = $_FILES[$name]['size'];
                if (is_uploaded_file($_FILES[$name]['tmp_name']) && move_uploaded_file($_FILES[$name]['tmp_name'], $file)) {
                    if (isset($_POST['uniqid']) && strlen($_POST['uniqid']) && isset($_POST['sessid']) && strlen($_POST['sessid'])) {

                        $uniqid = $_POST['uniqid'];

                        $session = new Zend_Session_Namespace('upload');
                        if (!isset($session->$uniqid)) {
                            $session->$uniqid = array();
                        }

                        $fileTypeString = HM_Files_FilesModel::getFileTypeString($fileNameWithExt);

                        $convertableToPdf = HM_Files_FilesModel::isConvertableToPdf($fileTypeString);

                        $lastUpload = array(
                            'id'         => uniqid(mt_rand(0, 9999)),
                            'tmp_name'   => $file,
                            'name'       => $fileNameWithExt,
                            'size'       => (int)$size,
                            'error'      => 0,
                            'time'       => time(),
                            'type'       => $fileTypeString,
                            'convertableToPdf' => $convertableToPdf,
                            'convertToPdf' => $convertableToPdf,
                        );
                        $lastUpload['deleteUrl'] = $this->view->url(array(
                            'action'  => 'drop',
                            'uniqid'  => $uniqid,
                            'file_id' => $lastUpload['id']
                        ));
                        $session->{$uniqid}[] = $lastUpload;

                        $this->_purge($session);

                        if ($isHtml5Upload) {
                            $result[] = $lastUpload;
                        } else {
                            echo "success";
                        }
                    } else {
                        if ($isHtml5Upload) {
                            $result[] = array('error' => _('Ошибка загрузки файла'));
                        } else {
                            echo "error uniqid or sessid not found";
                        }
                    }
                } else {
                    if ($isHtml5Upload) {
                        $result[] = array(
                            'tmp_name' => $file,
                            'name'     => basename($_FILES[$name]['name']),
                            'size'     => (int)$size,
                            'error'    => $_FILES[$name]['error'],
                            'time'     => time()
                        );
                    } else {
                        echo "error ".$_FILES[$name]['error']." --- ".$_FILES[$name]['tmp_name']." %%% ".$file."($size)";
                    }
                }
            }
        }
        if ($isHtml5Upload) {
            $this->getResponse()->setHeader('Content-Disposition', 'inline; filename="files.json"');
            $this->getResponse()->setHeader('X-Content-Type-Options', 'nosniff');
            $this->getResponse()->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate');
            $this->getResponse()->setHeader('Pragma', 'no-cache');
            $httpAccept = $this->getRequest()->getHeader('Accept');
            if (!is_string($httpAccept)) {
                $httpAccept = '';
            }
            if (stripos($httpAccept, 'application/json') === false) {
                $this->getResponse()->setHeader('Content-Type', 'text/plain', true);
            }
            // FILTER tmp_name
            foreach ($result as $key => $value) {
                unset($result[$key]['tmp_name']);
            }
            $this->view->assign($result);
        }
    }

    public function dropAction()
    {
        $uniqid = $this->_getParam('uniqid');
        $fileId = $this->_getParam('file_id');

        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();

        $this->getResponse()->setHeader('Content-Type', 'text/plain');

        if ($uniqid !== null && strlen($uniqid) && $fileId !== null && strlen($fileId)) {
            $session = new Zend_Session_Namespace('upload');
            if (isset($session->$uniqid) && is_array($session->$uniqid) && count($session->$uniqid)) {
                foreach ($session->$uniqid as $index => $file) {
                    if ($file['id'] == $fileId) {
                        @unlink($file['tmp_name']);
                        unset($session->{$uniqid}[$index]);
                    }
                }

                echo 'probably something was done';
                return;
            }
        }

        echo 'nothing was done';
    }

    private function _translit($str) {
        return $this->getService('Unmanaged')->translit($str);
    }


    private function _purge($session)
    {
        foreach($session as $uniqid => $files) {
            if (is_array($files) && count($files)) {
                foreach($files as $index => $file) {
                    if (($file['time']+24*60*60) < time()) {
                        @unlink($file['tmp_name']);
                        unset($session->{$uniqid}[$index]);
                    }
                }
            }
        }
    }
}
