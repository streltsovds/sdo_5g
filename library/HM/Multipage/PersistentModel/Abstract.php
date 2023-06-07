<?php
abstract class HM_Multipage_PersistentModel_Abstract extends HM_Model_Abstract
{
    protected $_model = array();
    protected $_results = array();
    protected $_comments = array();
    protected $_restoreResults = array();
    protected $_restoreComments = array();
    protected $_memoResults = array();

    protected $_items = array();
    protected $_currentItem = 0;

    protected $_redirectUrl = '/';

    public function getItems()
    {
        return $this->_items;
    }

    public function setItems($items)
    {
        $this->_items = $items;
        return $this;
    }

    public function getCurrentItem()
    {
        return $this->_currentItem;
    }

    public function setCurrentItem($itemId)
    {
        $this->_currentItem = $itemId;
        return $this;
    }

    public function getResults()
    {
        return $this->_results;
    }

    public function getComments()
    {
        return $this->_comments;
    }

    public function getMemoResults()
    {
        return $this->_memoResults;
    }

    public function setResults($itemId, $results)
    {
        if (isset($this->_results[$itemId])) {
            $this->_restoreResults[$itemId] = $this->_results[$itemId];
        }

        if ($questions = $this->_questions) {

            foreach ($results as $questionId => $result) {
                $question = $questions[$questionId];

                if ($question && $question->isEmptyResult($result)) {
                    unset($results[$questionId]);
                }
            }
        }

        $this->_results[$itemId] = $results;
        return $this;
    }

    public function setComments($itemId, $comments)
    {
        if (isset($this->_comments[$itemId])) {
            $this->_restoreComments[$itemId] = $this->_comments[$itemId];
        }
        $this->_comments[$itemId] = $comments;
        return $this;
    }

    public function restoreResults($itemId)
    {
        $this->_results[$itemId] = $this->_restoreResults[$itemId];
        return $this;
    }


    public function commentsResults($itemId)
    {
        $this->_comments[$itemId] = $this->_restoreComments[$itemId];
        return $this;
    }

    public function fillEmptyResults()
    {
        foreach ($this->_index as $itemId => $questionIds) {
            foreach ($questionIds as $questionId) {
                if (!isset($this->_results[$questionId])) {
                    $this->_results[$questionId] = 0;
                }
            }
        }
        return $this;
    }

    public function setMemoResults($memoResults)
    {
        $this->_memoResults = $memoResults;
        return $this;
    }

    public function getModel()
    {
        return $this->_model;
    }

    public function getRedirectUrl()
    {
        return $this->_redirectUrl;
    }

    public function setRedirectUrl($url)
    {
        $this->_redirectUrl = $url;
    }

}