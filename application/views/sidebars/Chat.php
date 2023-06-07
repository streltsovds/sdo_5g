<?php
/**
 * Created by PhpStorm.
 * User: k1p
 * Date: 6/20/19
 * Time: 5:08 PM
 */

class HM_View_Sidebar_Chat extends HM_View_Sidebar_Abstract
{
    function getIcon()
    {
        return 'services'; // @todo
    }

    function getContent()
    {
        $subject = $this->view->model;
        $subjectId = $subject->subid;
        $subjectName = $subject->name;

        $channelsSrv = $this->getService('ChatChannels');

        $channels = $channelsSrv->fetchAllManyToMany('Users', 'ChatRefUsers', $channelsSrv->getChannelsCondition($subjectId, $subjectName));
        $archive = $channelsSrv->getArchive($subjectId, $subjectName);
        foreach($channels as $k=>$channel){
            $uids = $channelsSrv->getChannelUserIds($channel);
            if(!in_array($this->getService('User')->getCurrentUserId(), $uids)) {
                unset($channels[$k]);
            }
            $users = $channelsSrv->usersOnline($channel);
            $channel->usersOnline = $users;
        }

        $data = [
            'archive' => $archive,
            'channels' => $channels,
            'curUserId' => $this->getService('User')->getCurrentUserId(),
            'model' => $this->getModel(),
        ];

        return $this->view->partial('chat.tpl', $data);
    }
}