<?php 


// $to => array($email => $nome.' '.$cognome)
function dream_mail($app, $to, $subject, $message, $htmlMessage = '') {

  $from = $app->config('email_sender');
  $app->log->info( 'email from '.var_export($from,true).' to '.var_export($to,true) );

  try {

    // To use the ArrayLogger
    $logger = new Swift_Plugins_Loggers_ArrayLogger();
    //$logger = new Swift_Plugins_Loggers_EchoLogger();

    // Create the message
    $message = Swift_Message::newInstance()
      ->setSubject($subject)
      ->setFrom( $from )
      ->setTo( $to )
      ->setBody($message);
    if ( !empty($htmlMessage) ) {
      $message->addPart($htmlMessage, 'text/html');
    }
    //->attach(Swift_Attachment::fromPath('my-document.pdf'));
    $smtpConfig = $app->config('smtp');
    $transport = Swift_SmtpTransport::newInstance($smtpConfig['host'], $smtpConfig['port'], $smtpConfig['security'])
      ->setUsername($smtpConfig['username'])
      ->setPassword($smtpConfig['password']);

    // Create the Mailer using your created Transport
    $mailer = Swift_Mailer::newInstance($transport);
    $mailer->registerPlugin(new Swift_Plugins_LoggerPlugin($logger));
    $failures = array();
    if (!$mailer->send($message, $failures))
    {
      $app->log->error('Fallito l\'invio : '.json_encode($failures));
      return false;
    } else {
      $app->log->info('Mail correttamente invata');
      return true;
    }
  }
  catch(Swift_RfcComplianceException $er){
    $message = $er->getMessage();
    $app->log->error($message);
    $app->log->error($er->getTraceAsString());
    $app->log->error($logger->dump());
  }
  catch(Swift_TransportException $e) {
    $message = $e->getMessage();
    $app->log->error($message);
    $app->log->error($e->getTraceAsString());
    $app->log->error($logger->dump());
  }
  
}