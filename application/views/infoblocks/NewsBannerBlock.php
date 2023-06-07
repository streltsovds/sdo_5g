<?php

class HM_View_Infoblock_NewsBannerBlock extends HM_View_Infoblock_Abstract
{
    const MAX_ITEMS = 10;
    
    protected $id = 'newsbannerblock';

    private function generateCompletelyRandomColor()
    {
        $basicColorsClasses = array('#D4FAE4', '#FAF3D8', '#D4E3FB', '#DAD3FD', '#FDE1D9', '#EDF4FC', '#FFE9B9', '#ABDCC6', '#DAC5E2');
        $randomBasicClsIdx = mt_rand(0, count($basicColorsClasses) - 1);
        $randomColorCls = $basicColorsClasses[$randomBasicClsIdx];

        return $randomColorCls;
    }

    /**
     * @param null $param
     * @return string
     * @throws Zend_Exception
     */
    public function newsBannerBlock($param = null)
    {
        $services = Zend_Registry::get('serviceContainer');

        if (false === strpos(Zend_Registry::get('config')->resources->db->adapter, 'mysql')) {
            $announceField = new Zend_Db_Expr('CAST(n.announce as VARCHAR(MAX))');
            $announceGroup = new Zend_Db_Expr('CAST(n.announce as VARCHAR(MAX))');
            $messageField  = new Zend_Db_Expr('CAST(n.message as VARCHAR(MAX))');
            $messageGroup  = new Zend_Db_Expr('CAST(n.message as VARCHAR(MAX))');
        } else {
            $announceField = 'n.announce';
            $announceGroup = 'n.announce';
            $messageField  = 'n.message';
            $messageGroup  = 'n.message';
        }

        // Перечень новостей
        $newsService = $services->getService('News');
        $select = $newsService->getSelect();
        $select->from(array('n' => 'news'), array(
            'n.id',
            'n.date',
            'n.author',
            'n.created',
            'n.created_by',
            'announce' => $announceField,
            'message' => $messageField,
            'n.subject_name',
            'n.subject_id',
            'n.url',
            'n.name',
            'n.visible',
            'n.date_end',
            'n.icon_url',
            'n.mobile',
            'classifier_id' => 'GROUP_CONCAT(c.classifier_id)',
        ));

        /*->where('s.reg_type = ?', HM_Subject_SubjectModel::REGTYPE_SELF_ASSIGN);*/
        $select
            ->where("(n.subject_name='' or n.subject_name is null) and (n.subject_id=0 or n.subject_id is null)")
            ->where("n.visible = 1 AND (n.date_end IS NULL OR n.date_end='' OR n.date_end > '".date('Y-m-d 23:59')."')")
            ->joinLeft(array('cl' => 'classifiers_links'), "cl.item_id = n.id AND cl.type = " . HM_Classifier_Link_LinkModel::TYPE_CLASSIFIER_NEWS, array())
            ->joinLeft(array('c' => 'classifiers'), "c.classifier_id = cl.classifier_id", array())
            ->group([
                'n.id',
                'n.date',
                'n.author',
                'n.created',
                'n.created_by',
                $announceGroup,
                $messageGroup,
                'n.subject_name',
                'n.subject_id',
                'n.url',
                'n.name',
                'n.visible',
                'n.date_end',
                'n.icon_url',
                'n.mobile',
            ]);

        $news = $select->query()->fetchAll();
        $news = $services->getService('News')->getMapper()->fetchAllFromArray($news);

        foreach($news as $n) {
            if(!$n->icon_url) {
                $n->icon_url = '/images/icons/news.jpg';
            }
        }

        $newsJs = array();
        $id = 0;

        if (count($news)) {
            foreach ($news as $item) {
                $banner = $item->icon_url;
                if (! $banner) $banner = Zend_Registry::get('config')->url->base . 'images/content-modules/tests/empty.png';
                $name = $item->getName() ? $item->getName() : '';
                $description = $item->getAnnounce() ? $item->getAnnounce() : '';

                $newsJs[] = array(
                    'name' => $name,
                    'description' => $description,
                    'id' => ++$id,
                    'url' => $item->getUrl(),
                    'image' => $banner,
                    'color' => $this->generateCompletelyRandomColor(),
                    'announce' => $item->getAnnounce(),
                    'message' => $item->message,//getFilteredMessage(),
                    'created' => $item->created,
                    'author' => $item->author,
                    'classifier_id' => array_filter(explode(',', $item->classifier_id)),
                );
            }
        }

        $this->view->slides = array_values($newsJs);
//        array_walk_recursive($this->view->slides, function (&$val, $index) {
//            $val = strip_tags($val);
//        });

        /** @var HM_Classifier_ClassifierService $classifierService */
        $classifierService = $this->getService('Classifier');
        $this->view->classifiers = [];
        $this->view->classifiers = $classifierService->getSelect()
            ->from(
                ['c' => 'classifiers'],
                [
                    'id' => 'c.classifier_id',
                    'c.name',
                ]
            )
            ->joinInner(
                ['cl' => 'classifiers_links'],
                $classifierService->quoteInto(
                    ['cl.classifier_id=c.classifier_id and cl.type = ?'],
                    [HM_Classifier_Link_LinkModel::TYPE_CLASSIFIER_NEWS]
                ),
                []
            )
            ->group([
                'c.classifier_id',
                'c.name',
            ])
            ->query()->fetchAll();


        foreach ($newsJs as $newsItem) {
            if($newsItem['classifier_id']) {
                array_unshift($this->view->classifiers, ['name' => 'Все']);
                break;
            }
        }

        $content = $this->view->render('newsBannerBlock.tpl');
        return $this->render($content);
    }
}