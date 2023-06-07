<?php


class Diagnostics_IndexController extends HM_Controller_Action
{
    public function indexAction()
    {
        require_once APPLICATION_PATH . '/../library/sinergi/SinergiBrowser.php';
        $allowedBrowsersFromConfig = Zend_Registry::get('config')->proctoring->allowedBrowsers;
        if(!is_object($allowedBrowsersFromConfig)) {
            throw new Exception('Missing proctoring allowed browses in config file');
        }

        $browsers = $allowedBrowsersFromConfig->toArray();
        $browserDetector = new SinergiBrowser();
        $currentBrowserName = $browserDetector->getName();
        $currentBrowserVersion = $browserDetector->getVersion();
        $currentBrowserVersionForCheck = (int) $currentBrowserVersion;

        $browserInfo = $browsers[$currentBrowserName];
        if (!empty($browserInfo) and
            $currentBrowserVersionForCheck >= $browserInfo['min']
        ) {
            if($currentBrowserVersionForCheck >= $browserInfo['min'] and
                $currentBrowserVersionForCheck <= $browserInfo['max']
            ) {
                $message = _('Поддерживаемый браузер/версия');
            } elseif($currentBrowserVersionForCheck > $browserInfo['max']) {
                $message = _("Версия вашего браузера еще не прошла тестирование. Возможны неполадки в работе");
            }
        } else {
            $message = _("Ваш браузер не поддерживается");
        }

        $this->view->assign(array(
            'message' => $message,
            'browsers' => $browsers,
            'currentBrowserName' => $currentBrowserName,
            'currentBrowserVersion' => $currentBrowserVersion,
        ));
    }
}