<?php

class Chat_ChatMessageController extends HM_Controller_Action
{
    public function getMessageItemsAction()
    {
        $query = $this->getRequest()->getParam('query', array());
        $lessonId = $this->getRequest()->getParam('lesson_id', array());
        $subjectId = $this->getRequest()->getParam('subject_id', array());
        $firstSign = mb_substr($query, 0, 1);
        $searchString = mb_substr($query, 1);
        $result = array();


        switch ($firstSign) {
            case '@':
                $type = HM_MessageItem_MessageItemModel::TYPE_USER;
                //fetchAllDependenceJoinInner doesn't work
                $select = $this->getService('User')
                    ->getSelect()->from(
                        array('p' => 'People'),
                        array(
                            'p.FirstName',
                            'p.LastName',
                            'p.Patronymic',
                            'p.MID'
                        )
                    )
                    ->joinLeft(array('d' => 'deans'), 'p.MID=d.MID', array())
                    ->joinLeft(array('t' => 'Teachers'), 'p.MID=t.MID', array())
                    ->joinLeft(array('sid' => 'scheduleId'), 'p.MID=sid.MID', array())
                    ->where($this->quoteInto(
                        array(
                            '(d.MID is not null) or (t.MID is not null and t.CID = ?) or ',
                            '(sid.MID is not null and sid.SHEID = ?)',
                        ),
                        array(
                            $subjectId,
                            $lessonId,
                        )
                    ))
                    ->group(array(
                        'p.FirstName',
                        'p.LastName',
                        'p.Patronymic',
                        'p.MID'
                    ));

                $searchString = explode(' ', $searchString);
                foreach ($searchString as $searchSubstring) {
                    $select->where($this->quoteInto(
                        array('p.FirstName like ?', ' or p.LastName like ?', ' or p.Patronymic like ?',),
                        array(
                            '%' . strtolower($searchSubstring) . '%',
                            '%' . strtolower($searchSubstring) . '%',
                            '%' . strtolower($searchSubstring) . '%',
                        )
                    ));
                }

                $select->order(array('p.LastName', 'p.FirstName', 'p.Patronymic'));
                $fetchItems = $select->query()->fetchAll();
                foreach ($fetchItems as $fetchItem) {
                    $userModel = $this->getService('User')->fetchRow(array('MID = ?' => $fetchItem['MID']));
                    $result[] = array(
                        'id' => $fetchItem['MID'],
                        'name' => $fetchItem['LastName'] . ' ' . $fetchItem['FirstName'],
                        'avatarUrl' => $userModel->getPhoto(),
                    );
                }
                break;
            }



        return $this->responseJson(array(
            'type' => $type,
            'items' => $result,
        ));
    }

    public function getFilteredMessagesAction()
    {
        $items = $this->getRequest()->getParam('items', array());
        $namespace = $this->getRequest()->getParam('namespace', array());
        $query = $this->getRequest()->getParam('query');
        $page = $this->getRequest()->getParam('page', 1);
        $itemsPerPage = $this->getRequest()->getParam('items_per_page', HM_ChatMessage_ChatMessageModel::ITEMS_PER_PAGE);

        $messageService = $this->getService('Message');
        $select = $messageService->getSelect()->distinct()->from(
            array('cm' => 'chat_messages'),
            array(
                'cm.message_id',
                'cm.message',
                'cm.user_id',
                'cm.created_at',
            )
        );
        $select->joinLeft(array('cmi' => 'chat_message_items'), 'cmi.message_id=cm.message_id', array());

        if($namespace) {
            $select->where('cm.namespace = ?', $namespace);
        }

        if($query) {
            $select->where('cm.message like ?', '%'.strtolower($query).'%');
        }

        if(count($items)) {
            $cond = array();

            foreach ($items as $groupType => $group) {
                $groupItems = array_filter(explode(',', $group));
                if(!count($groupItems)) continue;

                switch ($groupType) {
                    case HM_MessageItem_MessageItemModel::TYPE_USER:
                        $cond[] = $this->quoteInto(
                            array('(cmi.type = ?', ' and cmi.item_id in (?))'),
                            array($groupType, $groupItems)
                        );
                        break;
                }
            }
            $select->where(implode(' and ', $cond));
        }

        $select
            ->order('message_id DESC')
            ->limitPage($page, $itemsPerPage);
        $fetchItems = $select->query()->fetchAll();
        $result = array();

        foreach ($fetchItems as $fetchItem) {
            $resultItem = $fetchItem;
            /** @var HM_User_UserModel $user */
            $user = $this->getService('User')->fetchRow(array('MID = ?' => $fetchItem['user_id']));
            if($user) {
                $resultItem['user'] = array(
                    'id' => $user->MID,
                    'name' => $user->getName(),
                    'avatarUrl' => $user->getPhoto(),
                );
            }
            $resultItem['items'] = $this->getService('MessageItem')->fetchAll(array('message_id = ?' => $fetchItem['message_id']))->asArrayOfArrays();
            $result[] = $resultItem;
        }

        return $this->responseJson($result);
    }
}
