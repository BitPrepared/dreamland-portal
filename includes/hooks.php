<?php

$app->hook('slim.before.router', function () use ($app) {
    $req = $app->request;
    $allGetVars = $req->get();
    $allPostVars = $req->post();
    $allPutVars = $req->put();

    $vars = array_merge($allGetVars, $allPostVars);
    $vars = array_merge($vars, $allPutVars);

    $srcParam = json_encode($vars);

    $srcUri = $req->getRootUri();
    $srcUrl = $req->getResourceUri();
//    if (DEBUG) {
//        //$app->log->info(@Kint::dump( $srcUrl ));
//        $app->log->debug('REQUEST : '.var_export($_REQUEST,true));
//        $app->log->debug('URI : '.$srcUri);
//        $app->log->debug('URL : '.$srcUrl);
//        $app->log->debug('Params : '.$srcParam);
//        $req->isAjax() ? $app->log->debug('Ajax attivo') : $app->log->debug('Ajax non attivo');
//    }
});

$app->hook('slim.before.dispatch', function () use ($app) {

    $url = $app->request->getResourceUri();
    $api = strpos($url, 'api');

    if (!$app->request->isAjax() && !$app->request->isXhr() && !$api) {
        $dati = [];
        $an = $app->config('google');
        if (!empty($an['analytics']) && !DEBUG) {
            $dati['gaAnalyticsCode'] = $an['analytics'];
        } else {
            $dati['gaAnalyticsCode'] = null;
        }

//      1 solo livello di context-path
        $uri = explode('/', trim($app->request->getRootUri(), '/'));

        $wordpress = $app->config('wordpress');
        $dati = array_merge($dati, [
            'title'        => $app->config('title'),
            'baseUrl'      => '/'.$uri[0].'/',
            'wordpressUrl' => $wordpress['url'],
            'footerText'   => '&copy;2014 Return To Dreamland | AGESCI',
            'wordpress'    => $app->config('wordpress'),
        ]);

        try {
            $app->render('header.php', $dati);
        } catch (Exception $e) {
            $app->log->error($e->getMessage());
        }
    }
});

$app->hook('slim.after.dispatch', function () use ($app) {

    $url = $app->request->getResourceUri();
    $api = strpos($url, 'api');

    if (!$app->request->isAjax() && !$app->request->isXhr() && !$api) {
        $wordpress = $app->config('wordpress');
        $dati = [
            'title'        => $app->config('title'),
            'baseUrl'      => $app->request->getRootUri().'/',
            'wordpressUrl' => $wordpress['url'],
            'footerText'   => '&copy;2014 Return To Dreamland | AGESCI',
            'wordpress'    => $app->config('wordpress'),
        ];

        try {
            $app->render('footer.php', $dati);
        } catch (Exception $e) {
            $app->log->error($e->getMessage());
        }
    }
});
