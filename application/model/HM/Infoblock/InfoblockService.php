<?php

class HM_Infoblock_InfoblockService  extends HM_Service_Abstract
{
    private $_infoblocks = array();
    private $_forcedInfoblocks = array();

    public function __construct()
    {
        parent::__construct();

        $xml = new DOMDocument('1.0');
        $xml->load(realpath(APPLICATION_PATH.'/settings/infoblocks.xml'));

        $config = $xml->getElementsByTagName('main')->item(0);

        $news = $this->getNews();

        $elem = $news->getElementsByTagName('infoblock')->item(0);

        $elem = $xml->importNode($elem, true);

        $config->appendChild($elem);

        $infoBlocks = new HM_Config_Xml($xml->saveXML(), null, true);

        $this->_infoblocks = $infoBlocks->main->toArray();

        // Обязательные инфоблоки
        $xml->load(realpath(APPLICATION_PATH . /*$localePath .*/'/settings/forced_infoblocks.xml'));
        $roles = $xml->getElementsByTagName('role');

        $this->_forcedInfoblocks = array();
        foreach ($roles as $role) {
            $roleName = $role->getElementsByTagName('name')->item(0)->firstChild->nodeValue;
            $this->_forcedInfoblocks[$roleName] = array();

            $blocks = $role->getElementsByTagName('block');
            foreach ($blocks as $block) {
                $blockName = $block->getElementsByTagName('name')->item(0)->firstChild->nodeValue;
                $blockTitle = $block->getElementsByTagName('title')->item(0)->firstChild->nodeValue;
                $blockLayout = $block->getElementsByTagName('layout')->item(0) ? $block->getElementsByTagName('layout')->item(0)->firstChild->nodeValue : false;
                $this->_forcedInfoblocks[$roleName][] = array(
                    'name' => $blockName,
                    'title' => $blockTitle,
                    'layout' => $blockLayout ? : 'default',
                    'attribs' => array()
                );
            }
        }
    }

    public function getForcedInfoblocks($role = 'enduser')
    {
        $result = array();
        if (isset($this->_forcedInfoblocks[$role])) {
            $result = $this->_filterForcedInfoblocks($this->_forcedInfoblocks[$role]);
        }

        return $result;
    }

    /**
     * Переопределил не просто так - это нужно
     *
     * @return sfServiceContainer
     * @throws Zend_Exception
     */
    public function getServiceContainer()
    {
        return Zend_Registry::get('serviceContainer');
    }

    public function isBlockExists($name, $role = 'student')
    {
        $blocks = $this->getTree($role);

        if (!(is_array($blocks) && count($blocks))) {
            return false;
        }

            foreach ($blocks as $block) {

            if (isset($block['name']) && ($block['name'] == $name)) {
                return true;
            }

                if (isset($block['block']) && is_array($block['block']) && count($block['block'])) {

                    if (isset($block['block']['name'])) {
                    if ($block['block']['name'] == $name) {
                        return true;
                    }
                    } else {
                        foreach ($block['block'] as $subBlock) {
                        if (isset($subBlock['name']) && ($subBlock['name'] == $name)) {
                            return true;
                        }
                    }
                }

            }
        }

        return false;

    }

    public function getBlock($name, $role = 'student')
    {
        $blocks = $this->getTree($role);

        if (is_array($blocks) && count($blocks)) {

            foreach ($blocks as $block) {

                if (isset($block['name']) && ($block['name'] == $name)) {
                    return $this->_translateBlockFields($block);
                }

                if (isset($block['block']) && is_array($block['block']) && count($block['block'])) {
                    if (isset($block['block']['name'])) {
                        if ($block['block']['name'] == $name) {
                            return $this->_translateBlockFields($block['block']);
                        }
                    } else {
                        foreach ($block['block'] as $subBlock) {
                            if (isset($subBlock['name']) && ($subBlock['name'] == $name)) {
                                return $this->_translateBlockFields($subBlock);
                        }
                    }
                }
            }
        }
        }

        return null;

    }

    /**
     * Переводим поля инфоблока и возвращаем его
     * @param unknown_type $block
     * @return Ambiguous
     */
    private function _translateBlockFields($block)
    {
        $block["title"] = ( isset($block["title"]) )? _($block["title"]):'';
        $block["description"] = ( isset($block["description"]) )? _($block["description"]):'';
        return $block;
    }

    public function getTree($role = 'student', $all = true, $user_id = 0, $charset = null)
    {
        if ($role) {
            $blocks =  $this->_filterRoles($role, $charset);
        }

        if ($all == true) {
            return $blocks;
        } else {
            return $this->_filterExists($blocks, $role, $user_id);
        }
    }

    public function insertBlocks($widgets, $role, $user_id = 0)
    {
        $adapter = $this->getSelect()->getAdapter();

        $adapter->delete('interface', array(
            'user_id = ?' => array($user_id),
            'role = ?'     => array($role)
        ));

        
        foreach ($widgets as $widget) {
            
            $block = $widget['block'];
            $parId = isset($widget['param_id']) ? $widget['param_id'] : null;
            //
            // Decode/parse 'news_id'
            list( $_blk, $_par) = (empty($widget['block'])) ? : explode('_', $widget['block']);
            if ( $_blk == 'news' && !empty($_par)) {
                $block = $_blk;
                $parId = $_par;
            }
            
            $this->insert(array(
                'role'     => $role,
                'user_id'  => $user_id,
                'block'    => $block,
                'x'        => isset($widget['x']) ? $widget['x'] : 0,
                'y'        => $widget['y'],
                'width'    => isset($widget['width']) ? $widget['width'] : null,
                'param_id' => $parId,
            ));

                }
            }

    public function getNews()
    {
        $arr = $this->getService('Info')->fetchAll($this->quoteInto($this->quoteIdentifier('show') . '= ?',1));

        $xml = new DomDocument('1.0', 'UTF-8');

        $infoblock = $xml->appendChild($xml->createElement('infoblock'));

        $charset = Zend_Registry::get('config')->charset;

        $infoblock->appendChild($xml->createElement('title', iconv($charset, 'UTF-8', _('Информационные блоки'))));
        $infoblock->appendChild($xml->createElement('name', 'newsBlock'));

        $roles = HM_Role_Abstract_RoleModel::getBasicRoles(true, true);
        $roles = array_keys($roles);

        foreach ($roles as $values) {
            $infoblock->appendChild($xml->createElement('role', $values));
        }

        foreach ($arr as $val) {

            $block = $infoblock->appendChild($xml->createElement('block'));
            $block->appendChild($xml->createElement('name', 'news_' . $val->nID));

            foreach ($roles as $values) {
                $block->appendChild($xml->createElement('role', $values));
            }

            $block->appendChild($xml->createElement('title', iconv($charset, 'UTF-8', _($val->Title))));
            $block->appendChild($xml->createElement('description', iconv($charset, 'UTF-8', _('Текст информационного блока'))));

        }

       return $xml;

    }

    protected function _filterForcedInfoblocks($forcedInfoblocks)
    {
        $event = new sfEvent(null, HM_Extension_ExtensionService::EVENT_FILTER_INFOBLOCKS);
        Zend_Registry::get('serviceContainer')->getService('EventDispatcher')->filter($event, $forcedInfoblocks);
        return $event->getReturnValue();
    }

    public function _filterRoles($role = 'admin', $charset = null)
    {
        $acl = $this->getService('Acl');
        $blocks = $this->_infoblocks['infoblock'];

        $event = new sfEvent(null, HM_Extension_ExtensionService::EVENT_FILTER_INFOBLOCKS);
        Zend_Registry::get('serviceContainer')->getService('EventDispatcher')->filter($event, $blocks);
        $blocks = $event->getReturnValue();

        if (!is_array($blocks) || isset($blocks['block'])) {
            $blocks = array($blocks);
        }

        foreach ($blocks as $key => $block) {

            if (is_array($block['role']) && is_numeric(key($block['role']))) {
                if (!$acl->inheritsRole($role, $block['role'])) {
                    unset($blocks[$key]);
                    continue;
                }
            } elseif (isset($block['role'])) {
                if (!$acl->inheritsRole($role, $block['role'])) {
                    unset($blocks[$key]);
                    continue;
                }
            }

            $oneBlock = array(
                'description' => '',
                'name'        => '',
                'role'        => '',
                'title'       => ''
            );

            if (isset($block['block']) && is_array($block['block']) && is_numeric(key($block['block']))) {
                foreach ($block['block'] as $oneKey => &$oneBlock) {

                    if (isset($oneBlock['role']) && is_array($oneBlock['role']) && is_numeric(key($oneBlock['role']))) {
                        if (!$acl->inheritsRole($role, $oneBlock['role'])) {
                            unset($blocks[$key]['block'][$oneKey]);
                        }
                    } elseif (isset($oneBlock['role'])) {
                        if (!$acl->inheritsRole($role, $oneBlock['role'])) {
                            unset($blocks[$key]['block'][$oneKey]);
                        }
                    }
                }
            } else {
                if (isset($block['block'])) {
                    $oneBlock = $block['block'];
                } else {
                    $oneBlock = $block;
                }

                if (isset($oneBlock['role']) && is_array($oneBlock['role']) && is_numeric(key($oneBlock['role']))) {
                    if (!$acl->inheritsRole($role, $oneBlock['role'])) {
                        unset($blocks[$key]['block']);
                    }
                } elseif (isset($oneBlock['role'])) {
                    if (!$acl->inheritsRole($role, $oneBlock['role'])) {
                        unset($blocks[$key]['block']);
                    }
                }
            }
        }

        foreach ($blocks as $key => $value) {

            if (null !== $charset) {
                if (isset($value['title'])) {
                    $blocks[$key]['title'] = iconv(Zend_Registry::get('config')->charset, $charset, _($value['title']));
                }

                if (isset($value['description'])) {
                    $blocks[$key]['description'] = iconv(Zend_Registry::get('config')->charset, $charset, _($value['description']));
                }
            }

            if (empty($value['block'])) {
                unset($blocks[$key]);
            } else {
                if (is_array($value['block']) && count($value['block'])) {
                    if (isset($value['block']['title'])) {
                        if (null !== $charset) {
                        $blocks[$key]['block']['title'] = iconv(Zend_Registry::get('config')->charset, $charset, _($value['block']['title']));

                        if (isset($value['block']['description'])) {
                            $blocks[$key]['block']['description'] = iconv(Zend_Registry::get('config')->charset, $charset, _($value['block']['description']));
                        }
                        }

                    } else {
                        foreach ($value['block'] as $index => $block) {
                            if (null !== $charset) {
                                if (isset($block['title'])) {
                                    $blocks[$key]['block'][$index]['title'] = iconv(Zend_Registry::get('config')->charset, $charset, _($block['title']));
                                }

                                if (isset($block['description'])) {
                                    $blocks[$key]['block'][$index]['description'] = iconv(Zend_Registry::get('config')->charset, $charset, _($block['description']));
                                }
                            }
                        }
                    }
                }
            }
        }

        return $blocks;
    }

    public function _filterExists($blocks, $role, $userId = 0)
    {

        if (empty($userId)) {
            $userInfoBlock = $this->fetchAll(array('role = ?' =>$role, 'user_id = ?' => 0));
        } else {
            $userInfoBlock = $this->fetchAll(array('role = ?' =>$role, 'user_id = ?' => $userId));

            if (count($userInfoBlock) < 1) {
                $userInfoBlock = $this->fetchAll(array('role = ?' =>$role, 'user_id = ?' => 0));
            }
        }

        $exists = array();

        if (is_array($blocks) && !is_numeric(key($blocks))) {
            $blocks = array($blocks);
        }

        foreach ($blocks as $key => $block) {

            if (!(is_array($block['block']) && is_numeric(key($block['block'])))) {
                $block['block'] = array($block['block']);
            }

            foreach ($block['block'] as $oneKey => &$oneBlock) {

                for($iKey = 0; $iKey < count($userInfoBlock); $iKey++) {

                    $iValue = $userInfoBlock[$iKey];
                    $oneBlockName = $oneBlock['name'];
                    $blockName = $iValue->block;

                    if (($oneBlockName == $blockName) || ($oneBlockName == $blockName.'_'.$iValue->param_id)) {
                        $exists[] = array(
                            'category' => (isset($blocks[$key]['name'])) ? $blocks[$key]['name'] : null,
                            'name'     => $oneBlock['name'],
                            'title'    => _($oneBlock['title']),
                            'showTitle' => isset($oneBlock['showTitle']) ? (bool) $oneBlock['showTitle'] : true,
                            'showBorder' => isset($oneBlock['showBorder']) ? (bool) $oneBlock['showBorder'] : true,
                            'content'  => _($oneBlock['description']),
                            'layout'   => isset($oneBlock['layout']) ? $oneBlock['layout'] : 'default',
                            'x'        => $iValue->x,
                            'y'        => $iValue->y,
                            'width'    => $iValue->width,
                            'skin'     => $iValue->skin,
                            'height'   => $iValue->height,
                        );

                        //unset($blocks[$key]['block'][$oneKey]);
                    }

                }
            }
        }

        $blocksRet = array();

        foreach ($blocks as $listMain) {

            $arr = array();

            if (!empty($listMain['block']) && !is_string(key($listMain['block']))) {

                foreach ($listMain['block'] as $val) {
                    $arr[] = array(
                        'title'       => _($val['title']),
                        'description' => _($val['description']),
                        'id'          => $val['name']
                    );
                }

            } elseif (!empty($listMain['block'])) {

                $arr[] = array(
                    'title'       => _($listMain['block']['title']),
                    'description' => _($listMain['block']['description']),
                    'id'          => $listMain['block']['name']
                                    );
                    }

           $title = $listMain['title'];

            $blocksRet = array_merge($blocksRet, array(
                array(
                   'title' => _($title),
                   'id'   => isset($listMain['name']) ? $listMain['name'] : null
                ),
                $arr
            ));

        }

        return array(
            'all'     => $blocksRet,
            'current' => $exists
        );
    }


    // DEPRECATED!!!
    public function returnBlocks($array, $type)
    {
        if (count($array['current']) < 1) {
            return array();
        }

        $columns = array();

        foreach ($array['current'] as $value) {

            // преобразуем старый вариант к новому виду
            if ($value['width'] == null) {
                $tempX = $value['x'];
                $value['x'] = $value['y'];
                $value['y'] = $tempX;
            }

            if ($type == 'edit') {

                $columns[$value['y']][$value['x']] = array(
                    'block'   => 'screenForm',
                                                               'title'   => _($value['title']),
                                                               'content' => $value['content'],
                    'x'       => $value['x'],
                    'y'       => $value['y'],
                    'width'   => $value['width'],
                    'attribs' => array(
                        'data-category' => $value['category'],
                                                                                  'id'            => $value['name']
                                                                  )
                   );

            } else {

                $explode = explode('_', $value['name']);

                if (count($explode) == 2) {

                    list($block, $param) = $explode;

                    $columns[$value['y']][$value['x']]= array(
                        'block'   => $block,
                                                              'title'   => _($value['title']),
                        'x'       => $value['x'],
                        'y'       => $value['y'],
                        'width'   => $value['width'],
                        'attribs' => array(
                            'param' => $param
                        )
                                                        );

                } else {

                    $columns[$value['y']][$value['x']]= array(
                        'block'   => $value['name'],
                                                              'title'   => _($value['title']),
                        'x'       => $value['x'],
                        'y'       => $value['y'],
                        'width'   => $value['width'],
                                                              'attribs' => array()
                                                        );
                }
            }
        }

        foreach ($columns as &$column) {
            ksort($column);
        }

        ksort($columns);

        return $columns;
    }


    public function clearUserData($role, $user_id)
    {
        $adapter = $this->getSelect()->getAdapter();
        $adapter->delete('interface', array(
            'user_id = ?' => array($user_id),
            'role =?'     => array($role)
        ));
    }


}
