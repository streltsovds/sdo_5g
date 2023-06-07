<?php
class HM_View_Helper_SvgIcon extends Zend_View_Helper_Abstract
{
    /** Текст с svg-иконкой */
    public function svgIcon($name, $title = '', $size='24px', $params = []) {
        if (!$title) {
            $title = $name;
        }

        if (is_array($size)) {
            $width = $size[0];
            $height = $size[1];
        } else {
            $width = $height = $size;
        }

        return
            '<svg-icon 
                name="' . $name . '"
                :title="_(\'' . $title . '\')"
                width="' . $width . '"
                height="' . $height . '"
             >
             </svg-icon>
             <span>{{ _("' . $title . '") }}</span>';
    }
}