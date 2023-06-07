<?php

class Classifier_AjaxController extends HM_Controller_Action
{
    public function listAction()
    {
        $type = (int) $this->_getParam('type', 0);
        $itemId = (int) $this->_getParam('item_id', 0);
        $itemType = (int) $this->_getParam('item_type', 0); //HM_Classifier_Link_LinkModel::TYPE_SUBJECT

        $links = $this->getLinks($itemType, $itemId); // classifier_links table

        $where = array(
            sprintf('node.type = %d', $type),
            sprintf('parent.type = %d', $type),
        );

        $q = urldecode($this->_getParam('q', ''));
        if (strlen($q)) {
            $q = '%'.iconv('UTF-8', Zend_Registry::get('config')->charset, $q).'%';
            $where[] = $this->getService('User')->quoteInto('LOWER(node.name) LIKE LOWER(?)', $q);
        }

        $where = implode(' AND ', $where);
        $collection = $this->getService('Classifier')->getTree($where);

        $list = array();
        if (count($collection)) {
            foreach($collection as $item) {
                $data = $item->getValues(null, array('depth', 'type', 'classifier_id_external'));
                $data['selected'] = (bool)($links && $links->exists('classifier_id', $item->classifier_id));
                $list[] = $data;
            }
        }

        echo HM_Json::encodeErrorSkip($list);
        exit;
    }

    /**
     * Если сюда обращается не только /resource/catalog (или его аналоги, работающие с классификаторами)
     * и логика или именование параметров не соответствует фукции - то лучше разделить их, а не городить
     */
    public function getTreeBranchAction()
    {
        $tree = $rawItems = [];

        $keyType = $this->_getParam('keyType', 'type');
        $key = (int)$this->_getParam('key', 0);

        if ($keyType == 'classifier') {
            $rawItems = $this->getService('Classifier')->getChildren($key);
        } elseif ($keyType == 'type') {
            $rawItems = $this->getService('Classifier')->getChildren(0, true, 'node.type = ' . (int)$key);
        }

        foreach ($rawItems as $raw){
            $tree[] = [
                'title' => $raw->name,
                // нужно? 'count' => 0,
                'key' => $raw->classifier_id,
                'keyType' => 'classifier',
                'isLazy' => true,
                'isFolder' => $raw->rgt > $raw->lft+1,
                'expand' => false
            ];
        }

        echo HM_Json::encodeErrorSkip($tree);
        exit;
    }

    public function getLinks($itemType, $itemId)
    {
        $links = false;
        if ($itemId > 0) {
            $links = $this->getService('ClassifierLink')->fetchAll(
                $this->getService('ClassifierLink')->quoteInto(
                    array('item_id = ?', ' AND type = ?'),
                    array($itemId, $itemType)
                )
            );
        }
        return $links;
    }
    
    public function getCuratorResponsibilities($userId)
    {
        $links = false;
        if ($userId > 0) {
            $links = $this->getService('CuratorResponsibility')->getResponsibilities($userId);
        }
        return $links;
    }
}
