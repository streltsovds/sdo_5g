<?php
/**
 * Абстрактный helper 
 * для рисования  диаграмм
 *
 */

class HM_View_Helper_ChartVue extends HM_View_Helper_Abstract
{

    /**
     * @param $id
     * @param null $url
     * @param array $formFields
     * данные, которые необходимо добавить в post запрос
     * формат данных массив объектов со свойствами key и value
     * пример: [[ key => "key", value => "type" ], [ key => "value", value => v ]];
     *
     * @param array $data
     * данные для построения графика
     * формат: массив объектов со свойствами profile(значение) и title(подпись) по умолчанию.
     * если названия свойств отличаются от значений по умолчанию,
     * то их можно указать в dataValue и dataLabel соответственно
     * data перед построением графика форматируется функцией defaultFormatter или formatter
     *
     * @param string $type
     * типы: bar, pie, line, area
     * @param array $options
     * опции для построения графиков.
     * Опции по умолчанию: /hm-chart/charts/mixins/Options.js getDefaultOptions
     *
     * @param string $dataValue
     * значение указывается, если в данных для постороения название свойства содержащего
     * значение отличается от profile
     *
     * @param string $dataLabel
     * значение указывается, если в данных для постороения название свойства содержащего
     * подпись отличается от title
     *
     * @return string|void
     *
     */

    public function chartVue($id, $url = null, $formFields = array(), $data = array(), $type, $options = array(), $dataValue = null, $dataLabel = null)
    {
        if (!is_array($data)) return;

        $id = ZendX_JQuery::encodeJson($id);
        $url = ZendX_JQuery::encodeJson($url);
        $formFields = count($formFields) > 0 ? ZendX_JQuery::encodeJson($formFields) : '[]';
        $data =  count($data) > 0 ? ZendX_JQuery::encodeJson($data) : '[]';
        $type = ZendX_JQuery::encodeJson($type);
        $options = ZendX_JQuery::encodeJson($options);
        $dataValue = ZendX_JQuery::encodeJson($dataValue);
        $dataLabel = ZendX_JQuery::encodeJson($dataLabel);


        return <<<HTML
<hm-chart
    :id='$id'
    :url='$url'
    :form-fields='$formFields'
    :data='$data'
    :type='$type'
    :options='$options'
    :data-value='$dataValue'
    :data-label='$dataLabel'
>
</hm-chart>
HTML;
    }
}

