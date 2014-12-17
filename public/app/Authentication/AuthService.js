define(['angular','spinnerService'], function(angular,spinner){
   'use strict';

  var authenticationModule = angular.module('authentication',['spinnerService']); 

  authenticationModule.constant('USER_ROLES', {
    all: '*',
    administrator: 'administrator',
    editor: 'editor',
    utente_eg: 'eg',
    capo_reparto: 'cc',
    referente_regione: 'rr',
    guest: 'guest'
  }); 

  authenticationModule.factory('AuthService', function ($http, Session, $log, USER_ROLES) {

    var authService = {};

    authService.createSession = function(){

      Session.destroy();

      var x = $http
        .get('./api/asa/user/current')
        .success(function(data, status, headers, config) {
            // sessionId, userId, userRole, email
            var role = USER_ROLES.guest;
            data.roles.forEach(function(entry) {
              if ( typeof USER_ROLES[entry] != "undefined" ){
                role = USER_ROLES[entry];
              }
            });

            Session.create(data.id, data.username, role, data.email);
          })
        .error(function(data, status, headers, config) {
            Session.create(-1, 'guest', USER_ROLES.guest, '');
          });

      return x;
    }
   
    authService.login = function (credentials) {
      
      return $http
        .post('/api/asa/user/', credentials)
        .then(function (res) {
          $log.log(res);
          $log.log(res.data);
          //Session.create(res.data.id, res.data.user.id,res.data.user.role);
          //return res.data.user;
        }, function(err){
          $log.log('errore login');
        });
    };

    authService.isGuest = function() {
      return !!Session.userRole && Session.userRole == USER_ROLES.guest;
    }
   
    // !!undefined => false
    authService.isAuthenticated = function () {
      return !!Session.userRole;
    };
   
    authService.isAuthorized = function (authorizedRoles) {
      if (!angular.isArray(authorizedRoles)) {
        authorizedRoles = [authorizedRoles];
      }

      // all oppure prima verifico che sia autenticato dopo verifico se il ruolo e' coerente
      var response = authorizedRoles.indexOf(USER_ROLES.all) !== -1 || (authService.isAuthenticated() &&
        authorizedRoles.indexOf(Session.userRole) !== -1);

      $log.log('isAuthorized: ' + response);

      return response;
    };

    authService.getCurrentUserRole = function() {
      return Session.userRole;
    }
   
    return authService;

  });

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

  authenticationModule.service('Session', function ($log) {
  
  this.create = function (id, username, userRole, email) {
    this.id = id;
    this.username = username;
    this.userRole = userRole;
    this.email = email;
    $log.info('Creato utente ' , this);
  };

  this.destroy = function () {
    this.id = null;
    this.username = null;
    this.userRole = null;
    this.email = null;
    $log.info('Clean user');
  };

  return this;
})

  return authenticationModule;
});
