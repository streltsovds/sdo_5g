<?php

use HM_User_Loginlog_LoginlogModel as LoginlogModel;

class HM_Controller_Plugin_Ntlm extends HM_Controller_Plugin_Abstract
{
    const SERVER_VAR_NAME = 'AUTH_USER';

    private $_idField;

    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $options = Zend_Registry::get('serviceContainer')->getService('Option')->getOptions(HM_Option_OptionModel::SCOPE_AD);
        if (empty($options['adSsoEnable'])) return;
        
        $url = $_SERVER['REQUEST_URI'];

        if (false !== strstr($url, 'index.php')) {
            $url = $this->getView()->serverUrl('/');
        }

        //$_SERVER['AUTH_USER'] = 'test2013';

        /** @var HM_User_UserService $userService */
        $userService = $this->getService('User');

        if (!isset($_COOKIE['hmexit']) && !$userService->getCurrentUser() && isset($_SERVER[self::SERVER_VAR_NAME])) {

            $config = Zend_Registry::get('config');

            $login = trim($_SERVER[self::SERVER_VAR_NAME]);
            $domain = '';
            if(strpos($login, '@')!==false)
                list($login, $domain) = explode("@", $login);
            else
            if (false !== strstr($login, '\\')) {
                list($domain, $login) = explode('\\', $login);
            }

            if (strlen($login) && !empty($domain)) {
                try {
                    $authorized = $userService->authorizeByLogin($login, $domain);

                    if (!$authorized && $config->ntlm->createIfNotExists) {

                        // create if not exists user using ldap
                        if (!$userService->isLoginExists($login, $domain)) {

                            /** @var HM_Ldap $ldap */
                            $ldap = $this->getService('Ldap');
                            $ldapName = $this->getLdapName($domain);
                            $ldap->setLdapOptions($ldapName);

                            $entry = $ldap->findUserByLogin($login);

                            $this->_idField = $config->$ldapName->ldap->mapping->user->uniqueIdField;

                            if ($entry && isset($entry[$this->_idField][0])) {

                                $values = array(
                                    'Login' => $login,
                                    'Domain' => $domain,
                                    'isAD' => 1
                                );

                                $mapping = $config->$ldapName->ldap->mapping->user->toArray();
                                foreach($mapping as $field => $value) {
                                    if (isset($entry[$field][0])) {
                                        $values[$value] = $entry[$field][0];
                                    }
                                }
                                //$values['LastName']     = $entry['sn'][0];
                                //$values['FirstName']    = $entry['givenname'][0];
                                //$values['Patronymic']   = $entry['initials'][0];
                                $values['mid_external'] = $entry[$this->_idField][0];

                                if (strlen($values['mid_external'])) {

                                    $collection = $userService->fetchAll(
                                        $userService->quoteInto('mid_external LIKE ?', $values['mid_external'])
                                    );

                                    if (count($collection)) {
                                        $exists = $collection->current();
                                        if ($exists) {
                                            $values['MID'] = $exists->MID;
                                        }
                                    }
                                }

                                if (isset($values['MID'])) {
                                    $userService->update($values);
                                } else {
                                    $userService->insert($values);
                                }

                                $authorized = $userService->authorizeByLogin($login, $domain);

                            }
                        }
                    }
                } catch (Zend_Db_Exception $e) {
                    Zend_Registry::get('log_system')->debug(
                        $e->getMessage() . "\n" . $e->getTraceAsString()
                    );
                    throw $e;
                }

                /** @var HM_User_Loginlog_LoginlogService $userLoginLogService */
                $userLoginLogService = $this->getService('UserLoginLog');

                if ($authorized) {
                    $userLoginLogService->login($login, _('Пользователь успешно авторизован.'), LoginlogModel::STATUS_OK);
                    header('Location: '.$url);
                    exit();
                }else{
                    $userLoginLogService->login($login, _('Вы неверно ввели имя пользователя или пароль.'), LoginlogModel::STATUS_FAIL);
                }
            }
        }
    }

    private function getLdapName($domain)
    {
        $ldapNames = HM_Integration_Abstract_Model::getLdapNames();
        foreach ($ldapNames as $ldapName) {
            $accountDomainName = strtolower(Zend_Registry::get('config')->$ldapName->ldap->options->accountDomainName);
            $accountDomainNameShort = strtolower(Zend_Registry::get('config')->$ldapName->ldap->options->accountDomainNameShort);
            if ($accountDomainName == strtolower($domain)) return $ldapName;
            if ($accountDomainNameShort == strtolower($domain)) return $ldapName;
        }

        return null;
    }
}
