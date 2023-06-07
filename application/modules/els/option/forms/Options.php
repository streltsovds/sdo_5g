<?php
class HM_Form_Options extends HM_Form
{

    public function init()
    {
        $this->setMethod(Zend_Form::METHOD_POST)
            ->setName('Options');

        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'value' => $this->getView()->url(array('module' => 'default', 'controller' => 'index', 'action' => 'index'))
        ));

        // Контактная информация

        $this->addElement($this->getDefaultCheckboxElementName(), 'enable_email', array(
            'Label' => _('Email-рассылка включена'),
            'Value' => 1
        ));

        $this->addElement($this->getDefaultTextElementName(), 'dekanEMail', array(
            'Label' => _('E-mail администрации Портала')
        ));

        $this->addElement($this->getDefaultTextElementName(), 'dekanName', array(
            'Label' => _('Название администрации Портала (поле "От" в письмах, рассылаемых Системой)')
        ));

//        $this->addElement($this->getDefaultTextElementName(), 'externalUrl', array(
//            'Label' => _('Внешний URL Портала (для просмотра ресурсов с помощью Google)')
//        ));

        $this->addElement($this->getDefaultTextAreaElementName(), 'message_signature', array(
            'Label' => _('Подпись в сообщениях'),
            'Required' => false,
            'Filters' => array('HtmlSanitize'),
        ));

        $this->addDisplayGroup(array(
            'enable_email',
            'dekanEMail',
            'dekanName',
            'message_signature',
//            'externalUrl'
        ),
            'contacts',
            array('legend' => _('Контактная информация'))
        );

        // Отображение

        $this->addElement($this->getDefaultTextElementName(), 'grid_rows_per_page', array(
            'Label' => _('Количество строк в таблице')
        ));

        $this->addElement($this->getDefaultTextElementName(), 'maxUserEvents', array(
            'Label' => _('Максимальное количество сообщений в ленте')
        ));

// нужно для рекрутинга, по-хорошему надо скрывать ремувером
//        $this->addElement($this->getDefaultTextElementName(), 'custom_employer_name', array(
//            'Label' => _('Название компании для анонимных вакансий')
//        ));

        $this->addDisplayGroup(array(
            'grid_rows_per_page',
            'maxUserEvents',
//            'custom_employer_name'
        ),
            'vision',
            array('legend' => _('Отображение'))
        );

        // Функциональность

        $this->addElement($this->getDefaultCheckboxElementName(), 'use_techsupport', array(
            'Label' => _('Включить встроенную возможность обращения в техподдержку'),
            'Value' => 1
        ));

        $this->addElement($this->getDefaultCheckboxElementName(), 'generateCertificateFiles', array(
            'Label' => _('Автоматически генерировать файлы сертификатов об обучении'),
            'Value' => 0
        ));

        $this->addElement($this->getDefaultCheckboxElementName(), 'use_holidays', array(
            'Label' => _('Исключать выходные и праздничные дни при расчете относительных дат'),
            'Value' => 1
        ));

        // ремувер
//        $this->addElement($this->getDefaultTextElementName(), 'managers_notify_session_quarter_days_before', array(
//            'Label' => _('Количество дней для даты при уведомлении менеджеров (кв.планирование)')
//        ));

        $this->addDisplayGroup(array(
            'use_techsupport',
            'use_holidays',
//            'managers_notify_session_quarter_days_before',
            'generateCertificateFiles',

        ),
            'functions',
            array('legend' => _('Функциональность'))
        );

        // Безопасность


        $this->addElement($this->getDefaultCheckboxElementName(), 'disable_personal_info', array(
            'Label' => _('Запретить рядовым пользователям просмотр персональных данных коллег'),
            'Value' => 1
        ));

        $this->addElement($this->getDefaultCheckboxElementName(), 'disable_messages', array(
            'Label' => _('Запретить рядовым пользователям рассылку сообщений'),
            'Value' => 1
        ));

        $this->addElement($this->getDefaultCheckboxElementName(), 'security_logger', array(
            'Label' => _('Включить логирование доступа к основным элементам системы'),
            'Value' => 1
        ));

        $this->addElement($this->getDefaultCheckboxElementName(), 'scorm_tracklog', array(
            'Label' => _('Включить отладочную информацию SCORM API'),
            'Value' => 1
        ));

        $this->addElement($this->getDefaultCheckboxElementName(), 'disable_multiple_authentication', array(
            'Label' => _('Запретить одновременную аутентификацию пользователя через несколько устройств или браузеров'),
            'Value' => 1
        ));

        $this->addElement($this->getDefaultTextElementName(), 'images_allowed_domains', array(
            'Label' => _('Список разрешенных доменов для загрузки изображений'),
            'Value' => '*'
        ));

        $this->addElement($this->getDefaultTextElementName(), 'ext_pages_videos_allowed_domains', array(
            'Label' => _('Список разрешенных доменов для загрузки внешних страниц и видео'),
            'Value' => '*',
            'Description' => _("Список доменов через пробел; без https://, например: '*.wikipedia.org youtube.com'; маска '*' означает любой домен")
        ));

        $this->addDisplayGroup(array(
            'disable_personal_info',
            'disable_messages',
            'security_logger',
            'scorm_tracklog',
            'disable_multiple_authentication',
            'images_allowed_domains',
            'ext_pages_videos_allowed_domains',
        ),
            'safety',
            array('legend' => _('Безопасность'))
        );

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array(
            'Label' => _('Сохранить')));


        $event = new sfEvent(null, HM_Extension_ExtensionService::EVENT_FILTER_FORM_OPTIONS);
        $this->getService('EventDispatcher')->filter($event, $this);

        parent::init(); // required!
    }
}