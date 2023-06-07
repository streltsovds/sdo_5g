<?php
class HM_View_Helper_Footnote extends Zend_View_Helper_Placeholder_Container_Standalone
{
    CONST SYMBOL = '*';

    protected $_regKey = 'HM_View_Helper_Footnote';
    protected $_index;

    public function footnote($text = null, $number = null)
    {
        $text = (string) $text;
        if (($text !== '') && !isset($this->_index[$number])) {
            $this->append('<li class="caption mb-1">' . self::marker($number) . ' <span>' . $text . '</span></li>');
            $this->_index[$number] = true;
        }
        return $this;
    }

    public function toString()
    {
        $items = array();
        foreach ($this as $item) {
            $items[] = $item;
        }
        return count($items) ? '<v-divider></v-divider><v-card-actions><ul style="list-style:none;padding:0;margin:0;">' . implode('', $items) . '</ul></v-card-actions>' : '';
    }

    static public function marker($number)
    {
        return '<sup>' . str_pad('', $number, self::SYMBOL) . '</sup>';
    }
}
