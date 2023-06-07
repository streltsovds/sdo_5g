<?php
class User_IndexController extends HM_Controller_Action_User {

    protected $_isLaborSafety = 0;

    public function init()
    {
        $this->_isLaborSafety = $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY, HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL)) ? 1 : 0;
        return parent::init();
    }

    public function softAction()
    {
        $mid = $this->_getParam('user_id');
        $select = $this->getService('User')->getSelect();
        $select->from('sessions', array(
            'stop',
            'browser_name',
            'browser_version',
            'flash_version',
            'os',
            'screen',
            'cookie',
            'js',
            'java_version',
            'silverlight_version',
            'acrobat_reader_version',
            'msxml_version',
        ));
        $select->where($this->quoteInto('mid = ? AND browser_name is not null', $mid)); //#17968
        $select->order('stop DESC');
        $select->limit(1);
        $info = $select->query()->fetch();
        
        if ($info && !empty($info['browser_name'])) {
            $this->view->systemInfo = array(
                'browser' => array(
                    'name'  => $info['browser_name'],
                    'value' => $info['browser_version'],
                ),
                'flash' => array(
                    'value' => $info['flash_version']
                ),
                'os' => array(
                    'value' => $info['os']
                ),
                'screen' => array(
                    'value' => $info['screen']
                ),
                'cookie' => array(
                    'value' => $info['cookie']
                ),
                'js' => array(
                    'value' => $info['js']
                ),
                'java' => array(
                    'value' => $info['java_version']
                ),
                'silverlight' => array(
                    'value' => $info['silverlight_version']
                ),
                'acrobat_reader' => array(
                    'value' => $info['acrobat_reader_version']
                ),
                'msxml' => array(
                    'value' => $info['msxml_version']
                ),
            );
        } else {
            $this->view->systemInfo = false;
        }
        $this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/infoblocks/checksw/style.css');
    }

    public function studyHistorySubjectAction()
    {
        $subjectId = (int) $this->_getParam('subject_id');
        $userId = (int) $this->_getParam('user_id');
        $url = Zend_Registry::get('serviceContainer')->getService('Subject')->getDefaultUri($subjectId);
        if ($userId == $this->getService('User')->getCurrentUserId()) {
        $this->_flashMessenger->addMessage(array(
            'type'    => HM_Notification_NotificationModel::TYPE_CRIT,
            'message' => _('Прошедшие курсы доступны в ограниченном режиме. Невозможно запускать занятия на оценку, сервисы взаимодействия доступны только в режиме чтения.'),
        ));
        }
        $this->_redirector->gotoUrl($url);
    }

    public function deleteAction()
    {
        $sid = (int) $this->_getParam('SID', 0);
        $mid = (int) $this->_getParam('MID', 0);
        if ($sid) {
            $this->getService('Graduated')->delete($sid);
            $this->_flashMessenger->addMessage(_('Запись успешно удалена'));
        }
        $this->_redirector->gotoSimple('study-history', 'index', 'user', array('user_id' => $mid));
    }

    public function deleteByAction()
    {
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        $userId = (int) $this->_getParam('user_id', 0);
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                foreach($ids as $id) {
                    $this->getService('Graduated')->delete($id);
                }
                $this->_flashMessenger->addMessage(_('Записи успешно удалены'));
            }
        }
        $this->_redirector->gotoSimple('study-history', 'index', 'user', ['user_id' => $userId]);
    }

    public function studyHistoryAction()
    {
        $userId = $this->_userId;
        if (!$this->_hasParam('ordergrid_history') && !$this->isAjaxRequest()) {
            $this->_setParam('ordergrid_history', 'end_ASC');
        }

        $select = $this->getService('Graduated')->getSelect();
        $select->from(
            ['g' => 'graduated'],
            [
                'g.SID',
                'g.MID',
                'g.CID',
                's.name',
                'type' => 's.is_fulltime',
                's.scale_id',
                'f.file_id',
                'g.begin',
                'g.end',
                'certificate_type' => 'c.type',
                'certificate_end' => 'c.enddate',
                's.subid',
                'progress' => 'g.progress',
                'mark' => new Zend_Db_Expr("CASE WHEN m.mark IS NULL THEN -1 ELSE m.mark END"),
                'g.certificate_id',
                's.is_labor_safety'
            ]
        )
            ->joinInner(['s' => 'subjects'], 'g.CID = s.subid', [])
            ->joinLeft(['m' => 'courses_marks'], 'g.CID = m.cid AND g.MID = m.mid', [])
            ->joinLeft(['c' => 'certificates'], 'g.certificate_id = c.certificate_id', [])
            ->joinLeft(['f' => 'files'], '(c.certificate_id = f.item_id AND f.item_type = \'' . HM_Files_FilesModel::ITEM_TYPE_CERTIFICATE . '\')', [])
            ->where('g.MID = ?', $userId);

        $grid = $this->getGrid(
            $select,
            [
                'SID' => ['hidden' => true],
                'MID' => ['hidden' => true],
                'CID' => ['hidden' => true],
                'subid' => ['hidden' => true],
                'scale_id' => ['hidden' => true],
                'file_id' => ['hidden' => true],
                'certificate_number' => ['hidden' => true],
                'progress' => ['hidden' => true],
                'is_labor_safety' => ['hidden' => true],
                'name' => [
                    'title' => _('Название учебного курса'),
                    'callback' => ['function' => [$this, 'updateName'], 'params' => ['{{subid}}', '{{name}}', '{{is_labor_safety}}']],
                    'position' => 1
                ],
                'type' => [
                    'title' => _('Тип курса'),
                    'callback' => ['function' => [$this, 'updateType'], 'params' => ['{{type}}']],
                    'position' => 2
                ],
                'begin' => ['hidden' => true],
//                array(
//                    'title' => _('Дата начала обучения'),
//                    'position' => 3
//                ),
                //'progress' => array('title' => _('Прогресс, %')),
                'certificate_type' => [
                    'title' => _('Вид документа'),
                    'callback' => ['function' => [$this, 'updateCertificateType'], 'params' => ['{{certificate_type}}']],
                    'position' => 3
                ],
                'certificate_id' => [
                    'title' => _('Номер документа'),
                    'position' => 4
                ],
                'mark' => [
                    'title' => _('Итоговая оценка'),
                    'callback' => ['function' => [$this, 'updateMark'], 'params' => ['{{mark}}', '{{scale_id}}']],
                    'position' => 5
                ],
                'end' => [
                    'title' => _('Дата прохождения обучения'),
                    'format' => ['Date', ['date_format' => Zend_Locale_Format::getDateTimeFormat()]],
                    'position' => 6
                ],
                'certificate_end' => [
                    'title' => _('Дата истечения сертификата'),
                    'format' => ['Date', ['date_format' => Zend_Locale_Format::getDateTimeFormat()]],
                    'position' => 7
                ],
            ],
            [
                'sid' => null,
                'name' => null,
                'type' => ['values' => HM_Subject_SubjectModel::getTypes()],
                'certificate_type' => ['values' => HM_Certificates_CertificatesModel::getCertificateTypes()],
                'certificate_id' => null,
                'begin' => ['render' => 'Date'],
                'end' => ['render' => 'Date'],
                'certificate_end' => ['render' => 'Date'],
                'mark' => null
            ],
            $this->gridId
        );

        $grid->updateColumn('certificate_id', [
                'callback' => [
                    'function' => [$this, 'updateCertificateNumber'],
                    'params' => ['{{certificate_id}}', '{{file_id}}']
                ]
            ]
        );

        $grid->updateColumn('begin', [
                'format' => [
                    'date',
                    ['date_format' => HM_Locale_Format::getDateFormat()]
                ]
            ]
        );

        $grid->updateColumn('end', [
                'format' => [
                    'date',
                    ['date_format' => HM_Locale_Format::getDateFormat()]
                ]
            ]
        );

        $grid->addAction([
            'action' => 'delete'
        ],
            ['SID', 'MID'],
            $this->view->svgIcon('delete', _('Удалить'))
        );

        $grid->addAction([
            'module' => 'assign',
            'controller' => 'graduated',
            'action' => 'upload-certificate',
            'history' => 1
        ],
            ['CID', 'MID'],
            $this->view->svgIcon('upload', _('Загрузить сертификат'))
        );

        $grid->addMassAction(
            [
                'action' => 'delete-by',
                'user_id' => $userId
            ],
            _('Удалить записи из истории обучения'),
            _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
        );

        $grid->setActionsCallback(
            ['function' => [$this, 'updateStudyHistoryActions'],
                'params' => ['{{is_labor_safety}}']
            ]
        );

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;
    }


    public function updateName($subjectId, $name, $isLaborSafety)
    {
        if ($this->_isLaborSafety == $isLaborSafety) {
            return $this->view->cardLink(
                $this->view->url(array('module' => 'subject', 'controller' => 'list', 'action' => 'card', 'subject_id' => $subjectId)),
                _('Карточка учебного курса')
                ) . ' <a href="' . $this->view->url(array(
                    'module' => 'user',
                    'controller' => 'index',
                    'action' => 'study-history-subject',
                    'subject_id' => $subjectId
                ), null, true, false) . '">' . $name . '</a>';
        } else {
            return $name;
        }
    }

    public function updateType($type)
    {
        $types = HM_Subject_SubjectModel::getTypes();
        return $types[$type];
    }

    public function updateProgrammType($programmType)
    {
        $types = HM_Programm_ProgrammModel::getTypes();
        return $types[$programmType];
    }

    public function uploadPhotoAction()
    {
        $fileParamName = 'photo';
        $config = Zend_Registry::get('config');
        $isAdmin = $this->getService('Acl')->checkRoles(HM_Role_Abstract_RoleModel::ROLE_ADMIN);
        $currentUserId = $this->getService('User')->getCurrentUserId();
        $userId = $this->_request->getParam('user_id');
        $uploaded = false;

        if(!$isAdmin and $currentUserId != $userId) throw new HM_Exception(_('Недоступное действие'));

        $adapter = new Zend_File_Transfer_Adapter_Http();

        if($adapter->isUploaded($fileParamName)) {
            $path = $this->getService('User')->getPath($config->path->upload->photo, $userId);
            $adapter->addFilter('Rename', $path . $userId . '.jpg', 'photo');
            unlink($path . $userId . '.jpg');
            $adapter->receive();

            $img = PhpThumb_Factory::create($path . $userId. '.jpg');
            $img->resize(HM_User_UserModel::PHOTO_WIDTH, HM_User_UserModel::PHOTO_HEIGHT);
            $img->save($path . $userId . '.jpg');
            $uploaded = true;
        }

        return $this->responseJson(['uploaded' => $uploaded]);
    }

    public function pollsHistoryAction()
    {
        if (!$this->_getParam('ordergrid', '') && !$this->isAjaxRequest()) {
            $this->_setParam('ordergrid', 'name_ASC');
        }

    	$userId = $this->_userId;
        $select = $this->getService('LessonAssign')->getSelect();
        $select
        		//->distinct()
                ->from(
                    array('sch' => 'schedule'),
                    array(
                        'name' => 'sj.name',
   	                    'title' => 'sch.Title',
   	                    'sheid' => 's.SHEID',
   	                    'created' => 's.created',
   	                    'stop' => 'log.stop',
                        'status' => 'log.status',
                        'percent' => 'log.free',
                        'balmax' => 'log.balmax',
                        'balmin' => 'log.balmin',
                        'bal' => 'log.bal',
                        'balmax2' => 'log.balmax2',
                        'balmin2' => 'log.balmin2'
                    )
                )
                ->joinLeft(
                    array('log' => 'loguser'),
                    sprintf('log.sheid = sch.SHEID AND log.mid = %d', $userId),
                    array()
                )
        		->joinLeft(
                    array('s' => 'scheduleID'),
                    sprintf('sch.SHEID = s.SHEID AND s.MID = %d', $userId),
                    array()
                )
                ->join(
                	array('sj' => 'subjects'),
                	'sj.subid = sch.CID',
                	array()
                )
                ->where('sch.typeID IN (?)', array_keys(HM_Event_EventModel::getFeedbackPollTypes()))
                ->where($this->getService('LessonAssign')->quoteInto(array('(log.mid = ?', ' OR s.MID = ?)'), array($userId, $userId)))
                ->where('log.mid IS NOT NULL OR s.MID IS NOT NULL')
                //->order(array('sj.name', 'sch.Title'))
                ;

        $grid = $this->getGrid($select,
            array(
            	'name' => array('title' => _('Название курса')),
                'title' => array('title' => _('Название опроса')),
                'created' => array('title' => _('Дата назначения опроса')),
                'stop' => array('title' => _('Дата заполнения опроса')),
                'status' => array('title' => _('Статус')),
            	'percent' => array('title' => _('Средний процент выполнения')),
	            'balmax' => array('hidden' => true),
	            'balmin' => array('hidden' => true),
	            'bal' => array('title' => _('Средний балл')),
	            'balmax2' => array('hidden' => true),
	            'balmin2' => array('hidden' => true),
                'sheid' => array('hidden' => true),
                'stid' => array('hidden' => true)

            ),
            array(
            	'name' => null,
                'title' => null,
                'created' => array('render' => 'Date'),
                'status' => array('values' => HM_Test_Result_ResultModel::getStatuses()),
                'stop' => array('render' => 'DateTimeStamp'),
                'percent' => null,
                'bal' => null
            )
        );

        $grid->updateColumn('status',
                array(
                	'callback' =>
	                array(
	                    'function' => array(HM_Test_Result_ResultModel, 'getStatus'),
	                    'params' => array('{{status}}')
	                ))
        );

        $grid->updateColumn('percent',
                array(
                	'callback' =>
	                array(
	                    'function' => array(HM_Test_Result_ResultService, 'getEveragePercent'),
	                    'params' => array('{{balmax}}', '{{balmin}}', '{{bal}}', '{{balmax2}}', '{{balmin2}}')
	                ))
        );

        $grid->updateColumn('bal',
                array(
                	'callback' =>
	                array(
	                    'function' => array(HM_Test_Result_ResultService, 'getEverageMark'),
	                    'params' => array('{{balmax}}', '{{balmin}}', '{{bal}}', '{{balmax2}}', '{{balmin2}}')
	                ))
        );

        $grid->updateColumn('created', array(
	            'format' => array(
	                'date',
	                array('date_format' => HM_Locale_Format::getDateFormat())
	            ))
        );

        $grid->updateColumn('stop', array(
	            'format' => array(
	                'date',
	                array('date_format' => HM_Locale_Format::getDateFormat())
	            ))
        );

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;
    }
    
      
    public function sessionsAction()
    {
        $gridId = 'grid';

        $select = $this->getService('AtSessionUser')->getSelect();
        $select->from(array('su' => 'at_session_users'), array(
            'userid' => 'su.user_id',
            'session_user_id' => 'su.session_user_id',
            'status' => 'su.status',
            'session_id' => 's.session_id', 
            'name' => 's.name',
            'programm_type_id' => 's.programm_type',
            'programm_type' => 's.programm_type',
            'begin_date' => 's.begin_date',
            'end_date' => 's.end_date',
            'vacancy_id' => 'rv.vacancy_id',
            'vacancy_candidate_id' => 'rvc.vacancy_candidate_id',
            'newcomer_id' => 'rn.newcomer_id',
        ))
            ->joinInner(array('s' => 'at_sessions'), 'su.session_id = s.session_id', array())
            ->joinLeft(array('rv' => 'recruit_vacancies'), 'su.session_id = rv.session_id', array())
            ->joinLeft(array('rvc' => 'recruit_vacancy_candidates'), 'rvc.vacancy_candidate_id = su.vacancy_candidate_id', array())
            ->joinLeft(array('rn' => 'recruit_newcomers'), 'su.session_id = rn.session_id', array())
            ->where('su.user_id = ?', $this->_userId)
            ->where('su.status = ?', HM_At_Session_User_UserModel::STATUS_COMPLETED)
            ->where('s.programm_type IN (?)', array(HM_Programm_ProgrammModel::TYPE_RECRUIT, HM_Programm_ProgrammModel::TYPE_ASSESSMENT, HM_Programm_ProgrammModel::TYPE_ADAPTING))        
            ->group(array(
                'su.user_id',
                'su.session_user_id',
                'su.status',
                's.session_id',
                's.name',
                's.begin_date',
                's.end_date',
                's.programm_type',
                'rv.vacancy_id',
                'rvc.vacancy_candidate_id',
                'rn.newcomer_id',
            ));
        
//        exit ($select->__toString());
//        var_dump($select->__toString());
        
        $grid = $this->getGrid($select, array(
            
            'userid' => array('hidden' => true),
            'session_user_id' => array('hidden' => true),
            'status' => array('hidden' => true),
            'session_id' => array('hidden' => true),
            'vacancy_id' => array('hidden' => true),
            'newcomer_id' => array('hidden' => true),
            'vacancy_candidate_id' => array('hidden' => true),
            'programm_type_id' => array('hidden' => true),
            'name' => array(
                'title' => _('Название')
            ),
            'programm_type' => array(
                'title' => _('Тип сессии'),
                'callback' => array('function' => array($this, 'updateProgrammType'), 'params' => array('{{programm_type}}'))
            ),
            'begin_date' => array(
                'title' => _('Дата начала'),
                'format' => array('date',
                		array('date_format' => HM_Locale_Format::getDateFormat())
                	),
            ),
            'end_date' => array(
                'title' => _('Дата завершения'),
                'format' => array('date',
                		array('date_format' => HM_Locale_Format::getDateFormat())
                	),
            ),
        ),
        array(
            'name' => null,
            'programm_type' => array('values' => HM_Programm_ProgrammModel::getTypes()),
            'begin_date' => array('render' => 'SubjectDate'),
            'end_date' => array('render' => 'SubjectDate'),
        ), $gridId);
        
        $grid->addAction(array(
                'baseUrl' => 'at',
                'module' => 'session',
                'controller' => 'report',
                'action' => 'user',
                'user_id' => null,
            ),
            array('session_user_id', 'session_id'),
            _('Индивидуальный отчёт')
        );

        $grid->addAction(array(
                'baseUrl' => 'recruit',
                'module' => 'vacancy',
                'controller' => 'report',
                'action' => 'user',
                'user_id' => null,
            ),
            array('vacancy_candidate_id', 'vacancy_id'),
            _('Индивидуальный отчёт')
        );

        $grid->addAction(array(
                'baseUrl' => 'recruit',
                'module' => 'newcomer',
                'controller' => 'report',
                'action' => 'user',
                'user_id' => null,
            ),
            array('newcomer_id'),
            _('Индивидуальный отчёт')
        );

        $grid->setActionsCallback(
            array('function' => array($this,'updateActions'),
                'params'   => array('{{programm_type_id}}')
            )
        );
        
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;
        
         //*/
    }

    public function resumeAction()
    {
        if (count($this->_user->candidate)) {
            
            $candidateId = $this->_getParam('candidate_id', 0);
            if($candidateId){
                foreach($this->_user->candidate as $userCandidate){
                    if($userCandidate->candidate_id == $candidateId){
                        $candidate = $userCandidate;
                    }
                }
            }
            if (!empty($candidate->resume_external_url)) {
                $this->_redirector->gotoUrl($candidate->resume_external_url);
            } elseif ($this->_user->getResume()){
                $this->resumeDownloadAction();
            } else {
                $this->_flashMessenger->addMessage(array(
                    'type'    => HM_Notification_NotificationModel::TYPE_ERROR,
                    'message' => _('Резюме не найдено'),
                ));
                $request = $this->getRequest();
                $referer = $request->getHeader('referer');
                if(!$referer){
                    $referer = $this->view->serverUrl('/');
                }
                $this->_redirector->gotoUrl($referer);
            }
        }        
    }

    public function resumeDownloadAction()
    {
        if ($path = $this->_user->getResume()) {
            
            $filename = Zend_Registry::get('config')->path->upload->resume . '/' . $path;
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            
            $options = array('filename' => sprintf('%s.%s', $this->_user->getName(), $extension));
            if(!$this->_getParam('download', false)) $options['disposition'] = 'inline';
            
            if (file_exists($filename))
            {
                $this->_helper->SendFile(
                    $filename,
                    ($extension == 'docx') ? 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' : 'application/unknown', 
                    $options
                );
                die();
            }            
            
        } else {
            $this->_flashMessenger->addMessage(array(
                    'type'    => HM_Notification_NotificationModel::TYPE_ERROR,
                    'message' => _('Резюме не найдено'),
            )); 
            $this->_redirector->gotoSimple('index', 'index', 'default');
        }
                
    }

    public function updateActions($programmType, $actions) 
    {
        switch ($programmType) {
            case HM_Programm_ProgrammModel::TYPE_ASSESSMENT:
                $this->unsetAction($actions, array('module' => 'vacancy', 'controller' => 'report', 'action' => 'user'));
                $this->unsetAction($actions, array('module' => 'newcomer', 'controller' => 'report', 'action' => 'user'));
            break;
            case HM_Programm_ProgrammModel::TYPE_RECRUIT:
                $this->unsetAction($actions, array('module' => 'session', 'controller' => 'report', 'action' => 'user'));
                $this->unsetAction($actions, array('module' => 'newcomer', 'controller' => 'report', 'action' => 'user'));
            break;
            case HM_Programm_ProgrammModel::TYPE_ADAPTING:
                $this->unsetAction($actions, array('module' => 'vacancy', 'controller' => 'report', 'action' => 'user'));
                $this->unsetAction($actions, array('module' => 'session', 'controller' => 'report', 'action' => 'user'));
            break;
            default:
            break;
        }
        return $actions;
    }

    public function updateStudyHistoryActions($isLaborSafety, $actions)
    {
        if ($isLaborSafety != $this->_isLaborSafety) {

            $this->unsetAction($actions, array('action' => 'delete'));
            $this->unsetAction($actions, array(
                'module' => 'assign',
                'controller' => 'graduated',
                'action' => 'upload-certificate'
            ));
        }
        return $actions;
    }
    
    /**
     * @deprecated сейчас везде используется /user/responsibility/assign
     *
     * Пока область ограничивается только одним подразделением,
     * в базе предусмотрена возможность иметь несколько (на будущее)
     */
    public function responsibilityAction()
    {
        $this->view->setHeader(_('Ограничение области ответственности'));
        
        $form = new HM_Form_Responsibility();
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
    
                $values = $form->getValues();
                if (!$values['useResponsibility']) $values['soid'] = array();
                $this->getService('Responsibility')->set($this->_user->MID, HM_Responsibility_ResponsibilityModel::TYPE_STRUCTURE, $values['soid']);
    
                $this->_flashMessenger->addMessage(_('Области ответственности успешно изменены'));
                $this->_redirector->gotoSimple('responsibility', 'index', 'user', array('user_id' => $this->_user->MID));
            }
        } else {
            $values = array();
            if (count($responsibility = $this->getService('Responsibility')->get($this->_user->MID, HM_Responsibility_ResponsibilityModel::TYPE_STRUCTURE))) {
                $values['soid'] = array_shift($responsibility);
                $values['useResponsibility'] = 1;
            }
            $form->populate($values);
        }        
        $this->view->form = $form;
    }

    // @todo: унаследовать от Crud и убрать отсюда unsetAction
    public function unsetAction(&$actions, $unsetAction, $reset = true)
    {
        $return = array();
        $unsetUrl = $this->view->url($unsetAction, null, $reset);
        $urls = explode('<li>', $actions);
        foreach ($urls as $url) {
            if (!strpos($url, $unsetUrl.'"') && !strpos($url, $unsetUrl.'/')) {
                $return[] = $url;
            }
        }
        $actions = implode('<li>', $return);
    }

    public function externalSubjectsWithProvidersAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            header('HTTP/1.0 404 Not Found');
            exit;
        }

        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();
        $this->getResponse()->setHeader('Content-type', 'application/json; charset=UTF-8');

        $q = strtolower(trim($this->_request->getParam('tag')));
        $res = array();
        if(!empty($q)) {
            $q = '%'.$q.'%';

            $select = $this->getService('Subject')->getSelect();
            $select->from(
                array('s' => 'subjects'),
                array('subid' => 's.subid', 'name' => 's.name', 'provider' => 'pr.name'))
                ->joinLeft(array('pr' => 'tc_providers'), 's.provider_id = pr.provider_id', array())
                ->where("s.is_labor_safety = ?", $this->_isLaborSafety);

            $stmt = $select->query();
            $stmt->execute();
            $rows = $stmt->fetchAll();

            foreach($rows as $row) {
                $o = new stdClass();
                $o->key = $row['provider'] ? sprintf('%s (%s)', $row['name'], $row['provider']) : $row['name'];
                $o->value = $row['subid'];
                $res[] = $o;
            }
        }

        echo HM_Json::encodeErrorSkip($res);
    }

    public function newStudyHistoryAction()
    {
        $form = new HM_Form_StudyHistory();
        $request = $this->getRequest();
        if ($request->isPost()) {
            $isValid = $form->isValid($request->getParams());
            if ($isValid) {
                if ($form->file->isUploaded()) {
                    if ($form->file->receive() && $form->file->isReceived()) {
                        $result = $this->createStudyHistory($form);
                        if($result === FALSE){
                            $result = HM_Controller_Action_Crud::ERROR_COULD_NOT_CREATE;
                            $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => $this->_getErrorMessage($result)));
                            $this->_redirector->gotoSimple('study-history', 'index', 'user', array('user_id' => $form->getValue('user_id')));
                        }else{
                            $this->_flashMessenger->addMessage($this->_getMessage(HM_Controller_Action_Crud::ACTION_INSERT));
                            $this->_redirector->gotoSimple('study-history', 'index', 'user', array('user_id' => $form->getValue('user_id')));
                        }
                    }
                }
            } else {
                $populate = array();
                $form->populate($populate);
            }
        } else {
            $form->populate(array());
        }
        $this->view->form = $form;
    }

    public function getOnlineMatesAction()
    {
        $page = $this->_request->getParam('page', 1);
        $itemsPerPage = $this->_request->getParam('itemsPerPage', 10);
        $paginator = $this->getService('User')->getOnlineMates($page, $itemsPerPage);

        $response = [
            'items' => $paginator->getCurrentItems(),
            'pageCurrent' => $paginator->getCurrentPageNumber(),
            'pageCount' => $paginator->count(),
        ];

        return $this->responseJson($response);
    }

    protected function createStudyHistory(HM_Form_StudyHistory $form)
    {
        $values = $form->getValues();
        $userId     = $values['user_id'];
        $subjectId  = $values['subjects'][0];
        $begin      = $values['begin'] ? date('Y-m-d', strtotime($values['begin'])) : null;
        $end        = $values['end'] ? date('Y-m-d', strtotime($values['end'])) : null;
        $certificate_start  = date('Y-m-d', strtotime($values['certificate_date']));
        $certificate_months = (int) $values['certificate_months'];
        $certificate_end    = $certificate_months ? date("Y-m-d", strtotime("+". $certificate_months ." month", strtotime($certificate_start))) : null;
        $certificate_number = $values['certificate_number'];
        $certificate_type   = $values['certificate_type'];

        $file = $form->file->getFileName();

        if (!intval($subjectId))
            return false;

        $certificate = $this->getService('Certificates')->insert(
            array(
                'user_id'      => $userId,
                'subject_id'   => $subjectId,
                'created'      => date('Y-m-d'),
                'startdate'    => $certificate_start,
                'enddate'      => $certificate_end,
                'name'         => $file,
                'number'       => $certificate_number,
                'type'         => $certificate_type
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

        $this->getService('Graduated')->insert(
            array(
                'MID'            => $userId,
                'CID'            => $subjectId,
                'begin'          => $begin,
                'end'            => $end,
                'status'         => HM_Role_GraduatedModel::STATUS_SUCCESS,
                'certificate_id' => $certificate->certificate_id
            ), true
        );
    }

    public function _getMessages()
    {
        return array(
            HM_Controller_Action_Crud::ACTION_INSERT => _('Элемент успешно создан'),
            HM_Controller_Action_Crud::ACTION_UPDATE => _('Элемент успешно обновлён'),
            HM_Controller_Action_Crud::ACTION_DELETE => _('Элемент успешно удалён'),
            HM_Controller_Action_Crud::ACTION_DELETE_BY => _('Элементы успешно удалены')
        );
    }

    protected function _getErrorMessages()
    {
        return array(
            HM_Controller_Action_Crud::ERROR_COULD_NOT_CREATE => _('Элемент не был создан'),
            HM_Controller_Action_Crud::ERROR_NOT_FOUND        => _('Элемент не найден')
        );


    }

    private function _getErrorMessage($error)
    {
        $messages = $this->_getErrorMessages();
        if (isset($messages[$error])) {
            return $messages[$error];
        }else{
            return $error;
        }

        return _('Сообщение для данного события не установлено');
    }



    protected function _getMessage($action)
    {
        $messages = $this->_getMessages();
        if (isset($messages[$action])) {
            return $messages[$action];
        }

        return _('Сообщение для данного события не установлено');
    }
}