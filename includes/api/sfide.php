<?php

use RedBean_Facade as R;
use Dreamland\Errori;

function sfide($app) {

	$app->group('/sfide', function () use ($app) {
		
		$app->get('/:id', function ($sfida_id) use ($app) {
            $app->response->setStatus(500);
            $app->response->headers->set('Content-Type', 'application/json');
			try {

			    if ( !isset($_SESSION['wordpress']) ) {
				    throw new Exception('Wordpress login not found', Errori::WORDPRESS_LOGIN_REQUIRED);
			    }

                $wordpress = $_SESSION['wordpress'];
                $codicecensimento = $wordpress['user_info']['codicecensimento'];

				$drm_iscrizione_sfida = R::findOne('iscrizionesfida','codicecensimento = ? and idsfida = ?', array($codicecensimento,$sfida_id) );
				if ( null != $drm_iscrizione_sfida ) {

                    $categoria_sfida = null;
                    if ( $drm_iscrizione_sfida->categoriasfida != null) {
                        $categoria_sfida = new \stdClass();
                        $categoria_sfida->desc = $drm_iscrizione_sfida->categoriasfida;
                        $categoria_sfida->code = -1;
                    }

					$x = array(
						'idsfida' => intval($drm_iscrizione_sfida->idsfida),
						'titolo' => $drm_iscrizione_sfida->titolo,
                        'tipo' => $drm_iscrizione_sfida->tipo,
						'permalink' => $drm_iscrizione_sfida->permalink,
                        'categoria' => $categoria_sfida,
						'codicecensimento' => intval($drm_iscrizione_sfida->codicecensimento),
						'startpunteggio' => intval($drm_iscrizione_sfida->startpunteggio),
						'obiettivopunteggio' => intval($drm_iscrizione_sfida->obiettivopunteggio),
						'endpunteggio' => intval($drm_iscrizione_sfida->endpunteggio),
						'sfidaspeciale' => (bool)$drm_iscrizione_sfida->sfidaspeciale,
					);

					$app->response->setBody( json_encode( $x ) );
                    $app->response->setStatus(200);
				} else {
                    throw new Exception('Sfida non trovata',Errori::SFIDA_NON_TROVATA);
			    }

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
                    case Errori::SFIDA_NON_TROVATA:
                        $testo = 'Sfida non trovata';
                        $status = 404;
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

	    $app->get('/iscrizione/:id', function ($idsfida) use ($app) {

            $app->response->setStatus(500);
            $app->response->headers->set('Content-Type', 'text/html');
		    try {

			    if ( !isset($_SESSION['wordpress']) ) {
				    throw new Exception('Wordpress login not found', Errori::WORDPRESS_LOGIN_REQUIRED);
			    }

			    $sfide = $_SESSION['sfide'];
			    $wordpress = $_SESSION['wordpress'];
                $codicecensimento = $wordpress['user_info']['codicecensimento'];


			    if ( intval($sfide['sfida_id']) != intval($idsfida) ) {
				    $app->log->error('Sfida richiesta '.$idsfida.' sfida in sessione '.$sfide['sfida_id']);
                    throw new Exception('sfida non trovata', Errori::SFIDA_NON_TROVATA);
			    }

					// Array
					// (
					//     [sfida_url] => http://10.143.90.74:8080/wordpress/index.php/sfida_event/abc-2/
					//     [sfida_titolo] => ABC
					//     [sfida_id] => 123
					//     [sfidaspeciale] => 1
                    //     [categoria] = ['a','b']
					//     [punteggio_attuale] => 30
					//     [numero_componenti] => 12
					//     [numero_specialita] => 4
					//     [numero_brevetti] => 1
					// )


			    $squadriglia = R::findOne('squadriglia','codicecensimento = ?', array($codicecensimento) );
			    if ( null == $squadriglia ) {
				    $squadriglia = R::dispense('squadriglia');
				    $squadriglia->codicecensimento = $codicecensimento;
				    $squadriglia->componenti = intval($sfide['numero_componenti']);
				    $squadriglia->specialita = intval($sfide['numero_specialita']);
				    $squadriglia->brevetti = intval($sfide['numero_brevetti']);
				    R::store($squadriglia);
			    }

			    $sfida_id = $sfide['sfida_id'];
			    
				$drm_iscrizione_sfida = R::findOne('iscrizionesfida','codicecensimento = ? and idsfida = ?', array($codicecensimento,$sfide['sfida_id']) );
			    if ( null == $drm_iscrizione_sfida || !$drm_iscrizione_sfida['attiva'] ) {
				    if ( null == $drm_iscrizione_sfida ) $drm_iscrizione_sfida = R::dispense('iscrizionesfida');
				    $drm_iscrizione_sfida->idsfida = intval($sfida_id);
				    $drm_iscrizione_sfida->titolo = $sfide['sfida_titolo'];
				    $drm_iscrizione_sfida->permalink = $sfide['sfida_url'];
				    $drm_iscrizione_sfida->codicecensimento = intval($codicecensimento);
				    $drm_iscrizione_sfida->startpunteggio = intval($sfide['punteggio_attuale']);
				    $drm_iscrizione_sfida->obiettivopunteggio = intval($sfide['punteggio_attuale']);
				    $drm_iscrizione_sfida->endpunteggio = null;
				    $drm_iscrizione_sfida->sfidaspeciale = (bool)$sfide['sfidaspeciale'];
                    $drm_iscrizione_sfida->categoriasfida = count($sfide['categoria']) > 0 ? $sfide['categoria'][0] : null; //array vuoto grande sfida
                    $drm_iscrizione_sfida->attiva = false;
                    R::store($drm_iscrizione_sfida);
				    $app->log->info('Richiesta iscrizione '.$codicecensimento.' alla sfida '.$idsfida);
				} else {
					$app->log->info('Richiesta iscrizione '.$codicecensimento.' alla sfida '.$idsfida.' gia esistente');
					throw new Exception("Sfida gia esistente", Errori::SFIDA_GIA_ATTIVA);
				}

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
                    case Errori::SFIDA_GIA_ATTIVA:
                        $testo = 'Risultate gia\' iscritti a questa sfida';
                        $status = 412;
                        $warn = true;
                        break;
                    case Errori::SFIDA_NON_TROVATA:
                        $testo = 'Sfida non trovata';
                        $status = 404;
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

            unset($_SESSION['sfide']);

			//devo procedere a compilare il form (http://10.143.90.74:8080/portal/home#/sfide/iscr?id=123)
		    if ( $app->response->getStatus() == 201 ) $app->redirect('/portal/home#/sfide/iscr?id='.$idsfida);

	    });

		$app->put('/iscrizione/:id' , function($sfida_id) use ($app){

            $app->response->setStatus(500);
            $app->response->headers->set('Content-Type', 'application/json');

			$body = $app->request->getBody();
            $app->log->info('richiesta iscrizione sfida body: '.$body);

			try {

			    if ( !isset($_SESSION['wordpress']) ) {
				    throw new Exception('Wordpress login not found', Errori::WORDPRESS_LOGIN_REQUIRED);
			    }

				$obj_request = json_decode($body);

                $wordpress = $_SESSION['wordpress'];
                $codicecensimento = $wordpress['user_info']['codicecensimento'];

				$drm_iscrizione_sfida = R::findOne('iscrizionesfida','codicecensimento = ? and idsfida = ?', array($codicecensimento,$sfida_id) );
				if ( null != $drm_iscrizione_sfida ) {

					$drm_iscrizione_sfida->obiettivospecialita = $obj_request->specialitasquadriglierinuove;
				    $drm_iscrizione_sfida->obiettivobrevetti = $obj_request->brevettisquadriglierinuove;
				    $drm_iscrizione_sfida->obiettivopunteggio = $obj_request->obiettivopunteggio;
                    $drm_iscrizione_sfida->descrizione = $obj_request->descrizione;
                    $drm_iscrizione_sfida->categoriasfida = $obj_request->categoriaSfida->desc;
                    $drm_iscrizione_sfida->numeroprotagonisti = $obj_request->numeroprotagonisti;

                    if ( $drm_iscrizione_sfida->sfidaspeciale ) {
                        if ( $obj_request->tipo != 'missione' ){
                            $app->log->warn('Tipologia sfida in caso di sfida speciale deve essere missione, invece e\' '.$obj_request->tipo);
                        }
                    }

                    $drm_iscrizione_sfida->tipo = $obj_request->tipo;
                    $drm_iscrizione_sfida->attiva = true;

					R::store($drm_iscrizione_sfida);

                    $app->log->info('Richiesta inizio sfida di '.$codicecensimento.' alla sfida '.$sfida_id);

				} else {
                    throw new Exception('Sfida non trovata',Errori::SFIDA_NON_TROVATA);
			    }

                if ( $drm_iscrizione_sfida->tipo == "impresa" ) {
                    if (empty($drm_iscrizione_sfida->descrizione)) {
                        throw new Exception('Sfida tipo impresa senza descrizione', Errori::CAMPI_VUOTI);
                    }
                }

                $squadriglia = findDatiSquadriglia($codicecensimento);

                $ragazzo = findDatiRagazzo($codicecensimento);

                if ( null == $ragazzo ) {
                    throw new Exception('Utente '.$codicecensimento.' errato', Errori::CODICE_CENSIMENTO_NOT_FOUND);
                }

                $capoRepartoArray = findDatiCapoReparto($ragazzo->regione,$ragazzo->gruppo,$codicecensimento);

                if ( empty($capoRepartoArray) ){
                    throw new Exception('Capo del ragazzo '.$codicecensimento.' non trovato', Errori::CODICE_CENSIMENTO_NOT_FOUND);
                }

                $capoReparto = $capoRepartoArray[0];

                $to = array($capoReparto->email => $capoReparto->nome.' '.strtoupper($capoReparto->cognome[0]).'.');

                $message =  'Ciao '.$capoReparto->nome.",\n";
                $message .= 'La tua squadriglia '. $squadriglia->nome .' ha richiesto di partecipare ad una sfida su Dreamland'."\n";
                $message .= 'Titolo : '.$drm_iscrizione_sfida->titolo."\n";
                if ( $drm_iscrizione_sfida->sfidaspeciale ) {
                    $message .= 'Si tratta di una sfida speciale'."\n";
                } else {
                    $message .= 'Si tratta di una grande sfida di tipo '.$drm_iscrizione_sfida->tipo."\n";

                    if ( $drm_iscrizione_sfida->tipo == "impresa" ) {
                        $message .= 'Numero specialità che vogliono conquistare : ' . $drm_iscrizione_sfida->obiettivospecialita . "\n";
                        $message .= 'Numero di brevetti che vogliono conquistare : ' . $drm_iscrizione_sfida->obiettivobrevetti . "\n";
                        $message .= 'Descrizione : ' . $drm_iscrizione_sfida->descrizione . "\n";
                    } else {
                        $message .= 'Ricordiamo che in questo caso dovrete assegnare alla vostra squadriglia una missione da sogno riguardante la categoria scelta.'."\n";
                    }
                    $message .= 'Categoria Sfida : '.$drm_iscrizione_sfida->categoriasfida."\n";
                }

                $message .= 'Saranno protagonisti in : '.$drm_iscrizione_sfida->numeroprotagonisti."\n";
                $message .= ''."\n";

                $message .= ''."\n";

                $message .= 'Ulteriori dettagli qui: '.$drm_iscrizione_sfida->permalink."\n";


                if ( !$app->mail->send($codicecensimento, $to, 'Iscrizione Sfida', $message) ){
                    throw new Exception('Invio mail capo reparto di iscrizione sq. sfida fallita',Errori::INVIO_MAIL_FALLITO);
                }

                // sovrascrivo tutto per sicurezza
                $_SESSION['portal'] = array(
                    'request' => array(
                        'sfidaid' => $sfida_id
                    )
                );

                $app->response->setBody("");
                $app->response->setStatus(204);

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
                    case Errori::SFIDA_GIA_ATTIVA:
                        $testo = 'Sfida gia attiva';
                        $status = 412;
                        $warn = true;
                        break;
                    case Errori::SFIDA_NON_TROVATA:
                        $testo = 'Sfida non trovata';
                        $status = 404;
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

        $app->delete('/iscrizione/:id' , function($sfida_id) use ($app){

            $app->response->setStatus(500);
            $app->response->headers->set('Content-Type', 'application/json');

            $wordpress = $app->config('wordpress');
            $url = $wordpress['url'];
            $body = $app->request->getBody();

            try {

                if ( !isset($_SESSION['wordpress']) ) {
                    throw new Exception('Wordpress login not found', Errori::WORDPRESS_LOGIN_REQUIRED);
                }

                $wordpress = $_SESSION['wordpress'];
                $codicecensimento = $wordpress['user_info']['codicecensimento'];

                $drm_iscrizione_sfida = R::findOne('iscrizionesfida','codicecensimento = ? and idsfida = ? and attiva = ?', array($codicecensimento,$sfida_id,true) );
                if ( null != $drm_iscrizione_sfida ) {

                    $drm_annullo_sfida = R::dispense('annullosfida');
                    $drm_annullo_sfida->idsfida = $sfida_id;
                    $drm_annullo_sfida->codicecensimento = $codicecensimento;
                    R::store($drm_annullo_sfida);

                    R::trash($drm_iscrizione_sfida);

                    $app->log->info('Abortita sfida di '.$codicecensimento.' alla sfida '.$sfida_id);
                } else {
                    throw new Exception('Sfida non trovata di '.$codicecensimento.' id '.$sfida_id,Errori::SFIDA_NON_TROVATA);
                }

                $squadriglia = findDatiSquadriglia($codicecensimento);

                $ragazzo = findDatiRagazzo($codicecensimento);

                $capoRepartoArray = findDatiCapoReparto($ragazzo->regione,$ragazzo->gruppo,$codicecensimento);
                $capoReparto = $capoRepartoArray[0];


                $to = array($capoReparto->email => $capoReparto->nome.' '.strtoupper($capoReparto->cognome[0]).'.');

                $message =  'Ciao '.$capoReparto->nome.",\n";
                $message .= 'La tua squadriglia '. $squadriglia->nome .' ha rinunciato a partecipare ad una sfida su Dreamland'."\n";
                $message .= 'Titolo : '.$drm_iscrizione_sfida->titolo."\n";

                if ( !$app->mail->send($codicecensimento, $to, 'Rimozione Sfida', $message) ){
                    throw new Exception('Invio mail capo reparto di de-iscrizione sq. sfida fallita',Errori::INVIO_MAIL_FALLITO);
                }

                $app->log->info('squadriglia di '. $codicecensimento .' di-siscritta da '.$sfida_id);

                $app->response->setBody("");
                $app->response->setStatus(200);

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
                    case Errori::SFIDA_GIA_ATTIVA:
                        $testo = 'Sfida gia attiva';
                        $status = 412;
                        $warn = true;
                        break;
                    case Errori::SFIDA_NON_TROVATA:
                        $testo = 'Sfida non trovata';
                        $status = 404;
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

        $app->put('/chiusura/:id', function($sfida_id) use ($app) {

            $app->response->setStatus(501);
            $app->response->headers->set('Content-Type', 'application/json');

//            autovalutazione: 'insufficiente',
//            protagonisti : 0,
//            nuovespecialita : 0,
//            nuovibrevetti : 0,
//            punteggiosquadriglia : 0,
//            provasuperata : true

            $body = $app->request->getBody();

            try {

                if ( !isset($_SESSION['wordpress']) ) {
                    throw new Exception('Wordpress login not found', Errori::WORDPRESS_LOGIN_REQUIRED);
                }

                $obj_request = json_decode($body);

                $wordpress = $_SESSION['wordpress'];
                $codicecensimento = $wordpress['user_info']['codicecensimento'];

                $drm_iscrizione_sfida = R::findOne('iscrizionesfida','codicecensimento = ? and idsfida = ?', array($codicecensimento,$sfida_id) );
                if ( null != $drm_iscrizione_sfida ) {

                    $drm_chiusura_sfida = R::findOne('chiusurasfida','codicecensimento = ? and idsfida = ?', array($codicecensimento,$sfida_id) );
                    if ( null == $drm_chiusura_sfida ) {
                        $drm_chiusura_sfida = R::dispense('chiusurasfida');
                        //PK
                        $drm_chiusura_sfida->idsfida = $sfida_id;
                        $drm_chiusura_sfida->codicecensimento = $codicecensimento;
                    } else {
                        $app->log->info('Retry chiusura sfida '.$sfida_id.' da parte di '.$codicecensimento.' old data '.json_encode($drm_chiusura_sfida));
                    }

                    //nuovi
                    $drm_chiusura_sfida->protagonisti = $obj_request->protagonisti;
                    $drm_chiusura_sfida->nuovespecialita = $obj_request->nuovespecialita;
                    $drm_chiusura_sfida->nuovibrevetti = $obj_request->nuovibrevetti;

                    //da capire
                    $drm_chiusura_sfida->provasuperata = $obj_request->provasuperata;
                    $drm_chiusura_sfida->autovalutazione = $obj_request->autovalutazione;

                    //gia presente
                    $drm_chiusura_sfida->punteggiosquadriglia= $obj_request->punteggiosquadriglia;
                    $drm_chiusura_sfida->protagonisti = $obj_request->protagonisti;

                    $drm_chiusura_sfida->conferma = false;
                    $id_drm_chiusura_sfida = R::store($drm_chiusura_sfida);

                    $app->log->info('Chiusa sfida '.$sfida_id.' da parte di '.$codicecensimento.' creato rapporto '.$id_drm_chiusura_sfida);

                    //aggiorno iscrizione sfida
                    $drm_iscrizione_sfida->endpunteggio = $obj_request->punteggiosquadriglia;
                    R::store($drm_iscrizione_sfida);

                    $app->log->info('Aggiornata iscrizione sfida '.$sfida_id.' da parte di '.$codicecensimento);

                    //FIXME: logica invio mail caporeparto

                    $app->response->setBody("");
                    $app->response->setStatus(204);

                } else {
                    throw new Exception('Sfida non trovata',Errori::SFIDA_NON_TROVATA);
                }

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
                    case Errori::SFIDA_GIA_ATTIVA:
                        $testo = 'Sfida gia attiva';
                        $status = 412;
                        $warn = true;
                        break;
                    case Errori::SFIDA_NON_TROVATA:
                        $testo = 'Sfida non trovata';
                        $status = 404;
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

        $app->put('/conferma/:id/:cc', function($sfida_id,$codicecc) use ($app) {

            $app->response->setStatus(501);
            $app->response->headers->set('Content-Type', 'application/json');

            try {

                if ( !isset($_SESSION['wordpress']) ) {
                    throw new Exception('Wordpress login not found', Errori::WORDPRESS_LOGIN_REQUIRED);
                }

                $wordpress = $_SESSION['wordpress'];
                $codicecensimento = $wordpress['user_info']['codicecensimento'];

                $drm_chiusura_sfida = R::findOne('chiusurasfida','codicecensimento = ? and idsfida = ?', array($codicecensimento,$sfida_id) );
                if ( null != $drm_chiusura_sfida ) {

                    $drm_chiusura_sfida->conferma = true;
                    R::store($drm_chiusura_sfida);

                    $app->log->info('Sfida '.$sfida_id.' fatta da '.$codicecensimento.' confermata dal capo reparto '.$codicecc);

                } else {
                    throw new Exception('Sfida non trovata',Errori::SFIDA_NON_TROVATA);
                }

                $app->response->setBody("");
                $app->response->setStatus(204);


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
                    case Errori::SFIDA_GIA_ATTIVA:
                        $testo = 'Sfida gia attiva';
                        $status = 412;
                        $warn = true;
                        break;
                    case Errori::SFIDA_NON_TROVATA:
                        $testo = 'Sfida non trovata';
                        $status = 404;
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

	});

}