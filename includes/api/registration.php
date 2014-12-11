<?php

use \stdClass;
use RedBean_Facade as R;
use Dreamland\Errori;
use Dreamland\Ruoli;
use Mailgun\Mailgun;
use BitPrepared\Wordpress\ApiClient;

function registration($app){
	// Library group
    $app->group('/registrazione', function () use ($app) {

    	// Step Registrazione Capi Reparto
		$app->get('/info/:token', function ($token) use ($app) {

			$app->response->headers->set('Content-Type', 'application/json');
			try{
				
				// devo cercare $token nel db e recuperare le varie informazioni
				$findToken = R::findOne('registration',' token = ?',array($token));
				if ( null == $findToken ){
					throw new Exception('Token '.$token.' non valido', Errori::PORTAL_INVALID_TOKEN_STEP);
				}

				$info = new stdClass;
                $info->completato = (bool)$findToken['completato'];
				$info->nome = $findToken['nome'];
				$info->cognome = $findToken['cognome'];

				$gruppo = $findToken['gruppo'];
				$findGruppo = R::findOne('asa_gruppi','ord = ?',array($gruppo));
				$info->gruppoNome = trim($findGruppo['nome']);

				$regione = $findToken['regione'];
				$findRegione = R::findOne('asa_regioni','cregione = ?',array($regione));
				$info->regioneNome = trim($findRegione['nome']);

				$zona = $findToken['zona'];
				$findZona = R::findOne('asa_zone','czona = ? and cregione = ?',array($zona,$regione));
				$info->zonaNome = trim($findZona['nome']);

				// VANNO PULITI I DATI
				if ( strpos($info->zonaNome, '\r') != 0 ){
					$info->zonaNome = str_replace('\r', '', $info->zonaNome);
				}
				
				$info->cc = findDatiCapoReparto($regione,$gruppo);

				$email = $findToken['email'];
				$codicecensimento = intval($findToken['codicecensimento']);
				$_SESSION['portalCodiceCensimento'] = $codicecensimento;

				$app->response->setBody( json_encode($info) );

    		} catch(Exception $e) {
				$app->log->error($e->getMessage());
				$app->log->error($e->getTraceAsString());
				$testo = 'Dati Non Validi';
				if ( $e->getCode() == Errori::FORMATO_MAIL_NON_VALIDO ) $testo = $e->getMessage();
				$app->halt(412, json_encode($testo)); //Precondition Failed
			}

        });

    	// Step Registrazione
        $app->post('/step1', function () use ($app) {

        	$app->response->headers->set('Content-Type', 'application/json');
        	try{

				$body = $app->request->getBody();
				$obj_request = json_decode($body);
				$email = $obj_request->email;
				$codicecensimento = $obj_request->codicecensimento;
				$datanascita = $obj_request->datanascita;

				validate_email($app,$email);

				if ( strcmp($codicecensimento,'SANMARINO') == 0 ) {
					$app->log->info('Ragazzo di san marino : '.$email);
					$app->response->setBody( json_encode(array('message' => 'verrai contattato quanto prima all\'email '.$email)) );

					// DA FARE

				} else {

					//19990324 - Ricerca ASA E/G
					$find = R::findOne('asa_anagrafica_eg',' codicesocio = ? and datanascita = ?',array($codicecensimento,$datanascita));
					if ( $find == null ) {
						throw new Exception('Codice censimento '.$codicecensimento.' e Data Nascita : '.$datanascita .' non trovato', Errori::CODICE_CENSIMENTO_NOT_FOUND);
					}

					$mailgun = $app->config('mailgun');
					$mailgun_domain = $mailgun['domain'];
					$mailgun_key = $mailgun['key'];
					$mgClient = new Mailgun($mailgun_key);
					$result = $mgClient->sendMessage("$mailgun_domain",
						array(
							'from'    => 'Mailgun Sandbox <postmaster@sandbox8de4140d230448f49edbb569e9480eec.mailgun.org>',
				        'to'      => 'Staff Dreamland <return2dreamland@gmail.com>',
				        'subject' => 'Richiesta iscrizione',
				        'text'    => 'Richiesta iscrizione da parte di : '.$email.' '.$codicecensimento,
							'bcc'     => 'Staff Dreamland <return2dreamland@gmail.com>',
							'o:tag'   => array('Registrazione','step1'))
						);

                    // RICERCA REGISTRAZIONE PRECEDENTE E/G
					$token = '';
					$findToken = R::findOne('registration',' codicecensimento = ? and type = ?',array($find['codicesocio'],'EG'));
					if ( $findToken != null ) {
						$token = $findToken['token'];
						if ( $findToken['completato'] ) {
							$app->log->info('Utente gia registrato');
							$app->redirect('/error');
						}
					} else {
						$token = generateToken(18);
						$app->log->info('Generato token '.$token.' per '.$find['codicesocio'].' tipo E/G');

						$drm_registration = R::dispense('registration');
						// $drm_registration->token = md5(uniqid(rand(), true));
						$drm_registration->token = $token;
						$drm_registration->codicecensimento = $find['codicesocio'];
                        $drm_registration->type = 'EG';
						$drm_registration->email = $email;
						$drm_registration->nome = $find['nome'];
						$drm_registration->cognome = $find['cognome'];
						$drm_registration->regione = $find['creg'];
						$drm_registration->zona = $find['czona'];
						$drm_registration->gruppo = $find['ord'];
                        $drm_registration->completato = false;
						$drm_registration_id = R::store($drm_registration);

						$app->log->info('Nuova richiesta di registrazione '.$drm_registration_id.' tipo E/G');
					}

					$urlWithToken = "http://" . $_SERVER['HTTP_HOST']. $app->request->getRootUri().'/#/home/wizard?step=1&code='.$token;
					$to = array($email => $find['nome'].' '.strtoupper($find['cognome'][0]).'.');

					$message =  'Ciao '.$find['nome'].",\n";
					$message .= 'Hai richiesto di partecipare a Return To Dreamland'."\n";
					$message .= 'Per completare la tua registrazione visita il seguente link e completa la scheda di iscrizione: '."\n";
					$message .= 'Link: '.$urlWithToken;
					
					if ( !dream_mail($app, $to, 'Richiesta registrazione Return To Dreamland', $message) ){
						$app->halt(412, json_encode('Invio mail fallito')); //Precondition Failed
					}

					// json_encode(array('email' => $email,'codcens' => $codicecensimento))
					$app->response->setBody( json_encode('ok') );

				}

			} catch(Exception $e) {
				$app->log->error($e->getMessage());
				$testo = 'Dati Non Validi';
				if ( $e->getCode() == Errori::FORMATO_MAIL_NON_VALIDO ) $testo = $e->getMessage();
				elseif ( $e->getCode() == Errori::FORMATO_MAIL_NON_VALIDO_MAILGUN ) $testo = 'mail apparentemente non valida';
				elseif ( $e->getCode() == Errori::CODICE_CENSIMENTO_NOT_FOUND ) $testo = 'codice censimento non valido';
				else $app->log->error($e->getTraceAsString());
				$app->halt(412, json_encode($testo)); //Precondition Failed
			}

        });

		// Step Registrazione2
		$app->post('/step2/:token', function ($token) use ($app) {

			$app->response->headers->set('Content-Type', 'application/json');
			try{

				// devo cercare $token nel db e recuperare le varie informazioni
				$findToken = R::findOne('registration',' token = ? and completato = ?',array($token,0));
				if ( null == $findToken ){
					throw new Exception('Token '.$token.' non valido', Errori::PORTAL_INVALID_TOKEN_STEP);
				} 

				$nome = $findToken['nome'];
				$cognome = $findToken['cognome'];
				$gruppo = $findToken['gruppo'];

				$findGruppo = R::findOne('asa_gruppi','ord = ?',array($gruppo));
				$gruppoNome = trim($findGruppo['nome']);

				$regione = $findToken['regione'];
				$findRegione = R::findOne('asa_regioni','cregione = ?',array($regione));
				$regioneNome = trim($findRegione['nome']);

				$zona = $findToken['zona'];
				$findZona = R::findOne('asa_zone','czona = ? and cregione = ?',array($zona,$regione));
				$zonaNome = trim($findZona['nome']);
				
				$email = $findToken['email'];

				$codicecensimento = $findToken['codicecensimento'];

				$body = $app->request->getBody();
				$obj_request = json_decode($body);
				
				// CAPO REPARTO
				$ccnome = $obj_request->nomecaporeparto;
				$cccognome = $obj_request->cognomecaporeparto;
				$ccemail = $obj_request->emailcaporeparto;

				// CENSIMENTO SQ
				$nomesquadriglia = $obj_request->nomesq;

				// PUNTEGGIO
				$ncomponenti = $obj_request->numerosquadriglieri;
				$nspecialita = $obj_request->specialitasquadriglieri;
				$nbrevetti = $obj_request->brevettisquadriglieri;

				$punteggiosquadriglia = $obj_request->punteggiosquadriglia;
				
				$ruolosquadriglia = $obj_request->ruolosq->code;
				if ( !Ruoli::isValidValue($ruolosquadriglia) ) {
					throw new Exception('Ruolo in squadriglia '.$ruolosquadriglia.' errato', Errori::RUOLO_IN_SQUADRIGLIA_ERRATO);
				}

				$app->log->info('Devo registrare un e/g con il ruolo di ' . Ruoli::fromValue($ruolosquadriglia));

				validate_email($app,$ccemail);

				$newUser = null;

				$app->log->debug('Iscrizione capo reparto');

				// CAPO REPARTO
				// $ccnome
				// $cccognome
				// $ccemail

				$datiCapoReparto = findDatiCapoReparto($regione,$gruppo);
				$emailCapoReparto = $datiCapoReparto[0]->email;
				$nomeCapoReparto = $datiCapoReparto[0]->nome;
				$cognomeCapoReparto = $datiCapoReparto[0]->cognome;

				//VERIFICA CON DATI MANDATI DAL RAGAZZO
				if ( strcmp($datiCapoReparto[0]->nome, $ccnome) != 0 
					|| strcmp($datiCapoReparto[0]->cognome, $cccognome) != 0 ) 
				{
					$app->log->info('Capo Reparto cambiato nuovo :'.$ccnome.' '.$cccognome. ' ' .$ccemail);
					$emailCapoReparto = $ccemail;
					$cognomeCapoReparto = $cccognome;
					$nomeCapoReparto = $ccnome;
				}

				$token = '';
				$findTokenRegistrationCC = R::findOne('registration',' email = ? and type = ?',array($emailCapoReparto,'CC'));
				if ( $findTokenRegistrationCC != null ) {
					$token = $findTokenRegistrationCC['token'];
				} else {
					$token = generateToken(18);
					$app->log->info('Generato token '.$token.' per '.$emailCapoReparto);

					$drm_registration = R::dispense('registration');
					// $drm_registration->token = md5(uniqid(rand(), true));
					$drm_registration->token = $token;

					// VA RIMOSSO CODICE CENSIMENTO DA DB

					$drm_registration->email = $emailCapoReparto;
					$drm_registration->nome = $nomeCapoReparto;
                    $drm_registration->type = 'CC';
					$drm_registration->cognome = $cognomeCapoReparto;
					$drm_registration->regione = $regione;
					$drm_registration->zona = $zona;
					$drm_registration->gruppo = $gruppo;
                    $drm_registration->completato = false;
					$drm_registration_id = R::store($drm_registration);

					$app->log->info('Nuova richiesta di registrazione capo reparto '.$drm_registration_id);
				}

                $wordpress = $app->config('wordpress');

				$urlAdminDreamers = $wordpress['url'] . 'wp-admin/admin.php?page=dreamers';

				$urlWithToken = "http://" . $_SERVER['HTTP_HOST']. $app->request->getRootUri().'/api/registrazione/stepc/'.$token;
				$to = array($emailCapoReparto => $nomeCapoReparto.' '.strtoupper($cognomeCapoReparto[0]).'.');

				$message =  'Ciao '.$nomeCapoReparto.",\n";
				$message .= 'Una tua squadriglia ha richiesto di partecipare a Return To Dreamland'."\n";
				$message .= 'Se non hai gia\' completato l\'iscrizione sul portale, segui questo link: '."\n";
				$message .= 'Link: '."\n".$urlWithToken."\n";
				$message .= 'Una volta completata la registrazione potrai autorizzare le tue squadriglie a partecipare.'."\n";
				$message .= 'Link pagine autorizzazioni : '."\n".$urlAdminDreamers."\n";
				
				if ( !dream_mail($app, $to, 'Richiesta registrazione Return To Dreamland', $message) ){
                    $app->log->error('Invio mail capo reparto fallita');
					$app->halt(412, json_encode('Invio mail capo reparto fallita')); //Precondition Failed
				}

                $url = $wordpress['url'].'wp-json';
                $app->log->debug('Mi connettero a '.$url);

                $newUserRequest = array( 
						'username' => $codicecensimento,
						'password' => 'DA GENERARE RANDOM',
						'first_name' => 'Sq. '.$nomesquadriglia,
						'last_name' => 'Gruppo '.$gruppoNome,
						'nickname' => 'Sq. '.$nomesquadriglia. ' Gruppo '.$gruppoNome,
						'email' => $email,
						'meta' => array(
							'squadriglia' => $nomesquadriglia,
							'group' => $gruppo,
							'groupDisplay' => $gruppoNome,
							'zone' => $zona,
							'zoneDisplay' => $zonaNome,
							'region' => $regione,
							'regionDisplay' => $regioneNome,
							'codicecensimento' => $codicecensimento,
							'numerocomponenti' => $ncomponenti,
							'nspecialita' => $nspecialita,
							'nbrevetti' => $nbrevetti,
							'punteggio' => $punteggiosquadriglia,
							'ruolocensimento' => 'eg'
						)
					);

    //             try {
					
				// 	$wapi = new ApiClient($url, $wordpress['username'], $wordpress['password']);
	   //              $wapi->setRequestOption('timeout',5);
				// 	$newUser = $wapi->users->create(  );

				// } catch( Requests_Exception_HTTP_500 $e) {
				// 	$app->log->error('Wordpress code : '.$e->getCode());
				// 	$app->log->error($e->getTraceAsString());
				// 	// throw new Exception($e->getMessage(), Errori::WORDPRESS_PROBLEMA_CREAZIONE_UTENTE);

				$_SESSION['portal'] = array();
				$_SESSION['portal']['request'] = $newUserRequest;

                $findToken->completato = true;
				R::store($findToken);
				
				$app->log->info('Completata registrazione token '.$token);

				// } catch ( Requests_Exception_HTTP_404 $e2 ) {
				// 	$app->log->error('Wordpress code : '.$e2->getCode());
				// 	$app->log->error($e2->getTraceAsString());
				// 	throw new Exception($e2->getMessage(), Errori::WORDPRESS_NOT_FOUND);
				// } 

				// $_SESSION['wordpressUserId'] = $newUser->ID;

				// $app->log->info('Creato utente in wordpress '.$newUser->ID);

				// $findToken->completato = true;
				// R::store($findToken);

				// $app->response->setBody( json_encode('ok') );

			} catch(Exception $e) {
				$app->log->error($e->getMessage());
				$testo = 'Dati Non Validi';
				if ( $e->getCode() == Errori::FORMATO_MAIL_NON_VALIDO ) $testo = $e->getMessage();
				else $app->log->error($e->getTraceAsString());
				$app->halt(412, json_encode($testo)); //Precondition Failed
			}

			$app->response->setBody( json_encode($url.'/portal/pk') );

        });

		// Step Registrazione Capi Reparto
		$app->get('/stepc/:token', function ($token) use ($app) {

			try{
				

				// QUI BISOGNEREBBE CHIEDERE IL CODICE CENSIMENTO

				// devo cercare $token nel db e recuperare le varie informazioni
				$findToken = R::findOne('registration',' token = ?',array($token));
				if ( null == $findToken ){
					throw new Exception('Token '.$token.' non valido', Errori::PORTAL_INVALID_TOKEN_STEP);
				}

				if ( $findToken['completato'] ) {
					$app->log->warn('token gia usato');
					throw new Exception('Token '.$token.' non valido', Errori::PORTAL_INVALID_TOKEN_STEP);
				}

				$nome = $findToken['nome'];
				$cognome = $findToken['cognome'];
				$gruppo = $findToken['gruppo'];

				$findGruppo = R::findOne('asa_gruppi','ord = ?',array($gruppo));
				$gruppoNome = trim($findGruppo['nome']);

				$regione = $findToken['regione'];
				$findRegione = R::findOne('asa_regioni','cregione = ?',array($regione));
				$regioneNome = trim($findRegione['nome']);

				$zona = $findToken['zona'];
				$findZona = R::findOne('asa_zone','czona = ? and cregione = ?',array($zona,$regione));
				$zonaNome = trim($findZona['nome']);
				
				$email = $findToken['email'];

				// AVENDO RIOMOSSO IL CODICE CENSIMENTO VA TROVATA UNA ALTERNATIVA PER OTTENERLO
				$codicecensimento = 000000; //$findToken['codicecensimento'];

				$app->log->info('Devo registrare un cc');

				$wordpress = $app->config('wordpress');

				$url = $wordpress['url'].'wp-json';

				$app->log->debug('Mi connetto a '.$url);

				$newUserRequest = array( 
					'username' => $email,
					'password' => 'DA GENERARE RANDOM',
					'first_name' => $nome,
					'last_name' => $cognome,
					'nickname' => $nome . ' ' . $cognome,
					'email' => $email,
					'meta' => array(
						'group' => $gruppo,
						'groupDisplay' => $gruppoNome,
						'zone' => $zona,
						'zoneDisplay' => $zonaNome,
						'region' => $regione,
						'regionDisplay' => $regioneNome,
						'codicecensimento' => $codicecensimento,
						'ruolocensimento' => 'cr'
					)
				);

				// $newUser = null;
				// try {
				// 	$wapi = new ApiClient($url, $wordpress['username'], $wordpress['password']);
    //                 $wapi->setRequestOption('timeout',30);
				// 	$newUser = $wapi->users->create( $newUserRequest );
				// 	// SE E' GIA CREATO DA 500... 
				// 	//echo 'creato '.$newUser->ID."\n";
				// 	// redirect
				// 	$_SESSION['wordpressUserId'] = $newUser->ID;

				// 	$app->log->info('Creato utente in wordpress '.$newUser->ID);

				// 	$findToken->completato = true;
				// 	R::store($findToken);
					
				// 	$app->redirect($app->request->getRootUri().'/page/success_iscrizione'); 

				// } catch( Requests_Exception_HTTP_500 $e) {
				// 	$app->log->error('Wordpress code : '.$e->getCode());
				// 	$app->log->error($e->getTraceAsString());
				// 	throw new Exception($e->getMessage(), Errori::WORDPRESS_PROBLEMA_CREAZIONE_UTENTE);
				// } catch ( Requests_Exception_HTTP_404 $e2 ) {
				// 	$app->log->error('Wordpress code : '.$e2->getCode());
				// 	$app->log->error($e2->getTraceAsString());
				// 	throw new Exception($e2->getMessage(), Errori::WORDPRESS_NOT_FOUND);
				// } 

				$_SESSION['portal'] = array();
				$_SESSION['portal']['request'] = $newUserRequest;

				$findToken->completato = true;
				R::store($findToken);
				$app->log->info('Completata registrazione token '.$token);
				

				// $utenteCreato = $wapi->users->get($newUser->ID,true);

    		} catch(Exception $e) {
				$app->log->error($e->getMessage());
				$testo = 'Dati Non Validi';
				if ( $e->getCode() == Errori::FORMATO_MAIL_NON_VALIDO ) $testo = $e->getMessage();
				if ( $e->getCode() == Errori::PORTAL_INVALID_TOKEN_STEP ) $testo = 'Token non piu attivo.';
				else $app->log->error($e->getTraceAsString());
				$app->halt(412, json_encode($testo)); //Precondition Failed
			}

			$app->redirect($url.'/portal/pk');

        });

		// Step Registrazione Referente Regionale FAKE (http://10.143.90.74:8080/portal/api/registrazione/referenteregionale)
		$app->get('/referenteregionale', function () use ($app) {

			try{

				$wordpress = $app->config('wordpress');

				$url = $wordpress['url'].'wp-json';

				$newUserRequest = array( 
					'username' => 'referenteregionale',
					'password' => 'DA GENERARE RANDOM',
					'first_name' => 'referente',
					'last_name' => 'regionale',
					'nickname' => 'refreg',
					'email' => 'orset.to@gmail.com',
					'meta' => array(
						'group' => 2241,
						'groupDisplay' => 'BOLOGNA 13',
						'zone' => '1',
						'zoneDisplay' => 'BOLOGNA',
						'region' => 'F',
						'regionDisplay' => 'Emilia Romagna',
						'codicecensimento' => '123098423',
						'ruolocensimento' => 'rr'
					)
				);

				$_SESSION['portal'] = array();
				$_SESSION['portal']['request'] = $newUserRequest;

			} catch(Exception $e) {
				$app->log->error($e->getMessage());
				$testo = 'Dati Non Validi';
				if ( $e->getCode() == Errori::FORMATO_MAIL_NON_VALIDO ) $testo = $e->getMessage();
				if ( $e->getCode() == Errori::PORTAL_INVALID_TOKEN_STEP ) $testo = 'Token non piu attivo.';
				else $app->log->error($e->getTraceAsString());
				$app->halt(412, json_encode($testo)); //Precondition Failed
			}

			$app->redirect($url.'/portal/pk');

		});

    });
}