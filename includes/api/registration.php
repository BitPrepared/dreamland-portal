<?php

use \stdClass;
use RedBean_Facade as R;
use Dreamland\Errori;
use Dreamland\Ruoli;


function registration($app){
	// Library group
    $app->group('/registrazione', function () use ($app) {

    	// Step Registrazione Capi Reparto
		$app->get('/info/:token', function ($token) use ($app) {

            $app->response->setStatus(500);
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

				if ( strpos($info->zonaNome, '\r') != 0 ){
					$info->zonaNome = str_replace('\r', '', $info->zonaNome);
				}
				
				$info->cc = findDatiCapoReparto($regione,$gruppo);

                // $email = $findToken['email'];
				$codicecensimento = intval($findToken['codicecensimento']);
				$_SESSION['portalCodiceCensimento'] = $codicecensimento;

				$app->response->setBody( json_encode($info) );
                $app->response->setStatus(200);

    		} catch(Exception $e) {
				$app->log->error($e->getMessage());
				$app->log->error($e->getTraceAsString());
				$testo = 'Dati Non Validi';
				if ( $e->getCode() == Errori::FORMATO_MAIL_NON_VALIDO ) $testo = $e->getMessage();
                $app->response->setBody( json_encode($testo) );
                $app->response->setStatus(412); //Precondition Failed
			}

        });

    	// Step Registrazione
        $app->post('/step1', function () use ($app) {

            $app->response->setStatus(500);
            $app->response->headers->set('Content-Type', 'application/json');
        	try{

				$body = $app->request->getBody();
				$obj_request = json_decode($body);
				$email = $obj_request->email;
				$codicecensimento = $obj_request->codicecensimento;
				$datanascita = $obj_request->datanascita; //20080506

				validate_email($app,$email);

				if ( strcmp($codicecensimento,'SANMARINO') == 0 ) {
					$app->log->info('Ragazzo di san marino : '.$email);
					$app->response->setBody( json_encode(array('message' => 'verrai contattato quanto prima all\'email '.$email)) );

					//FIXME DA FARE

				} else {

					//19990324 - Ricerca ASA E/G
					$egAsa = R::findOne('asa_anagrafica_eg',' codicesocio = ? and datanascita = ?',array($codicecensimento,$datanascita));
					if ( $egAsa == null ) {
						throw new Exception('Codice censimento '.$codicecensimento.' e Data Nascita : '.$datanascita .' non trovato', Errori::CODICE_CENSIMENTO_NOT_FOUND);
					}

//                    if (!DEBUG) {
//                        $mailgun = $app->config('mailgun');
//                        if ( isset($mailgun['pubkey']) ) {
//                            $mailgun_domain = $mailgun['domain'];
//                            $mailgun_key = $mailgun['key'];
//                            $mgClient = new Mailgun($mailgun_key);
//                            $mgClient->sendMessage($mailgun_domain,
//                                array(
//                                    'from' => 'Mailgun Sandbox <postmaster@sandbox8de4140d230448f49edbb569e9480eec.mailgun.org>',
//                                    'to' => 'Staff Dreamland <return2dreamland@gmail.com>',
//                                    'subject' => 'Richiesta iscrizione da parte di : ' . $email . ' ' . $codicecensimento,
//                                    'text' => 'Richiesta iscrizione da parte di : ' . $email . ' ' . $codicecensimento . ' step 1.',
//                                    'bcc' => 'Staff Dreamland <return2dreamland@gmail.com>',
//                                    'o:tag' => array('Registrazione', 'step1'))
//                            );
//                        }
//                    }

                    // RICERCA REGISTRAZIONE PRECEDENTE E/G
					$drm_registration_prev = R::findOne('registration',' codicecensimento = ? and type = ?',array($egAsa->codicesocio,'EG'));
					if ( $drm_registration_prev != null ) {
						$token = $drm_registration_prev->token;
						if ( $drm_registration_prev->completato ) {
							$app->log->info('Utente gia registrato');
                            throw new Exception('Utente gia registrato',Errori::ISCRIZIONE_GIA_ATTIVA);
						} else {
                            if ( $drm_registration_prev->email != $email ) {
                                $app->log->warn('Cambio mail in step1 da '.$drm_registration_prev->email.' a '.$email);
                                $token = generateToken(18);
                                $app->log->info('Generato token '.$token.' per '.$egAsa->codicesocio.' tipo E/G');
                                $drm_registration_prev->email = $email;
                                $drm_registration_prev->token = $token;
                            }
                            R::store($drm_registration_prev);
                        }
					} else {
						$token = generateToken(18);
						$app->log->info('Generato token '.$token.' per '.$egAsa->codicesocio.' tipo E/G');

						$drm_registration = R::dispense('registration');
						// $drm_registration->token = md5(uniqid(rand(), true));
						$drm_registration->token = $token;
						$drm_registration->codicecensimento = $egAsa->codicesocio;
                        $drm_registration->type = 'EG';
						$drm_registration->email = $email;
						$drm_registration->nome = $egAsa->nome;
						$drm_registration->cognome = $egAsa->cognome;
						$drm_registration->regione = $egAsa->creg;
						$drm_registration->zona = $egAsa->czona;
						$drm_registration->gruppo = $egAsa->ord;
                        $drm_registration->legame = null;
                        $drm_registration->completato = false;
						$drm_registration_id = R::store($drm_registration);

						$app->log->info('Nuova richiesta di registrazione '.$drm_registration_id.' tipo E/G');
					}

					$urlWithToken = "http://" . $app->request->headers->HTTP_HOST . $app->request->getRootUri().'/#/home/wizard?step=1&code='.$token;
					$to = array($email => $egAsa->nome.' '.strtoupper($egAsa->cognome[0]).'.');

					$message =  'Ciao '.$egAsa->nome.",\n";
					$message .= 'Hai richiesto di partecipare a Return To Dreamland'."\n";
					$message .= 'Per completare la tua registrazione visita il seguente link e completa la scheda di iscrizione: '."\n";
					$message .= 'Link: '.$urlWithToken;
					
					if ( !$app->mail->send($codicecensimento,$to, 'Richiesta registrazione Return To Dreamland', $message) ){
                        throw new Exception('Invio mail registrazione fallito',Errori::INVIO_MAIL_FALLITO);
					}

					$app->response->setBody('');
                    $app->response->setStatus(201);
				}

			} catch(Exception $e) {
				$testo = 'Dati Non Validi';
                $warn = false;
                $status = 412;
                switch ($e->getCode()) {
                    case Errori::FORMATO_MAIL_NON_VALIDO:
                        $testo = $e->getMessage();
                        $warn = true;
                        break;
                    case Errori::FORMATO_MAIL_NON_VALIDO_MAILGUN:
                        $testo = 'mail apparentemente non valida';
                        $warn = true;
                        break;
                    case Errori::CODICE_CENSIMENTO_NOT_FOUND:
                        $testo = 'codice censimento non valido';
                        $warn = true;
                        break;
                    case Errori::ISCRIZIONE_GIA_ATTIVA:
                        $testo = 'Utente gia registrato';
                        $warn = true;
                        break;
                    case Errori::INVIO_MAIL_FALLITO:
                        $testo = 'Invio mail fallito';
                        $status = 500;
                        $warn = false;
                        break;
                }
                if ( !$warn ) {
                    $app->log->error('Request body: '.$body);
                    $app->log->error($e->getMessage());
                    $app->log->error($e->getTraceAsString());
                } else {
                    $app->log->warn($e->getMessage().' body: '.$body);
                }
                $app->response->setBody( json_encode($testo) );
                $app->response->setStatus($status);
			}

        });

		// Step Registrazione2
		$app->post('/step2/:token', function ($token) use ($app) {

            $app->response->setStatus(500);
            $app->response->headers->set('Content-Type', 'application/json');
            $body = $app->request->getBody();

			try{

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
                $regioneNomeCorto = trim($findRegione['nome_corto']);

				$zona = $findToken['zona'];
				$findZona = R::findOne('asa_zone','czona = ? and cregione = ?',array($zona,$regione));
				$zonaNome = trim($findZona['nome']);
				
				$email = $findToken['email'];

				$codicecensimento = $findToken['codicecensimento'];
                $app->log->info('Registrazione Step 2 per '.$codicecensimento);

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

                //STATISTICA
                $specialitadisquadriglia = $obj_request->specialitadisquadriglia;
                $rinnovospecialitadisquadriglia = $obj_request->rinnovospecialitadisquadriglia;

				$punteggiosquadriglia = $obj_request->punteggiosquadriglia;

                if ( empty($nomesquadriglia) || empty ($ncomponenti) ) {
                    throw new Exception('Alcuni campi obbligatori sono vuoti',Errori::CAMPI_VUOTI);
                }

                $squadriglia = R::findOne('squadriglia','codicecensimento = ?', array($codicecensimento) );
                if ( null == $squadriglia ) {
                    $squadriglia = R::dispense('squadriglia');
                    $squadriglia->codicecensimento = $codicecensimento;
                    $squadriglia->componenti = intval($ncomponenti);
                    $squadriglia->specialita = intval($nspecialita);
                    $squadriglia->brevetti = intval($nbrevetti);
                    $squadriglia->conquistaspecsq = $specialitadisquadriglia;
                    $squadriglia->rinnovospecsq = $rinnovospecialitadisquadriglia;
                    $squadriglia->nomesquadriglia = $nomesquadriglia;
                    $squadriglia->gruppo = $gruppoNome;
                    R::store($squadriglia);
                    $app->log->info('Salvata nuova squadriglia assegnata a '.$codicecensimento);
                } else {
                    $squadriglia->componenti = intval($ncomponenti);
                    $squadriglia->specialita = intval($nspecialita);
                    $squadriglia->brevetti = intval($nbrevetti);
                    $squadriglia->conquistaspecsq = $specialitadisquadriglia;
                    $squadriglia->rinnovospecsq = $rinnovospecialitadisquadriglia;
                    R::store($squadriglia);
                    $app->log->info('Aggiornata squadriglia assegnata a '.$codicecensimento);
                }

				$ruolosquadriglia = $obj_request->ruolosq->code;
				if ( !Ruoli::isValidValue($ruolosquadriglia) ) {
					throw new Exception('Ruolo in squadriglia '.$ruolosquadriglia.' errato', Errori::RUOLO_IN_SQUADRIGLIA_ERRATO);
				}

				$app->log->info('Devo registrare un e/g con il ruolo di ' . Ruoli::fromValue($ruolosquadriglia));
                $app->wapi->setRequestOption('timeout',30);
                $profileUser = null;
                try {
                    $profileUser = $app->wapi->profiles->get( $codicecensimento );
                } catch( Requests_Exception_HTTP_500 $e) {
                    $app->log->error('Wordpress code : '.$e->getCode());
                    $app->log->error($e->getTraceAsString());
                    $app->log->error(var_export($e->getData()->body,true));
                    throw new Exception($e->getMessage(), Errori::WORDPRESS_PROBLEMA_CREAZIONE_UTENTE);
                } catch ( Requests_Exception_HTTP_404 $e ) {
                    $app->log->info('Utente non presente su wordpress, procedo con la registrazione.');
                } catch ( Requests_Exception_HTTP_403 $e ) {
                    $app->log->error('Wordpress code : '.$e->getCode());
                    $app->log->error($e->getTraceAsString());
                    $app->log->error(var_export($e->getData()->body,true));
                    throw new Exception($e->getMessage(), Errori::WORDPRESS_LOGIN_REQUIRED);
                }

                if ( null != $profileUser ) {
                    $app->log->info('Utente in wordpress gia presente '.$profileUser->user_id.' quindi setto a completata l\'iscrizione. ');
                    $findToken->completato = true;
                    R::store($findToken);
                    throw new Exception('Utente già registrato', Errori::WORDPRESS_UTENTE_GIA_PRESENTE);
                }

                $app->log->debug('Iscrizione capo reparto');
                $capoRepartoAttualeArray = findDatiCapoReparto($regione,$gruppo,$codicecensimento);

                if ( count($capoRepartoAttualeArray) > 0 ) {
                    $app->log->warn('Capo reparto gia presente per questo ragazzo '.$codicecensimento);
                } else {

                    validate_email($app, $ccemail);

                    // CAPO REPARTO
                    $datiCapoReparto = findDatiCapoReparto($regione, $gruppo);
                    $emailCapoReparto = $datiCapoReparto[0]->email;
                    $nomeCapoReparto = $datiCapoReparto[0]->nome;
                    $cognomeCapoReparto = $datiCapoReparto[0]->cognome;

                    //VERIFICA CON DATI MANDATI DAL RAGAZZO
                    if (strcmp($datiCapoReparto[0]->nome, $ccnome) != 0
                        || strcmp($datiCapoReparto[0]->cognome, $cccognome) != 0
                    ) {
                        $app->log->info('Capo Reparto cambiato nuovo :' . $ccnome . ' ' . $cccognome . ' ' . $ccemail);
                        $emailCapoReparto = $ccemail;
                        $cognomeCapoReparto = $cccognome;
                        $nomeCapoReparto = $ccnome;
                    }

                    $findTokenRegistrationCC = R::findOne('registration', ' email = ? and type = ?', array($emailCapoReparto, 'CC'));
                    if ($findTokenRegistrationCC != null) {
                        $token = $findTokenRegistrationCC['token'];
                        $drm_registration = R::load('registration', $findTokenRegistrationCC['id']);
                        $app->log->info('Capo Reparto trovato :' . $drm_registration->nome . ' ' . $drm_registration->cognome . ' ' . $drm_registration->email);
                    } else {
                        $token = generateToken(18);
                        $app->log->info('Generato token ' . $token . ' per ' . $emailCapoReparto);
                        $drm_registration = R::dispense('registration');
                        // $drm_registration->token = md5(uniqid(rand(), true));
                        $drm_registration->token = $token;
                        $drm_registration->completato = false;
                    }

                    $drm_registration->email = $emailCapoReparto;
                    $drm_registration->nome = $nomeCapoReparto;
                    $drm_registration->cognome = $cognomeCapoReparto;

                    if ( empty($nomeCapoReparto) || empty ($cognomeCapoReparto) ) {
                        throw new Exception('Alcuni campi obbligatori sono vuoti',Errori::CAMPI_VUOTI);
                    }

                    $drm_registration->type = 'CC';
                    $drm_registration->regione = $regione;
                    $drm_registration->zona = $zona;
                    $drm_registration->gruppo = $gruppo;
                    $drm_registration->legame = null; //ex codicecensimento
                    $drm_registration_id = R::store($drm_registration);

                    legaCapoRepartoToRagazzo($emailCapoReparto, $codicecensimento);

                    $app->log->debug('Registrato capo reparto con rowId : ' . $drm_registration_id);

                    $wordpress = $app->config('wordpress');
                    $urlAdminDreamers = $wordpress['url'] . 'wp-admin/admin.php?page=dreamers';
                    $urlWithToken = "http://" . $app->request->headers->HTTP_HOST . $app->request->getRootUri() . '/#/home/reg/cc?code=' . $token;
                    $to = array($emailCapoReparto => $nomeCapoReparto . ' ' . strtoupper($cognomeCapoReparto[0]) . '.');

                    $message = 'Ciao ' . $nomeCapoReparto . ",\n";
                    $message .= 'La squadriglia ' . $nomesquadriglia . ' ha richiesto di partecipare a Return To Dreamland' . "\n";
                    if (!$drm_registration->completato) {
                        $message .= 'Se non hai gia\' completato l\'iscrizione sul portale, segui questo link: ' . "\n";
                        $message .= 'Link: ' . "\n" . $urlWithToken . "\n";
                        $message .= 'Una volta completata la registrazione potrai autorizzare le tue squadriglie a partecipare.' . "\n";
                    }
                    $message .= 'Link pagine autorizzazioni : ' . "\n" . $urlAdminDreamers . "\n";

                    if (!$app->mail->send($codicecensimento, $to, 'Richiesta registrazione Return To Dreamland', $message)) {
                        throw new Exception('Invio mail capo reparto fallita', Errori::INVIO_MAIL_FALLITO);
                    }

                }

                $app->log->info('Procedo a creare nuovo utente su wordpress');

                $newUserRequest = array(
                    'username' => $codicecensimento,
                    'password' => 'DA GENERARE RANDOM',
                    'first_name' => 'Sq. '.$nomesquadriglia,
                    'last_name' => 'Gruppo '.$gruppoNome,
                    'nickname' => 'Sq. '.$nomesquadriglia. ' Gruppo '.$gruppoNome,
                    'email' => $email,
                    'meta' => array(
                        'nome' => $nome,
                        'cognome' => $cognome,
                        'squadriglia' => $nomesquadriglia,
                        'group' => $gruppo,
                        'groupDisplay' => $gruppoNome,
                        'zone' => $zona,
                        'zoneDisplay' => $zonaNome,
                        'region' => $regione,
                        'regionDisplay' => $regioneNome,
                        'regionShort' => $regioneNomeCorto,
                        'codicecensimento' => $codicecensimento,
                        'numerocomponenti' => $ncomponenti,
                        'nspecialita' => $nspecialita,
                        'nbrevetti' => $nbrevetti,
                        'punteggio' => $punteggiosquadriglia,
                        'ruolocensimento' => 'eg'
                    )
                );

                $newUser = null;
                try {
                    $newUser = $app->wapi->users->create( $newUserRequest );
                } catch( Requests_Exception_HTTP_500 $e) {
                    $app->log->error('Wordpress code : '.$e->getCode());
                    $app->log->error($e->getTraceAsString());
                    $app->log->error(var_export($e->getData()->body,true));
                    throw new Exception($e->getMessage(), Errori::WORDPRESS_PROBLEMA_CREAZIONE_UTENTE);
                } catch ( Requests_Exception_HTTP_404 $e ) {
                    $app->log->error('Wordpress code : '.$e->getCode());
                    $app->log->error($e->getTraceAsString());
                    throw new Exception($e->getMessage(), Errori::WORDPRESS_NOT_FOUND);
                } catch ( Requests_Exception_HTTP_403 $e ) {
                     $app->log->error('Wordpress code : '.$e->getCode());
                     $app->log->error($e->getTraceAsString());
                     $app->log->error(var_export($e->getData()->body,true));
                     throw new Exception($e->getMessage(), Errori::WORDPRESS_LOGIN_REQUIRED);
                }

                $app->log->info('Creato utente in wordpress '.$newUser->ID);
                $findToken->completato = true;
                R::store($findToken);

                $app->response->setBody('');
                $app->response->setStatus(200);

                $app->log->debug('Completata registrazione token '.$token);

			} catch(Exception $e) {

                $testo = 'Dati Non Validi';
                $warn = false;
                $status = 412;
                switch ($e->getCode()) {
                    case Errori::FORMATO_MAIL_NON_VALIDO:
                        $testo = $e->getMessage();
                        $warn = true;
                        break;
                    case Errori::WORDPRESS_PROBLEMA_CREAZIONE_UTENTE:
                        $testo = 'errore creazione utente';
                        $warn = false;
                        $status = 500;
                        break;
                    case Errori::WORDPRESS_NOT_FOUND:
                        $testo = 'Configurazione wordpress errata';
                        $warn = false;
                        $status = 500;
                        break;
                    case Errori::WORDPRESS_UTENTE_GIA_PRESENTE:
                        $testo = 'Utente gia registrato';
                        $warn = true;
                        break;
                    case Errori::INVIO_MAIL_FALLITO:
                        $testo = 'Invio mail fallito';
                        $warn = false;
                        $status = 500;
                        break;
                    case Errori::CAMPI_VUOTI:
                        $testo = 'Alcuni campi obbligatori sono vuoti';
                        $warn = true;
                        $status = 412;
                        break;
                }
                if ( !$warn ) {
                    $app->log->error('Request body: '.$body);
                    $app->log->error($e->getMessage());
                    $app->log->error($e->getTraceAsString());
                } else {
                    $app->log->warn($e->getMessage().' body: '.$body);
                }
                $app->response->setBody( json_encode($testo) );
                $app->response->setStatus($status);
			}

        });

		// Step Registrazione Capi Reparto
		$app->post('/stepc/:token', function ($token) use ($app) {

            $app->response->setStatus(500);
            $app->response->headers->set('Content-Type', 'application/json');
			try{

				// devo cercare $token nel db e recuperare le varie informazioni
				$findToken = R::findOne('registration',' token = ?',array($token));
				if ( null == $findToken ){
					throw new Exception('Token '.$token.' non valido', Errori::PORTAL_INVALID_TOKEN_STEP);
				}

				if ( $findToken['completato'] ) {
					$app->log->warn('token gia usato');
					throw new Exception('Token '.$token.' non valido', Errori::PORTAL_INVALID_TOKEN_STEP);
				}

                $body = $app->request->getBody();
                $obj_request = json_decode($body);
                $codicecensimento = $obj_request->codicecensimento;

				$nome = $findToken['nome'];
				$cognome = $findToken['cognome'];
				$gruppo = $findToken['gruppo'];

				$findGruppo = R::findOne('asa_gruppi','ord = ?',array($gruppo));
				$gruppoNome = trim($findGruppo['nome']);

				$regione = $findToken['regione'];
				$findRegione = R::findOne('asa_regioni','cregione = ?',array($regione));
				$regioneNome = trim($findRegione['nome']);
                $regioneNomeCorto = trim($findRegione['nome_corto']);

				$zona = $findToken['zona'];
				$findZona = R::findOne('asa_zone','czona = ? and cregione = ?',array($zona,$regione));
				$zonaNome = trim($findZona['nome']);
				
				$email = $findToken['email'];

				$app->log->info('Devo registrare un cc');

				$wordpress = $app->config('wordpress');

				$url = $wordpress['url'].'wp-json';

				$app->log->debug('Mi connetto a '.$url);

				$newUserRequest = array( 
					'username' => $codicecensimento,
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
                        'regionShort' => $regioneNomeCorto,
						'codicecensimento' => $codicecensimento,
						'ruolocensimento' => 'cr'
					)
				);


				 try {

                    $app->wapi->setRequestOption('timeout',30);
				 	$newUser = $app->wapi->users->create( $newUserRequest );

				 	$app->log->info('Creato utente in wordpress '.$newUser->ID);

				 	$findToken->completato = true;
                    $findToken->codicecensimento = $codicecensimento;
				 	R::store($findToken);

                     $app->response->setStatus(200);

                } catch( Requests_Exception_HTTP_500 $e) {
                    $app->log->error('Wordpress code : '.$e->getCode());
                    $app->log->error($e->getTraceAsString());
                    $app->log->error(var_export($e->getData()->body,true));
                    throw new Exception($e->getMessage(), Errori::WORDPRESS_PROBLEMA_CREAZIONE_UTENTE);
                } catch ( Requests_Exception_HTTP_404 $e ) {
                    $app->log->error('Wordpress code : '.$e->getCode());
                     $app->log->error(var_export($e->getData()->body,true));
                    $app->log->error($e->getTraceAsString());
                    throw new Exception($e->getMessage(), Errori::WORDPRESS_NOT_FOUND);
                } catch ( Requests_Exception_HTTP_403 $e ) {
                    $app->log->error('Wordpress code : '.$e->getCode());
                    $app->log->error($e->getTraceAsString());
                    $app->log->error(var_export($e->getData()->body,true));
                    throw new Exception($e->getMessage(), Errori::WORDPRESS_LOGIN_REQUIRED);
                }

//            } catch( Requests_Exception_HTTP_500 $e) {
//                $app->log->error('Wordpress code : '.$e->getCode());
//                $app->log->error($e->getTraceAsString());
//                throw new Exception($e->getMessage(), Errori::WORDPRESS_PROBLEMA_CREAZIONE_UTENTE);
//            } catch ( Requests_Exception_HTTP_404 $e2 ) {
//                $app->log->error('Wordpress code : '.$e2->getCode());
//                $app->log->error($e2->getTraceAsString());
//                throw new Exception($e2->getMessage(), Errori::WORDPRESS_NOT_FOUND);
//            }

    		} catch(Exception $e) {
                $testo = 'Dati Non Validi';
                $warn = false;
                switch ($e->getCode()) {
                    case Errori::FORMATO_MAIL_NON_VALIDO:
                        $testo = $e->getMessage();
                        $warn = true;
                        break;
                    case Errori::PORTAL_INVALID_TOKEN_STEP:
                        $testo = 'Token non piu attivo';
                        $warn = true;
                        break;
                }
                if ( !$warn ) {
                    $app->log->error('Request body: '.$body);
                    $app->log->error($e->getMessage());
                    $app->log->error($e->getTraceAsString());
                } else {
                    $app->log->warn($e->getMessage().' body: '.$body);
                }
                $app->response->setBody( json_encode($testo) );
                $app->response->setStatus(412);
			}

        });

    });
}