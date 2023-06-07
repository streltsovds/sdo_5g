<?php

class HM_News_NewsService extends HM_Activity_ActivityService implements HM_Service_Schedulable_Interface
{    
	//const FILTER_TYPE_TAG = 'tag';
    const FILTER_TYPE_AUTHOR = 'author';
    //const FILTER_TYPE_DATE = 'date';//date format is yyyy-mm

    const EXIST_NEXT = 1;
    const EXIST_PREVIOUS = -1;

    protected $_isIndexable = false;

    public function insert($data, $unsetNull = true)
    {
        $news = parent::insert($data, $unsetNull);
        if ($news) {
            /*
            $doc = new HM_Activity_Search_Document(array(
                'activityName' => 'News',
                'activitySubjectName' => $news->subject_name,
                'activitySubjectId' => $news->subject_id,
                'id' => $news->id,
                'title' => $news->announce,
                'preview' => $news->announce
            ));

            $this->indexActivityItem($doc);
             *
             */
        }
        return $news;
    }

    public function linkClassifiers($newsId, $classifiers)
    {
        $this->getService('Classifier')->unlinkItem($newsId, HM_Classifier_Link_LinkModel::TYPE_CLASSIFIER_NEWS);
        if (is_array($classifiers) && count($classifiers)) {
            foreach($classifiers as $classifierId) {
                if ($classifierId > 0) {
                    $this->getService('Classifier')->linkItem($newsId, HM_Classifier_Link_LinkModel::TYPE_CLASSIFIER_NEWS, $classifierId);
                }
            }
        }
        return true;
    }

    public function getNews($newsId, $subjectName, $subjectId, $position, $fromDate = null)
    {
    	if(!$position) $news = $this->getOne($this->find($newsId));
    	else{
	   		$way = ($position < 0) ? '<' : '>';
	   		$order = ($position < 0) ? 'DESC' : 'ASC';

            $fields = array(
                'subject_id = ?',
                ' AND id '.$way.' ?',
                ' AND subject_name = ?'
            );
            $values = array(
                $subjectId,
                $newsId,
                $subjectName
            );

	    	$news = $this->getOne($this->fetchAll(
	    					$this->quoteInto(
	    							$fields,
	       							$values
	       					),
	       					array('created '.$order),
	       					1
	       	));
    	}
	    
    	return $news;
    }

    public function getRecentNews($subjectId, $subjectName = 'subject')
    {
        $news = $this->fetchAll($this->quoteInto(
            [
                'subject_id = ?',
                " and (subject_name = ? or subject_name = '')",
                new Zend_Db_Expr(' and created >= (CURDATE() - INTERVAL 3 DAY)')
            ],
            [
                $subjectId,
                'subject',
            ]
        ));

        return $news;
    }
    
    public function getNewsTriple($newsId, $subjectName, $subjectId){
    	
    	$selectPrev = $this->getSelect();
    	$selectPrev->from('News np', array('np.id', 'np.announce', 'np.subject_name', 'np.subject_id', 'np.author', 'np.created_by'));
    	$selectPrev->where('np.subject_name = ?', $subjectName);
    	$selectPrev->where('np.subject_id = ?', $subjectId);
    	$selectPrev->where('np.id < ?', $newsId);
    	$selectPrev->order('np.created DESC');
    	$selectPrev->limit(1);
    	
    	$selectNext = $this->getSelect();
    	$selectNext->from('News nn', array('nn.id', 'nn.announce', 'nn.subject_name', 'nn.subject_id', 'nn.author', 'nn.created_by'));
    	$selectNext->where('nn.subject_name = ?', $subjectName);
    	$selectNext->where('nn.subject_id = ?', $subjectId);
    	$selectNext->where('nn.id >= ?', $newsId);
    	$selectNext->order('nn.created ASC');
    	$selectNext->limit(2);
    	
    	$select = $this->getSelect();
    	$select->union(array($selectPrev, $selectNext));
    	
    	$res = $select->query()->fetchAll();
    	//pr($res);
    	return $res;
    	
    }
    
    public function onCreateLessonForm(Zend_Form $form, $activitySubjectName, $activitySubjectId, $title = null)
    {
        $form->addElement('select', 'module', array(
            'Label' => _('Новости'),
            'required' => true,
            'validators' => array(
                'int',
                array('GreaterThan', false, array(0))
            ),
            'filters' => array('int'),
            'multiOptions' => array(1 => 'Новость 1', 2 => 'Новость 2')
        ));
    }
    
    public function onLessonUpdate($lesson, $form)
    {
    }

    public function getLessonModelClass()
    {
        return "HM_News_NewsModel";
    }
    
    // передрано с blogService
    public function getNewsCondition($subjectId, $subjectName = null, $filter = array(), $split = false)
    {
        $where = array();
        $where['subject_id = ?'] = $subjectId;
        if ($subjectName) {
            $where['subject_name = ?'] = $subjectName;
        } else {
            $where["subject_name IS NULL OR subject_name LIKE ''"] = null;
        }
        foreach($filter as $type => $value) {
            switch($type) {
/*                case self::FILTER_TYPE_TAG:
                    $tagObj = $this->getOne($this->getService('Tag')->fetchAllManyToMany(
                        'Blog', 
                        'TagRefBlog', 
                        $this->quoteInto('BODY LIKE ?', $value)
                    ));
                    $ids = array();
                    foreach($tagObj->blogs as $blog) {
                        $ids []= $blog->id;
                    }
                    if(count($ids) > 0) {
                    $where['id IN (?)'] = $ids;
                    }
                break;*/
                case self::FILTER_TYPE_AUTHOR:
                    $where['created_by = ?'] = $value;
                break;
/*                case self::FILTER_TYPE_DATE:
                    $start = $value.'-01';
                    $end = $value.'-'.date('t', strtotime($start));
                    $where['created >= ?'] = $start;
                    $where['created <= ?'] = $end;
                break;*/
                default:
                    throw new InvalidArgumentException('Unknown filter type '.$type);
            }
        }
        if(!$split) {
            return $where;
        }
        $parts = array();
        foreach($where as $k=>$v) {
            $parts []= $this->quoteInto($k, $v);
        }
        return '('.implode(') AND (', $parts).')';
    }
    
    
    public function isNewsExists($newsId, $subjectName, $subjectId, $position)
    {
        $way = ($position < 0) ? '<' : '>';

        $fields = array(
            'subject_id = ?',
            ' AND id '.$way.' ?',
            ' AND subject_name = ?'
        );
        $values = array(
            $subjectId,
            $newsId,
            $subjectName
        );

        return $this->countAll(
            $this->quoteInto(
                $fields,
                $values
            ));
    }
}
