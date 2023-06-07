<?php
/**
 * @todo: ����������� ��� �������
 * Class HM_Poll_Link_LinkModel
 */
class HM_Poll_Link_LinkModel extends HM_Model_Abstract
{
    const TYPE_BLOG_ITEM = 0;
    const TYPE_NEWS_ITEM = 1;

    static public function getTypes()
    {
        return array(
            self::TYPE_BLOG_ITEM => _('"������� �����'),
            self::TYPE_NEWS_ITEM => _('"������� ��������� �����'),
        );
    }

    /**
     * ���������� ��� �������������� �������� � ����������� �� MCA-������ (model:controller:action)
     * @param null $mca
     * @return bool|int
     */
    static public function getTypeByMCA($mca = null)
    {
        if ( $mca === null ) {
            $request = Zend_Controller_Front::getInstance()->getRequest();
            $mca     = sprintf("%s:%s:%s", $request->getModuleName(), $request->getControllerName(), $request->getActionName());
        }

        switch( strtolower($mca) ) {
            case 'blog:index:view':
                $type = self::TYPE_BLOG_ITEM;
                break;
            case 'news:index:view':
                $type = self::TYPE_NEWS_ITEM;
                break;
            default:
                $type = false;
        }

        return $type;
    }

    /**
     * ��� ��������� �� �� �������� ����������� ��������
     * @param $type
     * @return string
     */
    static public function getUrlItemIdParamName($type)
    {
        switch ($type) {
            case self::TYPE_BLOG_ITEM :
                $paramName = 'blog_id';
                break;
            case self::TYPE_NEWS_ITEM :
                $paramName = 'news_id';
                break;
            default:
                $paramName = 'item_id';
        }
        return $paramName;
    }

    /**
     * ��������� ������ �� �������� ����������� ��������
     * @param $itemId
     * @param $itemType
     * @return string
     */
    static public function getItemPageUrl($itemId, $itemType)
    {
        switch ($itemType) {
            case HM_Poll_Link_LinkModel::TYPE_BLOG_ITEM:
                $data = array(
                    'module'     => 'blog',
                    'controller' => 'index',
                    'action'     => 'view',
                    'blog_id'    => $itemId
                );
                break;
            case HM_Poll_Link_LinkModel::TYPE_NEWS_ITEM:
                $data = array(
                    'module'     => 'news',
                    'controller' => 'index',
                    'action'     => 'view',
                    'news_id'    => $itemId
                );
                break;
            default: return '';
        }

        return Zend_Registry::get('view')->url($data, null, true);
    }

    /**
     * ������� ���������� ��������� ������� ������
     * !!! �� ����������, �������� ������ � �����
     * @param $linkId
     * @return null|string
     */
    public function getItemTitle()
    {
        return $this->getService()->getItemTitle($this->link_id);
    }

    public function getServiceName()
    {
        return 'PollLink';
    }

    /**
     * ���������� ��������������� �������� �������� ������ �������, ���������� ������� ������.
     * @param int $maxValue
     * @return mixed
     */
    public function getNormalizePageRank($maxValue = 100)
    {
        return $this->getService()->getService('Poll')->getNormalizePageRank($this->quiz_id, $this->link_id, $maxValue);
    }
}