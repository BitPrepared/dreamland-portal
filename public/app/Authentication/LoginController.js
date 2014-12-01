authenticationModule.controller('LoginController', function ($scope, $rootScope,$state, AUTH_EVENTS, AuthService) {

  $scope.credentials = {
    username: '',
    password: ''
  };

  $scope.login = function (credentials) {
    
    $scope.loginPromise = AuthService.login(credentials);

    $scope.loginPromise.then(function () {
      $rootScope.$broadcast(AUTH_EVENTS.loginSuccess);
      $state.go('home');
    }, function () {
      $rootScope.$broadcast(AUTH_EVENTS.loginFailed);
    });
  };

})
