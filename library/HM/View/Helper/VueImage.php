<?php
class HM_View_Helper_VueImage extends Zend_View_Helper_HtmlElement
{
    /**
     * @param $name
     * @param null $value
     * @param array $attribs
     * @param array $errors
     * @return string
     * @throws Exception
     */
    public function vueImage($name, $value = null, array $attribs = array(), array $errors = array())
    {
        foreach ($value['variants'] as $answerId => $answer) {

            $frontendAnswer = json_decode($answer['data'], false);

            $frontendAnswer->answer_id = $answerId;
            $frontendAnswer->is_correct = $answer['is_correct'];
            $frontendAnswer->type = 'rect';

            $answers[] = $frontendAnswer;
        }

        $areas_initial = HM_Json::encodeErrorThrow($answers);
        $img_initial = (!empty($value['file_id'])) ? "/file/get/file/file_id/" . $value['file_id'] : '';
        $showVariants = (string)$value['show_variants'];

        return <<<HTML
    <hm-multi-select-areas-image
        :areas-initial='$areas_initial'
        img-initial='$img_initial'
        show-variants='$showVariants'
     />
HTML;
    }
}