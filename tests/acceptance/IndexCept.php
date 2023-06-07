<?php 
function shutdown() {
    //    var_dump(xdebug_get_function_stack());
    var_dump(error_get_last());
}
// register_shutdown_function('shutdown');
/*
 * Файл для запуска тестов
 * Конкретный набор тестов определяется в /application/settings/config.ini 
*/

$result = true;
$errorMessage = '';

if (count(Codeception_Registry::get('config')->global->tests)) {
    $i = 0;
    do {
        $attemptPassed = true;
        $tests = Codeception_Registry::get('config')->global->tests->toArray();
        $runnedTests = array();
        foreach ($tests as $testName => $enabled) {
            if ($enabled) {
                $path = TEST_ROOT . sprintf('/tests/%s.php', $className = ucfirst($testName));
                require_once $path;
                $test = new $className($scenario);
                try {
                    $test->run();
                    $runnedTests[] = $test;
                } catch (Exception $e) {
                    
                    cd($errorMessage = $e->getMessage());
                    cd($e->getFile());
                    cd($e->getLine());
                    
                    $lastLoop = (Codeception_Registry::get('config')->global->maxAttempts == $i+1);
                    
                    $test->rollback();
                    $attemptPassed = false;
                    
                    if ($lastLoop) {
                        // если это последний цикл - валим ошибку
                        throw new Exception($errorMessage);
                    } else {
                        // если ещё есть циклы - просто возвращаем всё в исходное состояние и пытаемся еще раз 
                        $test->logout();
                        cd('*********************************** ОШИБКА! Идём на новый круг ***********************************');
                    }
                    $result = false;
                    break;
                }
            }
        }
        $runnedTests = array_reverse($runnedTests);
        foreach ($runnedTests as $test) {
            $test->rollback();
        }
    } while((++$i < Codeception_Registry::get('config')->global->maxAttempts) && !$attemptPassed);
    
    
    if (Codeception_Registry::get('config')->global->sendResults) {

        $mail = new Zend_Mail();
        $messageSubj = sprintf('%s: %s', implode(', ', array_keys($tests)), $result ? 'SUCCESS' : 'FAILURE');
        $messageText = $errorMessage ? $errorMessage : '';
        
        $mail->addTo(Codeception_Registry::get('config')->global->sendResultsEmail, 'QA');
        $mail->setSubject($messageSubj);
        
        $mail->setFrom('no-reply@codeception.els', 'Codeception');
        
        $mail->setBodyText($messageText);
        try {
            return $mail->send();
        } catch (Zend_Mail_Exception $e) {
            return false;
        }
    }
} 

?>