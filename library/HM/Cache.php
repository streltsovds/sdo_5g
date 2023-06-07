<?php

class HM_Cache
{

    /**
     * @param string $key
     */
    public static function removeCache($key)
    {

        try {
            $cache = Zend_Registry::get('cache');

            /**
             * Не требуется проверять постоянно $config->cache->enabled, потому что @see Zend_Cache_Core во всех функциях работы с кэшем идёт проверка _options['caching'],
             * который мы задаём в config.ini->cache.frontend.caching
             */
            $cache->remove($key);

        } catch (Exception $e) {
            Zend_Registry::get('log_system')->log('Cache remove error: ' . $e->getMessage(), Zend_Log::ERR);
        }
    }
}