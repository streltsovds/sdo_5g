<?php
class HM_View_Helper_PageRate extends HM_View_Helper_Abstract
{
    public function pageRate($type, $pollLinksIDs = null, $currentPollId = null)
    {
        if ( !is_array($pollLinksIDs) ) {
            $pollLinksIDs = (array) $pollLinksIDs;
        }

        $service = Zend_Registry::get('serviceContainer')->getService('Poll');
        if (!count($pollLinksIDs)) return '';

        $this->view->headLink()->appendStylesheet(
            $this->view->baseUrl('css/content-modules/page-rate.css')
        );

        // хардкод: одна атраница - один опрос
        // для привязки многих опросов, подгрузку статистики делать аяксом
        $currentLinkId  = array_shift($pollLinksIDs);
        $pollLinksIDs   = array($currentLinkId);

        if ( $currentLinkId && !$currentPollId ) {
            /**
             * @todo: если ИД опроса не пришел, то получить его по ИД линка
             */
        }

        $currentPoll = $service->getPollObject($currentPollId);

        $this->view->currentPollTitle = ($currentPoll)? $currentPoll->title : _('Оцените полезность этой страницы');
        $this->view->currentPollId    = $currentPollId;
        if ($type == 'RATED') {
            $this->view->respondentsCount = $service->getRespondentsCount($currentPollId);
            $this->view->pageRank         = $service->getNormalizePageRank($currentPollId, $currentLinkId);
            $this->view->pageRankPosition = $service->getPageRatePosition($currentLinkId);
        }
        $this->view->ids              = $pollLinksIDs;
        $this->view->type             = $type;

        return $this->view->render('pageRate.tpl');
    }
    
}