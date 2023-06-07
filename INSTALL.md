h1. Установка и настройка eLearning Server 4g

h2. Переменная окружения APPLICATION_ENV

h3. IIS 6

> TBD

h3. IIS 7

> TBD

h3. Apache

Эта переменная может быть задана в @.htaccess@ с помощью @SetEnv@

h2. Файл конфигурации

Проверить, что-бы на рабочей системе *отсутствовал* файл @application/settings/config.dev.ini@

h2. Включение кэширования статических файлов в production

h3. IIS 6

> TBD

h3. IIS 7

Переименовать файл @public/web.config.production@ в @public/web.config@

h3. Apache

Переименовать файл @public/.htaccess.production@ в @public/.htaccess@

h2. Отключение сквозной авторизации для каталога @/wrapper@

h3. IIS 6

> TBD

h3. IIS 7

> TBD

h2. Общие замечания

Нужно следить за тем, что-бы Header X-UA-Compatible был выставлен ТОЛЬКО на папках @COURSES/emulate-ie*@ и нигде более!

Также при разворачивании под IIS 7 и выше нужно проверять, чтобы файлы web.config находились только в @/public/@, @/public/unmanaged/COURSES/@ и @/public/unmanaged/COURSES/emulate-ie*@

h2. Особенности IIS 6

h3. MIME Types

Прописывание MIME типов для расширений о которых IIS не знает (см. #5742) и исправление тех, которые заданы неверно (верные значения можно найти в @public/.htaccess@)
Список расширений и их MIME типов можно найти в @doc/extensions.txt@

h3. Установка Header'а X-UA-Compatible

В остальных поддерживаемых серверах этот заголовок устанавливается с помощью @.htaccess@ и @web.config@
Этот заголовок нужно установить на каталогах @COURSES/emulate-ie*@ верные значения для заголовка можно подсмотреть в файлах @COURSES/emulate-ie*/.htaccess@
