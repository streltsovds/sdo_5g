<?php
class HM_View_Helper_Question extends HM_View_Helper_Abstract
{

    protected $_templatesPath = '';
    public function question($question, $result, $params)
    {
//         $this->view->headLink()->appendStylesheet($this->view->serverUrl('/css/content-modules/question.css'));
//         $this->view->headScript()->appendFile($this->view->serverUrl('/js/application/marksheet/index/index/question.js'));

        $this->view->result = $result;
        $this->view->question = $question;
        $this->view->params = $params;
        if (isset($params['comment'])) {
            $this->view->comment = $params['comment'];
        } else {
            $this->view->comment = '';
        }

        $questionQuest = $question->questionQuest->current();
        $quest = $scale = Zend_Registry::get('serviceContainer')->getService('Quest')->getOne(Zend_Registry::get('serviceContainer')->getService('Quest')->find($questionQuest->quest_id));

        $this->view->displaycomment = $quest->displaycomment;
        if ($quest->scale_id) {
            $this->view->displaymode = HM_Quest_Type_PollModel::DISPLAYMODE_HORIZONTAL;
        } else {
            $this->view->displaymode = HM_Quest_Type_PollModel::DISPLAYMODE_VERTICAL;
        }

        $this->view->free_variant = false;
        $variantIds = array();
        if (count($question->variants)) {
            foreach ($question->variants as $k => $variant) {
                $variantIds[$variant->question_variant_id] = $variant->question_variant_id;
            }

            if ($question->shuffle_variants)
            {
                $keys = (array_keys($question->variants));
                shuffle($keys);

                $temp_variants = array();

                foreach ($keys as $key)
                {
                    $temp_variants[$key] = $question->variants[$key];
                }
                $question->variants = $temp_variants;

            }
        }

        if (is_array($result)) {
            if (count($diff = array_diff($result, $variantIds))) {
                $this->view->free_variant = array_pop($diff);
            }
        } else {
            if (!in_array($result, $variantIds)) {
                $this->view->free_variant = $result;
            }
        }
        
        if (array_key_exists($question->type, HM_Quest_Question_QuestionModel::getTypes(false))) {
            $templatePath = "question/{$params['template_path']}{$question->type}.tpl";
            return $this->view->render($templatePath);
        } 
        return '';
    }
}