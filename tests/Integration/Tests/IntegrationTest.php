<?php
/**
 * Created by PhpStorm.
 * User: Stefano "Yoghi" Tamagnini
 * Date: 21/12/14 - 14:50
 * 
 */

namespace Integration\Tests;

use There4\Slim\Test\WebTestCase;
use RedBean_Facade as R;

if ( !class_exists('Integration\Tests\IntegrationTest') ) {

    abstract class IntegrationTest extends WebTestCase {

        public function getSlimInstance() {
            require APPLICATION_PATH.'/config-test.php';
            extract(configure_slim($config), EXTR_SKIP);

            require APPLICATION_PATH.'/includes/app.php';

            R::nuke(); //CLEAN DB

            require APPLICATION_PATH.'/includes/hooks.php';
            require APPLICATION_PATH.'/includes/routes.php';
            require APPLICATION_PATH.'/includes/api.php';

            if ( DEBUG ) {
                require BASE_DIR.'includes/development.php';
            }

            return $app;
        }

        protected function get($path,$data = array()) {
            $headers = array('HTTP_USER_AGENT' => 'WebTest', 'X_REQUESTED_WITH' => 'XMLHttpRequest'); //,'SCRIPT_NAME' => 'index.php'
            $this->client->get($path,$data,$headers);
        }

    }

}