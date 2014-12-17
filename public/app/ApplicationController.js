define(['angular','dreamApp'], function(angular,dreamApp){
   'use strict';

	var controller = dreamApp.controller('ApplicationController', function ($scope, $rootScope, $log, Session, USER_ROLES,AuthService,$state,$stateParams) {

		$scope.currentUser = null;
		$scope.userRoles = USER_ROLES;
		$scope.isAuthorized = AuthService.isAuthorized;
		$scope.isAuthenticated = AuthService.isAuthenticated;

		$scope.setCurrentUser = function (user) {
			$scope.currentUser = user;
		};

		$scope.check = function() {
			$log.log($state.current);
		};

		$scope.getter = function() {
			$log.log($state.current);
			$log.log($state.get());
		};

		$scope.logout = function() {
			Session.destroy();
		}



	});

	return controller;

});