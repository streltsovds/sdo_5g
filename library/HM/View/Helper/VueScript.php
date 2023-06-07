<?php
/**
 * Created by PhpStorm.
 * User: dvukhzhilov
 * Date: 01-Oct-18
 * Time: 2:23 PM
 */

class HM_View_Helper_VueScript extends Zend_View_Helper_Placeholder_Container_Standalone {

    /**
     * Map filename to real path from manifest.json
     *
     * @param string $file_name Filename
     * @param string $manifestDirectory Path to manifest.json
     * @return string fileNameWithContentHash
     * @throws Exception
     */
    public function mix($file_name, $manifestDirectory = "/frontend/app/")
    {
        $result = $this->getRealPath($file_name, $manifestDirectory);

        if (!$result) return;

        if (preg_match('/^.*\.(js)$/i', $file_name)) {
            $this->appendFile($result);
        }
        if (preg_match('/^.*\.(css)$/i', $file_name)) {
            $this->view->headLink()->appendStylesheet($result);
        }
    }

    public function getRealPath($file_name, $manifestDirectory = "/frontend/app/")
    {
        $public_path = PUBLIC_PATH;
        $manifestPath = $public_path . $manifestDirectory . 'manifest.json';

        if (! file_exists($manifestPath)) {
            throw new Exception('The manifest does not exist.');
        }

        $manifest = json_decode(file_get_contents($manifestPath), true);

        if (! isset($manifest[$file_name])) {
            //throw new Exception("Unable to locate Mix file: {$file_name}.");
            return;

        }
        return $manifest[$file_name];
    }
    public function VueScript()
    {
        return $this;
    }
    /**#@+
     * Script type contants
     * @const string
     */
    const FILE   = 'FILE';
    const SCRIPT = 'SCRIPT';
    /**#@-*/

      /**
     * Are arbitrary attributes allowed?
     * @var bool
     */
    protected $_arbitraryAttributes = false;

    /**#@+
     * Capture type and/or attributes (used for hinting during capture)
     * @var string
     */
    protected $_captureLock;
    protected $_captureScriptType  = null;
    protected $_captureScriptAttrs = null;
    protected $_captureType;
    /**#@-*/

    /**
     * Optional allowed attributes for script tag
     * @var array
     */
    protected $_optionalAttributes = array(
        'charset', 'defer', 'language', 'src'
    );

    /**
     * Required attributes for script tag
     * @var string
     */
    protected $_requiredAttributes = array('type');

    /**
     * Whether or not to format scripts using CDATA; used only if doctype
     * helper is not accessible
     * @var bool
     */
    public $useCdata = false;

    /**
     * Constructor
     *
     * Set separator to PHP_EOL.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->setSeparator(PHP_EOL);
    }

    /**
     * Start capture action
     *
     * @param  mixed $captureType
     * @param  string $typeOrAttrs
     * @return void
     */
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

    /**
     * End capture action and store
     *
     * @return void
     */
    public function captureEnd()
    {
        $content                   = ob_get_clean();
        $type                      = $this->_captureScriptType;
        $attrs                     = $this->_captureScriptAttrs;
        $this->_captureScriptType  = null;
        $this->_captureScriptAttrs = null;
        $this->_captureLock        = false;

        switch ($this->_captureType) {
            case Zend_View_Helper_Placeholder_Container_Abstract::SET:
            case Zend_View_Helper_Placeholder_Container_Abstract::PREPEND:
            case Zend_View_Helper_Placeholder_Container_Abstract::APPEND:
                $action = strtolower($this->_captureType) . 'Script';
                break;
            default:
                $action = 'appendScript';
                break;
        }
        $this->$action($content, $type, $attrs);
    }

    /**
     * Overload method access
     *
     * Allows the following method calls:
     * - appendFile($src, $type = 'text/javascript', $attrs = array())
     * - offsetSetFile($index, $src, $type = 'text/javascript', $attrs = array())
     * - prependFile($src, $type = 'text/javascript', $attrs = array())
     * - setFile($src, $type = 'text/javascript', $attrs = array())
     * - appendScript($script, $type = 'text/javascript', $attrs = array())
     * - offsetSetScript($index, $src, $type = 'text/javascript', $attrs = array())
     * - prependScript($script, $type = 'text/javascript', $attrs = array())
     * - setScript($script, $type = 'text/javascript', $attrs = array())
     *
     * @param  string $method
     * @param  array $args
     * @return Zend_View_Helper_HeadScript
     * @throws Zend_View_Exception if too few arguments or invalid method
     */
    public function __call($method, $args)
    {
        if (preg_match('/^(?P<action>set|(ap|pre)pend|offsetSet)(?P<mode>File|Script)$/', $method, $matches)) {
            if (1 > count($args)) {
                require_once 'Zend/View/Exception.php';
                $e = new Zend_View_Exception(sprintf('Method "%s" requires at least one argument', $method));
                $e->setView($this->view);
                throw $e;
            }

            $action  = $matches['action'];
            $mode    = strtolower($matches['mode']);
            $type    = 'text/javascript';
            $attrs   = array();

            if ('offsetSet' == $action) {
                $index = array_shift($args);
                if (1 > count($args)) {
                    require_once 'Zend/View/Exception.php';
                    $e = new Zend_View_Exception(sprintf('Method "%s" requires at least two arguments, an index and source', $method));
                    $e->setView($this->view);
                    throw $e;
                }
            }

            $content = $args[0];

            if (isset($args[1])) {
                $type = (string) $args[1];
            }
            if (isset($args[2])) {
                $attrs = (array) $args[2];
            }

            switch ($mode) {
                case 'script':
                    $item = $this->createData($type, $attrs, $content);
                    if ('offsetSet' == $action) {
                        $this->offsetSet($index, $item);
                    } else {
                        $this->$action($item);
                    }
                    break;
                case 'file':
                default:
                    if (!$this->_isDuplicate($content)) {
                        $attrs['src'] = $content;
                        $item = $this->createData($type, $attrs);
                        if ('offsetSet' == $action) {
                            $this->offsetSet($index, $item);
                        } else {
                            $this->$action($item);
                        }
                    }
                    break;
            }

            return $this;
        }

        return parent::__call($method, $args);
    }

    /**
     * Is the file specified a duplicate?
     *
     * @param  string $file
     * @return bool
     */
    protected function _isDuplicate($file)
    {
        foreach ($this->getContainer() as $item) {
            if (($item->source === null)
                && array_key_exists('src', $item->attributes)
                && ($file == $item->attributes['src']))
            {
                return true;
            }
        }
        return false;
    }

    /**
     * Is the script provided valid?
     *
     * @param  mixed $value
     * @param  string $method
     * @return bool
     */
    protected function _isValid($value)
    {
        if ((!$value instanceof stdClass)
            || !isset($value->type)
            || (!isset($value->source) && !isset($value->attributes)))
        {
            return false;
        }

        return true;
    }

    /**
     * Override append
     *
     * @param  string $value
     * @return void
     */
    public function append($value)
    {
        if (!$this->_isValid($value)) {
            require_once 'Zend/View/Exception.php';
            $e = new Zend_View_Exception('Invalid argument passed to append(); please use one of the helper methods, appendScript() or appendFile()');
            $e->setView($this->view);
            throw $e;
        }

        return $this->getContainer()->append($value);
    }

    /**
     * Override prepend
     *
     * @param  string $value
     * @return void
     */
    public function prepend($value)
    {
        if (!$this->_isValid($value)) {
            require_once 'Zend/View/Exception.php';
            $e = new Zend_View_Exception('Invalid argument passed to prepend(); please use one of the helper methods, prependScript() or prependFile()');
            $e->setView($this->view);
            throw $e;
        }

        return $this->getContainer()->prepend($value);
    }

    /**
     * Override set
     *
     * @param  string $value
     * @return void
     */
    public function set($value)
    {
        if (!$this->_isValid($value)) {
            require_once 'Zend/View/Exception.php';
            $e = new Zend_View_Exception('Invalid argument passed to set(); please use one of the helper methods, setScript() or setFile()');
            $e->setView($this->view);
            throw $e;
        }

        return $this->getContainer()->set($value);
    }

    /**
     * Override offsetSet
     *
     * @param  string|int $index
     * @param  mixed $value
     * @return void
     */
    public function offsetSet($index, $value)
    {
        if (!$this->_isValid($value)) {
            require_once 'Zend/View/Exception.php';
            $e = new Zend_View_Exception('Invalid argument passed to offsetSet(); please use one of the helper methods, offsetSetScript() or offsetSetFile()');
            $e->setView($this->view);
            throw $e;
        }

        $this->_isValid($value);
        return $this->getContainer()->offsetSet($index, $value);
    }

    /**
     * Set flag indicating if arbitrary attributes are allowed
     *
     * @param  bool $flag
     * @return Zend_View_Helper_HeadScript
     */
    public function setAllowArbitraryAttributes($flag)
    {
        $this->_arbitraryAttributes = (bool) $flag;
        return $this;
    }

    /**
     * Are arbitrary attributes allowed?
     *
     * @return bool
     */
    public function arbitraryAttributesAllowed()
    {
        return $this->_arbitraryAttributes;
    }

    /**
     * Create script HTML
     *
     * @param  string $type
     * @param  array $attributes
     * @param  string $content
     * @param  string|int $indent
     * @return string
     */
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
        $html  = '<script type="' . $type . '"' . $attrString . '>';
        if (!empty($item->source)) {
            $html .= PHP_EOL . $indent . '    ' . $escapeStart . PHP_EOL . $item->source . $indent . '    ' . $escapeEnd . PHP_EOL . $indent;
        }
        $html .= '</script>';

        if (isset($item->attributes['conditional'])
            && !empty($item->attributes['conditional'])
            && is_string($item->attributes['conditional']))
        {
            $html = $indent . '<!--[if ' . $item->attributes['conditional'] . ']> ' . $html . '<![endif]-->';
        } else {
            $html = $indent . $html;
        }

        return $html;
    }

    /**
     * Retrieve string representation
     *
     * @param  string|int $indent
     * @return string
     */
    public function toString($indent = null)
    {
        $indent = (null !== $indent)
            ? $this->getWhitespace($indent)
            : $this->getIndent();

        if ($this->view) {
            $useCdata = $this->view->doctype()->isXhtml() ? true : false;
        } else {
            $useCdata = $this->useCdata ? true : false;
        }
        $escapeStart = ($useCdata) ? '//<![CDATA[' : '//<!--';
        $escapeEnd   = ($useCdata) ? '//]]>'       : '//-->';

        $items = array();
        $this->getContainer()->ksort();
        foreach ($this as $item) {
            if (!$this->_isValid($item)) {
                continue;
            }

            $items[] = $this->itemToString($item, $indent, $escapeStart, $escapeEnd);
        }

        $return = implode($this->getSeparator(), $items);
        return $return;
    }

    /**
     * Create data item containing all necessary components of script
     *
     * @param  string $type
     * @param  array $attributes
     * @param  string $content
     * @return stdClass
     */
    public function createData($type, array $attributes, $content = null)
    {
        $data             = new stdClass();
        $data->type       = $type;
        $data->attributes = $attributes;
        $data->source     = $content;
        return $data;
    }
}