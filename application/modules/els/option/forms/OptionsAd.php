<?php
class HM_Form_OptionsAd extends HM_Form
{

    public function init()
    {
        $this->setMethod(Zend_Form::METHOD_POST)
            ->setName('Options');
//             ->setAttrib('class', 'all-fieldsets-collapsed');

        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'value' => $this->getView()->url(array('module' => 'default', 'controller' => 'index', 'action' => 'index'))
        ));


        $this->addElement($this->getDefaultCheckboxElementName(), 'adSsoEnable', array(
            'Label' => _('Включить прозрачную авторизацию'),
            'Description' => _('Если установлена данная опция, система будет пытаться авторизовать пользователей автоматически на основании их авторизации в Windows.'),
            'Value' => 1,
        ));

        $this->addElement($this->getDefaultCheckboxElementName(), 'adSyncEnable', array(
            'Label' => _('Включить периодическую синхронизацию с AD'),
            'Description' => _('Если установлена данная опция, система периодически (раз в сутки) синхронизирует список учётных записей с Active Directory.'),
            'Value' => 1,
        ));

        $this->addElement($this->getDefaultTextElementName(), 'adHost', array(
            'Label' => _('IP-адрес/имя хоста'),
            'Required' => true,
            'Description' => 'The default hostname of LDAP server',
            'Value' => '192.168.0.1',
        ));

        $this->addElement($this->getDefaultTextElementName(), 'adPort', array(
            'Label' => _('TCP-порт'),
            'Required' => true,
            'Description' => 'Default port of LDAP server',
            'Value' => '389',
        ));

        $this->addElement($this->getDefaultCheckboxElementName(), 'adUseStartTls', array(
            'Label' => _('Использовать TLS'),
            'Description' => 'Whether or not the LDAP client should use TLS (aka SSLv2) encrypted transport. A value of TRUE is strongly favored in production environments to prevent passwords from be transmitted in clear text. The default value is FALSE, as servers frequently require that a certificate be installed separately after installation.',
            'Value' => '0',
        ));

        $this->addElement($this->getDefaultTextElementName(), 'adUsername', array(
            'Label' => _('Имя пользователя (технологическая учётная запись)'),
            'Required' => true,
            'Description' => 'The default credentials username. Some servers require that this be in DN form. This must be given in DN form if the LDAP server requires a DN to bind and binding should be possible with simple usernames.',
            'Value' => '',
        ));

        $this->addElement($this->getDefaultTextElementName(), 'adPassword', array(
            'Label' => _('Пароль (технологическая учётная запись)'),
            'Description' => 'The default credentials password (used only with username above).',
            'Value' => '',
            'type' => 'password'
        ));

        $this->addElement($this->getDefaultTextElementName(), 'adAccountDomainName', array(
            'Label' => _('Имя домена'),
            'Required' => true,
            'Description' => 'The FQDN domain for which the target LDAP server is an authority (e.g., example.com).',
            'Value' => '',
        ));

        $this->addElement($this->getDefaultTextElementName(), 'adAccountDomainNameShort', array(
            'Label' => _('Краткое имя домена'),
            'Required' => true,
            'Description' => 'The "short" domain for which the target LDAP server is an authority. This is usually used to specify the NetBIOS domain name for Windows networks but may also be used by non-AD servers.',
            'Value' => '',
        ));

        $this->addElement($this->getDefaultTextElementName(), 'adBaseDn', array(
            'Label' => _('Базовый DN'),
            'Required' => true,    
            'Description' => 'The default base DN used for searching (e.g., for accounts). This option is required for most account related operations and should indicate the DN under which accounts are located.',
            'Value' => '',
        ));

        $this->addElement($this->getDefaultTextElementName(), 'adQuery', array(
            'Label' => _('LDAP-запрос'),
            'Required' => false,    
            'Value' => '',
        ));

        $this->addDisplayGroup(array(
            'adSsoEnable',
        ),
            'adSso',
            array('legend' => _('Прозрачная авторизация'))
        );

        $this->addDisplayGroup(array(
            'adSyncEnable',
            'adHost',
            'adPort',
            'adUseStartTls',
            'adUsername',
            'adPassword',
            'adAccountDomainName',
            'adAccountDomainNameShort',
            'adBaseDn',
            'adQuery',
        ),
            'adSync',
            array('legend' => _('Синхронизация учётных записей'))
        );

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array(
            'Label' => _('Сохранить')));

        parent::init(); // required!
    }
}