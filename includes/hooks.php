<?php

$app->hook('slim.before.router', function () use ($app) {

	$req = $app->request;
	$allGetVars = $req->get();
	$allPostVars = $req->post();
	$allPutVars = $req->put();
	
	$vars = array_merge($allGetVars,$allPostVars);
	$vars = array_merge($vars,$allPutVars);

	$srcParam = json_encode($vars);

	$srcUri = $req->getRootUri();
	$srcUrl = $req->getResourceUri();
	//Kint::dump( $srcUrl );
//    $app->log->info('REQUEST : '.var_export($_REQUEST,true));
//    $app->log->info('URI : '.$srcUri);
//    $app->log->info('URL : '.$srcUrl);
//    $app->log->info('Params : '.$srcParam);

});

$app->hook('slim.before.dispatch', function () use ($app) {

	$url = $app->request->getResourceUri();
	$api = strpos($url, 'api');

	if ( !$app->request->isAjax() && !$app->request->isXhr() && !$api ) {

        $dati = array();
        $an = $app->config('google');
        if ( !empty($an['analytics']) && !DEBUG ) {
            $dati['gaAnalyticsCode'] = $an['analytics'];
        } else {
            $dati['gaAnalyticsCode'] = null;
        }

		$dati = array_merge($dati,array(
			'title' => $app->config('title'),
			'baseUrl' => $app->request->getRootUri().'/',
			'wordpressUrl' => $app->config('wordpress')['url'],
			'footerText' => '&copy;2014 Return To Dreamland | AGESCI',
			'wordpress' => $app->config('wordpress')
		));

		try {
			$app->render('header.php',$dati);
		} catch(Exception $e) {
			$app->log->error($e->getMessage());
		}

	}
});
  
$app->hook('slim.after.dispatch', function () use ($app) {

	$url = $app->request->getResourceUri();
	$api = strpos($url, 'api');

	if ( !$app->request->isAjax() && !$app->request->isXhr() && !$api ) {

		$dati = array(
			'title' => $app->config('title'),
			'baseUrl' => $app->request->getRootUri().'/',
			'wordpressUrl' => $app->config('wordpress')['url'],
			'footerText' => '&copy;2014 Return To Dreamland | AGESCI',
			'wordpress' => $app->config('wordpress')
		);
		
		try {
			$app->render('footer.php',$dati);
		} catch(Exception $e) {
			$app->log->error($e->getMessage());
		}

	}
});

