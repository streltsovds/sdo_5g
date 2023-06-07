<?php
class HM_Faq_FaqService extends HM_Service_Abstract
{
    public function publish($faqId)
    {
        $return = $this->update(
            array(
                'faq_id' => $faqId,
                'published' => HM_Faq_FaqModel::STATUS_PUBLISHED
            )
        );

        $this->getService('Faq')->cleanUpCache(Zend_Cache::CLEANING_MODE_ALL, Zend_Cache::CLEANING_MODE_ALL);

        return $return;
    }

    public function unpublish($faqId)
    {
        $return = $this->update(
            array(
                'faq_id' => $faqId,
                'published' => HM_Faq_FaqModel::STATUS_UNPUBLISHED
            )
        );

        $this->getService('Faq')->cleanUpCache(Zend_Cache::CLEANING_MODE_ALL, Zend_Cache::CLEANING_MODE_ALL);

        return $return;
    }

}