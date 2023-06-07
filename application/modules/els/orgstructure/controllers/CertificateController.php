<?php
class Orgstructure_CertificateController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;

    protected $service = 'Certificates';
    protected $idParamName  = 'certificate_id';
    protected $idFieldName = 'certificate_id';
    
    private $_userId;
    
    public function init()
    {
        $userId = $this->_getParam('user_id', 0);
        if ($userId && !is_array($userId)) {
            $this->_userId = $userId;
            $user = $this->getService('User')->find($this->_userId)->current();
            $this->view->setExtended(
                array(
                    'subjectName' => 'User',
                    'subjectId' => $this->_userId,
                    'subjectIdParamName' => 'user_id',
                    'subjectIdFieldName' => 'MID',
                    'subject' => $user
                )
            );  
            $this->getService('Unmanaged')->getController()->page_id = 'm00';
    
            // если пользователь не админ и смотрит не свою карточку
            // то скрываем меню "Редактирование учетной записи"
            // скопировано из HM_Controller_Action_User
            if ( $userId != $this->getService('User')->getCurrentUserId() &&
                 !$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ADMIN)
                 //!in_array($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_ADMIN))
            ) {
                 $this->view->addContextNavigationModifier(
                     new HM_Navigation_Modifier_Remove_Page('resource', 'cm:user:page1')
                 );
            }              
        }
        parent::init();        
    }
    
    public function listAction()
    {
     $this->view->headScript()->appendFile($this->view->serverUrl('/js/lib/jquery/lightbox/js/lightbox.js'));
     $this->view->headLink()->appendStylesheet($this->view->serverUrl('/js/lib/jquery/lightbox/css/lightbox.css'));
     
     $switcher = $this->getSwitcherSetOrder();
     
     $userId = $this->_getParam('user_id', 0);
     $select = $this->getService('Certificates')->getSelect();
     $select->from(
         array(
           'c' => 'certificates'
         ),
         array(
           'c.certificate_id',
           'user' => new Zend_Db_Expr('CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, \' \'), p.FirstName), \' \'), p.Patronymic)'),
           'c.name',
           'c.organization',
           'c.startdate',
           'c.enddate',
           'c.filename',
           'structure_of_organ_name' => new Zend_Db_Expr('GROUP_CONCAT(o.name)')
         )
     );
     
     $select->joinLeft(array('p' => 'People'), 'p.MID = c.user_id', array())
            ->joinLeft(array('o' => 'structure_of_organ'), 'o.mid = p.MID', array())
            ->where('c.subject_id = 0');
     
     if ($userId) {
         $select->where('c.user_id = ?', $userId);
     }
     
     $select->group(array('c.certificate_id', 'p.FirstName', 'p.LastName', 'p.Patronymic', 'c.name', 'c.organization', 'c.startdate', 'c.enddate', 'c.filename'))
            ->order(array('certificate_id'));
     
     if (!$switcher) {
         $now = "'".date("Y-m-d H:i:s")."'";
         $select->where("c.startdate <= $now AND c.enddate >= $now");
     }
     
     if ($userId) {
         $fioField = array('hidden' => true);
     } else {
         $fioField = array('title' => _('ФИО'));
     }
     
     $grid = $this->getGrid($select,
        array(
            'certificate_id' => array('hidden' => true),
            'user' => $fioField,
            'name' => array('title' => _('Название')),
            'organization' => array('title' => _('Организация, выдавшая сертификат')),
            'startdate' => array('title' => _('Дата выдачи сертификата')),
            'enddate' => array('title' => _('Дата окончания срока действия сертификата')),
            'structure_of_organ_name' => array('title' => _('Подразделение')),
            'filename' => array('hidden' => true)
        ),
        array(
            'user' => null,
            'name' => null,
            'organization'=> null,
            'structure_of_organ_name' => null,
            'startdate' => array('render' => 'Date'),
            'enddate' => array('render' => 'Date')
        )
     );

     $grid->updateColumn('startdate', array('format' => array('date', array('date_format' => HM_Locale_Format::getDateFormat()))));
     $grid->updateColumn('enddate', array('format' => array('date', array('date_format' => HM_Locale_Format::getDateFormat()))));
     
     $grid->updateColumn('name',
         array('callback' =>
             array('function' =>
                 array($this,'updateName'),
                 'params'   => array('{{certificate_id}}', '{{filename}}', '{{name}}')
             )
         )
     );

     $grid->addAction(array(
             'module' => 'orgstructure',
             'controller' => 'certificate',
             'action' => 'edit'
         ),
         array('certificate_id'),
         $this->view->svgIcon('edit', 'Редактировать')
     );

     $grid->addAction(array(
             'module' => 'orgstructure',
             'controller' => 'certificate',
             'action' => 'delete'
         ),
         array('certificate_id'),
         $this->view->svgIcon('delete', 'Удалить')
     );

     $grid->setGridSwitcher(array(
         array('name' => 'actual', 'title' => _('актуальные'), 'params' => array('all' => 0)),
         array('name' => 'all', 'title' => _('все, включая просроченные'), 'params' => array('all' => 1)),
     ));

     $this->view->grid = $grid;

     $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
    }
    
    public function updateName($id, $fileName, $name)
    {
       if (!$fileName) {
          return $name;
       }
       return '<a 
                  href="'.$this->view->url(array('module' => 'orgstructure', 'controller' => 'certificate', 'action' => 'data', 'certificate_id' => $id)).'" 
                  rel="lightbox"
                  style="text-decoration: none;"
               >
                  <img 
                      src="/images/content-modules/grid/card.gif" 
                      title="'.$name.'" 
                      class="ui-els-icon"
                  >
               </a> '.$name;
    }
    
    public function deleteAction()
    {
        $certificate_id = $this->_getParam('certificate_id', 0);

        if (!$certificate_id) {
           return;
        }
        
        $certificate = $this->getService('Certificates')->getOne($this->getService('Certificates')->find($certificate_id));
        $data = $certificate->getValues();
        
        if ($data['subject_id']) {
           return;
        }
        
        $this->getService('Certificates')->delete($certificate_id);
        
        $this->_flashMessenger->addMessage(_('Сертификат успешно удален'));
        $this->_redirector->gotoSimple('list', 'certificate', 'orgstructure');
        
        
    }
    
    public function editAction()
    {
        $user_id = $this->_getParam('user_id', 0);
        
        $form = new HM_Form_Certificate(array('user_id' => $user_id));
        
        $form->addElement('hidden', 'certificate_id');
        
        $certificate_id = $this->_getParam('certificate_id', 0);

        if (!$certificate_id) {
           return;
        }
        
        $certificate = $this->getService('Certificates')->getOne($this->getService('Certificates')->find($certificate_id));
        $oldData = $certificate->getValues();
        
        if ($oldData['subject_id']) {
           return;
        }

        $this->view->form = $form;
        
        $request = $this->_request;
        
        if ($request->isPost())
        {
            $params = $this->_request->getParams();
            
            if ($form->isValid($this->_request->getPost()))
            {
                $fileName = false;
                
                if ($form->file->isUploaded()) {
                    $form->file->receive();
                    if ($form->file->isReceived()) {
                        $fileName = realpath($form->file->getFileName());
                    }
                }
                
                $certificate = $this->getService('Certificates')->update(
                    array(
                        'certificate_id' => $certificate_id,
                        'user_id'        => is_array($params['mid']) ? $params['mid'][0] : $params['mid'], 
                        'startdate'      => self::convertDateToISO($params['startdate']),
                        'enddate'        => self::convertDateToISO($params['enddate']),
                        'name'           => $params['name'][0],
                        'organization'   => $params['organization'][0],
                        'description'    => $params['description']
                    )
                );
                
                $certificateId = $certificate->certificate_id;
                                
                if ($certificateId) {
                 
                    if ($fileName) {
                        $fileInfo = pathinfo($fileName);
                        $destDir = Zend_Registry::get('config')->path->upload->certificates . "$certificateId/";
                        
                        mkdir($destDir, true);
                        chmod($destDir, 0777);
                        
                        $destFileName = $destDir . "{$fileInfo['basename']}";
                        
                        copy($fileName, $destFileName);
                        chmod($destFileName, 0777);
                        
                        $this->getService('Certificates')->update(array(
                          'certificate_id' => $certificateId,
                          'filename'       => $fileInfo['basename']
                        ));
                    }
                 
                    $this->_flashMessenger->addMessage(_('Сертификат успешно отредактирован'));
                    
                    $urlParams = array();
                    
                    if ($user_id) {
                        $urlParams['user_id'] = $user_id;
                    }
                    
                    $this->_redirector->gotoSimple('list', 'certificate', 'orgstructure', $urlParams);
                }
             
            } else {
                if (!$user_id) {
                    $user = $this->getService('User')->getOne($this->getService('User')->find($params['user_id'][0]));
    
                    $form->getElement('mid')->setValue(array(
                      $user->MID => $user->getName()
                    ));
                }
                $form->getElement('name')->setValue(array(
                    $params['name'][0] => $params['name'][0]
                ));
                $form->getElement('organization')->setValue(array(
                    $params['organization'][0] => $params['organization'][0]
                ));
            }
        }
        else 
        {
            $user = $this->getService('User')->getOne($this->getService('User')->find($oldData['user_id']));
            
            $oldData['mid']      = !$user_id ? array($oldData['user_id'] => $user->getName()) : $oldData['user_id'];
            $oldData['name']         = array($oldData['name'] => $oldData['name']);
            $oldData['organization'] = array($oldData['organization'] => $oldData['organization']);
            $oldData['startdate']    = self::convertISODateToRus($oldData['startdate']);
            $oldData['enddate']      = self::convertISODateToRus($oldData['enddate']);
            
            $form->setDefaults($oldData);
        }
    }
    
    public function newAction()
    {
        $user_id = $this->_getParam('user_id', 0);
     
        $form = new HM_Form_Certificate(array('user_id' => $user_id));

        $this->view->form = $form;
        
        $request = $this->_request;
        
        if ($request->isPost())
        {
            $params = $this->_request->getParams();
                
            if (!empty($params['mid'])) {
                $mid = is_array($params['mid']) ? (int) $params['mid'][0] : $params['mid'];
            } else {
                $mid = false;
            }
         
            if ($form->isValid($this->_request->getPost()))
            {
                $fileName = false;
                
                if ($form->file->isUploaded()) {
                    $form->file->receive();
                    if ($form->file->isReceived()) {
                        $fileName = realpath($form->file->getFileName());
                    }
                }
                
                $certificate = $this->getService('Certificates')->insert(
                    array(
                        'user_id'      => $mid,
                        'subject_id'   => 0,
                        'created'      => date("Y-m-d H:i:s"),
                        'startdate'    => self::convertDateToISO($params['startdate']),
                        'enddate'      => self::convertDateToISO($params['enddate']),
                        'name'         => $params['name'][0],
                        'organization' => $params['organization'][0],
                        'description'  => $params['description']
                    )
                );
                
                $certificateId = $certificate->certificate_id;
                                
                if ($certificateId) {
                 
                    if ($fileName) {
                        $fileInfo = pathinfo($fileName);
                        $destDir = Zend_Registry::get('config')->path->upload->certificates . "$certificateId/";
                        
                        mkdir($destDir, true);
                        chmod($destDir, 0777);
                        
                        $destFileName = $destDir . "{$fileInfo['basename']}";
                        
                        copy($fileName, $destFileName);
                        chmod($destFileName, 0777);
                        
                        $this->getService('Certificates')->update(array(
                          'certificate_id' => $certificateId,
                          'filename'       => $fileInfo['basename']
                        ));
                    }
                 
                    $this->_flashMessenger->addMessage(_('Сертификат успешно создан'));
                    
                    $urlParams = array();
                    
                    if ($user_id) {
                        $urlParams['user_id'] = $user_id;
                    }
                    
                    $this->_redirector->gotoSimple('list', 'certificate', 'orgstructure', $urlParams);
                }
             
            } else {
             
                $defaults = array();
             
                // восстанавливаем данные в FcbkComplete
                if ($mid) {
                    $peopleModel = $this->getService('User')->find($mid)->current();
                 
                    $defaults['mid'] = array($mid => $peopleModel->getName());
                }
                
                if (!empty($params['name'])) {
                    $defaults['name'] = array($params['name'][0] => $params['name'][0]);
                }
                
                if (!empty($params['organization'])) {
                    $defaults['organization'] = array($params['organization'][0] => $params['organization'][0]);
                }
                
                $form->setDefaults($defaults);
             
            }
        }
        else 
        {
            
        }
    }

    public function dataAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');

        $certificate_id = (int) $this->_getParam('certificate_id', 0);
        $certificate = $this->getService('Certificates')->getOne($this->getService('Certificates')->find($certificate_id));
        $disposition = $this->_getParam('disposition', 'inline');

        if($certificate){

            $fileName = Zend_Registry::get('config')->path->upload->certificates.$certificate_id.'/'.$certificate->filename;
            // And this one convert manually
            $originalFileName = iconv(Zend_Registry::get('config')->charset, 'UTF-8', $certificate->filename);

            $sendSuccess = $this->_helper->SendFile(
                $fileName,
                HM_Files_FilesModel::getMimeType($certificate->filename),
                array( // options
                    'disposition' => $disposition,
                    'filename'    => $originalFileName
                )
            );
            if($sendSuccess) die();
        }

        $this->getResponse()->setHttpResponseCode(404);
        die();
    }
    
    public function previewAction()
    {
        $this->_helper->layout->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->view->certificate_id = $this->_getParam('certificate_id', 0);
    }
    
    public function ajaxnamelistAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            header('HTTP/1.0 404 Not Found');
            exit;
        }
     
        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();
        
        $this->getResponse()->setHeader('Content-type', 'application/json; charset=UTF-8');
        
        $tag = $this->_getParam('tag', '');
        
        if (!$tag) {
            echo HM_Json::encodeErrorSkip(array());
            return;
        }
        
        $tag = "%$tag%";
        
        $select = $this->getService('Certificates')->getSelect();
        
        $select->from(
            array(
                'c' => 'certificates'
            ),
            array(
                'c.name'
            )
        );
        
        $select->where('c.name LIKE ?', $tag);
        $select->group(array('name'));
        $select->limit(20);
        
        $list = $select->query()->fetchAll();
        $res = array();
        
        foreach($list as $item) {
            
            $name = $item['name'];
         
            if (!$name) {
                continue;
            }
            
            $o = new stdClass();
            $o->key   = $name;
            $o->value = $name;
            $res[]    = $o;
        }
        
        echo HM_Json::encodeErrorSkip($res);
    }
    
    public function ajaxorganizationlistAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            header('HTTP/1.0 404 Not Found');
            exit;
        }
     
        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();
        
        $this->getResponse()->setHeader('Content-type', 'application/json; charset=UTF-8');
        
        $tag = $this->_getParam('tag', '');
        
        if (!$tag) {
            echo HM_Json::encodeErrorSkip(array());
            return;
        }
        
        $tag = "%$tag%";
        
        $select = $this->getService('Certificates')->getSelect();
        
        $select->from(
            array(
                'c' => 'certificates'
            ),
            array(
                'c.organization'
            )
        );
        
        $select->where('c.organization LIKE ?', $tag);
        $select->group(array('organization'));
        $select->limit(20);
        
        $list = $select->query()->fetchAll();
        $res = array();
        
        foreach($list as $item) {
            
            $name = $item['organization'];
         
            if (!$name) {
                continue;
            }
            
            $o = new stdClass();
            $o->key   = $name;
            $o->value = $name;
            $res[]    = $o;
        }
        
        echo HM_Json::encodeErrorSkip($res);
    }
    
    protected static function convertDateToISO($date)
    {
        return DateTime::createFromFormat('d.m.Y', $date)->format('Y-m-d');
    }
    
    protected static function convertISODateToRus($date)
    {
        return DateTime::createFromFormat('Y-m-d', $date)->format('d.m.Y');
    }
}