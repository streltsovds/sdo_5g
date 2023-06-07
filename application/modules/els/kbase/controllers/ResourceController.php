<?php
/*
 * 5G
 * Новая страница с действиями над инфоресурсами
 */

class Kbase_ResourceController extends HM_Controller_Action_Resource
{
    protected $notDeleted;

    public function indexAction()
    {
        $this->view->resource = $this->_resource;
    }

    protected function _redirectToIndex()
    {
        if ($this->_resource) {

            $redirect = [
                'module' => 'kbase',
                'controller' => 'resource',
                'action' => 'index',
                'resource_id' => $this->_resource->resource_id,
            ];
            if ($this->_resource->subject_id) {
                $redirect['subject_id'] = $this->_resource->subject_id;
            }
            $this->_redirector->gotoUrl($this->view->url($redirect));
        }
        $this->_redirector->gotoSimple('index', 'resources', 'kbase');
    }


    public function createAction()
    {
        $this->view->setSubHeader(_('Создание информационного ресурса'));

        $subjectId = $this->_getParam('subject_id');

        $form = new HM_Form_Resource();

        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {

                $title = $form->getValue('title');
                $createType = $this->_getParam('create_type');

                /** @var HM_Material_MaterialService $materialService */
                $materialService = $this->getService('Material');
                switch ($createType) {
                    case HM_Form_Resource::CREATE_TYPE_AUTODETECT:
                        $insertValue = $form->getValue('code');

                        $convertToPdf = $request->getParam('file_convertToPdf');

                        if (!$insertValue) {
                            $fileElement = $form->getElement('file');
                            try {
                                $fileElement->receive();
                            } catch (Zend_File_Transfer_Exception $exception) {
                                $this->_flashMessenger->addMessage([
                                    'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                                    'message' => _('Файл не выбран, создание инфоресурса было отменено.')
                                ]);
                                $this->_redirectToIndex();
                            }

                            if ($fileElement->isReceived()) {
                                $insertValue = realpath($fileElement->getFileName());
                            }
                        }

                        $resource = $materialService->importResource(
                            $insertValue,
                            null,
                            $title,
                            $subjectId,
                            null,
                            $convertToPdf
                        );

                        break;
                    case HM_Form_Resource::CREATE_TYPE_CARD:
                        $resource = $materialService->createCard(
                            $title,
                            $subjectId
                        );
                        break;
                    case HM_Form_Resource::CREATE_TYPE_MATERIAL:
                        $resource = $materialService->createDefault(
                            HM_Event_EventModel::TYPE_RESOURCE,
                            $title,
                            $subjectId
                        );
                        $resource->type = HM_Resource_ResourceModel::TYPE_HTML;
                        $this->getService('Resource')->update($resource->getData());
                        break;
                }
            }

            if ($resource) {
                $resource->location = HM_Resource_ResourceModel::LOCALE_TYPE_GLOBAL;
                $this->getService('Resource')->update($resource->getData());

                $this->_resource = $resource;
                $this->_flashMessenger->addMessage(_('Ресурс успешно создан'));
            } else {
                $this->_flashMessenger->addMessage([
                    'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                    'message' => _('Произошла ошибка при создании ресурса')
                ]);
            }

            

            if ($redirectUrl = $this->_getParam('redirectUrl')) {

                if (!strpos($redirectUrl, 'resource_id')) $redirectUrl = sprintf('%s/resource_id/%d', trim($redirectUrl, "/"), $resource->resource_id);
                $this->_redirector->gotoUrl(urldecode($redirectUrl));

            } elseif ($createType == HM_Form_Resource::CREATE_TYPE_MATERIAL) {

                // если выбран конструктор - принудительно к конструктору слайдов
                $this->_redirector->gotoUrl($this->view->url([
                    'module' => 'kbase',
                    'controller' => 'resource',
                    'action' => 'edit',
                    'resource_id' => $resource->resource_id,
                ]));

            } elseif ($createType == HM_Form_Resource::CREATE_TYPE_CARD) {

                // если только карточка - к редактированию карточки
                $this->_redirector->gotoUrl($this->view->url([
                    'module' => 'kbase',
                    'controller' => 'resource',
                    'action' => 'edit-card',
                    'resource_id' => $resource->resource_id,
                ]));

            } else {
                $this->_redirectToIndex();
            }
        }

        $this->view->form = $form;
    }

    public function cardAction()
    {
        $this->view->fields = $this->view->card($this->_resource, $this->_resource->getCardFields(), [], true);
        $this->view->title = _('Карточка информационного ресурса');
    }

    public function editCardAction()
    {
        $this->view->setSubHeader(_('Редактирование информационного ресурса'));

        $form = new HM_Form_ResourceCard();
        $request = $this->getRequest();

        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {

                $data = $form->getValues();
                $tags = $data['tags'];
                $data['related_resources'] = $data['related_resources'] ?: '';

                unset($data['tags']);
                unset($data['cancel']);
                unset($data['submit_and_redirect']);
                unset($data['stepper']);

                $classifiers = [];
                foreach ($data as $key => $value) {
                    if (substr_count($key, 'classifier')) {
                        list(, $classifierTypeId) = explode('_', $key);
                        if (!empty($value)) $classifiers[$classifierTypeId] = $value;
                        unset($data[$key]);
                    }
                }

                if (!is_array($data['criteria'])) $data['criteria'] = [];
                $this->_assignCriteria($this->_resourceId, $data['criteria'], HM_At_Criterion_CriterionModel::TYPE_CORPORATE);
                unset($data['criteria']);

                if ($form->getValue('icon') != null) {
                    HM_Resource_ResourceService::updateIcon($this->_resourceId, $form->getElement('icon'));
                    unset($data['icon']);
                }

                $this->getService('Resource')->update($data);

                $tagsIds = $this->getService('Tag')->updateTags(
                    $tags, $this->_resourceId, HM_Tag_Ref_RefModel::TYPE_RESOURCE, false
                );

                $this->getService('ClassifierLink')->deleteBy(
                    $this->quoteInto(
                        array('item_id = ?', ' AND type = ?'),
                        array($this->_resourceId, HM_Classifier_Link_LinkModel::TYPE_RESOURCE)
                    )
                );

                foreach ($classifiers as $classifierType => $classifiersOfType) {
                    foreach ($classifiersOfType as $classifierId) {
                        $this->getService('ClassifierLink')->insert([
                            'item_id' => $this->_resourceId,
                            'classifier_id' => $classifierId,
                            'type' => HM_Classifier_Link_LinkModel::TYPE_RESOURCE,
                        ]);
                    }
                }

                if ($redirectUrl = $this->_getParam('redirectUrl')) {
                    if (!strpos($redirectUrl, 'resource_id')) {
                        $redirectUrl = sprintf('%s/resource_id/%d', trim($redirectUrl, "/"), $this->_resource->resource_id);
                    }
                    $this->_redirector->gotoUrl(urldecode($redirectUrl));
                }

                $this->_flashMessenger->addMessage(_('Информационный ресурс успешно отредактирован'));
                $this->_redirectToIndex();
            }
        } else {
            $this->setDefaults($form);
        }

        $this->view->form = $form;
    }

    /*
     *  proxy к специализированным конструкторам
     */
    public function editAction()
    {
        if($this->_resource->location === HM_Resource_ResourceModel::LOCALE_TYPE_GLOBAL) {

            // Использование в сайдбаре "дополнительных материалов"
            $usedInSubjects = $this->getService('SubjectResource')->fetchAll(
                $this->quoteInto(
                    ['resource_id = ?'],
                    [$this->_resource->resource_id]
                )
            );

            $usedInLessons = $this->getService('Lesson')->fetchAll(
                $this->quoteInto(
                    ['material_id = ?', ' AND typeID = ?'],
                    [$this->_resource->resource_id, HM_Event_EventModel::TYPE_RESOURCE]
                )
            );


            $subjectsCount = $usedInSubjects->count();
            $lessonsCount = $usedInLessons->count();
            if ($subjectsCount + $lessonsCount) {
                $message = _('Внимание! Данный информационный ресурс используется в ');

                if ($subjectsCount)
                    $message .= $this->getService('Subject')->pluralFormCountPrepositionalCase($subjectsCount);

                if ($subjectsCount && $lessonsCount)
                    $message .= _(' и ');

                if ($lessonsCount)
                    $message .= $this->getService('Lesson')->pluralFormCountPrepositionalCase($lessonsCount);

                $this->view->message = $message;
            }
        }

        $action = sprintf('edit-resource-type-%s', HM_Resource_ResourceModel::getTypeString($this->_resource->type));
        $this->_forward($action, 'resource', 'kbase', ['resource_id' => $this->_resource->resource_id]);
    }


    /*** Формы по типам ресурсов ***/
    public function editResourceTypeExternalAction()
    {
        $this->view->setSubHeader(_('Редактирование материала'));
        $this->view->idType = $this->_getParam('idType', 0);

        $form = new HM_Form_ResourceTypeExtended();

        /** @var HM_Resource_ResourceService $resourceService */
        $resourceService =  $this->getService('Resource');

        /** @var Zend_Controller_Request_Http $request */
        $request = $this->getRequest();
        if ($request->isPost()) {


            if ($form->isValid($request->getParams())) {

                /** @var Zend_Form_Element_File $file */
                $file = $form->getElement('file');
                try {
                    $file->receive();
                } catch (Zend_File_Transfer_Exception $ex) {
//                     ignore
                }

                $convertToPdf = $request->getParam('file_convertToPdf');

                if ($file->isReceived()) {
                    $filePath = $file->getFileName();
                    $fileName = null;
                    $title = $this->_resource->title;
                    $import = true;
                } else {
                    $originalFileSrc = $this->_resource->getOriginFileSrc();
                    $withOriginal = file_exists($originalFileSrc);

                    $filePath = $this->_resource->getFilePath();//$resourceService->getFileSrc($this->_resource->resource_id);

                    $fileName =$this->_resource->filename;
                    $title =$this->_resource->title;
                    $import = !$withOriginal && $convertToPdf;
                }

                if ($import) {
                    $this->getService('Material')->importResource(
                        $filePath,
                        $fileName,
                        $title,
                        null,
                        $this->_resource->resource_id,
                        $convertToPdf
                    );
                }

                if ($redirectUrl = $this->_getParam('redirectUrl')) {
                    if (!strpos($redirectUrl, 'resource_id')) {
                        $redirectUrl = sprintf('%s/resource_id/%d', trim($redirectUrl, "/"), $this->_resource->resource_id);
                    }
                    $this->_redirector->gotoUrl(urldecode($redirectUrl));
                }

                $this->_flashMessenger->addMessage(_('Информационный ресурс успешно отредактирован'));
                $this->_redirectToIndex();
            }
        } else {
            $this->setDefaults($form);
        }

        $this->view->form = $form;
    }

    public function editResourceTypeHtmlAction()
    {
        $this->view->setSubHeader(_('Редактирование материала'));

        $form = new HM_Form_ResourceTypeHtml();
        $request = $this->getRequest();

        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {

                if ($url = $this->_getParam('content_url')) {
                    if (strpos($url, 'http') === false) $url = sprintf('https://%s', trim($url));
                    $this->_resource->type = HM_Resource_ResourceModel::TYPE_URL;
                    $this->_resource->url = $url;
                    $this->_resource->edit_type = HM_Resource_ResourceModel::EDIT_TYPE_URL;

                } elseif ($content = $this->_getParam('content_slider')) {
                    // @todo: проверить
                    $this->_resource->type = HM_Resource_ResourceModel::TYPE_HTML;
                    /**
                     * resource->content меняется отдельным запросом из iframe редактора на
                     * @see Resource_IndexController::editorAction(),
                     * не надо перетирать здесь
                     */
//                    $this->_resource->content = $content;
                    $this->_resource->edit_type = HM_Resource_ResourceModel::EDIT_TYPE_SLIDER;
                } else {
                    $contentEmbed = $this->_getParam('content_embed');
                    $contentPage = $this->_getParam('content_page');

                    $isYoutubeShortLink = (0 === strpos($contentEmbed, "https://youtu.be/"));
                    $isYoutubeLink = (0 === strpos($contentEmbed, "https://www.youtube.com/"));
                    $youtubeLinkHash = false;

                    if ($isYoutubeShortLink) {
                        preg_match('#^https://youtu.be/(.*)#i', $contentEmbed, $linkHashMatches);
                        $youtubeLinkHash = $linkHashMatches[1];
                    } elseif ($isYoutubeLink) {
                        preg_match('#^https://www.youtube.com/watch\?v=(.*)#i', $contentEmbed, $linkHashMatches);
                        $youtubeLinkHash = $linkHashMatches[1];
                    }

                    if($youtubeLinkHash) {
                        $contentEmbed = <<<EMBED
<iframe
    width="560"
    height="315"
    src="https://www.youtube.com/embed/$youtubeLinkHash"
    frameborder="0"
    allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen
></iframe>
EMBED;

                    }

                    $content = max($contentEmbed, $contentPage);

                    if($contentEmbed) {
                        $this->_resource->edit_type = HM_Resource_ResourceModel::EDIT_TYPE_CODE;
                    } elseif($contentPage) {
                        $this->_resource->edit_type = HM_Resource_ResourceModel::EDIT_TYPE_WYSIWYG;
                    }

                    $this->_resource->type = HM_Resource_ResourceModel::TYPE_HTML;
                    $this->_resource->content = $content;
                }

                $this->getService('Resource')->update($this->_resource->getData());

                if ($redirectUrl = $this->_getParam('redirectUrl')) {
                    if (!strpos($redirectUrl, 'resource_id')) {
                        $redirectUrl = sprintf('%s/resource_id/%d', trim($redirectUrl, "/"), $this->_resource->resource_id);
                    }
                    $this->_redirector->gotoUrl(urldecode($redirectUrl));
                }

                $this->_flashMessenger->addMessage(_('Информационный ресурс успешно отредактирован'));
                $this->_redirectToIndex();
            }
        } else {
            $this->setDefaults($form);
        }

        $this->view->form = $form;
    }

    public function editResourceTypeUrlAction()
    {
        $this->view->setSubHeader(_('Редактирование материала'));

        $form = new HM_Form_ResourceTypeUrl();
        $request = $this->getRequest();

        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {

                if ($url = $this->_getParam('url')) {
                    if (strpos($url, 'http') === false) $url = sprintf('https://%s', trim($url));
                    $this->_resource->url = $url;
                }

                $this->getService('Resource')->update($this->_resource->getData());

                if ($redirectUrl = $this->_getParam('redirectUrl')) {
                    if (!strpos($redirectUrl, 'resource_id')) {
                        $redirectUrl = sprintf('%s/resource_id/%d', trim($redirectUrl, "/"), $this->_resource->resource_id);
                    }
                    $this->_redirector->gotoUrl(urldecode($redirectUrl));
                }

                $this->_flashMessenger->addMessage(_('Информационный ресурс успешно отредактирован'));
                $this->_redirectToIndex();
            }
        } else {
            $this->setDefaults($form);
        }

        $this->view->form = $form;
    }

    public function editResourceTypeFilesetAction()
    {
        $this->view->setSubHeader(_('Редактирование материала'));
        $form = new HM_Form_ResourceTypeFileset();
        $request = $this->getRequest();

        if ($request->isPost()) {
            $successEdit = false;
            if ($form->isValid($request->getParams())) {

                $file = $form->getElement('file');
                $file->receive();

                if ($file->isReceived()) {
                    $filename = $file->getFileName();
                    $pathinfo = pathinfo($filename);
                    $title = $pathinfo['filename'];
                    $fileRealpath = realpath($filename);

                    if (is_file($fileRealpath) && is_readable($fileRealpath)) {
                        $fileType = HM_Files_FilesModel::getFileType($fileRealpath);
                        $pathInfo = pathinfo($fileRealpath);

                        if ($fileType == HM_Files_FilesModel::FILETYPE_ZIP) {
                            $unzipPath = realpath(Zend_Registry::get('config')->path->upload->tmp) . '/unzip';

                            if (!is_dir($unzipPath)) {
                                mkdir($unzipPath, 0755);
                            } else {
                                $this->getService('Course')->emptyDir($unzipPath);
                            }

                            $filter = new Zend_Filter_Decompress([
                                'adapter' => 'Zip',
                                'options' => ['target' => $unzipPath],
                            ]);
                            $filter->filter($fileRealpath);
                            $indexFilePath = $this->getService('Material')->autodetectHtmlSite($unzipPath);

                            if ($indexFilePath) {
                                //$this->_resource->title = $title;
                                $this->_resource->url = $indexFilePath;
                                $this->getService('Resource')->update($this->_resource->getData());

                                $targetPath = realpath(Zend_Registry::get('config')->path->upload->public_resource) . '/' . $this->_resource->resource_id . '/';
                                if (!is_dir($targetPath)) {
                                    mkdir($targetPath, 0755);
                                } else {
                                    $this->getService('Course')->emptyDir($targetPath);
                                }
                                $this->getService('Course')->copyDir($unzipPath, $targetPath);
                                $successEdit = true;
                            }
                        }
                    }
                }

                if ($redirectUrl = $this->_getParam('redirectUrl')) {
                    if (!strpos($redirectUrl, 'resource_id')) {
                        $redirectUrl = sprintf('%s/resource_id/%d', trim($redirectUrl, "/"), $this->_resource->resource_id);
                    }
                    $this->_redirector->gotoUrl(urldecode($redirectUrl));
                }

                if($successEdit) {
                    $this->_flashMessenger->addMessage(_('Информационный ресурс успешно отредактирован'));
                } else {
                    $this->_flashMessenger->addMessage(array(
                        'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                        'message' => _('При сохранении ресурса произошла ошибка.')
                    ));
                }

                $this->_redirectToIndex();
            }
        } else {

            $this->setDefaults($form);
        }

        $this->view->form = $form;
    }

    public function setDefaults(Zend_Form $form)
    {
        $data = $this->_resource->getData();
        $tabsElem = $form->getElement('tabs');

        $data['related_resources'] = $this->getService('Resource')
            ->setDefaultRelatedResources($data['related_resources']);

        if($tabsElem) {
            switch ((int) $this->_resource->edit_type) {
                case HM_Resource_ResourceModel::EDIT_TYPE_WYSIWYG:
                    $tabsElem->setAttrib('default', HM_Form_ResourceTypeHtml::TAB_PAGE);
                    break;
                case HM_Resource_ResourceModel::EDIT_TYPE_CODE:
                    $tabsElem->setAttrib('default', HM_Form_ResourceTypeHtml::TAB_EMBED);
                    break;
                case HM_Resource_ResourceModel::EDIT_TYPE_SLIDER:
                    $tabsElem->setAttrib('default', HM_Form_ResourceTypeHtml::TAB_SLIDER);
                    break;
                case HM_Resource_ResourceModel::EDIT_TYPE_URL:
                    $tabsElem->setAttrib('default', HM_Form_ResourceTypeHtml::TAB_URL);
                    break;
            }
        }

        if (!empty($this->_resource->content)) {
            if ($this->_resource->type = HM_Resource_ResourceModel::TYPE_HTML) {
                if ($embedElement = $form->getElement('content_embed')) {
                    $embedElement->setValue($this->_resource->content);
                }
                if ($pageElement = $form->getElement('content_page')) {
                    $pageElement->setValue($this->_resource->content);
                }
            }
        }

        if ($slideEditor = $form->getElement('sliderEditor')) {
            $slideEditor->setAttrib('url', '/editor/index.html?id=' . $this->_resourceId);
        }

        $fileElement = $form->getElement('file');
        if (!empty($this->_resource) and
            $fileElement instanceof HM_Form_Element_Vue_File
        ) {
            $fileElement->setUploadedFileInfo([$this->_resource->getFileInfo()]);
        }

        /** @var HM_Tag_Ref_RefService $tagRefService */
         $tagRefService = $this->getService('TagRef');
        /** @var HM_Tag_TagService $tagService */
        $tagService = $this->getService('Tag');
        $data['tags'] = $this->getService('Tag')->convertAllToStrings($tagService->getTags($this->_resource->resource_id, $tagRefService->getResourceType()));

        $form->populate($data);
    }

    public function deleteAction()
    {
        $id = (int) $this->_getParam('resource_id', 0);
        if ($id) {
            $temp = $this->delete($id);
            if ($temp === false) {
                $error = true;
            } else {
                $this->getService('Tag')->deleteTags($id, HM_Tag_Ref_RefModel::TYPE_RESOURCE);
            }
            if ($error === false) {
                $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_DELETE));
            } else {
                foreach ($this->notDeleted as $item) {
                    if (count($this->notDeleted)) {
                        $this->_flashMessenger->addMessage(array('message' => _('Вы не можете удалить ресурс, созданный в Базе знаний') . '. Ресурс "' . $item->title . '"" не удалён!', 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
                    }
                }
            }
            $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_DELETE));
            $this->notDeleted = null;
        }
        $this->_resource = $this->_resourceId = false;
        $this->_redirectToIndex();
    }

    public function deleteByAction()
    {
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                foreach ($ids as $id) {
                    $temp = $this->delete($id);
                    if ($temp === false) {
                        $error = true;
                    } else {
                        $this->getService('Tag')->deleteTags($id, HM_Tag_Ref_RefModel::TYPE_RESOURCE);
                    }
                }
                if ($error === false) {
                    $this->_flashMessenger->addMessage($this->_getMessage((count($ids) > 1) ? self::ACTION_DELETE_BY : self::ACTION_DELETE));
                } else {
                    $notDeleted = array();
                    foreach ($this->notDeleted as $item) {
                        $this->_flashMessenger->addMessage(array('message' => _('Вы не можете удалить ресурс, созданный в Базе знаний') . '. Ресурс "' . $item->title . '"" не удалён!', 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
                        $notDeleted[] = $item->title;
                    }
                    if (count($ids) - count($this->notDeleted)) {
                        $this->_flashMessenger->addMessage($this->_getMessage((count($ids) - count($this->notDeleted) > 1) ? self::ACTION_DELETE_BY : self::ACTION_DELETE));
                    } else {
                        $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_DELETE_BY));
                    }
                    $this->notDeleted = null;
                }
            }
        }
        $this->_redirectToIndex();
    }

    public function delete($id)
    {
        $subjectId = (int) $this->_getParam($this->idParamName, 0);
        /** @var HM_Resource_ResourceService $resourceService */
        $resourceService = $this->getService('Resource');

        $return = $resourceService->complexRemove($id, $subjectId);
        $resource = $resourceService->getOne($resourceService->findDependence('Revision', $id));

        if(!$return) {
            $this->notDeleted[$id] = $resource;
        }

        return $return;
    }

    protected function _assignCriteria($resourceId, $criteriaIds = [], $criteriaType = HM_At_Criterion_CriterionModel::TYPE_CORPORATE)
    {
        $this->getService('MaterialCriteria')->deleteBy([
            'material_id = ?' => $resourceId,
            'material_type = ?' => HM_Event_EventModel::TYPE_RESOURCE,
        ]);
        foreach ($criteriaIds as $criterionId) {
            $this->getService('MaterialCriteria')->insert([
                'material_id' => $resourceId,
                'material_type' => HM_Event_EventModel::TYPE_RESOURCE,
                'criterion_id' => $criterionId,
                'criterion_type' => $criteriaType,
            ]);
        }
    }

    protected function _getMessages()
    {
        return array(
            self::ACTION_INSERT => _('Ресурс успешно создан'),
            self::ACTION_UPDATE => _('Ресурс успешно обновлён'),
            self::ACTION_DELETE => _('Ресурс успешно удалён'),
            self::ACTION_DELETE_BY => _('Ресурсы успешно удалены')
        );
    }

    private function _getMessage($action)
    {
        $messages = $this->_getMessages();
        if (isset($messages[$action])) {
            return $messages[$action];
        }

        return _('Сообщение для данного события не установлено');
    }
}
