<?php
$navigation = [
    'main' => require APPLICATION_PATH . '/settings/navigation/main.php',
    'context' => require APPLICATION_PATH . '/settings/navigation/context.php',
    'custom' => require APPLICATION_PATH . '/settings/navigation/custom.php',
];

if (!function_exists('_autocompleteConfig')) {
    function _autocompleteConfig(&$item)
    {
        // для контейнеров вроде "Ещё"
        // определяем права по первому подэлементу
        if (
            !isset($item['application']) &&
            !isset($item['module']) &&
            !isset($item['controller']) &&
            !isset($item['action']) &&
            !isset($item['uri']) &&
            isset($item['pages']) &&
            count($item['pages'])
        ) {
            $subItem = $item['pages'][0];
            if(isset($subItem['module'])) {
                foreach (['application', 'module', 'controller', 'action'] as $key) {
                    $item[$key] = (isset($subItem[$key]) && $subItem[$key]) ? $subItem[$key] : null;
                }
            } elseif(isset($subItem['uri'])) {
                $item['uri'] = $subItem['uri'];
            } else {
                throw new Zend_Navigation_Exception('Don\'t have mca or uri in the pages config');
            }
        }


        if (isset($item['application'])) {
            if (isset($item['module'])) {
                $item['module'] = $item['application'] . '/' . $item['module'];
            } else {
                $item['module'] = $item['application'] . '/';
            }
            // закомментировал, потому что это поле теперь используется для отключения пунктов меню в ремуверах
//            unset($item['application']);
        }

        if (isset($item['module'])) {

            // здесь писать дефолтные параметры страниц
            $item['type'] = 'HM_Navigation_Page_Mvc';

            if (!isset($item['params'])) $item['params'] = array();

            $item['params']['baseUrl'] = '';

            if (!isset($item['controller'])) {
                $item['controller'] = 'index';
            }

            if (!isset($item['action'])) {
                $item['action'] = 'index';
            }
        } elseif(isset($item['uri'])) {
            // здесь писать дефолтные параметры страниц
            $item['type'] = 'HM_Navigation_Page_Uri';
        }

        foreach ($item as $key => &$subItem) {
            if (is_array($subItem)) {
                _autocompleteConfig($subItem);
            }
        }
    }
}

try {
    _autocompleteConfig($navigation);
} catch (Zend_Navigation_Exception $exception) {}

return $navigation;
