define(['angular','spinnerService','authService','angular-ui-router'], function(angular,spinner,auth,router){
   'use strict';
	var sharedServices = angular.module('sharedServices',['ui.router', 'authentication','spinnerService']);
	return sharedServices;
});