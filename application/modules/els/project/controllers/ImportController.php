<?php
class Project_ImportController extends HM_Controller_Action_Import
{
    protected $_importManagerClass = 'HM_Project_Import_Manager';

    public function indexAction()
    {
        parent::indexAction(); // required

        if (!$this->_valid && $this->_importManager->getCount()) {
            $this->_flashMessenger->addMessage(_('Новые курсы не найдены'));
            $this->_redirector->gotoSimple('index', 'list', 'project');
        }

    }

    public function processAction()
    {

        $importManager = new HM_Project_Import_Manager();
        if ($importManager->restoreFromCache()) {
            $importManager->init(array());
        } else {
            $importManager->init($this->_importService->fetchAll());
        }

        if (!$importManager->getCount()) {
            $this->_flashMessenger->addMessage(_('Новые курсы не найдены'));
            $this->_redirector->gotoSimple('index', 'list', 'user');
        }

        $importManager->import();

        $this->_flashMessenger->addMessage(sprintf(_('Были добавлены %d курсов и обновлены %d курсов'), $importManager->getInsertsCount(), $importManager->getUpdatesCount()));
        $this->_redirector->gotoSimple('index', 'list', 'project');
    }

    public function classifierAction()
    {
        $this->view->setHeader(_('Импортировать классификацию учебных курсов из csv'));

        $form = new HM_Form_Upload();
        $form->getElement('file')->setOptions(
            array(
                'Label' => _('Файл данных (csv)'),
                'Destination' => Zend_Registry::get('config')->path->upload->tmp,
                'Validators' => array(
                    array('Count', false, 1),
                    array('Extension', false, 'csv')
                ),
                'file_size_limit' => 0,
                'file_types' => '*.csv',
                'file_upload_limit' => 1,
                'Required' => true
            )
        );
        $processed = array();
        if ($this->_request->isPost() && $form->isValid($this->_request->getPost())) {
            if ($form->file->isUploaded()) {
                $form->file->receive();
                if ($form->file->isReceived()) {
                    if ($fh = fopen($form->file->getFileName(), 'r')) {
                        $count = 0;

                        $projects = array();
                        $collection = $this->getService('Project')->fetchAll();
                        if (count($collection)) {
                            foreach($collection as $item) {
                                $projects[trim($item->name)] = $item->projid;
                            }
                        }

                        $classifiers = array();
                        $collection = $this->getService('Classifier')->fetchAll();
                        if (count($collection)) {
                            foreach($collection as $item) {
                                $classifiers[trim($item->name)] = $item->classifier_id;
                            }
                        }

                        unset($collection);
                        if (count($projects) && count($classifiers)) {
                            while(($data = fgetcsv($fh, 0, ';', '"')) !== false) {
                                $count++;
                                if ($count <= 1) continue;

                                if (count($data) == 2) {
                                    $projectName = trim($data[0]);
                                    $classifierName = trim($data[1]);

                                    if (isset($projects[$projectName]) && isset($classifiers[$classifierName])) {
                                        if (!$this->getService('Classifier')->linkExists($projects[$projectName], HM_Classifier_Link_LinkModel::TYPE_SUBJECT, $classifiers[$classifierName])) {
                                            $this->getService('Classifier')->linkItem($projects[$projectName], HM_Classifier_Link_LinkModel::TYPE_SUBJECT, $classifiers[$classifierName]);
                                            $processed[$projectName] = $classifierName;
                                        }
                                    }
                                }
                            }
                        }
                        fclose($fh);
                    }

                    @unlink($form->file->getFileName());

                    $form = false;
                }
            }
        }

        $this->view->processed = $processed;
        $this->view->form = $form;
    }
}