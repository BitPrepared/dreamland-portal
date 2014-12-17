define(['angular','dreamApp'], function(angular,dreamApp){
   'use strict';

	var headerModule = dreamApp.controller('HeaderController' , function HeaderController($scope, $location, AuthService, USER_ROLES, WORDPRESS_URL){

		$scope.isAuthenticated = AuthService.isAuthenticated;

		$scope.isGuest = AuthService.isGuest;

		$scope.getCurrentUserRole = AuthService.getCurrentUserRole;

		$scope.userRoles = USER_ROLES

		$scope.isActive = function (viewLocation) { 
			return viewLocation === $location.path();
		};

		$scope.isAvailable = function (viewLocation) { 
			return false;
		};

		$scope.wordpressUrl = WORDPRESS_URL;

	});

	return headerModule;

});