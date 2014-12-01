authenticationModule.constant('AUTH_EVENTS', {
  loginSuccess: 'auth-login-success',
  loginFailed: 'auth-login-failed',
  logoutSuccess: 'auth-logout-success',
  sessionTimeout: 'auth-session-timeout',
  notAuthenticated: 'auth-not-authenticated',
  notAuthorized: 'auth-not-authorized'
});

authenticationModule.factory('AuthInterceptor', function ($rootScope, $q, AUTH_EVENTS) {
  return {
    responseError: function (response) {
      if (response.status === 401) {
        $rootScope.$broadcast(AUTH_EVENTS.unauthorizedResponse, response);
      }
      if (response.status === 403) {
        $rootScope.$broadcast(AUTH_EVENTS.notAuthorized, response);
      }
      if (response.status === 419 || response.status === 440) {
        $rootScope.$broadcast(AUTH_EVENTS.sessionTimeout, response);
      }
      return $q.reject(response);
    }
  };
});
