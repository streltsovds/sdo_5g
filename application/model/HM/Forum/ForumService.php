<?php

/**
 * Сервис форума
 * Является полным API форума.
 * Реализует бизнес-логику форума.
 * Ничего не знает о способе хранения данных, использует для доступа к данным сервисы-модели.
 *
 * Все вызовы касательно форума осуществляются только через него.
 * Работать на прямую с сервисами-моделями настоятельно не рекомендуется.
 */
class HM_Forum_ForumService extends HM_Activity_ActivityService implements 
            HM_Forum_Library_Constants,
            HM_Service_Schedulable_Interface
{

    const EVENT_GROUP_NAME_PREFIX = 'FORUM_MESSAGE_ADD';

    /**
     * Конфигурация по умолчанию
     *
     * @var array
     */
    protected $defaultConfig = array(
        // Настройки форумов
        'forums'    => array(
            // Модераторы
            'moderators' =>array(
                HM_Role_Abstract_RoleModel::ROLE_ADMIN,
                HM_Role_Abstract_RoleModel::ROLE_MANAGER,
                HM_Role_Abstract_RoleModel::ROLE_DEAN
            ),
            // Параметры для новых форумов
            'new' => array(
               'flags'  => array(
                    'active'      => true,  // форум активен и доступен для пользователей
                    'subsections' => false, // структура форума имеет подразделы
                    'subsecttree' => false, // структура форума допускает более одного уровня вложенности подразделов
                    'closed'      => false, // форум закрыт и доступен только для чтения
                    'private'     => false  // доступ на форум только по списку допусков пользователей
                )
            )
        ),

        // Настройки разделов
        'sections' => array(
            // Структура вывода разделов/тем
            'structure' => array(
                'order_last_msg' => true // Сортировка по последнему сообщению в теме
            ),
            // Параметры для новых разделов
            'new' => array(
                'flags'  => array(
                    'active'  => true,  // раздел активен и доступен для пользователей
                    'theme'   => false, // раздел является темой, не может содержать подразделы, может содержать только сообщения
                    'closed'  => false, // раздел закрыт и доступен только для чтения
                    'private' => false  // доступ в раздел только по списку допусков пользователей
                )
            )
        ),

        // Настройки сообщений
        'messages' => array(
            // Параметры для новых сообщений
            'new' => array(
                'flags' => array(
                    'active'  => true, // Сообщение показывается
                    'deleted' => false // Сообщение удалено
                )
            ),
            // Структура запроса и вывода сообщений
            // Для отображения в теме
            'structure' => array(
                'as_tree'             => true,  // Выводить сообщения в виде дерева
                'order_by_time'       => false,  // Сортировать сообщения по времени добавления
                'order_reverse'       => false,  // Обратный порядок сортировки (новые наверх)
                'only_new'            => false,  // Только новые сообщения (Запрос тяжёлый для БД !)
                'preview'             => false,  // Текст сообщений подгружается только если не превышает параметры максимального размера. Сообщения не помечаются как просмотренные
                'new_max_period'      => 604800, // Период времени в течении которого сообщение может считаться новым (в секундах)
            ),
            // Для отображения в предпросмотре (объединяются со "structure" при установленном параметре "preview")
            'structure_preview' => array(
                'order_by_time'       => true,
                'order_reverse'       => true,
            )
        ),

        // Создание форума по умолчанию
        'forum_init' => array(
            // Параметры форума
            'forum' => array(
                'title'    => 'Форум портала',
                'flags'    => array(
                    'subsections' => true,
                ),
            ),
            // Параметры разделов
            'sections' => array(
                array(
                    'title' => 'Общие вопросы',
                    'text'  => 'Общие вопросы'
                )
            )
        ),

        // Создание форума для курса
        'subject_init' => array(
            // Название форума
            'forum_name' => '%s',
            // Параметры форума
            'forum' => array(
                'flags' => array(
                    'subsections' => true
                )
            ),
        ),

        // Создание темы форума для занятия
        'lesson_init' => array(
            // Название темы
            'theme_name' => 'Тема занятия "%s"',
            // Параметры темы
            'section' => array(
                'flags' => array(
                    'theme' => true
                )
            )
        ),

        // ID форума по умолчанию
        'forum_default_id' => 1,

        // Оценки
        // 0 - оценка не выставлена
        'ratings' => array(
            5 => '5 (отлично)',
            4 => '4 (хорошо)',
            3 => '3 (удовлетворит.)',
            2 => '2 (неудовлетворит.)',
            1 => '1 (очень плохо)',
            6 => 'зачтено',
            7 => 'незачтено'
        )
    );

    /**
     * Конфигурация движка форума
     *
     * @var Zend_Config
     */
    protected $config;

    /**
     * Конструктор. Может принимать параметры конфигурации.
     *
     * @param Zend_Config | array $config
     */
    public function __construct($config = null){
        parent::__construct();
        $this->setConfig($config);
    }

    /**
     * Задать конфигурацию форума
     *
     * @param Zend_Config | array $config Параметры конфигурации
     */
    public function setConfig($config = null){
        if($this->config === null) $this->config = new Zend_Config($this->defaultConfig, true);

        switch(true){
            case is_array($config):
                $config = new Zend_Config($config);

            case $config instanceof Zend_Config:
                break;

            case Zend_Registry::isRegistered('config'):
                $config = Zend_Registry::get('config')->forum;
                if($config) break;

            default: return;
        }

        $this->config->merge($config);
    }

    /**
     * Получить объект конфигурации форума
     *
     * @return Zend_Config
     */
    public function getConfig(){
        return $this->config;
    }

    /**
     * Создать форум
     *
     * @param array $data параметры форума:
     * 'userId'   => id пользователя-владельца форума
     * 'userName' => ФИО пользователя-владельца форума
     * 'title'    => название форума
     * 'flags'    => опции
     *
     * @return HM_Forum_Forum_ForumModel
     */
    public function createForum(array $data){
        $data = array_replace_recursive($this->config->forums->new->toArray(), $data);
        $data = $this->_userData($data);

        return $this->getService('ForumForum')->createForum($data);
    }

    /**
     * Получить информацию о форуме
     * Так же является быстрым вариантом проверить существования форума
     *
     * @param int $forumId id форума
     * @return HM_Forum_Forum_ForumModel | null
     * @throws HM_Exception
     */
    public function getForumInfo($forumId){
        $forum = $this->getService('ForumForum')->getForum((int) $forumId);
        if(!$forum) throw new HM_Exception(_(self::ERR_MSG_NOFORUM), self::ERR_CODE_NOFORUM);
        return $forum;
    }

    /**
     * Получить форум по id форума (со всеми разделами и сообщениями)
     *
     * @param  int $forumId id форума
     * @param  int $sectionId id раздела
     * @return HM_Forum_Forum_ForumModel
     * @throws HM_Exception
     */
    public function getForum($forumId, $sectionId = null)
    {
        $forum = $this->_getForum((int) $forumId);

        $userService = $this->getService('User');

        // Определение прав пользователя на форуме и в темах
        $forum->moderator = in_array($userService->getCurrentUserRole(), $this->config->forums->moderators->toArray());
        $forum->current_user_id = (int) $userService->getCurrentUserId();

        if(!$sectionId) $sectionId = null;

        // Корень форума
        if($sectionId === null){
            // Разделы
            $sections = $this->_getSectionsByForumId($forum->forum_id);
            $sections = $this->_orderSort($sections, 'order', true, 'section_id');
            $forum->sections = $sections;

            // Подразделы
            if(!empty($sections)){
                $subsectionsUnsort = $this->_getSectionsBySectionId(array_keys($sections));
                $subsectionsGrp = array();
                foreach ($subsectionsUnsort as $subsection) {
                    if(!isset($subsectionsGrp[$subsection->parent_id])) $subsectionsGrp[$subsection->parent_id] = array();
                    $subsectionsGrp[$subsection->parent_id][$subsection->section_id] = $subsection;
                }

                // Сортировка подразделов по разделам с сортировкой по приоритету и времени последнего сообщения
                $orderBy = $this->config->sections->structure->order_last_msg ? 'last_msg' : null;
                foreach($subsectionsGrp as $id => $subsections){
                    $sections[$id]->subsections = $this->_orderSort($subsections, 'order', true, null, $orderBy, true);
                }
            }
        }
        // Запрошенный раздел
        else{
            // Раздел
            $section = $this->_getSection((int) $sectionId);

            // Родительский раздел
            if($section->parent_id > 0) $section->parent = $this->_getSection($section->parent_id);

            // Раздел является темой форума, может содержать сообщения
            if($section->flags->theme) $section->messages = $this->_getMessagesBySectionId($section->section_id);

            // Раздел может содержать подразделы, в т.ч. темы форума
            else{
                // Подразделы
                $subsections = $this->_getSectionsBySectionId($section->section_id);
                $data = $this->_orderSort($subsections, 'order', true, 'section_id', 'last_msg', true);
                $section->subsections = $data;
            }
            $forum->section = $section;
            $forum->section->current_user_id = (int) $userService->getCurrentUserId();
            $forum->section->user_photo = $userService->getPhoto((int) $forum->section->user_id);
        }

        return $forum;
    }

    /**
     * Получить id тьютора по объекту $lesson, чтобы установить владельца темы занятия
     * Если на занятие не установлен тьютор, берем первого из списка тьюторов курса
     * @param $lesson HM_Lesson_LessonModel
     * @return mixed|null
     */
    protected function _getTeacherIdByLesson(HM_Lesson_LessonModel $lesson)
    {
        if(!$lesson->teacher){
            // Получить курс $lesson->CID
            $course = $this->getOne($this->getService('Subject')->find($lesson->CID));
            // Получить список тьюторов и вставить id первого тьютора курса
            $teacher = $this->getOne($this->getService('Subject')->getTeachers($course->subid))->MID;
            return $teacher;
        } else {
            return $lesson->teacher;
        }
    }

    /**
     * Получить (или создать) основной раздел для форума курса и занятий
     *
     * @param HM_Forum_Forum_ForumModel $forum
     * @return HM_Forum_Section_SectionModel
     * @throws HM_Exception
     */
    protected function _getBasicSectionByForum(HM_Forum_Forum_ForumModel $forum)
    {
        $basicSection = $this->getService('ForumSection')->getBasicSectionByForumId($forum->forum_id);

        // Если основного раздела не существует, пытаемся его создать
        if(!$basicSection){
            $section['title'] = '';
            $section['subject'] = 'subject';
            $section['flags'] = [HM_Forum_Forum_ForumModel::FLAG_SUBSECTIONS];

            $basicSection = $this->createSection($section, $forum);
        }

        if(!$basicSection) throw new HM_Exception(self::ERR_MSG_NOSECTION, self::ERR_CODE_NOSECTION);

        return $basicSection;
    }

    /**
     * Получить форум по модели курса (с темами, сообщениями)
     * Используется для Форума курса и занятий
     *
     * @param  HM_Subject_SubjectModel $subjectId курс
     * @param  int $sectionId id раздела
     * @param  HM_Lesson_LessonModel $lesson занятие
     * @return HM_Forum_Forum_ForumModel
     * @throws HM_Exception
     */
    public function getForumBySubject($subject, $sectionId = null, $lesson = null){
        $forum = $this->_getForumBySubject($subject);
        if(!$sectionId) $sectionId = null;

        // Занятие типа "Форум"
        if ($lesson && $lesson->typeID == HM_Event_EventModel::TYPE_FORUM) {

            // Получаем (создаем, если нет) основной раздел - темы м.б. только в разделе
            $basicSection = $this->_getBasicSectionByForum($forum);
            // Получаем (создаем, если нет) тему форума для данного занятия
            $section = $this->_getSectionByLesson($lesson, $forum, $basicSection);
            $section->subject_id = $forum->subject_id;
            // Получаем сообщения темы
            $section->messages = $this->_getMessagesBySectionId($section->section_id, $forum->subject_id);

            // Родительский (основной) раздел
            if($section->parent_id > 0) $section->parent = $basicSection;

            $forum->section = $section;
        }
        // Форум курса
        elseif($sectionId === null){

            // Получаем (создаем, если нет) основной раздел
            $basicSection = $this->_getBasicSectionByForum($forum);
            // Получаем темы Форума курса
            $sections = $this->_getSectionsBySectionId($basicSection->section_id);
            $sections = $this->_orderSort($sections, 'order', true, 'section_id');
            $forum->sections = $sections;
            // Добавляем признак, что это форум курса (а не занятия)
            $forum->forum_variant = 'subject_common';
        }
        // Запрошенный раздел (Наследие 4G - возможно, НЕ используется сейчас)
        else{
            // Раздел
            $section = $this->_getSection((int) $sectionId);
            $section->subject_id = $forum->subject_id;

            // Сообщения
            $section->messages = $this->_getMessagesBySectionId($section->section_id, $forum->subject_id);

            $forum->section = $section;
        }

        return $forum;
    }

    /**
     * @param  int $forumId
     * @return HM_Forum_Forum_ForumModel
     * @throws HM_Exception
     */
    protected function _getForum($forumId)
    {
        $forum = $this->getService('ForumForum')->getForum($forumId);

        if(!$forum){
            // Если не найден форум по умолчанию
            if($forumId == $this->config->forum_default_id){

                // Создание форума
                $forum = $this->config->forum_init->forum->toArray();

                try{ $forum = $this->createForum($forum); }
                catch(HM_Exception $e){ throw $e; }
                catch(Exception $e){ throw new HM_Exception(_(self::ERR_MSG_DEFAULTFORUM), self::ERR_CODE_DEFAULTFORUM); }

                // Создание разделов форума
                $sections = array();
                foreach($this->config->forum_init->sections->toArray() as $section){
                    $this->createSection($section, $forum);
                    $sections[$section->section_id] = $section;
                }
                $forum->sections = $sections;
            }

            else throw new HM_Exception(_(self::ERR_MSG_NOFORUM), self::ERR_CODE_NOFORUM);
        }

        // Текущая конфигурация форума
        $forum->config = clone $this->config;
        $forum->config->setReadOnly();

        return $forum;
    }

    /**
     * @param  HM_Subject_SubjectModel $subject
     * @return HM_Forum_Forum_ForumModel
     * @throws HM_Exception
     */
    protected function _getForumBySubject($subject){

        $class = get_class($subject);
        
        switch ($class) {
            case 'HM_Tc_Subject_SubjectModel':
            case 'HM_Subject_SubjectModel':
                $type = 'subject';
                $subjectId = $subject->subid;
                break;
            case 'HM_Project_ProjectModel':
                $type = 'project';
                $subjectId = $subject->projid;
                break;
            default: 
                throw new Exception($class.' is not instance of HM_Subject_SubjectModel or HM_Project_ProjectModel');
        }
        
        $forum = $this->getService('ForumForum')->getForumBySubjectId($subjectId, $type);

        // При отсутствии форума соотсветствующего курсу пытаемся его создать
        if(!$forum){
            $data = array(
                'title'      => substr(sprintf($this->config->subject_init->forum_name, $subject->name),0,254),
                'user_name'  => substr($subject->name,0,254),
                'subject_id' => $subjectId,
                'subject'    => $type,
                'flags'      => array('subsections' => true)
            );

            try{ 
                $forum = $this->createForum($data); 
            } catch(HM_Exception $e){ 
                $this->criticalError($e->getMessage()); 
        }
        }

        // Определение привелегии модератора
        $role = $this->getService('User')->getCurrentUserRole();
        $forum->moderator = in_array($role, $this->config->forums->moderators->toArray())
                            || $this->isCurrentUserActivityModerator();

        // Текущая конфигурация форума
        $forum->config = clone $this->config;
        $forum->config->setReadOnly();

        return $forum;
    }

    /**
     * @param  int $sectionId
     * @return HM_Forum_Section_SectionModel
     * @throws HM_Exception
     */
    protected function _getSection($sectionId)
    {
        $section = $this->getService('ForumSection')->getSection($sectionId);
        if(!$section) throw new HM_Exception(_(self::ERR_MSG_NOSECTION), self::ERR_CODE_NOSECTION);

        return $section;
    }

    /**
     * @param  int $forumId
     * @return HM_Forum_Section_SectionModel[]
     */
    protected function _getSectionsByForumId($forumId){
        return $this->getService('ForumSection')->getSectionsList($forumId, null);
    }

    /**
     * @param  int $sectionId
     * @return HM_Forum_Section_SectionModel[]
     */
    protected function _getSectionsBySectionId($sectionId){
        $sections = $this->getService('ForumSection')->getSectionsList(null, $sectionId);
        if ($sections) {
            foreach ($sections as $section) {
                $section->user_photo = $this->getService('User')->getPhoto((int) $section->user_id);
            }
        }
        return $sections;
    }

    public function getSectionByLessonId($lessonId)
    {
        return $this->getService('ForumSection')->fetchRow(['lesson_id = ?' => $lessonId]);
    }

    /**
     * Получить (создать) тему для занятия типа "Форум"
     *
     * @param  HM_Lesson_LessonModel $lesson
     * @param  HM_Forum_Forum_ForumModel $forum
     * @return HM_Forum_Section_SectionModel[]
     * @throws HM_Exception
     */
    protected function _getSectionByLesson(HM_Lesson_LessonModel $lesson, HM_Forum_Forum_ForumModel $forum, HM_Forum_Section_SectionModel $basicSection){
        $section = $this->getService('ForumSection')->getSectionByLessonId($lesson->SHEID,$forum->subject);

        // Если темы не существует, пытаемся её создать
        if(!$section){
            $theme = $this->config->lesson_init->section->toArray();
            $theme['title'] = sprintf($this->config->lesson_init->theme_name, $lesson->title);
            $theme['lesson_id'] = $lesson->SHEID;
            $theme['user_id'] = $this->_getTeacherIdByLesson($lesson);
            $section = $this->createSection($theme, $forum, $basicSection);
        }

        // установка признака "скрытая тема" в зависимости от настроек занятия
        $params = $lesson->getParams();
        if ( intval($params['is_hidden']) != $section->is_hidden ) {
            $data              = $section->getValues();
            $data['is_hidden'] = intval($params['is_hidden']);
            $this->getService('ForumSection')->updateSection($data['section_id'], $data);
        }

        if(!$section) throw new HM_Exception(self::ERR_MSG_NOSECTION, self::ERR_CODE_NOSECTION);

        return $section;
    }
    /**
     * @param  HM_Meeting_MeetingModel $meeting
     * @param  HM_Forum_Forum_ForumModel $forum
     * @return HM_Forum_Section_SectionModel[]
     * @throws HM_Exception
     */
    protected function _getSectionByMeeting(HM_Meeting_MeetingModel $meeting, HM_Forum_Forum_ForumModel $forum){
        $section = $this->getService('ForumSection')->getSectionByLessonId($meeting->meeting_id, $forum->subject);

        // Если занятие имеет тип "форум" и соответствующей ему темы не существует пытаемся её создать
        if(!$section){
            $theme = $this->config->lesson_init->section->toArray();
            $theme['title'] = sprintf($this->config->lesson_init->theme_name, $meeting->title);
            $theme['lesson_id'] = $meeting->meeting_id;
            $theme['subject'] = $forum->subject;

            $section = $this->createSection($theme, $forum);
        }

        // установка признака "скрытая тема"  взависимости от настроек занятия
        $params = $meeting->getParams();
        if ( intval($params['is_hidden']) != $section->is_hidden ) {
            $data              = $section->getValues();
            $data['is_hidden'] = intval($params['is_hidden']);
            $this->getService('ForumSection')->updateSection($data['section_id'], $data);
        }

        if(!$section) throw new HM_Exception(self::ERR_MSG_NOSECTION, self::ERR_CODE_NOSECTION);

        return $section;
    }
    
    /**
     * Получить список сообщений по ID раздела(темы)
     *
     * @param  int $sectionId
     * @param int $subjectId
     * @return HM_Forum_Message_MessageModel[]
     */
    protected function _getMessagesBySectionId($sectionId, $subjectId = null){
        // Конфигурация для текущего запроса
        $structure = clone $this->config->messages->structure;
        if($structure->preview) $structure->merge($this->config->messages->structure_preview);

        $user = $this->getService('User')->getCurrentUser();
        $newPeriod = $structure->new_max_period;
        $timeNow = time();

        $messages = $this->getService('ForumMessage')->getMessagesList(null, $sectionId, $structure, $user->MID);
        if(empty($messages)) return array();

        // Сообщения плоским списком
        $messagesList = $messages;
        if($structure->as_tree){
            foreach($messages as $message) $messagesList = $messagesList + $message->getAnswers(true);
        }

        // В случае принадлежности к занятиям
        if($subjectId !== null && !$structure->preview){
            $students = $this->getService('Student')->getUsersIds($subjectId);
            $ratings = $this->config->ratings->toArray();

            foreach($messagesList as $message){
                $message->subject_id = $subjectId;
                $message->createdByStudent = isset($students[$message->user_id]);
                $message->rating_raw = $message->rating;
                $message->rating = $ratings[$message->rating];
            }
        }

        // Определение значения сущности $new
        $lastLogin = $user->getLastLoginTimestamp();
        foreach($messagesList as $message){
            $timeCreated = strtotime($message->created);
            if(!$timeCreated) $timeCreated = 0;

            if($lastLogin - $timeCreated < $newPeriod) $message->new = true;
            else $message->new = false;
        }

        // Только не прочитанные сообщения
        if($structure->only_new){
            foreach($messagesList as $message) $message->showed = false;
            $showedNew = array_keys($messagesList);
        }
        // Все запрошенные сообщения, определение значения сущности $showed
        else{
            $showedRaw = $this->getService('ForumShowed')->getShowedList($user->MID, array_keys($messagesList));

            $showed = array();
            foreach($showedRaw->asArray() as $messageShowed){
                $timeCreated = strtotime($messageShowed['created']);
                if(!$timeCreated) $timeCreated = 0;
                if($timeNow - $timeCreated < $newPeriod) $showed[$messageShowed['message_id']] = true;
            }
            foreach($messagesList as $message) $message->showed = isset($showed[$message->message_id]);
            $showedNew = array_keys(array_diff_key($messagesList, $showed));
        }

        // Добавление аватарки авторов сообщений
        foreach($messagesList as $message) {
            $message->user_photo = $this->getService('User')->getPhoto((int) $message->user_id);
        }

        // Добавление просмотренных
        if(!$structure->preview && !empty($showedNew)) $this->getService('ForumShowed')->addShowed($user->MID, $showedNew);

        return $messages;
    }

    /**
     * Удалить форум (со всеми связанными с ним темами и сообщениями)
     *
     * @param HM_Forum_Forum_ForumModel $forum
     */
    public function deleteForum($forum){
        $this->getService('ForumForum')->deleteForum($forum->forum_id);
        $this->getService('ForumSection')->deleteSectionsByForumId($forum->forum_id);
        $this->getService('ForumMessage')->deleteMessagesByForumId($forum->forum_id);
    }

    /**
     * Создать раздел форума / подраздел раздела
     *
     * @param  $data array параметры
     * @param  HM_Forum_Forum_ForumModel
     * @param  HM_Forum_Section_SectionModel | null
     * @return HM_Forum_Section_SectionModel
     * @throws HM_Exception
     */
    public function createSection(array $data, HM_Forum_Forum_ForumModel $forum, HM_Forum_Section_SectionModel $section = null){
        // Нельзя создать раздел в разделе если структура форума не имеет подразделы
        if($section && !$forum->flags->subsections){
            throw new HM_Exception(_(self::ERR_MSG_FORUMNOSECTIONS), self::ERR_CODE_FORUMNOSECTIONS);
        }

        $data = array_replace_recursive($this->config->sections->new->toArray(), $data);
        $data['forum_id'] = (int) $forum->forum_id;
        $data['parent_id'] = (int) $section->section_id;
        $data['text'] = (string) $data['text'];
        $data = $this->_userData($data);

        $section = $this->getService('ForumSection')->createSection($data);
        if(!empty($forum->subject_id)) $section->subject_id = (int) $forum->subject_id;
        return $section;
    }

    /**
     * Изменить приоритет вывода темы
     *
     * @param HM_Forum_Section_SectionModel $section section
     * @param int $order order
     * @return HM_Forum_Section_SectionModel
     */
    public function setOrderOfSection(HM_Forum_Section_SectionModel $section, $order = 0){
        return $this->getService('ForumSection')->updateSection($section->section_id, array('order' => (int) $order));
    }

    /**
     * Закрыть/Открыть тему
     *
     * @param HM_Forum_Section_SectionModel $section
     * @param bool $flag
     * @return HM_Forum_Section_SectionModel
     */
    public function setClosedFlagsOfSection(HM_Forum_Section_SectionModel $section, $flag = true)
    {
        $section->flags->closed = (bool) $flag;
        $data = array('flags' => $section->flags->getEncoded());

        return $this->getService('ForumSection')->updateSection($section->section_id, $data);
    }

    /**
     * Удалить раздел со всеми его подразделами и сообщениями
     *
     * @param HM_Forum_Section_SectionModel $section
     */
    public function deleteSection($section)
    {
        $sections = $this->getService('ForumSection')->deleteSection($section->section_id);
        $this->getService('ForumMessage')->deleteMessagesBySectionId($sections);
    }

    /**
     * Добавить сообщение в тему определённого форума
     *
     * @param  array $data данные сообщения
     * @param  HM_Forum_Forum_ForumModel
     * @param  HM_Forum_Section_SectionModel
     * @return HM_Forum_Message_MessageModel
     */
    public function addMessage(array $data, HM_Forum_Forum_ForumModel $forum, HM_Forum_Section_SectionModel $section = null){
        $data = array_replace_recursive($this->config->messages->new->toArray(), $data);
        $data['forum_id'] = (int) $forum->forum_id;
        $data['section_id'] = (int) $section->section_id;
        $data['text'] = (string) $data['text'];
        $data['title'] = (string) $data['title'];
        $data['text_preview'] = $this->_prepareTextPreview($data['text']);
        $data = $this->_userData($data);
        $message = $this->getService('ForumMessage')->addMessage($data);
        if(!empty($forum->subject_id)) $message->subject_id = (int) $forum->subject_id;

        // +1 Количество сообщений в теме
        $this->getService('ForumSection')->incMessagesCounter($message->section_id);
//[ES!!!] //array('message' => $message))
        return $message;
    }

    /**
     * Удалить сообщение
     * Фактически сообщение остаётся в базе, с установленным флагом "deleted"
     *
     * @param HM_Forum_Message_MessageModel $message
     * @return HM_Forum_Message_MessageModel
     */
    public function deleteMessage(HM_Forum_Message_MessageModel $message){
        $message->flags->deleted = true;
        $message->deleted_by = $this->getService('User')->getCurrentUserId();
        $message->delete_date = $this->getDateTime();
        $data = array('flags' => $message->flags->getEncoded());
        $data['deleted_by']  = $message->deleted_by;
        $data['delete_date'] = $message->delete_date;
        return $this->getService('ForumMessage')->updateMessage($message->message_id, $data);
    }

    /**
     * Удаляет одно или несколько сообщений из БД по id
     * Изменяет время последнего сообщения в теме
     * Уменьшает кол-во сообщений в теме
     *
     * @param $messageId - int || array
     * @param $sectionId - int
     */
    public function deleteMessageFromDb($messageId, $sectionId)
    {
        if(is_array($messageId)) $messagesCount = count($messageId);
        else $messagesCount = 1;

        $this->getService('ForumMessage')->deleteMessage($messageId);

        // Найти время создания последнего из оставшихся сообщений темы
        $lastMsgCreated = $this->getService('ForumMessage')->getLastMessageTimeBySection($sectionId);

        $this->getService('ForumSection')->decMessagesCounter($sectionId, $messagesCount, $lastMsgCreated);
    }

    /**
     * Редактировать отдельное сообщение по id
     *
     * @param $message_id
     * @param array $data
     * @return HM_Forum_Message_MessageModel || null
     */
    public function editMessage($message_id, array $data)
    {
        $message_id = (int) $message_id;
        //$data['edited_by'] = $this->getService('User')->getCurrentUserId();
        //$data['edited'] = $this->getDateTime();
        $this->getService('ForumMessage')->updateMessage($message_id, $data);
        return $this->getMessage($message_id);
    }

    /**
     * Выставить оценку сообщения
     *
     * @param HM_Forum_Message_MessageModel $message
     * @param int $rating
     * @return HM_Forum_Message_MessageModel
     */
    public function setMessageRating(HM_Forum_Message_MessageModel $message, $rating = 0){
        $ratings = $this->config->ratings->toArray();
        if(!isset($ratings[$rating])) return;

        return $this->getService('ForumMessage')->updateMessage($message->message_id, array('rating' => $rating));
    }

    /**
     * Получить сообщение с определённым ID
     *
     * @param  int $messageId id сообщения
     * @param  HM_Subject_SubjectModel курс, если сообщение имеет к таковому отношение
     * @return HM_Forum_Message_MessageModel | null
     */
    public function getMessage($messageId, HM_Subject_SubjectModel $subject = null){
        $userId = $this->getService('User')->getCurrentUserId();
        $showedService = $this->getService('ForumShowed');

        $message = $this->getService('ForumMessage')->getMessage((int) $messageId);
        if(!$message) return null;

        $message->showed = $showedService->getShowed($userId, $message->message_id);

        if(!$message->showed){
            $showedService->addShowed($userId, $message->message_id);
            $message->showed = true;
        }

        if($subject){
            $message->subject_id = $subject->subid;
            $message->createdByStudent = (bool) $this->getService('Subject')->isStudent($subject->subid, $message->user_id);
        }

        return $message;
    }

    /**
     * Пометить сообщение как прочитанное
     *
     * @param  HM_Forum_Message_MessageModel $message
     * @return HM_Forum_Message_MessageModel
     */
    public function markMessageShowed(HM_Forum_Message_MessageModel $message){
        if(!empty($message->showed)) return $message;

        $userId = $this->getService('User')->getCurrentUserId();
        $this->getService('ForumShowed')->addShowed($userId, $message->message_id);

        $message->showed = true;
        return $message;
    }

    /**
     * Получить список сообщений раздела
     *
     * @param  int $sectionId id раздела
     * @return HM_Forum_Message_MessageModel[] | null
     */
    public function getMessagesList($sectionId){
        return $this->_getMessagesBySectionId((int) $sectionId);
    }

    /**
     * Подготавливает текст для предпросмотра
     *
     * @param  string $text исходный текст
     * @param  int $length максимальная длинна текста
     * @return string
     */
    protected function _prepareTextPreview($text, $length = 64){
        if (!$text) return '';
        return substr(strip_tags($text), 0, $length);
    }

    /**
     * Сортировка объектов по заданной сущности
     *
     * @param  array $data data
     * @param  string $orderProp property name
     * @param  bool $reverse reverse sort
     * @param  string $propAsKey property as key
     * @param  string $groupsByTimeProp sort by time property
     * @param  bool $gbtReverse sort by time reverse
     * @return array
     */
    protected function _orderSort($data, $orderProp, $reverse = null, $propAsKey = null, $groupsByTimeProp = null, $gbtReverse = null){
        $groups = array();
        foreach($data as $section){
            if(!isset($groups[$section->$orderProp])) $groups[$section->$orderProp] = array();
            $groups[$section->$orderProp][] = $section;
        }

        $reverse ? krsort($groups) : ksort($groups);

        $data = array();
        foreach($groups as $group){
            if($groupsByTimeProp) $group = $this->_timeSort($group, $groupsByTimeProp, $gbtReverse);
            foreach($group as $item){
                if($propAsKey) $data[$item->$propAsKey] = $item;
                else $data[] = $item;
            }
        }

        return $data;
    }

    /**
     * Сортировка объектов по заданной сущности рассматриваемой как строчный timestamp (2006-05-23 12:25:50)
     *
     * @param  array $data data
     * @param  string $timeProperty property name
     * @param  bool $reverse reverse sort
     * @return array data
     */
    protected function _timeSort(array $data, $timeProperty, $reverse = null){
        $sortedKeys = array();
        $sortedValues = array();
        foreach($data as $key => $item){
            $time = strtotime($item->$timeProperty);
            if(isset($sorted[$time])) ++$time;
            $sortedKeys[$time] = $key;
            $sortedValues[$time] = $item;
        }

        $reverse ? krsort($sortedValues) : ksort($sortedValues);

        $data = array();
        foreach($sortedValues as $key => $item) $data[] = $item;

        return $data;
    }

    /**
     * Дополняет не достающие данные:
     * id пользователя
     * ip-адрес пользователя
     * ФИО пользователя
     *
     * Проверяет существование пользователя, от имени которого производится действие
     *
     * @param  array $data
     * @return array
     * @throws HM_Exception
     */
    protected function _userData($data){
        // user & user id
        $userService = $this->getService('User');
        if(isset($data['user_id'])){
            $data['user_id'] = (int) $data['user_id'];
            $user = $userService->getOne($userService->find($data['user_id']));
            if(!$user) throw new HM_Exception(_(self::ERR_MSG_NOUSER), self::ERR_CODE_NOUSER);
        }
        else{
            $user = $userService->getCurrentUser();
            $data['user_id'] = $user->MID;
        }

        // user name
        if(!isset($data['user_name'])) $data['user_name'] = substr($user->getName(),0,254);

        // user ip address
        if(!isset($data['user_ip'])) $data['user_ip'] = Zend_Controller_Front::getInstance()->getRequest()->getClientIp();

        return $data;
    }

    public function getLessonModelClass(){
        return 'HM_Lesson_Forum_ForumModel';
    }

    public function getMeetingModelClass(){
        return 'HM_Meeting_Forum_ForumModel';
    }
    
    public function onCreateLessonForm(Zend_Form $form, $activitySubjectName, $activitySubjectId, $title = null){

        $lessonID = Zend_Controller_Front::getInstance()->getRequest()->getParam('lesson_id',0);
        $isHidden = false;
        if ($lessonID) {
            $lesson   = $this->getService('Lesson')->getOne($this->getService('Lesson')->find($lessonID));
            if ($lesson) {
                $params = $lesson->getParams();
                $isHidden = (bool) $params['is_hidden'];
            }
        }

        $form->addElement(
            'checkbox',
            'is_hidden',
            array(
                'Label' => _('Включить режим скрытых ответов в теме форума'),
                'description' => _('Сообщения участников в режиме скрытых ответов видит только автор темы. Сообщения же автора видят все участники.'),
                'value' => $isHidden
            )
        );
        return $form;
    }
    public function onCreateMeetingForm(Zend_Form $form, $activitySubjectName, $activitySubjectId, $title = null){

        $lessonID = Zend_Controller_Front::getInstance()->getRequest()->getParam('meeting_id',0);
        $isHidden = false;
        if ($lessonID) {
            $lesson   = $this->getService('Meeting')->getOne($this->getService('Meeting')->find($lessonID));
            if ($lesson) {
                $params = $lesson->getParams();
                $isHidden = (bool) $params['is_hidden'];
            }
        }

        $form->addElement(
            'checkbox',
            'is_hidden',
            array(
                'Label' => _('Включить режим скрытых ответов в теме форума'),
                'description' => _('Сообщения участников в режиме скрытых ответов видит только автор темы. Сообщения же автора видят все участники.'),
                'value' => $isHidden
            )
        );
        return $form;
    }
    public function onLessonUpdate($lesson, $form,$subjectType='subject'){
        // если необходимо создаем канал для подписки
        switch ($subjectType) {
            case 'project':
                $this->createMeetingSubscriptionChannel($lesson);
                break;
            default:
        $this->createLessonSubscriptionChannel($lesson);
    }
    }
    public function onMeetingUpdate($meeting, $form){
        // если необходимо создаем канал для подписки
        $this->createMeetingSubscriptionChannel($meeting);
    }

    public function onSetDefaultsLessonForm(Zend_Form $form, HM_Lesson_LessonModel $lesson) {

    }
    
    public function getRelatedUserList($id) {
        $db =  Zend_Db_Table_Abstract::getDefaultAdapter();
        $select = $db->select();
        $result = array();

        $messageSelect = clone $select;
        $messageSelect->from(array('fm' => 'forums_messages'), array())
            ->join(array('fl' => 'forums_list'), 'fl.forum_id = fm.forum_id',
                array(
                    'subid' => 'fl.subject_id',
                    'section_id' => 'fm.section_id'
                )
            )
            ->where('fm.message_id = ?', $id, 'INTERGER');
        $stmt = $messageSelect->query();
        $stmt->execute();
        $subjectRow = $stmt->fetchAll();
        $subjectId = $subjectRow[0]['subid'];
        $sectionId = $subjectRow[0]['section_id'];
        if ($subjectId === null || intval($subjectId) == 0) {
            $select->from(array('fm1' => 'forums_messages'), array())
                ->join(array('fm2' => 'forums_messages'), 'fm1.forum_id = fm2.forum_id AND fm1.section_id=fm2.section_id', array('MUID' => 'fm2.user_id'))
                ->join(array('fs' => 'forums_sections'), 'fm1.section_id=fs.section_id', array('SUID' => 'fs.user_id'))
                ->join(array('fs2' => 'forums_sections'), 'fs.parent_id = fs2.section_id', array('FUID' => 'fs2.user_id'))
                ->where('fm1.message_id = ?', $id, 'INTEGER')
                ->group(array('fm2.user_id', 'fs.user_id', 'fs2.user_id'));
            $stmt = $select->query();
            $stmt->execute();
            $rows = $stmt->fetchAll();
            foreach ($rows as $index => $item) {
                if ($index == 0) {
                    $result[] = intval($item['FUID']);
                    $result[] = intval($item['SUID']);
                }
                $result[] = intval($item['MUID']);
            }
            $result = array_unique($result);
        } else {
            $teachersSubselect = clone $select;
            $studentsSubselect = clone $select;
            $unionSelect = clone $select;
            $teachersSubselect->from(array('s' => 'subjects'), array())
                ->join(array('t' => 'Teachers'), 't.CID = s.subid AND s.subid='.intval($subjectId), array('UserId' => 't.MID'));
            $studentsSubselect->from(array('s' => 'subjects'), array())
                ->join(array('st' => 'Students'), 'st.CID = s.subid AND s.subid='.intval($subjectId), array('UserId' => 'st.MID'));
            if(!($sectionId === null && intval($sectionId) == 0)) {
                $studentsSubselect->join(array('l' => 'schedule'),
                    'st.CID = l.CID',
                    array())
                    ->join(array('la' => 'scheduleID'),
                        'la.MID = st.MID and l.SHEID = la.SHEID',
                        array())
                    ->join(array('fs' => 'forums_sections'),
                        'fs.lesson_id = la.SHEID AND fs.section_id = '.$sectionId,
                        array()
                    );

            }
            $mainSelect = $unionSelect->union(array($teachersSubselect, $studentsSubselect))
                ->group('UserId');
            $stmt  = $mainSelect->query();
            $stmt->execute();
            $rows = $stmt->fetchAll();
            foreach ($rows as $item) {
                $result[] = intval($item['UserId']);
            }
            array_unique($result);
        }
        return $result;
    }

    public function closeSectionByLessonId($lessonId)
    {
        $forumSection = $this->getSectionByLessonId($lessonId);
        if($forumSection) {
            $this->setClosedFlagsOfSection($forumSection, 1);
        }
    }

    /**
     * Создание дефолтной модели форума для занятия
     * (пока - заглушка, для унификации с др. типами занятий)
     *
     * @param $title
     * @param $subjectId
     * @return HM_Forum_ForumModel
     * @throws HM_Exception
     */
    public function createDefault($title)
    {
        if(!strlen($title)) {
            throw new HM_Exception(_('Ошибка при создании занятия'));
        }
        return new HM_Forum_ForumModel(['title' => $title]);
    }

    /**
     * Создание занятия типа "Форум"
     *
     * @param $subjectId
     * @param HM_Forum_ForumModel $forumModel
     * @return HM_Model_Abstract
     * @throws Zend_Exception
     */
    public function createLesson($subjectId, HM_Forum_ForumModel $forumModel)
    {
        $values = array(
            'title' => $forumModel->getName(),
            'begin' => date('Y-m-d 00:00:00'),
            'end' => date('Y-m-d 23:59:00'),
            'createID' => $this->getService('User')->getCurrentUserId(),
            'createDate' => date('Y-m-d H:i:s'),
            'typeID' => HM_Event_EventModel::TYPE_FORUM,
            'vedomost' => 1,
            'CID' => $subjectId,
            'startday' => 0,
            'stopday' => 0,
            'timetype' => 2,
            'isgroup' => 0,
            'teacher' => $this->getService('User')->getCurrentUserId(),
            'params' => '',
            'material_id' => 0,
            'all' => 1,
            'cond_sheid' => '',
            'cond_mark' => '',
            'cond_progress' => 0,
            'cond_avgbal' => 0,
            'cond_sumbal' => 0,
            'cond_operation' => 0,
            'isfree' => HM_Lesson_LessonModel::MODE_PLAN,
        );

        $lesson = $this->getService('Lesson')->insert($values);

        $students = $lesson->getService()->getAvailableStudents($subjectId);
        if (is_array($students) && count($students)) {
            $this->getService('Lesson')->assignStudents($lesson->SHEID, $students);
        }

        // Создаем канал на форуме для этого занятия - ?deprecated
        //$this->createLessonChannel($lesson);

        return $lesson;
    }
}
