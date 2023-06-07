<?php
/**
 * Абстрактный helper 
 * для рисования диаграмм с несколькими категориями
 * 
 * @param array $data: 
 * [data] => Array
 *     (
 *         [0] => Array
 *             (
 *                 [title] => Мыслит системно
 *                 [user] => 2.00
 *                 [profile] => 2
 *             )
 *         [1] => Array
 *             (
 *                 [title] => Умеет общаться
 *                 [user] => 3.00
 *                 [profile] => 4
 *             )
 *     )
 * 
 * @param array $graphs: 
 * [graphs] => Array
 *     (
 *         [user] => Array
 *             (
 *                 [legend] => Профиль успешности должности пользователя
 *                 [color]  => #ff0000
 *             )
 *         [profile] => Array
 *             (
 *                 [legend] => Профиль пользователя по итогам текущей оценочной сессии
 *                 [color] => #00ff00
 *             )
 *     )
 * 
 * @param array $options: width|height|title|id
 * @return string
 *
 */

class HM_View_Helper_ChartJS extends HM_View_Helper_Abstract {

    public function chartJS($data = [], $graphs = [], $options = [])
    {
        if (!is_array($data)) return;

        array_walk($data, ['HM_View_Helper_ChartJS', '_prepareTitles']);

        foreach ($data as $k => $datum) {
            foreach ($datum as $key => $value) {
                if (isset($graphs[$key])) {
                    $newKey = $graphs[$key]['legend'];
                    $datum[$newKey] = $value;
                    unset($datum[$key]);
                }
            }
            $data[$k] = $datum;
        }
        
        $this->view->options = $options = array_merge(self::_getDefaultOptions(), $options);
        $this->view->data = $options['type'] !== 'radar'
            ? $data
            : [
            'chart' => $data,
            'legends' => $graphs
        ];

        return $this->view->render('chart-js.tpl');
    }
    
    static protected function _prepareTitles($item)
    {
        $item["title"] = wordwrap($item["title"], 45, "\n");
    }    
    
    protected function _getDefaultOptions()
    {
        return array(
            'type' => 'bar',
            'width' => 500,
            'height' => 500,
        );
    }    
}
