# eLearning Server 5G

## Настройки php, apache

`mbstring.func_overload=7`

см. http://projects.hypermethod.com:8080/redmine/issues/36400#note-14

## init project

```composer install```

## Run migrations
Linux:

```php ./vendor/bin/phinx migrate```

```php ./vendor/bin/phinx seed:run```

Windows:

```./vendor/robmorgan/phinx/bin/phinx migrate```

## Начальные данные

На данный момент миграции не наполнят базу необходимыми данными; необходимо вручную выполнить (`./!dumps/mysql/db_dump2.sql`).

## Create migrations

```./vendor/bin/phinx create MyNewMigration```

## run unit tests:
Linux:

```./vendor/bin/phpunit --bootstrap ./tests/PhpUnit/bootstrap.php --testdox  ./tests/PhpUnit/```

Windows:

```./vendor/bin/phpunit.bat --bootstrap ./tests/PhpUnit/bootstrap.php --testdox  ./tests/PhpUnit/```

##Instruction for using Redis as a backend for Zend_Cache
1. Don't forget to run ```composer install```.
2. Download latest windows msi (https://github.com/microsoftarchive/redis/releases/tag/win-3.2.100)
3. Start installation with adding Redis commands to the environment.
4. For launching Redis as a service, run cmd, cd to installation directory and execute next two commands:

    ```redis-server redis.windows-service.conf  --service-install```

    ```redis-server redis.windows-service.conf  --service-start```

5. Download php_redis.dll for your PHP 7 version from https://pecl.php.net/package/redis/5.1.1/windows
6. Put php_redis.dll to /ext directory of PHP 7 installation path.
7. Add the record about this file to php.ini
8. Restart your web-server.
9. Comment/uncomment related lines in your application/settings/config.ini to enable Redis as your caching backend.
10. For enhancing your development opportunities your could learn more about the package "colinmollenhour/cache-backend-redis".
11. Enjoy.

P.S. You can download very convenient client for interact with Redis: https://www.redily.app/

P.P.S. If you get an exceptions when some pages or infoblocks loads, you may need to delete the content of data/cache in a sake of refreshing early cached data.

## sphinx

Supported versions: 2.2.11, 2.3.2
Version 3.x is not supported (event with last sphinx lib)

