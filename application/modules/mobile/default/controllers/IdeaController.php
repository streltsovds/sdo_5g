<?php

class IdeaController extends HM_Controller_Action_Mobile {

    public function jsonAction() {
        $ideaId = (int) $this->_request->getParam('idea_id', 0);
        $idea = $this->getService('Idea')->find($ideaId)->current();

        $idea->tags = $this->getService('Tag')->getTags($ideaId, HM_Tag_Ref_RefService::getIdeaType());
        $idea->classifier = $this->getService('Classifier')->getItemClassifiers($ideaId, HM_Classifier_Link_LinkModel::TYPE_IDEA)->getList('classifier_id', 'name');
        $idea->image = '/upload/idea/'.$ideaId.'.jpg';
        $idea->urls = $this->getService('IdeaUrl')->fetchAll(array('idea_id = ?' => $ideaId))->getList('idea_url_id', 'url');

        $select = $this->getService('IdeaLike')->getSelect();
        $result = $select->from(array('i' => 'idea_like'),
            array('summ' => new Zend_Db_Expr('SUM(value)'),
                  'cnt' => new Zend_Db_Expr('COUNT(*)'),
            )
        )->where('idea_id = ?', $ideaId)->query()->fetchAll();
        $idea->likes = $result[0];

        die(json_encode($idea));
    }

    public function jsonListAction() {
        $select = $this->getService('Idea')->getSelect();

        $select2 = $this->getService('IdeaLike')->getSelect();
        $select2->from(array('il' => 'idea_like'),
            array('summ' => new Zend_Db_Expr('SUM(il.value)'))
        )->where('il.idea_id = i.idea_id');

        $select3 = $this->getService('IdeaLike')->getSelect();
        $select3->from(array('il' => 'idea_like'),
            array('summ' => new Zend_Db_Expr('COUNT(il.value)'))
        )->where('il.idea_id = i.idea_id AND il.value>0');

        $select4 = $this->getService('IdeaLike')->getSelect();
        $select4->from(array('il' => 'idea_like'),
            array('summ' => new Zend_Db_Expr('COUNT(il.value)'))
        )->where('il.idea_id = i.idea_id AND il.value<0');

        $select->from(array('i' => 'idea'),
            array(
                'idea_id',
                'name',
                'description',
                'status',
                'date_created',
                'classifiers' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT cl2.name)'),
                'tags' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT t.body)'),
                'likes'=> new Zend_Db_Expr("({$select2})"),
                'likes_up'=> new Zend_Db_Expr("({$select3})"),
                'likes_down'=> new Zend_Db_Expr("({$select4})"),
            )
        )->joinLeft(
            array('cl' => 'classifiers_links'),
            'i.idea_id = cl.item_id AND cl.type = ' . HM_Classifier_Link_LinkModel::TYPE_IDEA,
            array()
        )->joinLeft(
            array('cl2' => 'classifiers'),
            'cl2.classifier_id = cl.classifier_id',
            array()
        )->joinLeft(
            array('tr' => 'tag_ref'),
            'i.idea_id = tr.item_id AND tr.item_type = ' . HM_Tag_Ref_RefModel::TYPE_IDEA,
            array()
        )->joinLeft(
            array('t' => 'tag'),
            'tr.tag_id = t.id',
            array()
/*        )->joinLeft(
            array('f' => 'files'),
            'f.item_id = i.idea_id AND f.item_type = ' . HM_Files_FilesModel::ITEM_TYPE_IDEA,
            array()
*/
        )->group(array('idea_id','i.name','description','status','date_created'));
//die($select);
        $ideas = $select->query()->fetchAll();

        $path = Zend_Registry::get('config')->path->upload->idea;
        $result = array();
        foreach($ideas as $idea) {
            $idea['classifiers'] = $idea['classifiers'] ? explode(',', $idea['classifiers']) : array();
            $idea['tags'] = $idea['tags'] ? explode(',', $idea['tags']) : array();
            $idea['image'] = '/upload/idea/'.$idea['idea_id'].'.jpg';
            $result[] = $idea;
        }

        die(json_encode($result));
    }


}
