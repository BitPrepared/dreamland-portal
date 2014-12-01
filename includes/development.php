<?php 

function decodeInfoPhp() {
	ob_start();
	phpinfo(INFO_GENERAL);
	$phpinfo = array('phpinfo' => array());
	if(preg_match_all('#(?:<h2>(?:<a name=".*?">)?(.*?)(?:</a>)?</h2>)|(?:<tr(?: class=".*?")?><t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>)?)?</tr>)#s', ob_get_clean(), $matches, PREG_SET_ORDER))
	    foreach($matches as $match)
	        if(strlen($match[1]))
	            $phpinfo[$match[1]] = array();
	        elseif(isset($match[3])) {
	        	$t = array_keys($phpinfo);
	        	$v = end($t);
	            $phpinfo[$v][$match[2]] = isset($match[4]) ? array($match[3], $match[4]) : $match[3];
	        }
	        else {
	        	$t = array_keys($phpinfo);
	        	$v = end($t);
	            $phpinfo[$v][] = $match[2];
	        }
	ob_end_clean();	        	
	return $phpinfo;
}

$app->get('/version', function () use ($app) {  
	$app->response->headers->set('Content-Type', 'text/html');
	$info = decodeInfoPhp();
	$app->response->setBody( $info['phpinfo'][0] );
});