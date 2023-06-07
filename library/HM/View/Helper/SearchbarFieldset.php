<?php
/**
 * Created by PhpStorm.
 * User: k1p
 * Date: 11/29/18
 * Time: 6:27 PM
 */

class HM_View_Helper_SearchbarFieldset extends Zend_View_Helper_FormElement
{
    public function searchbarFieldset($name, $content, $attribs = null)
    {
        $info = $this->_getInfo($name, $content, $attribs);
        extract($info);

        // get legend
        $legend = '';
        if (isset($attribs['legend'])) {
            $legendString = trim($attribs['legend']);
            if (!empty($legendString)) {
                $legend = '<div class="headline v-card__title">'
                    . (($escape) ? $this->view->escape($legendString) : $legendString)
                    . '</div><hr class="v-divider theme--light"/>' . PHP_EOL;
            }
            unset($attribs['legend']);
        }

        // get id
        if (!empty($id)) {
            $id = ' id="' . $this->view->escape($id) . '"';
        } else {
            $id = '';
        }

        // render fieldset
        $xhtml = '<div class="flex"><div class="v-card v-card--tile theme--light" '
            . $id
            . $this->_htmlAttribs($attribs)
            . '>'
            . $legend
            .'<div class="v-card__text">'
            . $content
            .'</div>'
            . '</div></div>';

        return $xhtml;
    }
}