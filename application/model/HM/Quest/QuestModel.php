<?php
class HM_Quest_QuestModel extends HM_Model_Abstract implements HM_Material_Interface
{
    const TYPE_TEST = 'test';
    const TYPE_POLL = 'poll';
    const TYPE_PSYCHO = 'psycho';
    const TYPE_FORM = 'form';

    const STATUS_UNPUBLISHED = 0;
    const STATUS_RESTRICTED = 1;

    const SETTINGS_SCOPE_GLOBAL = 0;
    const SETTINGS_SCOPE_SUBJECT = 1;
    const SETTINGS_SCOPE_LESSON = 3;
    const SETTINGS_SCOPE_MEETING = 4; // проектный офис (конкурсы)
    const SETTINGS_SCOPE_SESSION = 2; // assessment

    const MODE_DISPLAY_BY_CLUSTERS = 0;
    const MODE_DISPLAY_LIMIT_CLUSTERS = 1;
    const MODE_DISPLAY_LIMIT_QUESTIONS = 2;

    const MODE_SELECTION_ALL = 0;
    const MODE_SELECTION_LIMIT = 1;
    const MODE_SELECTION_LIMIT_BY_CLUSTER = 2;
    const MODE_SELECTION_LIMIT_CLUSTER = 3;

    const PAGE_SEQUENTIAL_PASS = 0; // Нельзя пропускать страницы, нельзя возвращаться назад
    const PAGE_FREE_SWITCHING = 1;  // Свободное переключение между страницами


    const BUILTIN_TYPE_FINALIZE_ADAPTING = 1;
    const BUILTIN_TYPE_FINALIZE_RECRUIT  = 2;
    const BUILTIN_TYPE_FINALIZE_RESERVE  = 3;
    const BUILTIN_TYPE_FINALIZE_WELCOME  = 4;

    protected $_primaryName = 'quest_id';

    protected $_scopeType;
    protected $_scopeId;
    protected $_settings;

    static public function getBuiltinTypeFinalizeIds()
    {
        return array(
            self::BUILTIN_TYPE_FINALIZE_ADAPTING,
            self::BUILTIN_TYPE_FINALIZE_RECRUIT,
            self::BUILTIN_TYPE_FINALIZE_RESERVE,
            self::BUILTIN_TYPE_FINALIZE_WELCOME,
        );
    }


    const NEWCOMER_POLL_ID = 5;  // опрос обратной связи

    static public function getHardcodeEditIds() {
        return array();
    }

    static public function getHardcodeDeleteIds() {

        return array(
            self::NEWCOMER_POLL_ID,
            self::BUILTIN_TYPE_FINALIZE_ADAPTING,
            self::BUILTIN_TYPE_FINALIZE_RECRUIT,
            self::BUILTIN_TYPE_FINALIZE_RESERVE,
            self::BUILTIN_TYPE_FINALIZE_WELCOME,
        );
    }

    public function getServiceName()
    {
        return 'Quest';
    }

    public function init() {}

    public function _initSettings()
    {
        if ($this->_settings === null) {

            if (!$this->settings || !count($this->settings)) {
                // желательно всегда получать Quest с dependence Settings
                // но если нет - здесь получаем
                $this->settings = Zend_Registry::get('serviceContainer')->getService('QuestSettings')->fetchAll(array('quest_id = ?' => $this->quest_id));
            }

            if (count($this->settings) == 1) {
                // если контекст всего один
                $this->_settings = $this->settings->current();
            } elseif (isset($this->_scopeType) && isset($this->_scopeId)) {
                foreach ($this->settings as $settings) {
                    if (($settings->scope_type == $this->_scopeType) && ($settings->scope_id == $this->_scopeId)) {
                        $this->_settings = $settings;
                        return;
                    }
                }
            } else {
                $this->settings->rewind();
                $this->_settings = $this->settings->current(); // АХТУНГ! используем первые попавшиеся (скорее всего дефолтные) настройки
                //throw new Exception('Do not know what scope settings to use.');
            }
        }
    }

    /**
     * @return HM_Quest_Settings_SettingsModel
     */
    public function getSettings()
    {
        $this->_initSettings();
        return $this->_settings;
    }

    /**
     * Устанавливает и возвращает настройки для области видимости,
     * либо null, если таких настроек не найдено
     * @param $scopeType
     * @param $scopeId
     * @return HM_Quest_Settings_SettingsModel|null
     */
    public function getScopeSettings($scopeType, $scopeId = 0) {
        if (!$this->hasScopeSettings($scopeType, $scopeId)) {
            return null;
        }
        $this->setScope($scopeType, $scopeId);
        return $this->getSettings();
    }

    public function setScope($scopeType, $scopeId = 0)
    {
        $this->_scopeType = $scopeType;
        $this->_scopeId = $scopeId;

        // После смены области видимости, необходима новая инициализация
        $this->_settings = null;
        $this->_initSettings();

        return $this;
    }

    /**
     * Проверяет наличие настроек для области видимости
     * @param int $scopeType
     * @param int $scopeId
     * @return bool
     */
    public function hasScopeSettings($scopeType, $scopeId) {
        $this->_initSettings();
        foreach ($this->settings as $settings) {
            if (($settings->scope_type == $scopeType) && ($settings->scope_id == $scopeId)) {
                return true;
            }
        }
        return false;
    }

    static public function factory($data, $default = 'HM_Quest_Type_TestModel')
    {
        if (isset($data['type']))
        {
            switch($data['type']) {
                case self::TYPE_TEST:
                    $quest = parent::factory($data, 'HM_Quest_Type_TestModel');
                    break;
                case self::TYPE_POLL:
                    $quest = parent::factory($data, 'HM_Quest_Type_PollModel');
                    break;
                case self::TYPE_PSYCHO:
                    $quest = parent::factory($data, 'HM_Quest_Type_PsychoModel');
                    break;
                case self::TYPE_FORM:
                    $quest = parent::factory($data, 'HM_Quest_Type_FormModel');
                    break;
            }
            $quest->init();
            return $quest;
        }
        return parent::factory($data, $default);
    }

    static public function getTypes()
    {
        return array(
            self::TYPE_TEST => _('Тест'),
            self::TYPE_POLL => _('Опрос'),
            self::TYPE_PSYCHO => _('Психологический опрос'),
            self::TYPE_FORM => _('Произвольная оценочная форма'),
        );
    }

    public function getType()
    {
        $types = self::getTypes();
        return isset($types[$this->type]) ? $types[$this->type] : '';
    }

    static public function getStatuses()
    {
        return array(
            self::STATUS_RESTRICTED => _('Ограниченное использование'),
            self::STATUS_UNPUBLISHED => _('Не опубликован'),
        );
    }

    public function getName()
    {
        return $this->name;
    }

    static public function getDisplayModes()
    {
        return array(
            self::MODE_DISPLAY_BY_CLUSTERS => _('Разбить на страницы по блокам вопросов'),
            self::MODE_DISPLAY_LIMIT_QUESTIONS => _('Фиксировать количество вопросов на странице'),
            self::MODE_DISPLAY_LIMIT_CLUSTERS => _('Фиксировать количество страниц'),
        );
    }

    static public function getSelectionModes()
    {
        return array(
            self::MODE_SELECTION_ALL => _('Включить в тест все вопросы'),
            self::MODE_SELECTION_LIMIT => _('Выбрать случайным образом'),
            self::MODE_SELECTION_LIMIT_BY_CLUSTER => _('Выбрать случайным образом из каждого блока вопросов'),
            self::MODE_SELECTION_LIMIT_CLUSTER => _('Выбрать случайным образом фиксированное количество вопросов из каждого блока'),
        );
    }

    static public function getPageModes()
    {
        return array(
            self::PAGE_SEQUENTIAL_PASS => _('Нельзя пропускать страницы, нельзя возвращаться назад'),
            self::PAGE_FREE_SWITCHING => _('Свободное переключение между страницами'),
       );
    }

    public function getAvailableTypes()
    {
        return HM_Quest_Question_QuestionModel::getTypes();
    }

    static public function getBuiltInTypeIds()
    {
        return array(
            HM_Quest_QuestModel::BUILTIN_TYPE_FINALIZE_RECRUIT,
            HM_Quest_QuestModel::BUILTIN_TYPE_FINALIZE_ADAPTING,
            HM_Quest_QuestModel::BUILTIN_TYPE_FINALIZE_RESERVE,
            HM_Quest_QuestModel::BUILTIN_TYPE_FINALIZE_WELCOME,
        );
    }

    public function isTimeElapsed($start)
    {
        if ($this->limit_time) {
            $now = new HM_Date();
            $begin = new HM_Date($start);
            return ($now->sub($this->limit_time, Zend_Date::MINUTE) > $begin);
        }
        return false;
    }

    public function getTestStartMessage()
    {
        $nQuestions = Zend_Registry::get('serviceContainer')->getService('QuestQuestionQuest')->fetchAll(array('quest_id = ?' => $this->quest_id));

        $params = array(_('Название') => $this->name, _('Количество вопросов') => count($nQuestions), _('Количество попыток') => ($this->limit_attempts?$this->limit_attempts:_('не ограничено')), _('Ограничение по времени') => ($this->limit_time?$this->limit_time._(' мин.'):_('нет')));
        foreach($params as $i=>$p) {
            $returnString[] = "{$i}: {$p}";
        }
        $returnString[] = "";//отступ
        return implode('<br/><br/>', $returnString);//потом надо сделать культурно, с шаблоном, в т.ч. и для психитестов
    }


    /********** ниже набор методов для прозрачной работы с Settings ************/

    public function setData($data)
    {
        list($dataQuest, $dataSettings) = HM_Quest_Settings_SettingsModel::split($data);

        if (is_array($dataQuest)) {
            $this->_data = $dataQuest;
        }

        if (count($dataSettings)) {
            $this->_initSettings();
            $this->_settings->setData($dataSettings);
        }
    }

    public function getData()
    {
        $this->_initSettings();
        if (is_a($this->_settings, 'HM_Quest_Settings_SettingsModel')) {
            return array_merge($this->_data, $this->_settings->getData());
        }
        return $this->_data;
    }

    public function getClusterLimits()
    {
        return $this->_settings->getClusterLimits();
    }

    public function getValue($key)
    {
        if (isset($this->_data[$key])) {
            return $this->_data[$key];
        }
        $this->_initSettings();
        return $this->_settings->getValue();
    }

    public function setValue($key, $value)
    {
        if (!in_array($key, HM_Quest_Settings_SettingsModel::getSettingsAttributes())) {
            $this->_data[$key] = $value;
        } else {
            $this->_initSettings();
            return $this->_settings->setValue($key, $value);
        }
    }


    public function __get($key)
    {
        if (!in_array($key, HM_Quest_Settings_SettingsModel::getSettingsAttributes())) return parent::__get($key);

        $this->_initSettings();
        if (!$this->_settings) {
            return null;
        }
        return $this->_settings->$key;
    }

    public function getQuestionListLabels()
    {
        $labels = array(
            self::TYPE_TEST => array(
                0  => _('используемые в данном тесте'),
                1  => _('все, включая вопросы из других тестов'),
                2  => _('Используется в тестах'),
                3  => _('Использовать в данном тесте'),
                4  => _('Не использовать в данном тесте'),
                5  => _('Вы действительно хотите исключить данный вопрос из списка вопросов теста? При этом его можно будет использовать в других тестах.'),
                6  => _('Вы действительно хотите удалить вопросы? Применимо только к вопросам, созданным в данном тесте.'),
                7  => _('Вы действительно хотите удалить вопросы? При этом он будет удалён из всех тестов, в которых он используется.'),
                8  => _('Вопрос успешно включен в тест'),
                9  => _('Вопросы успешно включены в тест'),
                10 => _('Вопрос успешно исключен из теста'),
                11 => _('Вопросы успешно исключены из теста'),
                12 => _('Невозможно создать вопрос в тесте из базы знаний!'),
            ),
            self::TYPE_POLL => array(
                0  => _('вопросы опроса'),
                1  => _('вопросы, не включенные в опрос'),
                2  => _('Используется в опросах'),
                3  => _('Использовать в данном опросе'),
                4  => _('Не использовать в данном опросе'),
                5  => _('Вы действительно хотите исключить данный вопрос из списка вопросов опроса? При этом его можно будет использовать в других опросах.'),
                6  => _('Вы действительно хотите удалить вопросы? Применимо только к вопросам, созданным в данном опросе.'),
                7  => _('Вы действительно хотите удалить вопросы? При этом он будет удалён из всех опросов, в которых он используется.'),
                8  => _('Вопрос успешно включен в опрос'),
                9  => _('Вопросы успешно включены в опрос'),
                10 => _('Вопрос успешно исключен из опроса'),
                11 => _('Вопросы успешно исключены из опроса'),
                12 => _('Невозможно создать вопрос в опросе из базы знаний!'),
            ),
            self::TYPE_PSYCHO => array(
                0  => _('вопросы психологического опроса'),
                1  => _('вопросы, не включенные в психопросах'),
                2  => _('Используется в опросах'),
                3  => _('Использовать в данном опросе'),
                4  => _('Не использовать в данном опросе'),
                5  => _('Вы действительно хотите исключить данный вопрос из списка вопросов опроса? При этом его можно будет использовать в других психологических опросах.'),
                6  => _('Вы действительно хотите удалить вопросы? Применимо только к вопросам, созданным в данном опросе.'),
                7  => _('Вы действительно хотите удалить вопросы? При этом он будет удалён из всех опросов, в которых он используется.'),
                8  => _('Вопрос успешно включен в опрос'),
                9  => _('Вопросы успешно включены в опрос'),
                10 => _('Вопрос успешно исключен из опроса'),
                11 => _('Вопросы успешно исключены из опроса'),
                12 => _('Невозможно создать вопрос в опросе из базы знаний!'),
            ),
            self::TYPE_FORM => array(
                0  => _('вопросы оценочная формы'),
                1  => _('вопросы, не включенные в оценочную форму'),
                2  => _('Используется в формах'),
                3  => _('Использовать в данной форме'),
                4  => _('Не использовать в данной форме'),
                5  => _('Вы действительно хотите исключить данный вопрос из списка вопросов формы? При этом его можно будет использовать в других формах.'),
                6  => _('Вы действительно хотите удалить вопросы? Применимо только к вопросам, созданным в данной форме.'),
                7  => _('Вы действительно хотите удалить вопросы? При этом он будет удалён из всех форм, в которых он используется.'),
                8  => _('Вопрос успешно включен в форму'),
                9  => _('Вопросы успешно включены в форму'),
                10 => _('Вопрос успешно исключен из формы'),
                11 => _('Вопросы успешно исключены из формы'),
                12 => _('Невозможно создать вопрос в форме из базы знаний!'),
            ),
        );

        return $labels[$this->type];
    }

    /*
     * 5G
     * Implementing HM_Material_Interface
     */
    public function becomeLesson($subjectId)
    {
        return $this->getService()->createLesson($subjectId, $this->quest_id);
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getViewUrl()
    {
        if(!$this->quest_id) return false;
        return array(
            'module' => 'quest',
            'controller' => 'question',
            'action' => 'list',
            'quest_id' => $this->quest_id,
        );
    }

    public function getIconClass()
    {
        return '';
    }

    public function getCreateUpdateDate()
    {
        return '';
    }

    public function getUnifiedData()
    {
        $modelData = $this->getData();
        $unifiedData = [
            'id' => $modelData['quest_id'],
            'title' => $modelData['name'],
            'kbase_type' => $modelData['type'],
            'created' => '',
            'updated' => '',
            'tag' => $modelData['tag'],
            'classifiers' => $modelData['classifiers'],
            'subject_id' => $modelData['subject_id'],
        ];

        $view = Zend_Registry::get('view');
        $unifiedData['viewUrl'] = $view->url([
            'module' => 'quest',
            'controller' => 'question',
            'action' => 'list',
            'quest_id' => $modelData['quest_id'],
        ]);

        return array_merge($modelData, $unifiedData);
    }
}