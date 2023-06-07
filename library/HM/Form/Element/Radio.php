<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Form
 * @subpackage Element
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** Zend_Form_Element_Multi */
require_once 'Zend/Form/Element/Multi.php';

/**
 * Radio form element
 *
 * @category   Zend
 * @package    Zend_Form
 * @subpackage Element
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Radio.php 22329 2010-05-30 15:12:58Z bittarman $
 */
class HM_Form_Element_Radio extends Zend_Form_Element_Radio
{
    /**
     * Use formRadio view helper by default
     * @var string
     */
    public $helper = 'formRadio';

    public function render(Zend_View_Interface $view = null)
    {
        $label = $this->getLabel();
        $id = $this->getId();
        $val = $this->getValue();
        $name = $this->getName();


        //$label     = $this->getLabel();
        //$separator = $this->getSeparator();
        //$placement = $this->getPlacement();
        //$tag       = $this->getTag();
        //$id        = $this->getName();
        //$class     = $this->getClass();
        //$options   = $this->getOptions();
        //$r = $element->getType();
        //$w  = $element->getType();
        $opt = $this->getMultiOptions();
        $opt2 = $this->getAttribs();


        $value = $val ? 'value="'.$val.'"' : '';
        $checked = $val === '1' ? 'checked="checked"' : '';
        $required = $this->isRequired() ? 'required' : '';
        $label_text = $this->isRequired() ? $label.'<sup class="error--text">*</sup>' : $label;
        //$errors = $element->getDecorator('RedErrors');
        $helper_text = $this->getDescription() ? '<span class="helper-text">'.$this->getDescription() .'</span>': '';
        $return_markup = '';
        foreach ($opt as $key => $option) {
            $return_markup.=
                '<label>
                    <input name="'.$name.'" class="with-gap" id="'.$id.'-'.$key.'" type="radio" '.$required.'>
                    <span>'.$option.'</span>
                    '.$helper_text.'
                </label>';
        }

        return
            '<div class="v-input--selection-controls v-input--radio-group v-input--radio-group--column">
                <label class="text--secondary body-1">'.$label_text.'</label>               
                <div class="v-input--radio-group__input">
                    '.$return_markup.'
                </div>
            </div>';
    }

    /**
     * Load default decorators
     *
     * Disables "for" attribute of label if label decorator enabled.
     *
     * @return void
     */
    public function loadDefaultDecorators()
    {
        if ($this->loadDefaultDecoratorsIsDisabled()) {
            return $this;
        }
        parent::loadDefaultDecorators();
        $this->addDecorator('Label', array('tag' => 'dt',
            'disableFor' => true));
        return $this;
    }
}
