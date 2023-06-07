<?php
class HM_View_Helper_Score extends HM_View_Helper_Abstract
{
    const MODE_PLAIN = 'plain'; // мелко и без рамочки, readonly 
    const MODE_DEFAULT = 'default'; // в рамочке, readonly 
    const MODE_FORSTUDENT = 'forstudent'; // в рамочке, writeable 
    const MODE_MARKSHEET = 'marksheet'; // в таблице

    const CONTEXT_TYPE_LESSON = 'lesson';
    const CONTEXT_TYPE_SUBJECT = 'subject';

    public function score($params = null)
    {
        if (is_null($params) || (is_array($params) && sizeof($params) == 0)) return;

        $this->view->score = ($params['score'] == intval($params['score'])) ? intval($params['score']) : round(floatval($params['score']), 2);
        $this->view->tabindex = isset($params['tabindex']) ? $params['tabindex'] : null;
        $this->view->userId = isset($params['user_id']) ? $params['user_id'] : null;
        $this->view->contextId = isset($params['context_id']) ? $params['context_id'] : null;
        $this->view->contextType = $params['context_type'] ? : self::CONTEXT_TYPE_LESSON;
        $this->view->placeholder = (!empty($params['placeholder'])) ? $params['placeholder'] : _("Нет");
        $this->view->disabled = isset($params['disabled']) ? $params['disabled'] : null;

        $this->view->key = implode('_', array($params['user_id'], $params['context_id'], $params['context_type']));

        if (empty($params['scale_id'])||$params['scale_id']==HM_Scale_ScaleModel::TYPE_TC_FEEDBACK) $params['scale_id'] = HM_Scale_ScaleModel::TYPE_CONTINUOUS;
        if (empty($params['mode'])) $params['mode'] = self::MODE_DEFAULT;

        return $this->view->render("score/{$params['scale_id']}/{$params['mode']}.tpl");
    }
}