<?php
require_once 'Zend/View/Helper/InlineScript.php';

class HM_View_Helper_InlineScript extends Zend_View_Helper_InlineScript
{

    public function inlineScript($mode = Zend_View_Helper_HeadScript::FILE, $spec = null, $placement = 'APPEND', array $attrs = array(), $type = 'text/javascript')
    {
        // если у нас файл в заинлайненом скрипте, то мы присваимваем defer, чтобы браузер сначала разобрал дом дерево
        $attrs['defer'] = true;
        return $this->headScript($mode, $spec, $placement, $attrs, $type);
    }

    public function itemToString($item, $indent, $escapeStart, $escapeEnd)
    {
        $attrString = '';
        if (!empty($item->attributes)) {
            foreach ($item->attributes as $key => $value) {
                if (!$this->arbitraryAttributesAllowed()
                    && !in_array($key, $this->_optionalAttributes))
                {
                    continue;
                }
                if ('defer' == $key) {
                    $value = 'defer';
                }
                $attrString .= sprintf(' %s="%s"', $key, ($this->_autoEscape) ? $this->_escape($value) : $value);
            }
        }

        $type = ($this->_autoEscape) ? $this->_escape($item->type) : $item->type;

        /**
         * обязательно нужно оборачивать инлайновые скрипты в вызов по событию 'DOMContentLoaded' !!!! иначе правильная
         * работа функций не гарантируется, ибо основные скрипты, от которых может зависеть этот заинлайненый скрипт могут загружаться
         * до этого события.
         * */
        $html  = '<script data-type="inlineScript"' . $attrString . '>'.PHP_EOL.'window.addEventListener(\'DOMContentLoaded\', function() {';
        if (!empty($item->source)) {
            $html .= PHP_EOL . $indent . '    ' . $escapeStart . PHP_EOL . $item->source . $indent . '    ' . $escapeEnd . PHP_EOL . $indent;
        }
        // тут закрываем скобочки перед закрывающим тэгом
        $html .= '});'. PHP_EOL .'</script>';

        return $html;
    }

    public function captureStart($captureType = Zend_View_Helper_Placeholder_Container_Abstract::APPEND, $type = 'text/javascript', $attrs = array())
    {
        if ($this->_captureLock) {
            require_once 'Zend/View/Helper/Placeholder/Container/Exception.php';
            $e = new Zend_View_Helper_Placeholder_Container_Exception('Cannot nest headScript captures');
            $e->setView($this->view);
            throw $e;
        }

        $this->_captureLock        = true;
        $this->_captureType        = $captureType;
        $this->_captureScriptType  = $type;
        $this->_captureScriptAttrs = $attrs;
        ob_start();
    }

    public function captureEnd($offset = null)
    {
        if (is_string($offset) && strlen($offset) > 0)
        {
            $content                   = ob_get_clean();
            $type                      = $this->_captureScriptType;
            $attrs                     = $this->_captureScriptAttrs;
            $this->_captureScriptType  = null;
            $this->_captureScriptAttrs = null;
            $this->_captureLock        = false;

            $this->offsetSetScript($offset, $content, $type, $attrs);
        }
        else
        {
            parent::captureEnd();
        }
    }

}
