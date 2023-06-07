<?php
class HM_View_Helper_ImageMap extends Zend_View_Helper_FormElement
{
    protected $id;

    public function imageMap($name, $value = array(), $options = array())
    {
        $defaultOptions = array(
            'readOnly' => false,
            'value' => null,
            'imageFileId' => 0,
            'showVariants' => true
        );

        $options = array_merge($defaultOptions, $options);

        $view = $this->view;

        $id = $view->id('hm-image-map');

        $data = array();

        if (!empty($value)) {
            foreach ($value as $valueId => $val) {

                if (is_object($val)) {
                    $val = $val->getValues();
                }

                $answer = json_decode($val['data'], true);
                $answer['answerData'] = array(
                    'is_correct'  => $options['readOnly'] ? 0 : (int) $val['is_correct'],
                    'variant'     => $val['variant'],
                    'question_id' => $val['question_id'],
                    'id'          => $valueId
                );

                $data[] = $answer;
            }
        }

        $this->_addJs(array(
            'editMode' => !$options['readOnly'],
            'renderTo' => "#$id",
            'name' => $name,
            'value' => $options['value'],
            'imageFileId' => $options['imageFileId'],
            'showVariants' => $options['showVariants'],
            'answerName' => isset($options['answerName']) ? $options['answerName'] : 'answer',
            'data' => $data
        ));

        $view->headScript()
            ->appendFile($view->serverUrl('/js/lib/fileupload/jquery.iframe-transport.min.js'))
            ->appendFile($view->serverUrl('/js/lib/fileupload/jquery.fileupload.min.js'));

        return '<div id="'.$view->escape($id).'"></div>';

    }

    protected function _addJs($cfg)
    {
        ?>
        <script>
            $(function() {
                HM.create('hm.core.ui.form.helper.imageMap.ImageMapEditor', <?php echo json_encode($cfg) ?>);
            });
        </script>
        <?php
    }

}
