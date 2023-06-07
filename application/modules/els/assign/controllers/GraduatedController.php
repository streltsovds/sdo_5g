<?php

class Assign_GraduatedController extends HM_Controller_Action_Assign
{
    use HM_Controller_Action_Trait_Grid;
    use HM_Controller_Action_Trait_Context;

    protected $service     = 'Subject';
    protected $idParamName = 'subject_id';
    protected $idFieldName = 'subid';
    protected $id          = 0;

    protected $_subjectId      = null;
    protected $_subject        = null;
    protected $gridId          = '';

    public function init()
    {
        $this->_subjectId = $subjectId = $this->_getParam('subid', $this->_getParam('subject_id', 0));

        if (!$this->isAjaxRequest()) {

            $this->_subject = $this->getOne($this->getService('Subject')->find($subjectId));
            if ($this->_subject) {
                $this->initContext($this->_subject);
                $this->view->addSidebar('subject', [
                    'model' => $this->_subject,
                ]);
            }
        }

        parent::init();

        $this->gridId = ($this->_subjectId) ? "grid{$this->_subjectId}" : 'grid';
    }

    public function indexAction()
    {
        if (!$this->isAjaxRequest() && !$this->view->getSubHeader()) {
            $this->view->setSubHeader(_('Прошедшие обучение'));
        }

        $subjectId = (int) $this->_getParam('subject_id', 0);
        $gridId = $this->gridId;
        $sorting = $this->_request->getParam("order{$gridId}");
        if ($sorting == "") $this->_request->setParam("order{$gridId}", 'fio_ASC');
        $this->_request->setParam('masterOrdergrid', 'notempty DESC');

        $select = $this->getService('Graduated')->getSelect();

        $select->from(
            array('g' => 'graduated'),
            array(
                'g.SID',
                'g.MID',
                'g.CID',
                'g.status',
                'notempty' => "CASE WHEN (p.LastName IS NULL AND p.FirstName IS NULL AND  p.Patronymic IS NULL) OR (p.LastName = '' AND p.FirstName = '' AND p.Patronymic = '') THEN 0 ELSE 1 END",
                'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
                's.name',
                's.scale_id',
                'g.end',
                'certificate_type' => 'c.type',
                'certificate_end' => 'c.enddate',
                'g.certificate_id',
                'm.mark',
                'f.file_id',
            )
        )
        ->joinInner(array('p' => 'People'), 'g.MID = p.MID', array())
        ->joinLeft(array('d' => 'structure_of_organ'), 'd.mid = p.MID', array())
        ->joinInner(array('s' => 'subjects'), 'g.CID = s.subid', array())
        ->joinLeft(array('m' => 'courses_marks'), '(m.cid = g.CID AND m.mid = g.MID)', array())
        ->joinLeft(array('c' => 'certificates'), '(c.subject_id = g.CID AND c.user_id = g.MID)', array())
        ->joinLeft(array('f' => 'files'), '(c.certificate_id = f.item_id AND f.item_type = \''. HM_Files_FilesModel::ITEM_TYPE_CERTIFICATE .'\')', array())
        ;

        $select->group(array(
            'p.LastName',
            'p.FirstName',
            'p.Patronymic',
            'g.SID',
            'g.MID',
            'g.CID',
            'g.status',
            's.name',
            's.scale_id',
            'g.begin',
            'g.end',
            'g.certificate_id',
            'm.mark',
            'c.enddate',
            'c.type',
            'f.file_id',
        ));

        /** @var HM_Acl $acl */
        $acl = $this->getService('Acl');

        // Область ответственности
        if($acl->checkRoles(HM_Responsibility_ResponsibilityModel::getResponsibilityRoles())) {
            $select = $this->getService('Responsibility')->checkUsers($select, '', 'p.MID');
            $select = $this->getService('Responsibility')->checkSubjects($select, 'g.CID');
        }

        if ($subjectId) {
            $select->where('g.CID = ?', $subjectId);
        }

        if ($this->currentUserRole(array(
            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL
        ))) {
            $soid = $this->getService('Responsibility')->get();
            $responsibilityPosition = $this->getOne($this->getService('Orgstructure')->find($soid));
            if ($responsibilityPosition) {
                $subSelect = $this->getService('Orgstructure')->getSelect()
                    ->from('structure_of_organ', array('soid'))
                    ->where('lft > ?', $responsibilityPosition->lft)
                    ->where('rgt < ?', $responsibilityPosition->rgt);
                $select->where("d.soid IN (?)", $subSelect);
            } else {
                $select->where('1 = 0');
            }
        }

        $grid = $this->getGrid(
            $select,
            array(
                'SID'            => array('hidden' => true),
                'MID'            => array('hidden' => true),
                'CID'            => array('hidden' => true),
                'status'         => array('hidden' => true),
                'notempty'       => array('hidden' => true),
                'scale_id'       => array('hidden' => true),
                'file_id'        => array('hidden' => true),
                'fio'            => array(
                    'title' => _('ФИО'),
                    'decorator' =>
                        $this->view->cardLink(
                                $this->view->url(array(
                                    'module' => 'user',
                                    'controller' => 'list',
                                    'action' => 'view',
                                    'user_id' => ''
                                ), null, true).'{{MID}}',_('Карточка пользователя')).
                                '<a href="'.$this->view->url(array(
                                    'module' => 'user',
                                    'controller' => 'edit',
                                    'action' => 'card',
                                    'user_id' => ''), null, true) . '{{MID}}'.'">'.'{{fio}}</a>',
                    'position' => 1,
                ),
                'name' => ($subjectId ? array('hidden' => true) : array(
                    'title' => _('Курс'),
                    'position' => 2,
                )),
                'mark' => array(
                    'title' => _('Результат'),
                    'position' => 3,
                    'callback' => array(
                        'function' => array($this, 'updateMark'),
                        'params' => array('{{mark}}', '{{scale_id}}')
                    )
                ),
                'certificate_type' => array(
                    'title' => _('Вид документа'),
                    'callback' => array('function' => array($this, 'updateCertificateType'), 'params' => array('{{certificate_type}}')),
                    'position' => 4,
                ),
                'certificate_id' => array(
                    'title' => _('Номер документа'),
                    'position' => 5,
                    'callback' => array(
                        'function' => array($this, 'updateCertificateNumber'),
                        'params' => array('{{certificate_id}}', '{{file_id}}')
                    )
                ),
                'end' => array(
                    'title' => _('Дата прохождения'),
                    'format' => array('Date', array('date_format' => Zend_Locale_Format::getDateTimeFormat())),
                    'position' => 6
                ),
                'certificate_end' => array(
                    'title' => _('Дата истечения сертификата'),
                    'format' => array('Date', array('date_format' => Zend_Locale_Format::getDateTimeFormat())),
                    'position' => 7
                ),
                'employer' => array('hidden' => true), // array('title' => _('Место работы')),
            ),
            array(
                'fio'      => null,
                'begin'    => array('render' => 'DateSmart'),
                'end'      => array('render' => 'DateSmart'),
                'certificate_type' => array('values' => HM_Certificates_CertificatesModel::getCertificateTypes()),
                'certificate_id' => null,
                'certificate_end' => array('render' => 'DateSmart'),
//                'employer' => null,
                'name'     => null,
                'mark'     => array(
                     'callback' => array(
                         'function'=>array($this, 'markFilter'),
                         'params'=>array()
                    )
                ),
            )
        , $gridId);

        if (!$acl->checkRoles(array(HM_Role_Abstract_RoleModel::ROLE_TEACHER, HM_Role_Abstract_RoleModel::ROLE_TEACHER))) {

            $grid->addAction([
                    'module' => 'message',
                    'controller' => 'send',
                    'action' => 'index'
                ],
                ['MID'],
                $this->view->svgIcon('say-bubble', _('Отправить сообщение'))
            );

            $grid->addAction([
                    'module' => 'assign',
                    'controller' => 'graduated',
                    'action' => 'upload-certificate',
                    'subject' => !$subjectId ? null : 1
                ],
                ['MID', 'CID'],
                $this->view->svgIcon('download', _('Загрузить сертификат'))
            );

            $grid->addAction([
                'module' => 'assign',
                'controller' => 'student',
                'action' => 'change-student',
                'model' => 'Graduated',
                'return_subid' => $subjectId
            ],
                ['MID', 'CID'],
                $this->view->svgIcon('users', _('Заменить пользователя'))

            );

            if ($acl->checkRoles([HM_Role_Abstract_RoleModel::ROLE_DEAN, HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL])) {

                $grid->addAction([
                    'module' => 'assign',
                    'controller' => 'graduated',
                    'action' => 'login-as'
                ],
                    ['MID'],
                    $this->view->svgIcon('enter', _('Войти от имени пользователя'))
,
                    _('Вы действительно хотите войти в систему от имени данного пользователя? При этом все функции Вашей текущей роли будут недоступны. Вы сможете вернуться в свою роль при помощи обратной функции "Выйти из режима". Продолжить?') // не работает??
                );

//            $grid->addMassAction(
//                $this->view->url(array('module' => 'assign', 'controller' => 'graduated', 'action' => 'order')),
//                _('Печать приказа об окончании обучения')
//            );
//            $grid->addMassAction(
//                $this->view->url(array('module' => 'assign', 'controller' => 'graduated', 'action' => 'certificates')),
//                _('Печать сертификатов')
//            );
                $grid->addMassAction(
                    $this->view->url(['module' => 'assign', 'controller' => 'graduated', 'action' => 'delete']),
                    _('Исключить из списка'),
                    _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
                );
            }

            $grid->addMassAction(['module' => 'message',
                'controller' => 'send',
                'action' => 'index'],
                _('Отправить сообщение'));

            if (in_array($subjectId, HM_Subject_SubjectModel::getBuiltInCourses())) {

                $grid->addMassAction([
                    'baseUrl' => 'tc',
                    'module' => 'subject',
                    'controller' => 'fulltime',
                    'action' => 'protocol',
                    'subject_id' => $subjectId,
                ], _('Сформировать протокол'));
            }
        }

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;
        Zend_Registry::get('session_namespace_default')->userCard['returnUrl'] = $_SERVER['REQUEST_URI'];
    }

    public function markFilter($data)
    {
        $field = $data['field'];
        $value = $data['value'];
        $select = $data['select'];

        if (is_numeric($value)) {
            $select->where('LOWER(m.mark) LIKE LOWER('.$value.')');
        } else {
            $textStatuses = HM_Scale_Value_ValueModel::getAllTextStatuses();
            $condition = array();
            foreach ($textStatuses as $text => $status) {
                if (strpos(mb_strtolower($text, 'UTF-8'), mb_strtolower($value, 'UTF-8')) !== false) $condition[] = $status;
            }

            if (!empty($condition)) {
                $select->where('m.mark IN (?)', array_unique($condition));
            }
        }
    }

    public function uploadCertificateAction()
    {
        $form = new HM_Form_UploadFile();

        $user_id     = $this->getRequest()->getParam('MID');
        $subject_id  = $this->getRequest()->getParam('CID');
        $subjectParam= $this->getRequest()->getParam('subject');
        $history     = $this->getRequest()->getParam('history');

        if ($this->_request->isPost() && $form->isValid($this->_request->getPost())) {
            if ($form->file->isUploaded() && $form->file->receive() && $form->file->isReceived()) {

                $file = $form->file->getFileName();

                // только один сертификат
                $old = $this->getService('Certificates')->fetchAll(array(
                    'user_id = ?' => $user_id,
                    'subject_id = ?' => $subject_id,
                ))->current();

                $this->getService('Certificates')->deleteBy(array(
                    'user_id = ?' => $user_id,
                    'subject_id = ?' => $subject_id,
                ));

                $certificate = $this->getService('Certificates')->insert(
                    array(
                        'user_id'    => (int) $user_id,
                        'type'    => HM_Certificates_CertificatesModel::TYPE_CERTIFICATE,
                        'subject_id' => (int) $subject_id,
                        'created'    => date("Y-m-d H:i:s"),
                        'name' => $old->name,
                        'description' => $old->description,
                        'organization' => $old->organization,
                        'startdate' => $old->startdate,
                        'enddate' => $old->enddate,
                        'filename' => $old->filename,
                        'number' => $old->number
                    )
                );

                $fileData = $this->getService('Certificates')->addCertificateFile(realpath($file), $certificate->certificate_id);

                $filePath = HM_Certificates_CertificatesService::getPath($fileData->fileData);

                $this->getService('Files')->update(
                    array(
                        'file_id'   => $fileData->file_id,
                        'path'	    => realpath($filePath),
                        'file_size' => filesize($filePath),
                        'item_id'   => $certificate->certificate_id
                    )
                );

                $this->getService('Graduated')->updateWhere(
                    array(
                        'certificate_id' => $certificate->certificate_id
                    ), array(
                        'MID = ?' => $user_id,
                        'CID = ?' => $subject_id,
                    )
                );

                $this->_flashMessenger->addMessage(_('Сертификат загружен'));
                if ($history) {
                    $this->_redirector->gotoSimple('study-history', 'index', 'user', array('user_id' => $user_id));
                } elseif ($subjectParam) {
                    $this->_redirector->gotoSimple('index', 'graduated', 'assign', array('subject_id' => $subject_id));
                } else {
                    $this->_redirector->gotoSimple('index');
                }

            } else {
                throw new HM_Exception(_('Ошибка загрузки файла'));
            }
        }
        $this->view->form = $form;

    }

    public function updateSubjectName($name, $subjectId)
    {
        $name = trim($name);
        if (!strlen($name)) {
            $name = sprintf(_('Учебный курс #%d'), $subjectId);
        }
        return $name;
    }

    public function orderAction()
    {
        $ids = $this->_request->getParam('postMassIds_grid');
        $ids = explode(',', $ids);
        if (count($ids)) {
            $this->_helper->getHelper('layout')->disableLayout();
            Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
            $this->getHelper('viewRenderer')->setNoRender();

            $graduated = $this->getService('Graduated')->findDependence(array('User', 'Subject'), $ids);

            $word = new HM_Word();

            $word->appendHtml($this->getService('Option')->getOption('template_order_header'));
            $word->appendHtml($this->getService('Option')->getOption('template_order_text'));

            if (count($graduated)) {
                $data = array();
                foreach($graduated as $item) {
                    $data[] = array($item->getUser()->getName(), $item->getSubject()->name);
                }

                $word->appendTable(array(_('ФИО'), _('Курс')), $data);
            }

            $word->appendHtml($this->getService('Option')->getOption('template_order_footer'));
            $word->send();

        } else {
            $this->_flashMessenger->addMessage(_('Не выбраны прошедшие обучение'));
            $this->_redirector->gotoSimple('index', 'graduated', 'assign');
        }

    }

    /**
     * Печать сертификатов для окончивших обучение
     */
    public function certificatesAction()
    {
        $ids = $this->_request->getParam('postMassIds_grid');
        $ids = explode(',', $ids);
        if (count($ids)) {

            $graduated = $this->getService('Graduated')->find($ids);

            if (count($graduated)) {

                $pdf = new Zend_Pdf();
                $oldEncoding = mb_internal_encoding();
		        mb_internal_encoding("Windows-1251");

                foreach($graduated as $item) {
                    $cert_path = Zend_Registry::get('config')->path->upload->certificates . "{$item->certificate_id}.pdf";
                    if ( file_exists($cert_path) ) {
                        $user_cert = Zend_Pdf::load($cert_path);
                        $pages = $user_cert->pages;
                        // добавляем все страницы
                        foreach ($pages as $page) {
                            $pdf->pages[] = clone $page;
                        }
                    }
                }

                if ( count($pdf->pages) ) {

                    $this->_helper->getHelper('layout')->disableLayout();
                    Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
                    $this->getHelper('viewRenderer')->setNoRender();

                    $this->getResponse()
                         ->setHeader('Content-Type', 'application/x-pdf',true)
                         ->setHeader('Content-Disposition','filename="certificates_'.date("Y-m-d_H-i-s").'.pdf"',true)
                         ->appendBody($pdf->render());
                } else {
                    $this->_flashMessenger->addMessage(_('Не найдены сертификаты выбранных пользователей'));
                    $this->_redirector->gotoSimple('index', 'graduated', 'assign');
                }
                mb_internal_encoding($oldEncoding);
            } else {
                $this->_flashMessenger->addMessage(_('Не найдены прошедшие обучение'));
                $this->_redirector->gotoSimple('index', 'graduated', 'assign');
            }


        } else {
            $this->_flashMessenger->addMessage(_('Не выбраны прошедшие обучение'));
            $this->_redirector->gotoSimple('index', 'graduated', 'assign');
        }

    }
    
    public function deleteAction()
    {
        $subjectId = (int) $this->_request->getParam('subject_id');
        $postMassParamName = 'postMassIds_grid' . ($subjectId ?: '');
        $ids = $this->_request->getParam($postMassParamName, []);
        $ids = explode(',', $ids);
        foreach($ids as $id){
            $this->getService('Graduated')->delete($id);
        }
        $this->_flashMessenger->addMessage(_('Слушатели успешно исключены из списка'));
        $this->_redirector->gotoSimple('index', 'graduated', 'assign', array($this->idParamName => $subjectId));
    }

    public function assignAction(){}

    protected function _preAssign($personId, $courseId){}
    protected function _postAssign($personId, $courseId){}
    protected function _assign($personId, $courseId) {}
    protected function _postUnassign($personId, $courseId){}
    protected function _finishAssign(){}
    protected function _finishUnassign(){}

    public function unassignAction(){
        $this->deleteAction();
    }
    protected function _unassign($personId, $courseId){}
    protected function _preUnassign($personId, $courseId){}
}