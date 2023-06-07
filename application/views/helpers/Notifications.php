<?php
//require_once "ZendX/JQuery/View/Helper/UiWidget.php";
class HM_View_Helper_Notifications extends Zend_View_Helper_Abstract
{
    public function notifications($messages, $params = null, $attribs = null)
    {
        if (is_array($messages) && count($messages)) {
            $html = "";
            $js = "";
            $globalClear = "";
            $localClear = "";
            if (!isset($params['html'])) {
                //$params['html'] = true;
            }
            $flush_messages = is_array($params) && $params['html'];
            $mappings = array(
                HM_Notification_NotificationModel::TYPE_NOTICE  => "notice",
                HM_Notification_NotificationModel::TYPE_SUCCESS => "success",
                HM_Notification_NotificationModel::TYPE_ERROR   => "error",
                HM_Notification_NotificationModel::TYPE_CRIT    => "crit"
            );
            foreach($messages as $index => $message) {
                // fallback to TYPE_NOTICE if $message is array && type is not set
                if (is_array($message) && !isset($message['type'])) {
                    $message['type'] = HM_Notification_NotificationModel::TYPE_NOTICE;
                }
                if (is_string($message)) {
                    $message = array('type' => HM_Notification_NotificationModel::TYPE_SUCCESS, 'message' => $message);
                }
                $message_text = (isset($message['hasMarkup']) && $message['hasMarkup']) ? $message['message'] : $this->view->escape($message['message']);
                $id = $this->view->id('error-message');
                $shown_id = $this->view->escape($id);
                $dissmiss_btn = ''; //"<a id='{$shown_id}_close-alert' class=\"v-alert__dismissible v-alert_close-alert\"><i aria-hidden=\"true\" class=\"v-icon v-icon--right material-icons theme--light\">cancel</i></a>";
                switch ($message['type']) {
                    case HM_Notification_NotificationModel::TYPE_NOTICE:
                        $html.="<div id='{$shown_id}' class=\"v-alert info\">
                                    <i aria-hidden=\"true\" class=\"v-icon material-icons theme--light v-alert__icon\">info</i>
                                    <div>{$message_text}</div>
                                    {$dissmiss_btn}
                                </div>";
                        break;
                    case HM_Notification_NotificationModel::TYPE_SUCCESS:
                        $html.="<div id='{$shown_id}' class=\"v-alert success\">
                                   <div style='display:flex;'>
                                        <i aria-hidden=\"true\" class=\"v-icon material-icons theme--light v-alert__icon\">check_circle</i>
                                        <div>{$message_text}</div>
                                   </div>
                                    {$dissmiss_btn}
                               </div>";
                        break;
                    case HM_Notification_NotificationModel::TYPE_ERROR:
                        $html.="<div id='{$shown_id}' class=\"v-alert warning\">
                                    <i aria-hidden=\"true\" class=\"v-icon material-icons theme--light v-alert__icon\">priority_high</i>
                                    <div>{$message_text}</div>
                                    {$dissmiss_btn}
                                </div>";
                        break;
                    case HM_Notification_NotificationModel::TYPE_CRIT:
                        $html.="<div id='{$shown_id}' class=\"v-alert error\">
                                    <i aria-hidden=\"true\" class=\"v-icon material-icons theme--light v-alert__icon\">warning</i>
                                    <div>{$message_text}</div>
                                    {$dissmiss_btn}
                                </div>";
                        break;
                    case HM_Notification_NotificationModel::TYPE_INSTANT:
                        $html.="<div id='{$shown_id}' class=\"v-alert info\">
                                    <i aria-hidden=\"true\" class=\"v-icon material-icons theme--light v-alert__icon\">info</i>
                                    <div>{$message_text}</div>
                                    {$dissmiss_btn}
                                </div>";
                        break;
                    default:
                        $html.="<div id='{$shown_id}' class=\"v-alert info\">
                                    <i aria-hidden=\"true\" class=\"v-icon material-icons theme--light v-alert__icon\">info</i>
                                    <div>{$message_text}</div>
                                    {$dissmiss_btn}
                                </div>";
                        break;
                }
                $js_btn_id = "{$shown_id}_close-alert";
                $js_alert_id = "{$shown_id}";
//                $closing_script = <<<JS
//                    document.getElementById('$js_btn_id').addEventListener('click', function() {
//                      document.getElementById('$js_alert_id').display = 'none';
//                    })
//JS;
//                $this->view->inlineScript()->appendScript($closing_script);
                // we must check type
//                if ($message['type'] === HM_Notification_NotificationModel::TYPE_NOTICE
//                        || $message['type'] === HM_Notification_NotificationModel::TYPE_SUCCESS
//                        || $message['type'] === HM_Notification_NotificationModel::TYPE_ERROR
//                        || $message['type'] === HM_Notification_NotificationModel::TYPE_CRIT
//                ) {
//                    $local_html = '<div id="'.$this->view->escape($id).'" title="'.$this->view->escape($message['short-message']).'">'.($message['hasMarkup']
//                        ? $message['message']
//                        : $this->view->escape($message['message'])
//                    ).'</div>';
//                    if ($flush_messages) {
//                        $localClear = 'jQuery.ui.errorbox.clear(jQuery('.HM_Json::encodeErrorSkip("#{$id}").'));';
//                        $html .= $local_html;
//                        $js .= 'jQuery('.HM_Json::encodeErrorSkip("#{$id}").')';
//                    } else {
//                        $globalClear = 'jQuery.ui.errorbox.clear();';
//                        $js .= 'jQuery('.HM_Json::encodeErrorSkip(strval($local_html)).')';
//                    }
//                    $js .= '.errorbox('.HM_Json::encodeErrorSkip(array( 'level' => $mappings[$message['type']] )).');';
//                } elseif ($message['type'] === HM_Notification_NotificationModel::TYPE_INSTANT) {
//                    $js .= sprintf("$.gritter.add({title: '%s', image: '%s', text: '%s'});\r\n",
//                         $this->view->escape($message['instantTitle']),
//                         $this->view->escape($message['instantImage']),
//                         $message['message']
//                    );
//                }
            }
            //$this->jquery->addOnLoad($globalClear.$localClear.$js);

            return $html;
        }
        return '';
    }
}