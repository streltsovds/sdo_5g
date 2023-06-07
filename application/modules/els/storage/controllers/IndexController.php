<?php

class Storage_IndexController extends HM_Controller_Action_Activity
{
    protected $subjectName;
    protected $subjectId;
    protected $isModerator;

    public function preDispatch()
    {
        // откатил все правки по задаче #13020 - неверная постановка задачи + само решение с багами
        try {
            parent::preDispatch();
        } catch (HM_Permission_Exception $e) {}

        $this->subjectName = $this->getParam('subject', '');
        if (empty($this->subjectName)) {
            $this->subjectName = null;
        }
        $this->subjectId = (int) $this->getParam('subject_id', 0);

        $this->view->subjectName = $this->subjectName;
        $this->view->subjectId   = $this->subjectId;
        $this->view->isModerator = $this->isModerator = $this->getService('Storage')->isCurrentUserActivityModerator();
    }

    public function indexAction()
    {
        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(),  array(
            HM_Role_Abstract_RoleModel::ROLE_HR, HM_Role_Abstract_RoleModel::ROLE_GUEST)
        )) {
            $this->_redirector->gotoUrl($this->view->url(array('module' => 'default', 'controller' => 'index', 'action' => 'index')));
        }

//        // TODO - move to Unit tests it
//         $fsService = $this->getService('StorageFileSystem');
//         $items = $fsService->fetchAll();
//         foreach ($items as $item) {
//             $p = $fsService->getPath($item);
//             if (!file_exists($p)) {
//                 print_r($item);
//                 print $p;
//                 exit;
//             }
//         }
    }

    public function elfinderAction()
    {
        $cmd = $_REQUEST['cmd'];
        $elFinder = new HM_ElFinder($this->subjectName, $this->subjectId, $this->isModerator);

        if (!$cmd && $_SERVER["REQUEST_METHOD"] == 'POST') {
            header("Content-Type: text/html");
            exit(json_encode(['error' => 'Data exceeds the maximum allowed size']));
        }

        if ($cmd && !method_exists($elFinder, $cmd)) {
            header("Content-Type: text/html");
            exit(json_encode(['error' => 'Unknown command']));
        }

        $connector = new elFinderConnector($elFinder);
        try { $connector->run();
        } catch (Exception $e) {}
    }
}