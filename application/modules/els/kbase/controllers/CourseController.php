<?php
/*
 * 5G
 *
 */
class Kbase_CourseController extends HM_Controller_Action_Course
{
    protected $notDeleted;

    public function indexAction()
    {
        $lesson = null;
        $lessonId = $this->_getParam('lesson_id', 0);
        if ($lessonId) {
            $this->view->lessonId = $lessonId;
            $collection = $this->getService('Lesson')->find($lessonId);
            if (count($collection)) {
                $lesson = $collection->current();
                $this->view->setHeader($lesson->title);
            }
        }

        $this->view->courseId = $this->_courseId;
    }

    public function createAction()
    {
        $this->view->setSubHeader(_('Создание учебного модуля'));

        $subjectId = $this->_getParam('subject_id');

        $form = new HM_Form_Course();

        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {

                $title = $form->getValue('title');
                $createType = $this->_getParam('create_type');

                /** @var HM_Material_MaterialService $materialService */
                $materialService = $this->getService('Material');

                switch ($createType) {

                    case HM_Form_Course::CREATE_TYPE_AUTODETECT:
                        $fileElement = $form->getElement('file');
                        $fileElement->receive();

                        if ($fileElement->isReceived()) {
                            $insertValue = realpath($fileElement->getFileName());

                            try {
                                $course = $materialService->insert(
                                    $insertValue,
                                    $title,
                                    $subjectId
                                );
                            } catch (HM_Exception_Upload $e) {
                                $this->_flashMessenger->addMessage(['type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => $e->getMessage()]);
                            }
                        }
                        break;

                    case HM_Form_Course::CREATE_TYPE_CARD:
                        $course = $materialService->createDefault(
                            HM_Event_EventModel::TYPE_COURSE,
                            $title,
                            $subjectId
                        );
                        break;
                }
            }

            if ($course) {

                $this->_course = $course;
                $this->_flashMessenger->addMessage(_('Учебный модуль успешно создан'));

                if ($redirectUrl = $this->_getParam('redirectUrl')) {

                    if (!strpos($redirectUrl, 'course_id')) $redirectUrl = sprintf('%s/course_id/%d', trim($redirectUrl, "/"), $course->CID);
                    $this->_redirector->gotoUrl(urldecode($redirectUrl));

                } elseif ($createType == HM_Form_Course::CREATE_TYPE_MATERIAL) {

                    // если выбран конструктор - принудительно к конструктору слайдов
                    $this->_redirector->gotoUrl($this->view->url([
                        'module' => 'kbase',
                        'controller' => 'course',
                        'action' => 'constructor',
                        'course_id' => $course->CID,
                    ]));

                } else {
                    $this->_redirectToIndex();
                }
            }
        }

        $this->view->form = $form;
    }

    public function editCardAction()
    {
        $this->view->setSubHeader(_('Редактирование учебного модуля'));

        $form = new HM_Form_CourseCard();
        $request = $this->getRequest();

        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {

                $data = $form->getValues();
                $tags = $data['tags'];

                unset($data['tags']);
                unset($data['cancel']);
                unset($data['submit_and_redirect']);

                $classifiers = [];
                foreach ($data as $key => $value) {
                    if (substr_count($key, 'classifier')) {
                        list(, $classifierTypeId) = explode('_', $key);
                        if (!empty($value)) $classifiers[$classifierTypeId] = $value;
                        unset($data[$key]);
                    }
                }

                $this->getService('Course')->update($data);

                if (count($tags)) {
                    $this->getService('Tag')->updateTags(
                        $tags, $this->_courseId, $this->getService('TagRef')->getCourseType()
                    );
                }

                $this->getService('ClassifierLink')->deleteBy(
                    $this->quoteInto(
                        array('item_id = ?', ' AND type = ?'),
                        array($this->_courseId, HM_Classifier_Link_LinkModel::TYPE_COURSE)
                    )
                );

                foreach ($classifiers as $classifierType => $classifiersOfType) {
                    foreach ($classifiersOfType as $classifierId) {
                        $this->getService('ClassifierLink')->insert([
                            'item_id' => $this->_courseId,
                            'classifier_id' => $classifierId,
                            'type' => HM_Classifier_Link_LinkModel::TYPE_COURSE,
                        ]);
                    }
                }

                if ($redirectUrl = $this->_getParam('redirectUrl')) {
                    if (!strpos($redirectUrl, 'course_id')) {
                        $redirectUrl = sprintf('%s/course_id/%d', trim($redirectUrl, "/"), $this->_course->CID);
                    }
                    $this->_redirector->gotoUrl(urldecode($redirectUrl));
                }

                $this->_flashMessenger->addMessage(_('Учебный модуль успешно отредактирован'));
                $this->_redirector->gotoUrl($this->_backUrl);
            }
        } else {
            $this->setDefaults($form);
        }

        $this->view->form = $form;
    }

    public function importAction()
    {
        if ($this->_course->subject_id == 0) {
            $usedInLessons = $this->getService('Lesson')->fetchAll(
                $this->quoteInto(
                    ['material_id = ?', ' AND typeID = ?'],
                    [$this->_course->CID, HM_Event_EventModel::TYPE_COURSE]
                )
            );

            if ($count = $usedInLessons->count()) {
                $this->view->message = sprintf(_('Внимание! Данный учебный модуль используется в %s'), $this->getService('Lesson')->pluralFormCountPrepositionalCase($count));
            }
        }

        $subHeader = _('Импорт учебного модуля');
        if ($this->_getParam('edition', 0)) {
            $subHeader = _('Редактирование материала');
        }
        $this->view->setSubHeader($subHeader);
        $this->view->course = $this->_course;
        $this->view->idType = $this->_getParam('idType', 0);
        $form = new HM_Form_CourseImport();

        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {

                $result = false;

                try {
                    $file = $form->getElement('file');
                    $file->receive();

                    if ($file->isReceived()) {

                        $filename = $file->getFileName();

                        if (is_file($filename) && is_readable($filename)) {
                            $result = $this->_import($filename);
                        }
                    }
                } catch (Exception $e) {
                    $result = false;
                }

                if ($redirectUrl = $this->_getParam('redirectUrl')) {
                    if (!strpos($redirectUrl, 'course_id')) {
                        $redirectUrl = sprintf('%s/course_id/%d', trim($redirectUrl, "/"), $this->_course->CID);
                    }
                    $this->_redirector->gotoUrl(urldecode($redirectUrl));
                }

                if ($result) {
                    $this->_flashMessenger->addMessage(_('Учебный модуль успешно импортирован'));
                } else {
                    $this->_flashMessenger->addMessage([
                        'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                        'message' => _('Произошла ошибка при импорте учебного модуля'),
                    ]);
                }
                $this->_redirectToIndex();
            }
        } else {

            $this->setDefaults($form);
        }

        $this->view->form = $form;
    }

    protected function _import($filename)
    {
        $result = false;
        $unzipPath = $this->getService('Files')->unzip($filename);

        if (!empty($unzipPath)) {
            /** @var HM_Course_CourseService $courseService */
            $courseService = $this->getService('Course');

            if (HM_Material_MaterialService::autodetectScorm($unzipPath)) {
                if ($result = $courseService->importScorm($this->_courseId, $unzipPath)) {
                    $this->_course->format = HM_Course_CourseModel::FORMAT_SCORM;
                }
            } elseif (HM_Material_MaterialService::autodetectTincan($unzipPath)) {
                if ($result = $courseService->importTincan($this->_courseId, $unzipPath)) {
                    $this->_course->format = HM_Course_CourseModel::FORMAT_TINCAN;
                }
// @todo: Eauthor
//                        } elseif (HM_Material_MaterialService::autodetectEauthor($unzipPath)) {
//                            $this->getService('Course')->importEauthor($this->_courseId, $unzipPath);
            } elseif (HM_Material_MaterialService::autodetectFree($unzipPath)) {

                $courseService->importFree($this->_courseId, $unzipPath);
            }

            if ($course = $courseService->findOne($this->_courseId)) {
                if ($course->format != 'free') {
                    $this->_course = $courseService->findOne($this->_courseId); // refresh
                }
            }

            $courseService->update($this->_course->getData());
        }

        return $result;
    }

    public function setDefaults(Zend_Form $form)
    {
        $data = $this->_course->getData();

        $courseTagType = $this->getService('TagRef')->getCourseType();
        /** @var HM_Tag_TagService $tagService */
        $tagService = $this->getService('Tag');
        $data['tags'] = $this->getService('Tag')->convertAllToStrings($tagService->getTags($this->_course->CID, $courseTagType));

        $form->populate($data);
    }

    public function deleteAction()
    {
        $id = (int) $this->_getParam('course_id', 0);
        if ($id) {
            $temp = $this->delete($id);
            if ($temp === false) {
                $error = true;
            } else {
                $this->getService('Tag')->deleteTags($id, HM_Tag_Ref_RefModel::TYPE_COURSE);
            }
            if ($error === false) {
                $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_DELETE));
            } else {
                foreach ($this->notDeleted as $item) {
                    if (count($this->notDeleted)) {
                        $this->_flashMessenger->addMessage(array('message' => _('Вы не можете удалить учебный модуль, созданный в Базе знаний') . '. Ресурс "' . $item->title . '"" не удалён!', 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
                    }
                }
            }
            $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_DELETE));
            $this->notDeleted = null;
        }
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
                        $this->getService('Tag')->deleteTags($id, HM_Tag_Ref_RefModel::TYPE_COURSE);
                    }
                }
                if ($error === false) {
                    $this->_flashMessenger->addMessage($this->_getMessage((count($ids) > 1) ? self::ACTION_DELETE_BY : self::ACTION_DELETE));
                } else {
                    $notDeleted = array();
                    foreach ($this->notDeleted as $item) {
                        $this->_flashMessenger->addMessage(array('message' => _('Вы не можете удалить учебный модуль, созданный в Базе знаний') . '. Учебный модуль "' . $item->title . '"" не удалён!', 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
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
        $this->getService('Course')->delete($id);
        unset($this->_course);

        return true;
    }

    protected function _getMessages()
    {
        return array(
            self::ACTION_INSERT => _('Учебный модуль успешно создан'),
            self::ACTION_UPDATE => _('Учебный модуль успешно обновлён'),
            self::ACTION_DELETE => _('Учебный модуль успешно удалён'),
            self::ACTION_DELETE_BY => _('Учебные модули успешно удалены')
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

    protected function _redirectToIndex()
    {
        if ($this->_course && $this->_course->subject_id) {
            $this->_redirector->gotoUrl($this->view->url([
                'module' => 'subject',
                'controller' => 'lessons',
                'action' => 'edit',
                'course_id' => $this->_course->subject_id,
            ]));
        }
        $this->_redirector->gotoSimple('index', 'courses', 'kbase');
    }
}
