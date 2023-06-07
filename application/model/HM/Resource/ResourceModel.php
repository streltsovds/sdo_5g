<?php
class HM_Resource_ResourceModel
    extends HM_Model_Abstract
    implements HM_Search_Item_Interface, HM_Material_Interface, HM_Material_Recommended_Interface, HM_Rest_Interface
{
    protected $_primaryName = 'resource_id';

    const TYPE_EXTERNAL = 'external';
    const TYPE_HTML = 'html';
    const TYPE_URL = 'url';
    const TYPE_FILESET = 'fileset'; // HTML site
    const TYPE_WEBINAR = 'webinar';
    const TYPE_ACTIVITY = 'activity';
    //const TYPE_HTML_SLIDER = 'slider';
    const TYPE_CARD = 'card';

    //Статусы
    const STATUS_UNPUBLISHED = 0;
    const STATUS_PUBLISHED   = 1;
    const STATUS_ARCHIVED   = 2;
    const STATUS_STUDYONLY   = 7;

    // DEPRECATED!
    // use subject_id
    const LOCALE_TYPE_LOCAL  = 0;
    const LOCALE_TYPE_GLOBAL = 1;

    const EDIT_TYPE_NOT_SET = -1;
    const EDIT_TYPE_WYSIWYG = 0;
    const EDIT_TYPE_SLIDER = 1;
    const EDIT_TYPE_CODE = 2;
    const EDIT_TYPE_URL = 3;

    const EXTERNAL_VIEWER_GOOGLE = 'google';
    const ORIGIN_FILE_POSTFIX = 'origin';

    public function getClassName()
    {
        return _('Информационный ресурс');
    }

    static public function getTypes()
    {
        return array(
            self::TYPE_EXTERNAL => _('Файл'),
            self::TYPE_HTML => _('HTML-страница'),
            self::TYPE_FILESET => _('HTML-сайт'),
            self::TYPE_URL => _('Ссылка на внешний ресурс'),
            self::TYPE_WEBINAR => _('Запись вебинара'),
            self::TYPE_CARD => _('Только карточка'),
        );
    }

    static public function getEditableTypes()
    {
        $types = self::getTypes();
        unset($types[self::TYPE_WEBINAR]);
        return $types;
    }

    static public function getStatuses()
    {
        return array(
            self::STATUS_UNPUBLISHED    => _('Не опубликован'),
            self::STATUS_PUBLISHED      => _('Опубликован'),
            self::STATUS_STUDYONLY      => _('Ограниченное использование'),
            self::STATUS_ARCHIVED      => _('Архивный'),
        );
    }

    static public function getLocaleStatuses()
    {
        return array(
            self::LOCALE_TYPE_LOCAL  => _('Учебный курс'),
            self::LOCALE_TYPE_GLOBAL => _('База знаний')
        );
    }

    public function getLinkTitle(){

        if(!$this->resource_id) return false;
        return array(
            'module' => 'file',
            'controller' => 'get',
            'action' => 'resource',
            'resource_id' => $this->resource_id,
            'download' => 1,
            'name' => $this->title
        );
    }

    public function getCardFields(){
        $fields = [
            'getName()' => _('Название'),
            'getType()' => _('Тип'),
            'getCreateDate()' => _('Дата создания')
        ];

        if($this->getCreateDate() !== $this->getUpdateDate()) {
            $fields['getUpdateDate()'] = _('Дата последнего обновления');
        }

        $fields['getAuthorName()'] = _('Автор');

        return $fields;
    }

    public function getAuthorName()
    {
        $return = '';

        $user = Zend_Registry::get('serviceContainer')->getService('User')->find($this->created_by);
        if(count($user)) {
            $return = $user->current()->getName();
        }

        return $return;
    }

    public function getName()
    {
        return $this->title;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getType(){
        $types = $this->getTypes();
        return $types[$this->type];
    }

    public function getTypeByClassifier($classifierTypeId)
    {
        if (count($this->classifierLinks)) {
            $classifierIds = $this->classifierLinks->getList('classifier_id');
            $classifiers = Zend_Registry::get('serviceContainer')->getService('Classifier')->fetchAll(array(
                'classifier_id IN (?)' => $classifierIds,
                'type = ?' => $classifierTypeId,
            ))->getList('name');
            return implode(', ', $classifiers);
        }
        return '';
    }

    // todo: сделать все типы viewable
    public function isViewable()
    {
        return
            in_array($this->type, array(HM_Resource_ResourceModel::TYPE_HTML, HM_Resource_ResourceModel::TYPE_URL, HM_Resource_ResourceModel::TYPE_FILESET)) ||
            in_array($this->filetype, array(
                HM_Files_FilesModel::FILETYPE_HTML,
                HM_Files_FilesModel::FILETYPE_FLASH,
                HM_Files_FilesModel::FILETYPE_IMAGE,
                HM_Files_FilesModel::FILETYPE_TEXT,
                HM_Files_FilesModel::FILETYPE_AUDIO,
                HM_Files_FilesModel::FILETYPE_PDF,
                HM_Files_FilesModel::FILETYPE_VIDEO
            ));
    }

    public function getIconClass()
    {
        return HM_Resource_ResourceService::getIconClass($this->type, $this->filetype, $this->filename, $this->activity_type);
    }

    public function getCardUrl()
    {
        if(!$this->resource_id) return false;
        return array(
            'baseUrl' => '',
            'module' => 'resource',
            'controller' => 'index',
            'action' => 'card',
            'resource_id' => $this->resource_id,
        );
    }

    public function getViewUrl()
    {
        if(!$this->resource_id) return false;
        return array(
            'baseUrl' => '',
            'module' => 'resource',
            'controller' => 'index',
            'action' => 'index',
            'resource_id' => $this->resource_id,
        );
    }

    public function getKbaseUrl() {
        if(!$this->resource_id) return false;
        return array(
            'baseUrl' => '',
            'module' => 'kbase',
            'controller' => 'resource',
            'action' => 'index',
            'resource_id' => $this->resource_id,
        );
    }

    public function getCreateUpdateDate()
    {
        $return = sprintf(_('Создан: %s'), $this->getCreateDate());
        if ($this->created != $this->updated) {
            $return .= ', ' . sprintf(_('обновлён: %s'), $this->getUpdateDate());
        }
        return $return;
    }

    public function getCreateDate()
    {
        return $this->dateTime($this->created);
    }

    public function getUpdateDate()
    {
        return $this->dateTime($this->updated);
    }

    public function getServiceName()
    {
        return 'Resource';
    }

    public function getFilesList($revisionId = 0)
    {
        $collection = $this->getService()->fetchAll(array(
            'parent_id = ?' => $this->resource_id,
            'parent_revision_id = ?' => $revisionId,
        ));
        if (count($collection)) {
            $ret = '';
            foreach($collection as $item) {
                $ret .= sprintf('<a class="text" href="/file/get/resource/resource_id/%d">%s</a>', $item->resource_id, $item->filename);
            }
            return $ret;
        }

        return false;
    }

    public function getCreateBy()
    {
        $createby = '';
        if (Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(
            Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(),array(
            HM_Role_Abstract_RoleModel::ROLE_DEVELOPER,
            HM_Role_Abstract_RoleModel::ROLE_MANAGER
        ))
        ){
            $select=Zend_Registry::get('serviceContainer')->getService('User')->getSelect();
            $select->from(array('t1' => 'People'),array(
                'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(t1.LastName, ' ') , t1.FirstName), ' '), t1.Patronymic)"),
                'department' => new Zend_Db_Expr("org2.name")
            ));
            $select->joinInner(array('org' => 'structure_of_organ'),'t1.MID = org.MID',array());
            $select->joinLeft(array('org2' => 'structure_of_organ'),'org.owner_soid = org2.soid',array());
            $select->where('t1.MID = ?',$this->created_by);
            $user=$select->query()->fetchAll();
            if ($user)
                $createby = $user[0]['fio'].' ('.$user[0]['department'].')';
        }
        return $createby;
    }

    public function getKbaseType()
    {
        return HM_Kbase_KbaseModel::TYPE_RESOURCE;
    }

    static public function getTypeString($type)
    {
        switch ($type) {
            case HM_Resource_ResourceModel::TYPE_EXTERNAL:
                $actionNamePostfix = 'External';
                break;
            case HM_Resource_ResourceModel::TYPE_HTML:
                $actionNamePostfix = 'Html';
                break;
            case HM_Resource_ResourceModel::TYPE_FILESET:
                $actionNamePostfix = 'Fileset';
                break;
            case HM_Resource_ResourceModel::TYPE_URL:
                $actionNamePostfix = 'Url';
                break;
            default:
                throw new HM_Exception('Don\'t have action for this resource type.');
        }
        return $actionNamePostfix;
    }

    /*
     * 5G
     * Implementing HM_Material_Interface
     */
    public function becomeLesson($subjectId)
    {
        return $this->getService()->createLesson($subjectId, $this->resource_id);
    }

    public function getUnifiedData()
    {
        $modelData = $this->getData();
        $unifiedData = [
            'id' => $modelData['resource_id'],
            'title' => $modelData['title'],
            'kbase_type' => $modelData['kbase_type'],
            'created' => $modelData['created'],
            'updated' => $modelData['updated'],
            'tag' => $modelData['tag'],
            'classifiers' => $modelData['classifiers'],
            'subject_id' => $modelData['subject_id'],
            'url' => Zend_Registry::get('view')->url(['module' => 'kbase', 'controller' => 'resource', 'action' => 'index', 'resource_id' => $modelData['resource_id']])
        ];

        $unifiedData['viewUrl'] = $unifiedData['url'];

        return array_merge($modelData, $unifiedData);
    }

    public function getDataForExtrasSidebar($subjectId)
    {
        $result = $this->getPlainData();
        $view = Zend_Registry::get('view');
        $request = Zend_Controller_Front::getInstance()->getRequest();

        $result['viewUrl'] = ($this->type !== HM_Resource_ResourceModel::TYPE_CARD) ? $view->url(['module' => 'kbase', 'controller' => 'resource', 'action' => 'index', 'resource_id' => $this->resource_id, 'subject_id' =>  $subjectId]) : false;
        $result['editUrl'] = $view->url(['module' => 'kbase', 'controller' => 'resource', 'action' => 'edit-card', 'resource_id' => $this->resource_id, 'subject_id' => $subjectId,
            'redirectUrl' => urlencode($view->url(['module' => $request->getModuleName(), 'controller' => $request->getControllerName(), 'action' => $request->getActionName(), 'subject_id' => $subjectId], null, true))
        ]);

        $result['deleteUrl'] = $view->url(['module' => 'subject', 'controller' => 'extra', 'action' => 'unlink', 'resource_id' => $this->resource_id, 'subject_id' => $subjectId]);

        foreach ($result as &$resultItem) {
            $resultItem = htmlspecialchars($resultItem, ENT_QUOTES|ENT_HTML401);
        }

        return $result;
    }

    public function getFileInfo()
    {
        $view = Zend_Registry::get('view');

        $fileSrc = $this->getFilePath();// $this->getService()->getFileSrc($this->resource_id);

        $resourceUrl = $this->resource_id ? $view->url([
            'module' => 'file',
            'controller' => 'get',
            'action' => 'resource',
            'resource_id' => $this->resource_id,
        ]) : '';

        $originalFileSrc = $this->getOriginFileSrc();
        $withOriginal = file_exists($originalFileSrc);

        $originalResourceUrl = $this->resource_id && $withOriginal ? $view->url([
            'module' => 'file',
            'controller' => 'get',
            'action' => 'origin-resource',
            'resource_id' => $this->resource_id,
        ]) : '';

        $uploadedItem = new HM_DataType_Form_Element_Vue_UploadedItem();
        $fileTypeString = HM_Files_FilesModel::fileTypeToString($this->filetype);
        $file = new HM_DataType_Form_Element_Vue_FileInfo();
        $uploadedItem->file = $file;
        $file->name = $this->filename;
        $file->url = $resourceUrl;
        $file->mimeType = function_exists('mime_content_type') ? mime_content_type($fileSrc) : '';
        $file->type = $fileTypeString;
        $file->size = filesize($fileSrc);
        $file->previewUrl = $resourceUrl;

        if ($withOriginal) {
            $originalFile = new HM_DataType_Form_Element_Vue_FileInfo();
            $uploadedItem->originalFile = $originalFile;
            $originalFile->name = $this->origin_filename;
            $originalFile->url = $originalResourceUrl;
            $originalFile->mimeType = mime_content_type($originalFileSrc);
            $originalFile->type = HM_Files_FilesModel::fileTypeToString($this->origin_filetype);
            $originalFile->size = filesize($originalFileSrc);
            $originalFile->previewUrl = $originalResourceUrl;
        } else {
            $file->convertableToPdf = HM_Files_FilesModel::isConvertableToPdf($fileTypeString);
        }

        return $uploadedItem;
    }

    public static function getIconFolder($subjectId = 0)
    {
        $folder = Zend_Registry::get('config')->path->upload->public_resource_icons;
        $maxFilesPerFolder = Zend_Registry::get('config')->path->upload->maxfilescount;
        $folder = $folder . floor($subjectId / $maxFilesPerFolder) . '/';

        if(!is_dir($folder)){
            mkdir($folder, 0774);
            chmod($folder, 0774);
        }
        return $folder;
    }

    public function getIcon($alwaysReturn = false)
    {
        $path = HM_Resource_ResourceModel::getIconFolder($this->resource_id) . $this->resource_id . '.jpg';
        if ($alwaysReturn || is_file($path)){
            return preg_replace('/^.*public\//', Zend_Registry::get('config')->url->base, $path);
        }
        return '';
    }

    public function getRecommendedName()
    {
        return $this->title;
    }

    public function getRecommendedDescription()
    {
        return $this->description;
    }

    public function getRecommendedImage()
    {
        return $this->getIcon(true);
    }

    public function getRecommendedRubrics()
    {
        $materialCriterion = Zend_Registry::get('serviceContainer')->getService('MaterialCriteria')
        ->fetchAllDependence('Criterion', [
            'material_id = ?' => $this->resource_id,
            'material_type = ?' => HM_Event_EventModel::TYPE_RESOURCE
        ]);

        if ($materialCriterion->count()) {
            $data = [];

            foreach ($materialCriterion as $mc) {
                $data = array_merge($data, $mc->criterion->getList('name'));
            }

            return $data;
        }

        return [];
    }

    public function isInPublic() 
    {
        return $this->type==HM_Resource_ResourceModel::TYPE_EXTERNAL && ($this->filetype==HM_Files_FilesModel::FILETYPE_AUDIO || $this->filetype==HM_Files_FilesModel::FILETYPE_VIDEO);

    } 

    public function getFilePath($bRealPath=false, $resourceId=false) // $resourceId используется при копировании
    {
        if($this->isInPublic()) {
            $parts = explode('.', $this->filename);
            $ext = strtolower($parts[count($parts)-1]);
            return ($bRealPath ? realpath(Zend_Registry::get('config')->path->upload->public_resource) : Zend_Registry::get('config')->path->upload->public_resource)."/".($resourceId ? $resourceId : $this->resource_id).".".$ext;
        }
        return ($bRealPath ? realpath(Zend_Registry::get('config')->path->upload->resource) : Zend_Registry::get('config')->path->upload->resource).'/'.($resourceId ? $resourceId : $this->resource_id);
    }

    public function getOriginFileSrc()
    {
        return $this->getFilePath() . HM_Resource_ResourceModel::ORIGIN_FILE_POSTFIX;
    }

    public function getPublicUrl()
    {
        if(!$this->isInPublic()) return false;

        $parts = explode('.', $this->filename);
        $ext = strtolower($parts[count($parts)-1]);
        $path = explode('/', Zend_Registry::get('config')->path->upload->public_resource);
        unset($path[count($path)-1]);
        $url = "/{$path[count($path)-2]}/{$path[count($path)-1]}/{$this->resource_id}.{$ext}";
        if($_SERVER['HTTP_REFERER']) {
            $prefix = explode('/', $_SERVER['HTTP_REFERER']);
            $prefix = implode('/', array($prefix[0], $prefix[1], $prefix[2]));
            $prefix .= '/';
        }

        return "{$prefix}{$url}";
    }

    public function getRestDefinition()
    {
        return [
            'id' => (int)$this->resource_id,
            'externalId' => (string)$this->resource_id_external,
            'title' => (string)$this->title,
            'description' => (string)$this->description,
            'image_url' => (string)$this->getIcon(), // Кто тут?
            'view_url' => (string)Zend_Registry::get('view')->url($this->getViewUrl()),
        ];
    }


}
