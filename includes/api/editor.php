<?php
/**
 * Created by PhpStorm.
 * User: Stefano "Yoghi" Tamagnini
 * Date: 22/12/14 - 21:49
 * 
 */

use RedBean_Facade as R;
use Dreamland\Errori;
use Faker\Factory;

function editor($app)
{
    // Library group
    $app->group('/editor', function () use ($app) {

        $app->post('/eg', function () use ($app) {
            $app->response->setStatus(500);
            $app->response->headers->set('Content-Type', 'application/json');
            try {

                if ( !isset($_SESSION['wordpress']) ) {
                    throw new Exception('Wordpress login not found', Errori::WORDPRESS_LOGIN_REQUIRED);
                }

                $body = $app->request->getBody();
                $obj = json_decode($body);
                $gruppoCode = $obj->gruppo;

                if ( defined('BETA') and BETA ){
                    $app->log->info('Richiesta creazione nuovo utente eg per il gruppo '.$gruppoCode);
                } else {
                    throw new Exception('Beta non attiva', Errori::BETA_NON_ATTIVA);
                }

                $find = R::findOne('asa_gruppi',' ord = ?',array($gruppoCode));

                if ( null == $find ){
                    throw new Exception('Gruppo inesistente',Errori::GRUPPO_NON_VALIDO);
                }
                while(true){
                    $faker = Factory::create();
                    $user = new \stdClass();
                    $user->nome = $faker->firstName; // 'Lucy'
                    $user->cognome = $faker->lastName; // 'Curry'

                    $user->codRegione = $find['creg'];
                    $user->codGruppo = $gruppoCode;
                    $user->codZona = $find['czona'];

//                    $user->codZona = $faker->numberBetween(1, 26); //codicezona
//                    $user->codGruppo = $faker->numberBetween(1, 9000); //codicegruppo
//                    $user->codRegione = $faker->randomLetter(); //regione

                    $user->codicecensimento = $faker->randomNumber(5); //codicecensimento
                    $user->datanascita = $faker->date($format = 'Ymd', $max = 'now');

                    if ( null == findDatiRagazzo($user->codicecensimento) ) {

                        R::$f->begin()->addSQL('
                            INSERT INTO asa_anagrafica_eg(Id, creg, ord, cun, prog, codicesocio, cognome, nome, datanascita, status, czona)
                            VALUES(1,"'.$user->codRegione.'","'.$user->codGruppo.'","O",1,'.$user->codicecensimento.',"'.$user->cognome.'","'.$user->nome.'","'.$user->datanascita.'","S",'.$user->codZona.');
                        ')->get();

                        break;

                    }

                }

                $app->response->setBody( json_encode($user) );
                $app->response->setStatus(201);

            } catch ( Exception $e ) {

                $testo = 'Internal Error';
                $warn = false;
                $status = 500;
                $wordpress = $app->config('wordpress');
                switch ($e->getCode()) {
                    case Errori::WORDPRESS_LOGIN_REQUIRED:
                        $url_login = $wordpress['url'].'wp-login.php';
                        $testo = 'Wordpress login not found - '.$url_login;
                        $status = 403;
                        $warn = false;
                        break;
                    case Errori::GRUPPO_NON_VALIDO:
                        $testo = 'Gruppo non valido';
                        $status = 404;
                        $warn = true;
                        break;
                    case Errori::BETA_NON_ATTIVA:
                        $testo = 'Beta non attiva';
                        $status = 403;
                        $warn = true;
                        break;
                }
                if ( !$warn ) {
                    $app->log->error($e->getMessage());
                    $app->log->error($e->getTraceAsString());
                } else {
                    $app->log->warn($e->getMessage());
                }
                $app->response->setBody( json_encode($testo) );
                $app->response->setStatus($status);

            }
        });

        $app->post('/cc', function () use ($app) {
            $app->response->setStatus(500);
            $app->response->headers->set('Content-Type', 'application/json');
            try {

                if ( !isset($_SESSION['wordpress']) ) {
                    throw new Exception('Wordpress login not found', Errori::WORDPRESS_LOGIN_REQUIRED);
                }

                $body = $app->request->getBody();
                $obj = json_decode($body);
                $gruppoCode = $obj->gruppo;

                if ( defined('BETA') and BETA ){
                    $app->log->info('Richiesta creazione nuovo utente cc per il gruppo '.$gruppoCode);
                } else {
                    throw new Exception('Beta non attiva', Errori::BETA_NON_ATTIVA);
                }

                $find = R::findOne('asa_gruppi',' ord = ?',array($gruppoCode));

                if ( null == $find ){
                    throw new Exception('Gruppo inesistente',Errori::GRUPPO_NON_VALIDO);
                }
                while(true){
                    $faker = Faker\Factory::create();
                    $user = new \stdClass();
                    $user->nome = $faker->firstName; // 'Lucy'
                    $user->cognome = $faker->lastName; // 'Curry'

                    $user->codRegione = $find['creg'];
                    $user->codGruppo = $gruppoCode;
                    $user->codZona = $find['czona'];

                    $user->codicecensimento = $faker->randomNumber(5); //codicecensimento
                    $user->datanascita = $faker->date($format = 'Ymd', $max = 'now');
                    $user->email = $faker->email();

                    if ( null == findDatiRagazzo($user->codicecensimento) ) {

                        R::$f->begin()->addSQL('
                            INSERT INTO asa_capireparto_ruolo(Id, creg, ord, cun, prog, codicesocio, fnz)
                            VALUES(1,"'.$user->codRegione.'","'.$user->codGruppo.'","O",1,'.$user->codicecensimento.',1);
                        ')->get();

                        R::$f->begin()->addSQL('
                            INSERT INTO asa_anagrafica_capireparto(Id, codicesocio, cognome, nome, status, czona)
                            VALUES(1,'.$user->codicecensimento.',"'.$user->cognome.'","'.$user->nome.'","S","'.$user->codZona.'");
                        ')->get();

                        R::$f->begin()->addSQL('
                            INSERT INTO asa_capireparto_email(Id, recapito, tipo, codicesocio)
                            VALUES(1,"'.$user->email.'","E",'.$user->codicecensimento.');
                        ')->get();

                        break;

                    }

                }

                $app->response->setBody( json_encode($user) );
                $app->response->setStatus(201);

            } catch ( Exception $e ) {

                $testo = 'Internal Error';
                $warn = false;
                $status = 500;
                $wordpress = $app->config('wordpress');
                switch ($e->getCode()) {
                    case Errori::WORDPRESS_LOGIN_REQUIRED:
                        $url_login = $wordpress['url'].'wp-login.php';
                        $testo = 'Wordpress login not found - '.$url_login;
                        $status = 403;
                        $warn = false;
                        break;
                    case Errori::GRUPPO_NON_VALIDO:
                        $testo = 'Gruppo non valido';
                        $status = 404;
                        $warn = true;
                        break;
                    case Errori::BETA_NON_ATTIVA:
                        $testo = 'Beta non attiva';
                        $status = 403;
                        $warn = true;
                        break;
                }
                if ( !$warn ) {
                    $app->log->error($e->getMessage());
                    $app->log->error($e->getTraceAsString());
                } else {
                    $app->log->warn($e->getMessage());
                }
                $app->response->setBody( json_encode($testo) );
                $app->response->setStatus($status);

            }
        });


    });

}