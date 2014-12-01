dreamApp.controller('ApplicationController', function ($scope, $rootScope,Session, USER_ROLES,AuthService,$state,$stateParams) {

	$scope.currentUser = null;
	$scope.userRoles = USER_ROLES;
	$scope.isAuthorized = AuthService.isAuthorized;
	$scope.isAuthenticated = AuthService.isAuthenticated;

	$scope.setCurrentUser = function (user) {
		$scope.currentUser = user;
	};

	$scope.check = function() {
		console.log($state.current);
	};

	$scope.getter = function() {
		console.log($state.current);
		console.log($state.get());
	};

	$scope.logout = function() {
		Session.destroy();
	}



});
