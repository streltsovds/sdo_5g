<?php
class Idea_ListController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;

    public function indexAction() {


        $select = $this->getService('Idea')->getSelect();

        $select->from(array('i' => 'idea'),
            array(
                'idea_id',
                'name',
                'description',
                'status',
                'date_created',
                'classifiers' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT cl.classifier_id)'),
                'tags'=>'idea_id'
            )
        )->joinLeft(
            array('cl' => 'classifiers_links'),
            'i.idea_id = cl.item_id AND cl.type = ' . HM_Classifier_Link_LinkModel::TYPE_IDEA,
            array()
        )
        ->group(array('idea_id','name','description','status','date_created'));

/*        ->joinLeft(array('d' => 'structure_of_organ'),
            'd.mid = t1.MID',
            array(
                'position' => 'name'
            ))
        ->group(
            array(
                't1.MID',
                't1.LastName',
                't1.FirstName',
                't1.Patronymic',
                't1.Login',
                't1.Email',
                't1.email_confirmed',
                't1.Registered',
                't1.Password',
                't1.blocked',
                't1.isAD',
                'd.soid',
                'd.name',
                'd.is_manager',
                't1.duplicate_of',
                'sub.is_absent'
            )
        );
*/

// print $select; exit;


        $grid = $this->getGrid($select,
            array(
                'idea_id' => array('hidden' => true),
                'name' => array('title' => _('Формулировка')),
                'status' => array('title' => _('Статус')),
                'date_created' => array('title' => _('Дата создания')),
                'tags' => array('title' => _('Ключевые слова')),
                'classifiers' => array('title' => _('Классификация')),
            ),
            array('name' => null,
                'login' => null,
                'date_created' => array('render' => 'DateSmart'),
                'status' => array('values' => HM_Idea_IdeaModel::getStates()),
                'tags' => array('callback' => array('function' => array($this, 'filterTags'))),
            ),
            'grid',
            'notempty'
        );
        $grid->beHappy();

        $grid->addAction(array(
            'module' => 'idea',
            'controller' => 'list',
            'action' => 'edit'
        ),
            array('idea_id'),
            $this->view->svgIcon('edit', 'Редактировать')
        );

        $grid->addAction(array(
            'module' => 'idea',
            'controller' => 'list',
            'action' => 'delete'
        ),
            array('idea_id'),
            $this->view->svgIcon('delete', 'Удалить')
        );

        $grid->updateColumn('tags', array(
            'callback' => array(
                'function'=> array($this, 'displayTags'),
                'params'=> array('{{tags}}', $this->getService('TagRef')->getIdeaType())
            )
        ));
        $grid->updateColumn('status',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'updateStatus'),
                    'params' => array('{{status}}')
                )
            )
        );

        $grid->updateColumn('date_created', array(
            'format' => array(
                'DateTime',
                array('date_format' => Zend_Locale_Format::getDateTimeFormat())
            ),
        )
        );

        $grid->updateColumn('classifiers',
            array('callback' =>
                array('function' => array($this, 'classifiersCache'),
                      'params'   => array('{{classifiers}}', $select)
                )
            )
        );

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();

        $this->view->grid = $grid;
    }

    public function newAction() 
    {
        $form = new HM_Form_Idea();

        if ($this->_request->isPost()) {
            if ($form->isValid($this->_request->getParams())) {
                $array = array('name' => $form->getValue('name'),
                    'name' => $form->getValue('name'),
                    'status' => $form->getValue('status'),
                    'anonymous' => $form->getValue('anonymous'),
                    'description' => $form->getValue('description'),
                    'date_created' => date('Y-m-d')
                );

                $idea = $this->getService('Idea')->insert($array);

                $this->_saveFiles($idea, $form);
                
            } else {
                $form->populate($this->_request->getParams());
            }
        }

        $this->view->form = $form;
    }

    /**
     * Экшн для редактирования пользователя
     */
    public function editAction() 
    {
        $ideaId = (int) $this->_request->getParam('idea_id', 0);

        $idea = $this->getOne($this->getService('Idea')->find($ideaId));

        $form = new HM_Form_Idea();


        if ($this->_request->isPost()) {

            if ($form->isValid($this->_request->getParams())) {

                $array = array(
                    'idea_id' => $ideaId,
                    'name' => $form->getValue('name'),
                    'status' => $form->getValue('status'),
                    'anonymous' => $form->getValue('anonymous'),
                    'description' => $form->getValue('description')
                );

                $idea = $this->getService('Idea')->update($array);

                $this->_saveFiles($idea, $form);

                $this->_flashMessenger->addMessage(_('Идея отредактирована успешно!'));
                $this->_redirector->gotoUrl($form->getValue('cancelUrl'));

            } else {
                $elem = $form->getElement('photo');
//                $elem->setOptions(array('user_id' => $userId));
                // $form->populate($arr);
                $arr = array(
                    'name' => $idea->name,
                    'description' => $idea->description,
                    'status' => $idea->status,
                    'anonymous' => $idea->anonymous,
                );

                // чтобы тэги не сбрасывались после неуспешной валидации
                $post = $this->_request->getParams();
                if (isset($post['tags']))
                    $post['tags'] = $this->getService('Tag')->convertAllToStrings($post['tags']);
                $form->populate(array_merge($arr, $post));
            }
        } else {

            $urlsDB = $this->getService('IdeaUrl')->fetchAll(array('idea_id = ?' => $idea->idea_id))->getList('url');
            $urls = array();    
            foreach($urlsDB as $url) {
                $urls[] = array('variant'=>$url);
            }
            $arr = array(
                'name' => $idea->name,
                'description' => $idea->description,
                'status' => $idea->status,
                'anonymous' => $idea->anonymous,
                'urls' => $urls
            );

            $elem = $form->getElement('photo');
//            $elem->setOptions(array('user_id' => $userId));
            $form->populate($arr);

        }
        $this->view->form = $form;
    }
/*
    protected function receiveResume($resumeFile, $candidateId, $update = false)
    {
        if ($resumeFile->isUploaded()) {
            $candidateService = $this->getService('RecruitCandidate');
            $extension = 'docx';

            if ($update) {
                $resumeFileOld = realpath($candidateService->getResumeFile($candidateId));
                if ($resumeFileOld) {
                    @unlink($resumeFileOld);
                }

                $extension = pathinfo($resumeFile->getTransferAdapter()->getFileName(), PATHINFO_EXTENSION);
            }

            $path = $this->getService('User')->getPath(Zend_Registry::get('config')->path->upload->resume, $candidateId);
            $resumeFile->addFilter('Rename', $path . $candidateId . '.' . $extension, 'resume_file', array('overwrite' => true)); // @todo: у меня что-то не работает overwrite..?
            $resumeFile->receive();
        }
*/
    /**
     * Экшн для обзора пользователя
     */
    public function viewAction() {
//        $this->view->data = $idea;
    }

    public function updateStatus($id) {
        $states = HM_Idea_IdeaModel::getStates();
        return $states[$id];

    }
/*
    public function updateDate($date)
    {
        if (!strtotime($date)) return '';
        return $date;
    }
*/


    public function classifiersCache($field, $select){

        if($this->classifierCache === array()){
            $smtp = $select->query();
            $res = $smtp->fetchAll();
            $tmp = array();
            foreach($res as $val){
                $tmp[] = $val['classifiers'];
            }
            $tmp = implode(',', $tmp);
            $tmp = explode(',', $tmp);
            $tmp = array_unique($tmp);
            $this->classifierCache = $this->getService('Classifier')->fetchAll(array('classifier_id IN (?)' => $tmp));
        }

        $fields = array_unique(explode(',', $field));
        $fields = array_filter($fields, array(get_class($this), '_filterCachedClassifiers'));
        $result = (is_array($fields) && (count($fields) > 1)) ? array('<p class="total">' . Zend_Registry::get('serviceContainer')->getService('Classifier')->pluralFormCount(count($fields)) . '</p>') : array();
        foreach($fields as $value){
            $tempModel = $this->classifierCache->exists('classifier_id', $value);
            $result[] = "<p>{$tempModel->name}</p>";
        }
        if($result)
            return implode(' ',$result);
        else
            return _('Нет');
    }

    protected function _filterCachedClassifiers($id) {
        return $this->classifierCache->exists('classifier_id', $id);
    }


    private function _saveFiles($idea, Zend_Form $form)
    {
        $ideaId = $idea->idea_id;
        $urls = $form->getValue('urls');
        $variants = array();
        foreach($urls as $i=>$url) {
            if($i==='new') {
                $variants = array_merge($variants, $url['variant']);//;
            } else {
                $variants[] = $url['variant'];
            }
        }

        $urls = $variants;//$urls['new']['variant'];
        $urlsDB = $this->getService('IdeaUrl')->fetchAll(array('idea_id = ?' => $ideaId));
        $bNeedRefresh = count($urlsDB) != count($urls);
        if($bNeedRefresh) {
            $this->getService('IdeaUrl')->deleteBy(array('idea_id = ?' => $ideaId));
            foreach($urls as $url) {
                $this->getService('IdeaUrl')->insert(array('idea_id' => $ideaId, 'url'=>$url));
            }
        } else {
            foreach($urlsDB as $i=>$url) {
                $url = $url->getValues();
                $url['url'] = $urls[$i];
                $this->getService('IdeaUrl')->update($url);
            }
        }

        $classifiers = $form->getClassifierValues();
        $this->getService('Classifier')->unlinkItem($ideaId, HM_Classifier_Link_LinkModel::TYPE_IDEA);
        if (is_array($classifiers) && count($classifiers)) {
            foreach($classifiers as $classifierId) {
                if ($classifierId > 0) {
                    $this->getService('Classifier')->linkItem($ideaId, HM_Classifier_Link_LinkModel::TYPE_IDEA, $classifierId);
                }
            }
        }

       // Обрабатываем фотку
        $photo = $form->getElement('photo');
        if($photo->isUploaded()){
            $path = Zend_Registry::get('config')->path->upload->idea;
            $photo->addFilter('Rename', $path . $ideaId . '.jpg', 'photo', array( 'overwrite' => true));
            unlink($path . $ideaId . '.jpg');
            $photo->receive();
            $img = PhpThumb_Factory::create($path . $ideaId . '.jpg');
            $img->resize(150, 300);
            $img->save($path . $ideaId . '.jpg');
        }

        // метки
        $tags = array_unique($form->getParam('tags', array()));
        $this->getService('Tag')->updateTags($tags, $ideaId, $this->getService('TagRef')->getIdeaType());

/*
//        $populatedFiles = $this->getService('TaskVariant')->getPopulatedFiles($variant->variant_id);
        $deletedFiles = array();//$form->files->updatePopulated($populatedFiles);
        if(count($deletedFiles))
        {
            $this->getService('Files')->deleteBy(array('file_id IN (?)' => array_keys($deletedFiles)));
            $this->getService('TaskVariantFile')->deleteBy(array('file_id IN (?)' => array_keys($deletedFiles)));
        }
//
*/      // !!!!! УДАЛЕНИЕ ФАЙЛОВ НЕ СДЕЛАНО

        $files = $form->getElement('files');
        if ($files->isUploaded())
        {   
            if ($files->receive())
            {   
                $files = $files->getFileName();
                if(!is_array($files)) {
                    $files = array($files);
                }

                $this->getService('Files')->addFile($file, $fileInfo['basename'], HM_Files_FilesModel::ITEM_TYPE_IDEA, $ideaId);

                foreach($files as $file)
                {   $fileInfo = pathinfo($file);
                    $file = $this->getService('Files')->addFile($file, $fileInfo['basename'], HM_Files_FilesModel::ITEM_TYPE_IDEA, $ideaId);
                }
            }
        }
    }

}