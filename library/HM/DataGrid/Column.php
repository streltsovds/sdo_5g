<?php

/**
 *
 */
class HM_DataGrid_Column
{
    protected $name;
    protected $expression;
    protected $title;
    protected $handler;
    protected $decorator;
    protected $callback;
    protected $position;
    protected $color;
    protected $filter;
    protected $hidden;

    static protected $usedColors = [];

    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
     * @param mixed $expression
     */
    public function setExpression($expression)
    {
        $this->expression = $expression;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * @param mixed $handler
     */
    public function setHandler($handler)
    {
        $this->handler = $handler;
    }

    /**
     * @return mixed
     */
    public function getDecorator()
    {
        return $this->decorator;
    }

    /**
     * @param mixed $decorator
     */
    public function setDecorator($decorator)
    {
        $this->decorator = $decorator;
    }

    /**
     * @return mixed
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * @param mixed $callback
     */
    public function setCallback($callback)
    {
        $this->callback = $callback;
    }

    /**
     * @return mixed
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param mixed $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return mixed
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @param mixed $color
     */
    public function setColor($color): void
    {
        $this->color = $color;
    }

    /**
     * @return mixed
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * @param mixed $filter
     */
    public function setFilter($filter)
    {
        $this->filter = $filter;
    }

    /**
     * @return mixed
     */
    public function getHidden()
    {
        return $this->hidden;
    }

    /**
     * @param mixed $hidden
     */
    public function setHidden($hidden)
    {
        $this->hidden = $hidden;
    }

    static public function colorize($field)
    {
        $colors = self::getColorMap();

        if (isset($colors[$field])) {
            self::$usedColors[$field] = $colors[array_rand($colors)];
            return $colors[$field];
        } else {
            return self::randomColor($field);
        }
    }

    static function getColorMap()
    {
        return [
            'tags' => '#DAD3FD',
            'roles' => '#D4E3FB',
            'classes' => '#FAF3D8',
            'classifiers' => '#FAF3D8',
            // Спорный цвет
            'subjects' => '#DAC5E2',
            'sessions' => '#DAC5E2',
            // учебные группы (StudyGroups)
            'groups' => '#D4E3FB',
            // учебные программы
            // немного странный цвет в сочетании с синим текстом
            'programms' => '#FDE1D9',
            /** @see Info_ListController::indexAction */
            'used' => '#EDF4FC',
            /**
             * @see Quest_QuestionController::listAction
             * Тут надо подобрать цвет, который будет хорошо выглядень на зеленоватом фоне используемых вопросов (css: .highlighted-success)
             */
            'quests' => '#FFE9B9',
            '#FDE1D9',
            // Очень некрасивые цвета
            '#CC83E9',
            '#05C985'
        ];
    }

    static private function randomColor($field)
    {
        $colors = self::getColorMap();

        $randomColor = $colors[array_rand($colors)];
        if (!in_array($randomColor, self::$usedColors) && (count($colors) > count(self::$usedColors))) return $randomColor;

        array_shift(self::$usedColors);
        self::$usedColors[$field] = $colors[array_rand($colors)];
        return self::randomColor($field);
    }
}