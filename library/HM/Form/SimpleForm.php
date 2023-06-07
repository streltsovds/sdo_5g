<?php
abstract class HM_Form_SimpleForm extends HM_Form
{
    protected $_cancelUrl = false;

    protected $_name = 'simple_form';

    protected $_lastElements = array();

    public function init()
    {
        $this->setMethod($this->_getMethod());

        $this->setName($this->_name);

        if ($this->_cancelUrl) {
            $this->simpleHiddenElement('cancelUrl', array(
                'Required' => false,
                'Value'    => $this->_cancelUrl
            ));
        }

        $this->_initElements();

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array(
            'Label' => _('Сохранить')
        ));

        parent::init();
    }

    abstract protected function _initElements();



















    // =======================================================================================
    //
    //                 Методы, которые, возможно, понадобится переопределить
    //
    // =======================================================================================

    protected function _getMethod()
    {
        return Zend_Form::METHOD_POST;
    }






















    // =======================================================================================
    //
    //                       Методы для упрощённой генерации формы
    //
    // =======================================================================================

    /**
     * @param string|Zend_Form_Element $element
     * @param null $name
     * @param null $options
     */
    public function addElement($element, $name = null, $options = null)
    {
        $isHidden = false;
        $elementName = $name;

        if (is_string($element)) {

            $isHidden = ($element === 'hidden');

        } elseif (is_object($element)) {

            $isHidden = ($element instanceof Zend_Form_Element_Hidden);
            $elementName = $element->getName();

        }

        if (!$isHidden) {
            $this->_lastElements[] = $elementName;
        }

        parent::addElement($element, $name, $options);

    }

    public function clearLastElementsList()
    {
        $this->_lastElements = array();
    }

    /**
     * Создаёт группу, добавляя в неё все созданные до этого элементы формы
     *
     * @param string $legend
     * @param string $name
     * @param null $options
     *
     * @return $this
     */
    public function simpleGroup($legend = '', $name = '', $options = null)
    {
        static $counter = 0;

        if ($name === '') {
            $name = 'group_'.$counter++;
        }

        if ($legend !== '') {

            if ($options === null) {
                $options = array();
            }

            $options['legend'] = $legend;
        }

        $this->addDisplayGroup($this->_lastElements, $name, $options);

        $this->clearLastElementsList();

        return $this;
    }

    /**
     * Создание элемента типа text с автодополнением
     *
     * @param $name
     * @param $options
     *
     * @return $this
     */
    public function simpleAutoCompleteElement($name, $options = array())
    {
        $default = array(
            'Label'         => '',
            'Description'   => _('Произвольные слова, предназначены для поиска и фильтрации, после ввода слова нажать «Enter»'),
            'Required'      => false,
            'Value'         => array(),
            'DataUrl'       => '/', // array|string
            'MaxItems'      => 0,
            'AllowNewItems' => false
        );

        $options = array_merge($default, $options);

        if (is_array($options['DataUrl'])) {
            $options['DataUrl'] = $this->getView()->url($options['DataUrl']);
        }

        $this->addElement($this->getDefaultTagsElementName(), $name, array(
            'Label'       => $options['Label'],
            'Description' => $options['Description'],
            'json_url'    => $options['DataUrl'],
            'value'       => $options['Value'],
            'Required'    => $options['Required'],
            'maxitems'    => $options['MaxItems'],
            'newel'       => $options['AllowNewItems'],
            'Filters'     => array(),
        ));

//        $this->addElement(new HM_Form_Element_FcbkComplete($name, array(
//            'Label'       => $options['Label'],
//            'Description' => $options['Description'],
//            'json_url'    => $options['DataUrl'],
//            'value'       => $options['Value'],
//            'Required'    => $options['Required'],
//            'maxitems'    => $options['MaxItems'],
//            'newel'       => $options['AllowNewItems'],
//            'Filters'     => array(),
//        )));

        return $this;

    }

    /**
     * Создание элемента типа hidden
     *
     * @param $name
     * @param $options
     *
     * @return $this
     */
    public function simpleHiddenElement($name, $options = array())
    {
        $default = array(
            'Required'  => true,
            'Value'     => '',
            'Type'      => 'string' // string|int
        );

        $options = array_merge($default, $options);

        $elementOptions = array(
            'Required' => $options['Required'],
            'Value'    => $options['Value'],
        );

        switch ($options['Type']) {
            case 'int':
                $elementOptions = array_merge($elementOptions, array(
                    'Validators' => array(
                        'Int'
                    ),
                    'Filters' => array(
                        'Int'
                    ),
                ));
                break;
        }

        $this->addElement('hidden', $name, $elementOptions);

        return $this;

    }

    /**
     * Создание элемента типа text
     *
     * @param $name
     * @param $options
     *
     * @return $this
     */
    public function simpleTextElement($name, $options = array())
    {
        $default = array(
            'Label'       => '',
            'Required'    => false,
            'MaxLength'   => 255,
            'Value'       => '',
            'Description' => '',
            'Wide'        => true,
        );

        $options = array_merge($default, $options);

        $classNames = array();

        if ($options['Wide']) {
            $classNames[] = 'wide';
        }

        $this->addElement($this->getDefaultTextElementName(), $name, array(
            'Label' => $options['Label'],
            'Required' => $options['Required'],
            'Value' => $options['Value'],
            'Validators' => array(
                array('StringLength', $options['MaxLength'], 1)
            ),
            'Filters' => array('StripTags'),
            'Description' => $options['Description'],
            'class' => implode(' ', $classNames),
        ));

        return $this;

    }

    /**
     * Создание элемента типа integer
     *
     * @param $name
     * @param $options
     *
     * @return $this
     */
    public function simpleIntElement($name, $options = array())
    {
        $default = array(
            'Label'       => '',
            'Required'    => false,
            'Value'       => '',
            'Description' => '',
            'Wide'        => true,
        );

        $options = array_merge($default, $options);

        $classNames = array();

        if ($options['Wide']) {
            $classNames[] = 'wide';
        }

        $this->addElement($this->getDefaultTextElementName(), $name, array(
            'Label' => $options['Label'],
            'Required' => $options['Required'],
            'Validators' => array(
                'Int'
            ),
            'Filters' => array(
                'Int'
            ),
            'Value' => $options['Value'],
            'Description' => $options['Description'],
            'class' => implode(' ', $classNames),
        ));

        return $this;
    }

    /**
     * Создание элемента типа textarea
     *
     * @param $name
     * @param $options
     *
     * @return $this
     */
    public function simpleTextAreaElement($name, $options = array())
    {
        $default = array(
            'Label'       => '',
            'Required'    => false,
            'MaxLength'   => 4096,
            'Value'       => '',
            'Description' => '',
            'Wide'        => true,
        );

        $options = array_merge($default, $options);

        $classNames = array();

        if ($options['Wide']) {
            $classNames[] = 'wide';
        }

        $this->addElement($this->getDefaultTextAreaElementName(), $name, array(
            'Label' => $options['Label'],
            'Required' => $options['Required'],
            'Value' => $options['Value'],
            'Description' => $options['Description'],
            'Validators' => array(
                array('StringLength', $options['MaxLength'], 1)
            ),
            'Filters' => array('StripTags'),
            'class' => implode(' ', $classNames),
        ));

        return $this;

    }

    /**
     * Создание элемента типа визивиг
     *
     * @param $name
     * @param array $options
     *
     * @return $this
     */
    public function simpleWysiwygElement($name, $options = array())
    {
        $default = array(
            'Label'       => '',
            'Required'    => false,
            'Value'       => '',
            'Description' => '',
            'Wide'        => true,
        );

        $options = array_merge($default, $options);

        $classNames = array();

        if ($options['Wide']) {
            $classNames[] = 'wide';
        }



        $this->addElement($this->getDefaultWysiwygElementName(), $name, array(
            'Label' => $options['Label'],
            'Description' => $options['Description'],
            'Required' => $options['Required'],
            'class' => implode(' ', $classNames),
            'Filters' => array('HtmlSanitize'),
            'Value' => $options['Value'],
        ));

        return $this;

    }

    /**
     * Создание элемента типа select
     *
     * @param $name
     * @param array $options
     *
     * @return $this
     */
    public function simpleSelectElement($name, $options = array())
    {
        $default = array(
            'Label'        => '',
            'Required'     => false,
            'Value'        => '',
            'Description'  => '',
            'Options' => array(
                1 => 'Title 1',
                2 => 'Title 2'
            ),
            'Type'         => 'int', // int|string
        );

        $options = array_merge($default, $options);

        $elementOptions = array(
            'Label' => $options['Label'],
            'Description' => $options['Description'],
            'Required' => $options['Required'],
            'multiOptions' => $options['Options'],
            'Value' => $options['Value'],
        );

        switch ($options['Type']) {
            case 'int':
                $elementOptions = array_merge($elementOptions, array(
                    'Validators' => array('Int'),
                    'Filters'    => array('Int'),
                ));
                break;
        }

        $this->addElement($this->getDefaultSelectElementName(), $name, $elementOptions);

        return $this;

    }

    /**
     * Создание элемента типа multi select
     *
     * @param $name
     * @param array $options
     *
     * @return $this
     */
    public function simpleMultiSelectElement($name, $options = array())
    {
        $default = array(
            'Label'        => '',
            'Description'  => '',
            'Required'     => false,
            'Options' => array(
                1 => 'Title 1',
                2 => 'Title 2'
            ),
            'Value'        => '',
            'Type'         => 'int', // int|string
        );

        $options = array_merge($default, $options);

        $elementOptions = array(
            'Label'        => $options['Label'],
            'Description'  => $options['Description'],
            'Required'     => $options['Required'],
            'multiOptions' => $options['Options'],
            'Value'        => $options['Value']
        );

        switch ($options['Type']) {
            case 'int':
                $elementOptions = array_merge($elementOptions, array(
                    'Filters' => array(
                        'Int'
                    ),
                ));
                break;
        }

        $this->addElement($this->getDefaultMultiSelectElementName(), $name, $elementOptions);

        return $this;

    }

    public function simpleFileElement($name, $options = array())
    {
        $default = array(
            'Label'       => _('Файлы'),
            'Required'    => false,
            'Value'       => array(),
            'Description' => '',
            'Destination' => Zend_Registry::get('config')->path->upload->tmp,
            'MaxSize'     => 0,
            'MaxCount'    => 10,
            'FileTypes'   => 'all', // all|images|docs|*.ext1;*.ext2
        );

        $options = array_merge($default, $options);

        $fileTypes = $options['FileTypes'];

        switch ($fileTypes) {
            case 'all':
                $fileTypes = '*.*';
                break;
            case 'images':
                $fileTypes = '*.jpg;*.png;*.gif;*.jpeg';
                break;
            case 'docs':
                $fileTypes = '*.doc;*.docx;*.pdf';
                break;
        }

        $this->addElement($this->getDefaultFileElementName(), $name, array(
            'Label'        => $options['Label'],
            'Description'  => $options['Description'],
            'Required'     => $options['Required'],
            'Validators' => array(
                array('Count', false, $options['MaxCount'])
            ),
            'Destination' => $options['Destination'],
            'file_size_limit' => $options['MaxSize'],
            'file_types' => $fileTypes,
            'file_upload_limit' => $options['MaxCount'],
        ));

        return $this;
    }
































    // =======================================================================================
    //
    //                                    Геттеры / сеттеры
    //
    // =======================================================================================

    /**
     * @param (string|array) $url
     */
    public function setCancelUrl($url)
    {
        if (is_array($url)) {
            $url = $this->getView()->url($url);
        }

        $this->_cancelUrl = $url;
    }

}