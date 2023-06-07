<?php

class HM_Storage_StorageService extends HM_Activity_ActivityService
{
    const NOT_FILE = 0;
    const ITS_FILE = 1;

    private $cache;
    private $cabinet;
    private $rootHash;
    private $rootTitle;
    private $usersFolders;

    /**
     * HM_Storage_StorageService constructor.
     * @param null $mapperClass
     * @param null $modelClass
     * @throws Zend_Exception
     */
    public function __construct($mapperClass = null, $modelClass = null)
    {
        parent::__construct($mapperClass, $modelClass);

        PHP_OS == "Windows" ||
        PHP_OS == "WINNT"    ? define("SEPARATOR", "\\") : define("SEPARATOR", "/");

        $config             = Zend_Registry::get('config');
//        $this->cache['hashes'] = $this->getHashMap();
        $this->cabinet      = self::getCabinet();
        $this->rootHash     = $config->elFinder->root_hash;
        $this->usersFolders = $config->elFinder->users_folders;
        $this->rootTitle    = $config->elFinder->root_title;
    }

    /**
     * @return array
     */
    protected function getActivityModeratorRoles()
    {
        // Не стал добавлять препода в HM_Activity_ActivityService, сделал возможность переназначать роли на каждом сервисе отдельно
        return array(
            HM_Role_Abstract_RoleModel::ROLE_ADMIN,
            HM_Role_Abstract_RoleModel::ROLE_TEACHER,
            HM_Role_Abstract_RoleModel::ROLE_DEAN,
            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_MANAGER,
            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,
            HM_Role_Abstract_RoleModel::ROLE_HR,
            HM_Role_Abstract_RoleModel::ROLE_CURATOR
        );
    }

    /**
     * @param $name
     * @param HM_Storage_StorageModel $item
     * @param $elFinder
     * @return HM_Model_Abstract|HM_Storage_StorageModel
     */
    public function rename($name, HM_Storage_StorageModel $item, $elFinder)
    {
        $alias = $this->getAlias($name);
        $volume   = $elFinder->getVolume($this->rootHash);
        $rootName = $volume->getRootName();

        $data = [];
        $data['id']    = $item->id;
        $data['alias'] = $name;
        $data['name']  = $alias;
        $phash         = $volume->decode($item->getValue('phash'));
        $hash          = $volume->encode($phash . SEPARATOR . $alias);
        if (($rootName != 'files') && ($rootName == $this->getService('User')->getCurrentUserId())) {
            $parts = explode($rootName, $phash);
            $hash = $volume->encode($this->usersFolders . SEPARATOR . $rootName . $this->getAliasPath($parts[1])  . SEPARATOR . $alias);
        }
        $data['hash']     = $hash;

        $item = $this->update($data);
        try {
            $this->transliterateFileOnFileSystem($item, $elFinder);
        } catch (Exception $e) {}

        return $item;
    }

    /**
     * @param $name
     * @param $parent
     * @param $elFinder
     * @return HM_Model_Abstract
     * @throws Exception
     */
    public function createDir($name, $parent, $elFinder)
    {
        $alias = $this->getAlias($name);
        $volume   = $elFinder->getVolume($this->rootHash);
        $rootName = $volume->getRootName();

        $data  = [];
        $data['parent_id']    = $parent->id;
        $data['subject_id']   = $this->cabinet->getActivitySubjectId();
        $data['subject_name'] = $this->cabinet->getActivitySubjectName();
        $data['name']         = $alias;
        $data['alias']        = $name;
        $data['is_file']      = self::NOT_FILE;
        $data['phash']        = $parent->getValue('hash');
        $phash                = $volume->decode($parent->getValue('hash'));
        $hash                 = $volume->encode($phash . SEPARATOR . $alias);
        if (($rootName != 'files') && ($rootName == $this->getService('User')->getCurrentUserId())) {
            $parts = explode($rootName, $phash);
            $hash  = $volume->encode($this->usersFolders . SEPARATOR . $rootName . $this->getAliasPath($parts[1])  . SEPARATOR . $alias);
        }
        $data['hash']  = $hash;

        if ($parent->user_id) {
            $data['user_id'] = $parent->user_id;
        }

        $item =  $this->insert($data);
        try {
            $this->transliterateFileOnFileSystem($item, $elFinder);
        } catch (Exception $e) {}

        return $item;
    }

    /**
     * @param $user
     * @param $elFinder
     * @return HM_Model_Abstract
     * @throws ReflectionException
     * @throws Exception
     */
    public function createRoot($user, $elFinder)
    {
        $uRoot    = $user ? $this->getUsersRoot($elFinder) : null;
        $rootPath = $this->getRootPath($elFinder);
        $userId   = $user instanceof HM_User_UserModel ? $user->MID : $user->user_id;

        if ($user && $userId) {
            $rootPath .= SEPARATOR . $uRoot->name;
            $rootPath .= SEPARATOR . $userId;
        }

        if (!file_exists($rootPath)) {
            $oldUmask = umask(0);
            if (@mkdir($rootPath, 0777, true) === false) {
                throw new Exception(sprintf('Cannot create dir %s', $rootPath));
            }
            umask($oldUmask);
        }

        $phash = $uRoot ? $uRoot->getValue('hash') : '';
        $data = [
            'subject_id'   => $this->cabinet->getActivitySubjectId(),
            'subject_name' => $this->cabinet->getActivitySubjectName() ?: '',
            'is_file'      => self::NOT_FILE,
            'alias'        => $user ? $user->getName() : $this->rootTitle,
            'name'         => $user ? $userId : _('files'),
            'phash'        => $phash,
            'hash'         => $elFinder->getVolume($this->rootHash)->encode($rootPath)
        ];

        if ($user && $userId) {
            $data['user_id'  ] = $userId;
            $data['parent_id'] = $uRoot->id;
        }
        return $this->insert($data);
    }

    public function decodeHash($hash, $elFinder)
    {
        return $elFinder->getVolume($this->rootHash)->decode($hash);
    }

    public function encodeHash($hash, $elFinder)
    {
        return $elFinder->getVolume($this->rootHash)->encode($hash);
    }

    public function getItem($hash, $elFinder)
    {
        $volume    = $elFinder->getVolume($this->rootHash);
        $rootName  = $volume->getRootName();
        $path      = $volume->decode($hash);
        $parts     = explode($rootName, $path);
        $pathParts = explode(SEPARATOR, $path);
        $name      = $this->getAlias($pathParts[count($pathParts) - 1]);
        unset($pathParts[count($pathParts) - 1]);

        if (($rootName != 'files') && ($rootName == $this->getService('User')->getCurrentUserId()))
            $hash = $volume->encode($this->usersFolders . SEPARATOR . $rootName . (($hash !== $this->rootHash) ? $this->getAliasPath($parts[1]) : ''));
        $collection = /*count($this->cache['hashes']) ? $this->cache['hashes'][$hash] :*/ $this->fetchAll(['hash = ?' => $hash]);
        if (!$collection || !count($collection)) {
            $parentDir = implode(SEPARATOR, $pathParts);
            $parentHash = $volume->encode($parentDir);
            $collection = $this->fetchAll([
                'phash = ?' => ($parentHash !== $this->rootHash) ? $parentHash : $volume->encode($this->usersFolders . SEPARATOR . $rootName),
                'name  = ?' => $name
            ]);
        }

        return $collection->current();
    }

    /**
     * @param $src
     * @param $parent
     * @param $elFinder
     * @return array
     * @throws Exception
     */
    public function duplicate($src, $parent, $elFinder)
    {
        $newItems[] = [];
        if ($src->is_file) {
            $newItems[] = $this->duplicateItem($src, $parent, $elFinder);
        } else {
            $newTrg = $this->duplicateItem($src, $parent, $elFinder);
            $items = $this->getContent($src->id);
            foreach ($items as $item) {
                $newItems[] = $this->copy($item, $newTrg, $elFinder);
            }
        }

        return $newItems;
    }

    /**
     * @param $name
     * @param $parent
     * @param $elFinder
     * @return HM_Model_Abstract
     * @throws Exception
     */
    public function saveFile($name, $parent, $elFinder)
    {
        $alias    = $this->getAlias($name);
        $volume   = $elFinder->getVolume($this->rootHash);
        $rootName = $volume->getRootName();

        $data = [];
        $data['parent_id']    = $parent->getValue('id');
        $data['subject_id']   = $this->cabinet->getActivitySubjectId();
        $data['subject_name'] = $this->cabinet->getActivitySubjectName();
        $data['name']         = $alias;
        $data['alias']        = $name;
        $data['is_file']      = self::ITS_FILE;
        $data['phash']        = $parent->getValue('hash');
        $phash                = $volume->decode($parent->getValue('hash'));
        $hash                 = $volume->encode($phash . SEPARATOR . $alias);
        if (($rootName != 'files') && ($rootName == $this->getService('User')->getCurrentUserId())) {
            $parts = explode($rootName, $phash);
            $hash = $volume->encode($this->usersFolders . SEPARATOR . $rootName . $this->getAliasPath($parts[1])  . SEPARATOR . $alias);
        }
        $data['hash']         = $hash;
        if ($parent->user_id) {
            $data['user_id'] = $parent->user_id;
        }
        $item =  $this->insert($data);
        try {
            $this->transliterateFileOnFileSystem($item, $elFinder);
        } catch (Exception $e) {}

        return $item;
    }

    /**
     * Return or create if not exists root item. If $user not null - for $user
     * @param null $user
     * @param $elFinder
     * @return HM_Model_Abstract
     * @throws Exception
     */
    public function getRoot($user, $elFinder)
    {
        $key   = -1;
        $where = [];

        if (is_array($user)) {
            $where['hash = ?'] = $user['hash'];
            $user = $this->getService('User')->find($user['name'])->current();
        } elseif (is_a($user, 'HM_Storage_StorageModel')) {
            $where['hash = ?'] = $user->getValue('hash');
            $user = $this->getService('User')->find($user->getValue('user_id'))->current();
        } elseif (is_a($user, 'HM_User_UserModel')) {
            $key = $user->MID;
            $where['is_file = ?'] = 0;
            $where['subject_id = ?'] = $this->cabinet->getActivitySubjectId();

            $subjectName = $this->cabinet->getActivitySubjectName();
            if ($subjectName) {
                $where['subject_name = ?'] = $subjectName;
            } else {
                $where['(subject_name IS NULL) OR (subject_name = ?)'] = '';
            }
            $where['user_id = ?'] = $user->MID;
            $where['parent_id = ?'] = $this->getUsersRoot($elFinder)->id;
        }
//        if (isset($this->cache['roots'][$key]))  return $this->cache['roots'][$key];

        $root = $this->fetchAll($where, '', 1)->current();
        if (!$root) {
            $root = $this->find(1)->current();
            $root = $root ?: $this->createRoot($user, $elFinder);
        }

        return $this->cache['roots'][$key] = $root;
    }

    public function getUsersRoot($elFinder)
    {
//        if (isset($this->cache['usersRoot'])) {
//            return $this->cache['usersRoot'];
//        }
        $volume   = $elFinder->getVolume($this->rootHash);

        $root = $this->getRoot(null, $elFinder);
        $where = [];
        $where['parent_id = ?'] = $root->id;
        $where['name      = ?'] = $this->usersFolders;
        $uRoot = $this->fetchAll($where, '', 1)->current();
        if (!$uRoot || !$uRoot->id) {
            $data = [];
            $data['parent_id']    = $root->id;
            $data['subject_id']   = $this->cabinet->getActivitySubjectId();
            $data['subject_name'] = $this->cabinet->getActivitySubjectName();
            $data['alias']        = _('Личные папки');
            $data['name']         = $this->usersFolders;
            $data['is_file']      = self::NOT_FILE;
            $data['phash']        = $root->getValue('hash');
            $data['hash']         = $volume->encode($this->getRootPath($elFinder) . $data['name']);
            $uRoot = $this->insert($data);
        }
        return $this->cache['usersRoot'] = $uRoot;
    }

    public function getUsersRootContent($elFinder, $onlyFolders = false)
    {
        if (isset($this->cache['usersRootContent'][$onlyFolders])) {
            return $this->cache['usersRootContent'][$onlyFolders];
        }

        $where = [];
        if ($onlyFolders) {
            $where['is_file = ?'] = 0;
        }
        $where['parent_id = ?'] = $this->getUsersRoot($elFinder)->id;
        $where['user_id IS NULL'] = null;
        return $this->cache['usersRootContent'][$onlyFolders] = $this->fetchAll($where);
    }

    public function getContent($id)
    {
        if (isset($this->cache['contents'][$id])) {
            return $this->cache['contents'][$id];
        }
        $where = [];
        $where['parent_id = ?'] = $id;
        return $this->cache['contents'][$id] = $this->fetchAll($where);
    }

    public function getChilds($item, $checkForCabinet = false)
    {
        if (isset($this->cache['children'][$item->id])) {
            return $this->cache['children'][$item->id];
        }
        $where = [];
        if ($checkForCabinet) {
            $where['subject_id = ?'] = (int) $this->cabinet->getActivitySubjectId();
            if ($this->cabinet->getActivitySubjectName()) {
                $where['subject_name = ?'] = (string) $this->cabinet->getActivitySubjectName();
            } else {
                $where['subject_name = ? OR subject_name IS NULL'] = (string) $this->cabinet->getActivitySubjectName();
            }
        }
        $where['is_file = ?'] = 0;
        $where['parent_id = ?'] = $item->id;
        return $this->cache['children'][$item->id] = $this->fetchAll($where);
    }

    public function getParent($item)
    {
        if (is_a($item, 'HM_User_UserModel')) {
            $key = $item->MID;
        } elseif (is_a($item, 'HM_Storage_StorageModel')) {
            $key = $item->id;
        }
        if (isset($this->cache['parents'][$key])) {
            return $this->cache['parents'][$key];
        }
        return $this->cache['parents'][$key] = $this->find($item->parent_id)->current();
    }

    public function insert($data, $unsetNull = true)
    {
        $data['created'] = $this->getDateTime();
        $data['changed'] = $this->getDateTime();
        $item = parent::insert($data);
        return $item;
    }

    public function update($data, $unsetNull = true)
    {
        $data['changed'] = $this->getDateTime();
        $item = parent::update($data);
        return $item;
    }

    public function remove($item, $elFinder)
    {
        $childs = $this->getContent($item->id);
        foreach($childs as $child) {
            $this->remove($child, $elFinder);
        }
        if ($item->is_file) {
            $res = @unlink($this->getPath($item, $elFinder));
        } else {
            $res = @rmdir($this->getPath($item, $elFinder));
        }
        if ($res === false) {
            return $res;
        }
        $this->delete($item->id);

        $this->getService('Resource')->deleteBy(['storage_id = ?' => $item->id]);

        return true;
    }

    /**
     * @param HM_Storage_StorageModel $src
     * @param HM_Storage_StorageModel $parent
     * @param HM_ElFinder $elFinder
     * @return HM_Model_Abstract
     * @throws ReflectionException
     */
    private function duplicateItem(HM_Storage_StorageModel $src, HM_Storage_StorageModel $parent, HM_ElFinder $elFinder)
    {
        $data     = $src->getValues();
        $copy = _('копия');
        list($aliasPart, $extPart) = explode('.', $src->alias);
        list($alias,) = explode($copy, $aliasPart);
        $collection = $this->fetchAll([
            'parent_id = ?' => $parent->id,
            'alias LIKE ?' => "%" . rtrim($alias) . ' ' . $copy . "%"
        ]);

        $dot = $src->is_file ? '.' : '';
        if ($copyNumber = count($collection))
            $data['alias'] = rtrim($alias) . ' ' . $copy . ' ' . ($copyNumber + 1) . $dot . $extPart;
        else
            $data['alias'] = rtrim($alias) . ' ' . $copy . ' 1' . $dot . $extPart;

        $data['name'] = $this->getAlias($data['alias']);
        $volume   = $elFinder->getVolume($this->rootHash);

        $data['parent_id']    = $parent->getValue('id');
        $data['phash']        = $parent->getValue('hash');
        $phash                = $this->execMethod('decode', $volume, $parent->getValue('hash'));
        $rootName = $this->getService('User')->getCurrentUserId();
        $parts = explode($rootName, $phash);
        $data['hash'] = $this->execMethod(
            'encode', $volume,
            $this->usersFolders . SEPARATOR . $rootName . $this->getAliasPath($parts[1])  . SEPARATOR . $data['alias']
        );
        if ($parent->getValue('user_id')) {
            $data['user_id'] = $parent->getValue('user_id');
        }
        unset($data['id']);

        return $this->insert($data);
    }

    /**
     * @param HM_Storage_StorageModel $src
     * @param HM_Storage_StorageModel $parent
     * @param HM_ElFinder $elFinder
     * @return HM_Model_Abstract
     * @throws ReflectionException
     */
    private function copyItem(HM_Storage_StorageModel $src, HM_Storage_StorageModel $parent, HM_ElFinder $elFinder)
    {
        $data     = $src->getValues();
        $alias    = $this->getAlias($data['name']);
        $volume   = $elFinder->getVolume($this->rootHash);

        $data['parent_id']    = $parent->getValue('id');
        $data['phash']        = $parent->getValue('hash');
        $phash                = $this->execMethod('decode', $volume, $parent->getValue('hash'));
        $rootName = $this->getService('User')->getCurrentUserId();
        $parts = explode($rootName, $phash);
        $data['hash'] = $this->execMethod(
            'encode', $volume,
            $this->usersFolders . SEPARATOR . $rootName . $this->getAliasPath($parts[1])  . SEPARATOR . $alias
        );
        if ($parent->getValue('user_id')) {
            $data['user_id'] = $parent->getValue('user_id');
        }
        unset($data['id']);

        return $this->insert($data);
    }

    /**
     * @param HM_Storage_StorageModel $src
     * @param $parent
     * @param $elFinder
     * @return array
     * @throws ReflectionException
     */
    private function moveItem(HM_Storage_StorageModel $src, $parent, $elFinder)
    {
        $data = [
            'parent_id' => $parent->id,
            'id'        => $src->id
        ];

        $name     = $src->getValue('name');
        $volume   = $elFinder->getVolume($this->rootHash);

        $data['phash'] = $parent->getValue('hash');
        $phash         = $volume->decode($parent->getValue('hash'));
        $rootName = $this->getService('User')->getCurrentUserId();
        $parts = explode($rootName, $phash);
        $data['hash'] = $this->execMethod(
            'encode', $volume,
            $this->usersFolders . SEPARATOR . $rootName . $this->getAliasPath($parts[1])  . SEPARATOR . $name
        );

        if ($parent->user_id) {
            $data['user_id'] = $parent->user_id;
        }

        return $this->update($data);
    }

    /**
     * @param $src
     * @param $trg
     * @param $elFinder
     *
     * @return array
     * @throws Exception
     */
    public function copy($src, $trg, $elFinder)
    {
        $newItems = [];
        if ($src->is_file) {
            $newItems[] = $this->copyItem($src, $trg, $elFinder);
        } else {
            $newTrg = $this->copyItem($src, $trg, $elFinder);
            $items = $this->getContent($src->id);
            foreach ($items as $item) {
                $newItems[] = $this->copy($item, $newTrg, $elFinder);
            }
        }
        return $newItems;
    }

    public function move($src, $trg, $elFinder)
    {
        $newItems = [];
        if ($src->is_file) {
            $newItems[] = $this->moveItem($src, $trg, $elFinder);
        } else {
            $newTrg = $this->moveItem($src, $trg, $elFinder);
            $items = $this->getContent($src->id);
            foreach ($items as $item) {
                $newItems[] = $this->move($item, $newTrg, $elFinder);
            }
        }
        return $newItems;
    }

    public function syncMaterials($subjectId, $elFinder)
    {
        $collection = $this
            ->getService('Resource')
            ->fetchAllDependenceJoinInner('Lesson', $this->quoteInto([
                'Lesson.typeID = ? AND ',
                'Lesson.CID = ? AND ',
                'self.type = ?',
            ], [
                HM_Event_EventModel::TYPE_RESOURCE,
                $subjectId,
                HM_Resource_ResourceModel::TYPE_EXTERNAL
            ]));

        $resources = $collection->asArrayOfObjects();
        $resources2StorageIds = $collection->getList('resource_id', 'storage_id');

        $storageItems = $this
            ->fetchAll([
                'subject_name = ?' => HM_Storage_StorageModel::CONTEXT_SUBJECT_MATERIALS,
                'subject_id = ?'   => $subjectId,
                'is_file = ?'      => self::ITS_FILE,
            ])->asArrayOfObjects();

        $root = $this->getRoot(null, $elFinder);
        foreach ($resources as $resource) {
            if (empty($resource->storage_id) || !array_key_exists($resource->storage_id, $storageItems)) {
                $name = $this->getAlias($resource->filename);
                $storageItem = $this->insert([
                    'parent_id'    => $root->id,
                    'subject_name' => HM_Storage_StorageModel::CONTEXT_SUBJECT_MATERIALS,
                    'subject_id'   => $subjectId,
                    'name'         => $name,
                    'alias'        => $resource->filename,
                    'is_file'      => self::ITS_FILE,
                    'created'      => date('Y-m-d H:i:s'),
                    'changed'      => date('Y-m-d H:i:s'),
                ]);

                $resource->storage_id = $storageItem->id;
                $this->getService('Resource')->update($resource->getValues());
                $currentUserId = $this->getService('User')->getCurrentUserId();
                $sourceFile = realpath(Zend_Registry::get('config')->path->upload->resource) . SEPARATOR . $currentUserId . SEPARATOR . $resource->resource_id;
                $targetFile = $this->getRootPath($elFinder) . SEPARATOR . $this->usersFolders . SEPARATOR . $name;
                @copy($sourceFile, $targetFile);
            }
        }

        foreach ($storageItems as $storageItem) {
            if (!in_array($storageItem->id, $resources2StorageIds)) {
                $pathInfo = pathinfo($storageItem->name);
                list($title,) = explode('.', $storageItem->alias);
                $resource = $this->getService('Resource')->insert([
                    'storage_id' => $storageItem->id,
                    'subject_id' => $subjectId,
                    'status'     => HM_Resource_ResourceModel::STATUS_STUDYONLY,
                    'title'      => $title, // $pathInfo['filename'],
                    'type'       => HM_Resource_ResourceModel::TYPE_EXTERNAL,
                    'filename'   => $pathInfo['basename'],
                    'filetype'   => HM_Files_FilesModel::getFileType($pathInfo['basename']),
                ]);

                $resource->assignToSubject($subjectId, HM_Lesson_LessonModel::MODE_FREE);

                $sourceFile = $elFinder->getVolume($this->rootHash)->decode($storageItem->hash);
                $targetFile = realpath(Zend_Registry::get('config')->path->upload->resource) . SEPARATOR . $resource->resource_id;
                @copy($sourceFile, $targetFile);
            }
        }
    }

    public function addLessons($subjectId, $elFinder)
    {
        $storageItems = $this
            ->fetchAll([
                'subject_name = ?' => HM_Storage_StorageModel::CONTEXT_SUBJECT_LESSONS,
                'subject_id = ?'   => $subjectId,
                'is_file = ?'      => self::ITS_FILE,
            ])->asArrayOfObjects();
        $lessons = [];

        /** @var HM_Material_MaterialService $materialService */
        $materialService = $this->getService('Material');

        foreach ($storageItems as $storageItem) {
            list($title,) = explode('.', $storageItem->alias);

            $sourceFile = $elFinder->getVolume($this->rootHash)->decode($storageItem->hash);

            try
            {
                $material = $materialService->insert($sourceFile, $title, $subjectId);
            } catch (HM_Exception_Upload $e) {
                // ?
            }

            if ($material) {
                $lesson = $material->becomeLesson($subjectId);

                if(HM_Event_EventModel::TYPE_ECLASS == $lesson->typeID) {
                    $students = $lesson->getService()->getAvailableStudents($subjectId);
                    $material->webinarPush(['lesson' => $lesson, 'students' => $students]);
                    $lesson = $this->getService('Lesson')->fetchRow(['SHEID = ?' => $lesson->SHEID]);
                }
            }

            if($lesson) $lessons[] = $lesson->getEditPlainData();

            unlink($sourceFile);
            $this->delete(['id' => $storageItem->id]);
        }

        return $lessons;
    }

    public function addExtraMaterials($subjectId, $elFinder)
    {
        $storageItems = $this
            ->fetchAll([
                'subject_name = ?' => HM_Storage_StorageModel::CONTEXT_SUBJECT_EXTRA_MATERIALS,
                'subject_id = ?'   => $subjectId,
                'is_file = ?'      => self::ITS_FILE,
            ])->asArrayOfObjects();
        $result = [];

        foreach ($storageItems as $storageItem) {

            list($title,) = explode('.', $storageItem->alias);
            $sourceFile = $elFinder->getVolume($this->rootHash)->decode($storageItem->hash);
            /** @var HM_Resource_ResourceModel $resource */
            $resource = $this->getService('Material')->insert($sourceFile, $title, $subjectId);

            if ($resource) {
                $this->getService('SubjectResource')->link(
                    $resource->resource_id,
                    $subjectId,
                    'subject'
                );

                $result[] = $resource->getDataForExtrasSidebar($subjectId);
            }

            unlink($sourceFile);
            $this->delete(['id' => $storageItem->id]);
        }

        return $result;
    }

    private function getDefaultElFinder()
    {
        $isModerator = $this->isCurrentUserActivityModerator();
        $elFinder = new HM_ElFinder('', 0, $isModerator);
        return $elFinder;

    }

    public function createUserDirIfNotExists($userId, $elFinder = null)
    {
        if(is_null($elFinder)) {
            $elFinder = $this->getDefaultElFinder();
        }

        $usersRoot = $this->getUsersRoot($elFinder);
        $userDir = $this->getUserDir($userId, $elFinder);

        if(!$userDir) {
            $userDir = $this->createDir($userId, $usersRoot, $elFinder);
            $userDir = $this->update([
                'id' => $userDir->id,
                'subject_id' => 0,
                'subject_name' => '',
                'user_id' => $userId,
            ]);
        }

        $config = Zend_Registry::get('config');
        $filesPath = $config->path->upload->files;
        $personalFolders = $config->elFinder->users_folders;
        $userFolderSrc = $filesPath.$personalFolders . DIRECTORY_SEPARATOR . $userId;

        if (!file_exists($userFolderSrc)) {
            mkdir($userFolderSrc);
        }

        return $userDir;
    }

    public function getUserDir($userId, $elFinder = null)
    {
        if(is_null($elFinder)) {
            $elFinder = $this->getDefaultElFinder();
        }

        $usersRoot = $this->getUsersRoot($elFinder);
        $userDir = $this->fetchRow([
            'is_file = ?' => 0,
            'user_id = ?' => $userId,
            'parent_id = ?' => $usersRoot->id,
            'name = ?' => $userId,
        ]);

        return $userDir;
    }

    public function createSubjectDirs($subjectId)
    {
        $config = Zend_Registry::get('config');
        $uploadPath = $config->path->upload->files;

        if (file_exists($uploadPath.$subjectId) and
            file_exists($uploadPath.$this->getSubjectLessonsPath($subjectId)) and
            file_exists($uploadPath.$this->getSubjectExtrasPath($subjectId))
        ) {
            return;
        }

        $isModerator = $this->getService('Storage')->isCurrentUserActivityModerator();
        $elFinder = new HM_ElFinder('', $subjectId, $isModerator);
        if (!file_exists($uploadPath.$subjectId))
            mkdir($uploadPath.$subjectId);

        $rootDir = $this->getRoot(null, $elFinder);
        $subjectDir = $this->createDir($subjectId, $rootDir, $elFinder);

        $lessonsFolder = $config->elFinder->lessons_folder;
        if (!file_exists($uploadPath.$this->getSubjectLessonsPath($subjectId)))
            mkdir($uploadPath.$this->getSubjectLessonsPath($subjectId));
        $subjectPlanDir = $this->createDir($lessonsFolder, $subjectDir, $elFinder);

        $extrasFolder = $config->elFinder->extras_folder;
        if (!file_exists($uploadPath.$this->getSubjectExtrasPath($subjectId)))
            mkdir($uploadPath.$this->getSubjectExtrasPath($subjectId));
        $subjectMaterialsDir = $this->createDir($extrasFolder, $subjectDir, $elFinder);
    }

    public function getSubjectLessonsPath($subjectId)
    {
        $config = Zend_Registry::get('config');
        $lessonsFolder = $config->elFinder->lessons_folder;
        return $subjectId . DIRECTORY_SEPARATOR . $lessonsFolder;
    }

    public function getSubjectExtrasPath($subjectId)
    {
        $config = Zend_Registry::get('config');
        $extrasFolder = $config->elFinder->extras_folder;
        return $subjectId . DIRECTORY_SEPARATOR . $extrasFolder;
    }

    /************************************************************/
    /**                          Utilities                     **/
    /************************************************************/

    /**
     * @param HM_Model_Abstract $item
     * @param bool $checkForCabinet
     * @return array
     */
    public function getChildsTree(HM_Model_Abstract $item, $checkForCabinet = false)
    {
        $dirInfo = $item->getInfo();
        $dirs = $this->getChilds($item, $checkForCabinet);
        foreach ($dirs as $dir) {
            $dirInfo['dirs'][] = $this->getChildsTree($dir, $checkForCabinet);
        }
        return $dirInfo;
    }

    public function getParents($item, $dirs = [])
    {
        $parent = $this->getParent($item);
        if (!$parent) return $dirs;

        if ($parent->id && $parent->parent_id) {
            $dirs []= $parent;
            $dirs = $this->getParents($parent, $dirs);
        }
        return $dirs;
    }

    public function getUrl($item, $elFinder)
    {
        $path = [];
        if ($item->user_id) {
            $path []= rtrim($this->getRootUrl(), '/');
            $path []= $this->getUsersRoot($elFinder)->name;
            $path []= $item->user_id;
        } else {
            $path []= $this->getRootUrl();
        }

        return implode('/', $path) . $this->getPathParts($item);
    }

    private function getPathParts($item)
    {
        $path = [];
        $parents = $this->getParents($item);
        $parents = array_reverse($parents);
        foreach ($parents as $itm) {
            if ($item->user_id) {
                if ($itm->user_id) {
                    $path[] = $itm->name;
                }
            } else {
                $path[] = $itm->name;
            }
        }
        if (!$item->user_id && $item->name != 'files') $path[] = $item->name;

        return implode('/', $path);
    }

    /**
     * @param $item
     * @param $elFinder
     * @return string
     */
    public function getPath($item, $elFinder)
    {
        $path =  $elFinder->getVolume($this->rootHash)->decode($item->getValue('hash'));
        return str_replace($item->getValue('alias'), $item->getValue('name'), $path);
    }

    /**
     * @param null $elFinder
     * @return mixed|string
     * @throws ReflectionException
     * @throws Zend_Exception
     */
    public function getRootPath($elFinder = null)
    {
        if ($elFinder) {
            $volume = $elFinder->getVolume($this->rootHash);
            return $this->accessToProtectedProperty($volume, 'root');
        }
        $config = Zend_Registry::get('config');
        $path = APPLICATION_PATH.'/../public/'. $config->src->upload->files;

        $subjectName = $this->cabinet->getActivitySubjectName();
        if ($subjectName) {
            $path .= $subjectName . '/';
        }
        $path .= $this->cabinet->getActivitySubjectId() . '/';
        return $path;
    }

    public function getRootUrl()
    {
        $config = Zend_Registry::get('config');
        $protocol = 'http';
        // не уверен что на эту переменную можно положиться; точно работает в апаче на солярке
        if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on')) $protocol = 'https';
        $path = $protocol . '://' . $_SERVER['HTTP_HOST'] . $config->url->base . $config->src->upload->files;

        $subjectName = $this->cabinet->getActivitySubjectName();
        if ($subjectName) {
            $path .= $subjectName . '/';
            $path .= $this->cabinet->getActivitySubjectId() . '/';
        }

        return $path;
    }

    public function getAliasPath($path)
    {
        return implode('/', array_map(function ($item) {return $this->getAlias($item);}, explode(SEPARATOR, $path)));
    }

    public function getAlias($str)
    {
        $str = strip_tags(trim($str));
        $str = str_replace(
            [' ' . _('копия') . ' '],
            ' copy ',
            $str);
        $str = str_replace(
            ['\\', '/', ':', '*', '?', '"', '<', '>', '|'],
            '',
            $str);
        $str = str_replace(
            ['і', 'ї', 'є', 'І', 'Ї', 'Є'],
            ['i', 'i', 'e', 'I', 'I', 'E'],
            $str);//uk symbols
        return $this->transliteration($str);
    }

    /**
     * @param $parent
     * @param $name
     * @param $isFile
     * @return bool
     */
    public function isExists($parent, $name, $isFile)
    {
        $where = [];
        $where['is_file = ?'  ] = $isFile;
        $where['parent_id = ?'] = $parent->id;
        $where['name LIKE ?'  ] = $name;

        return ($this->fetchAll($where)->count() > 0);
    }

    /**
     *
     * @param $item
     * @param $elFinder
     * @throws Exception
     */
    protected function transliterateFileOnFileSystem($item, $elFinder)
    {
        $volume = $elFinder->getVolume($this->rootHash);
        $rootName = $volume->getRootName();

        $newPath = $volume->decode($item->getValue('hash'));
        $oldPath = $volume->decode($item->getValue('phash')) . SEPARATOR . $item->getValue('alias');

        if (($rootName != 'files') && ($rootName == $this->getService('User')->getCurrentUserId())) {
            $parent = $this->getParent($item);
            $phash = $volume->decode($parent->getValue('hash'));
            $parts = explode($rootName, $phash);
            $newPath = $volume->getRootPath() . $this->getAliasPath($parts[1]) . SEPARATOR . $item->getValue('name' );
            $oldPath = $volume->getRootPath() . $this->getAliasPath($parts[1]) . SEPARATOR . $item->getValue('alias');
        }

        if (@rename($oldPath, $newPath) === false) {
            throw new Exception(
                sprintf('Cannot rename %s to %s', $this->getPath($item, $elFinder), $newPath)
            );
        }
    }

    /**
     * @param $obj
     * @param $prop
     * @return mixed
     * @throws ReflectionException
     */
    private function accessToProtectedProperty($obj, $prop) {
        $reflection = new ReflectionClass($obj);
        $property = $reflection->getProperty($prop);
        $property->setAccessible(true);
        return $property->getValue($obj);
    }

    /**
     * @param $methodName
     * @param $object
     * @param $path
     * @return mixed
     * @throws ReflectionException
     */
    protected function execMethod($methodName, $object, $path)
    {
        $reflection = new ReflectionClass('elFinderVolumeLocalFileSystem');
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        $return  = $method->invoke($object, $path);
        return $return;
    }

    /**
     * @param $propertyName
     * @param $value
     * @param $object
     * @throws ReflectionException
     */
    public function setProperty($propertyName, $value, $object)
    {
        $reflection = new ReflectionClass('elFinderVolumeLocalFileSystem');
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }

    private function getHashMap()
    {
        $hashes = [];
        $result = $this->fetchAll()->getList('hash', 'id');
        foreach ($result as $hash => $id) {
            $hashes[$hash] = $this->find($id);
        }

        return $hashes;
    }

    private function transliteration($str) {
        $str=str_replace(
            ['Ш', 'Щ',  'Ж', 'Я', 'Ч', 'Ю', 'Ё', 'ш', 'щ',  'ж', 'я', 'ч', 'ю', 'ё', 'Й','Ц','У','К','Е','Н','Г','З','Х','Ъ','Ф','Ы','В','А','П','Р','О','Л','Д','Э','С','М','И','Т','Ь','Б','й','ц','у','к','е','н','г','з','х','ъ','ф','ы','в','а','п','р','о','л','д','э','с','м','и','т','ь','б',' '],
            ['SH','SCH','ZH','YA','CH','YU','YO','sh','sch','zh','ya','ch','yu','yo', 'J', 'C', 'U', 'K', 'E', 'N', 'G', 'Z', 'H', '_', 'F', 'Y', 'V', 'A', 'P', 'R', 'O', 'L', 'D', 'E', 'S', 'M', 'I', 'T', '_', 'B', 'j', 'c', 'u', 'k', 'e', 'n', 'g', 'z', 'h', '_', 'f', 'y', 'v', 'a', 'p', 'r', 'o', 'l', 'd', 'e', 's', 'm', 'i', 't', '_', 'b', '_'],
            $str);
        $str = str_replace(
            ['_copy_'],
            ' copy ',
            $str);
        return $str;
    }
}
