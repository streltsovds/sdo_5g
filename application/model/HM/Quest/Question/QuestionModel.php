<?php
class HM_Quest_Question_QuestionModel extends HM_Model_Abstract
{
    const TYPE_SINGLE = 'single';
    const TYPE_MULTIPLE = 'multiple';
    const TYPE_TEXT = 'text';
    const TYPE_FREE = 'free';
    const TYPE_FILE = 'file';
    const TYPE_IMAGEMAP = 'imagemap';
    const TYPE_MAPPING = 'mapping';
    const TYPE_CLASSIFICATION = 'classification';
    const TYPE_SORTING = 'sorting';
    const TYPE_PLACEHOLDER = 'placeholder';
    const TYPE_SUBJECTS = 'subjects';
    const TYPE_RESERVE_POSITIONS = 'reservepositions';
    const TYPE_LISTENING_WORD = 'listening_word';
    const TYPE_LISTENING_LETTER = 'listening_letter';
    
    const MODE_SCORING_OFF = 0;
    const MODE_SCORING_CORRECT = 1;
    const MODE_SCORING_WEIGHT = 2;
    
    const SHOW_FREE_VARIANT_OFF = 0;
    const SHOW_FREE_VARIANT_ON = 1;

    const VARIANTS_USE_WYSIWYG_OFF = 0;
    const VARIANTS_USE_WYSIWYG_ON = 1;

    const RESULT_CONTEXT_DETAILED = 'report_detailed_grid';
    const RESULT_CONTEXT_DIAGRAM = 'report_diagram';
    const RESULT_CONTEXT_ATTEMPT = 'report_attempt';

    const RESULT_DEFAULT_DELIMITER = ',<br>';
    
    protected $_primaryName = 'question_id';

    /**
     * @param $data
     * @param string $default
     * @return HM_Quest_Question_QuestionModel
     */
    static public function factory($data, $default = 'HM_Quest_Question_Type_SingleModel')
    {
        if (isset($data['type']))
        {
            switch($data['type']) {
                case self::TYPE_SINGLE:
                    $question = parent::factory($data, 'HM_Quest_Question_Type_SingleModel');
                    break;
                case self::TYPE_MULTIPLE:
                    $question = parent::factory($data, 'HM_Quest_Question_Type_MultipleModel');
                    break;
                case self::TYPE_TEXT:
                    $question = parent::factory($data, 'HM_Quest_Question_Type_TextModel');
                    break;
                case self::TYPE_FREE:
                    $question = parent::factory($data, 'HM_Quest_Question_Type_FreeModel');
                    break;
                case self::TYPE_FILE:
                    $question = parent::factory($data, 'HM_Quest_Question_Type_FileModel');
                    break;
                case self::TYPE_IMAGEMAP:
                    $question = parent::factory($data, 'HM_Quest_Question_Type_ImageMapModel');
                    break;
                case self::TYPE_MAPPING:
                    $question = parent::factory($data, 'HM_Quest_Question_Type_MappingModel');
                    break;
                case self::TYPE_CLASSIFICATION:
                    $question = parent::factory($data, 'HM_Quest_Question_Type_ClassificationModel');
                    break;
                case self::TYPE_SORTING:
                    $question = parent::factory($data, 'HM_Quest_Question_Type_SortingModel');
                    break;
                case self::TYPE_PLACEHOLDER:
                    $question = parent::factory($data, 'HM_Quest_Question_Type_PlaceholderModel');
                    break;
                case self::TYPE_SUBJECTS:
                    $question = parent::factory($data, 'HM_Quest_Question_Type_SubjectsModel');
                    break;
                case self::TYPE_RESERVE_POSITIONS:
                    $question = parent::factory($data, 'HM_Quest_Question_Type_ReservePositionsModel');
                    break;
            }
            //$question->init();
            return $question;
        }
        return parent::factory($data, $default);        
    }

    public function __construct($data)
    {
        $this->_unSerializeData($data);

        parent::__construct($data);

    }

    protected function _unSerializeData(&$data)
    {
        // в поле data хранятся сериализованные данные,
        // уникальные для каждого типа теста
        if (!empty($data['data'])) {

            $dataExtending = unserialize($data['data']);

            unset($data['data']);

            $data = array_merge($data, $dataExtending);

        }
    }

    /**
     * При необходимости, переопределить для уникального типа вопроса
     * Пример можно посмотреть в HM_Quest_Question_Type_ImageMapModel
     *
     * @param $data
     */
    protected function _serializeData(&$data)
    {

    }

    public function getValues($keys = null, $excludes = null)
    {
        $data = parent::getValues($keys, $excludes);

        if ($keys === null && $excludes === null) {
            $this->_serializeData($data);
        }

        return $data;

    }
    
    static public function getTypes($onlyUserTypes = true)
    {
        if (in_array(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER, HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL, HM_Role_Abstract_RoleModel::ROLE_HR, HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL))) {
            $return = array(
                self::TYPE_SINGLE => _('Одиночный выбор'),
                self::TYPE_MULTIPLE => _('Множественный выбор'),
                self::TYPE_FREE => _('Ввод текста'), // длинного текста, автоматически не оценивается
                self::TYPE_FILE => _('Загрузка файла'),
            );
        } else {
            $return = array(
                self::TYPE_SINGLE => _('Одиночный выбор'),
                self::TYPE_MULTIPLE => _('Множественный выбор'),
                self::TYPE_TEXT => _('Ввод значения'),
                self::TYPE_FREE => _('Ввод текста'), // длинного текста, автоматически не оценивается
                self::TYPE_FILE => _('Загрузка файла'),
                self::TYPE_IMAGEMAP => _('Выбор области на картинке'),
                self::TYPE_MAPPING => _('Соответствие'),
                self::TYPE_CLASSIFICATION => _('Классификация'),
                self::TYPE_SORTING => _('Упорядочивание'),
                self::TYPE_PLACEHOLDER => _('Заполнение пропусков'),
            );
        }

        if (!$onlyUserTypes) {
            $return[self::TYPE_SUBJECTS] = _('Рекомендуемые курсы');
            $return[self::TYPE_RESERVE_POSITIONS] = _('Должности кадрового резерва');
        }
        return $return;
    }      
    
    public function getScale()
    {
        return array('score_min'=>$this->score_min, 'score_max'=>$this->score_max);
    }

    public function getType()
    {
        $types = self::getTypes();
        return isset($types[$this->type]) ? $types[$this->type] : '';
    } 

    public static function getScoringModes()
    {
        return array(
            self::MODE_SCORING_CORRECT => _('C указанием правильного ответа'),        
            self::MODE_SCORING_WEIGHT => _('C указанием весов ответов'),        
        );
    }

    // при необходимости можно реализовать отдельно в каждом классе
    // @todo: отмечать правильные-неправильные 
    public function displayUserResult($questionResult, $delimiter = self::RESULT_DEFAULT_DELIMITER, $context = self::RESULT_CONTEXT_ATTEMPT)
    {
        if (count($this->variants)) {
            $variants = is_array($this->variants) ? $this->variants : $this->variants->asArrayOfObjects();
        }

        $variant = array();
        // в variant и free_variant сейчас хранится одно и то же
        if ($arr = unserialize($questionResult->variant)) {
            foreach ($arr as $key => $i) {
                if ((($key != -1) && ($i != -1)) || (($key == -1) && ($i != 'другое'))) $variant[] = isset($variants[$i]) ? $variants[$i]->variant : $i;
            }
        } elseif (isset($variants[$questionResult->variant])) {
            $variant[] = $variants[$questionResult->variant]->variant;
        }
        /* elseif (!empty($questionResult->free_variant)) {
            $variant[] = $questionResult->free_variant;
        } */

        return ($variant[0] != -1) ? implode($delimiter, $variant) : $variant[1];
    }

    public function emptyVariantsAllowed()
    {
        if ($this->type == self::TYPE_PLACEHOLDER) {
            return true;
        }
        return false;
    }

    public function isEmptyResult($result)
    {
        return false;
    }
    
    public function getAsTxt(){
        return '';
    }

    static public function getHardcodeEditIds()
    {
        return array(1,2,4,6); // см. db_dump2
    }

    static public function getHardcodeDeleteIds()
    {
        return array(1,2,4,6); // см. db_dump2
    }

    public static function getColors() {
        return array(
            '#C24E5F',
            '#CF7725',
            '#D4B922',
            '#949E08',
            '#C759D2',
            '#4B8C3E',
            '#003F7E'
        );
    }

    public static function getCorrectColors() {
        return array(
            '#60f060',
            '#50d050',
            '#40b040',
            '#309030',
            '#207020',
            '#105010',
        );
    }

    public static function getIncorrectColors() {
        return array(
            '#f06060',
            '#d05050',
            '#b04040',
            '#903030',
            '#702020',
            '#501010',
        );
    }

    public static function getUnknownColors() {
        return array(
            '#6060f0',
            '#5050d0',
            '#4040b0',
            '#303090',
            '#202070',
            '#101050',
        );
    }

    public function getSelfTestWeights($variantId) {
        if (is_array($this->variants) && count($this->variants)) {

            $weights  = array();
            $variants = $this->variants;
            foreach ($variants as $variant) {
                $weights[$variant->question_variant_id] = $variant->weight;
            }

            sort($weights);
            $weightCur = 0;
            $weightMin = $weights[0];
            $weightMax = $weights[count($weights) - 1];

            $weightCur = $variants[$variantId]->weight;

            switch ($weightCur) {
                case $weightMin:
                    return '#ff1a1a'; // red
                    break;
                case $weightMax:
                    return 'green';
                    break;
                default:
                    return '#f3dc80'; // yellow
                    break;
            }
        }
        return $this->score_min;
    }
}