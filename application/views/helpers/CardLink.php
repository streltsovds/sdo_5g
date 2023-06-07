<?php

class HM_View_Helper_CardLink extends HM_View_Helper_Abstract
{

    public function cardLink(
        $url = '',
        $title = null,
        $type = 'icon-svg',
        $className = 'pcard',
        $relName = 'pcard',
        $iconType = false,
        //, $img = 'card'
        $params = [] // additional params
    ) {
        if (null == $title) $title = _('Карточка');
//        $this->view->headScript()->appendFile($this->view->serverUrl('/js/lib/jquery/jquery-ui.lightdialog.js'));
        $this->view->url   = $url;
        $this->view->title = in_array($type, array('icon', 'icon-custom', 'icon-svg')) ? $this->view->escape($title) : "";
//        $this->view->type  = $type;
//        $this->view->iconType  = $iconType;
//        $this->view->cls   = is_array($className) ? $className : array( $className );
        $this->view->rel   = $relName;
//        $this->view->img = $img;
        $this->view->content = $this->getContent($type, $iconType, $title, $params);

        if (isset($params['textVueColor'])) {
            $this->view->textVueColor = $params['textVueColor'];
        }
        if (isset($params['class'])) {
            $this->view->class = $params['class'];
        }
        $this->view->float = isset($params['float']) ? $params['float'] : '';

        return $this->view->render('cardlink.tpl');
    }

    // TODO переписать, не использовать
    private function getContent($type, $iconType, $title, $params) {
        switch($type) {
            case 'icon': return $this->view->icon('card');
            case 'icon-svg':
                if (!isset($params['iconVueColor'])) {
                    $params['iconVueColor'] = 'colors.textLight';
                }

                $iconType = $iconType ? $iconType : 'list-items';

                return '<svg-icon 
                    name="' . $iconType . '" 
                    :color="' . $params['iconVueColor'] . '" 
                    title="' . $title . '"
                >
                </svg-icon>';
            case 'icon-custom' : return $this->view->icon($iconType, $title, '', '', '', 'span');
            case 'html': return $title;
            case 'icon-and-text': return "{$this->view->icon('card')}<span>{$this->view->escape($title)}</span>";
            case 'icon-and-html': return "{$this->view->icon('card')}<span>{$title}</span>";
            case 'candidate': return '<img src="/images/content-modules/grid/pcard-up.png">';
            default: return "<span>{$this->view->escape($title)}</span>";
        }
    }
}