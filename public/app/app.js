'use strict';

var underscoreModule = angular.module('underscore', []);
underscoreModule.factory('_', function() { 
  return window._; 
});

var authenticationModule = angular.module('authentication',['cgBusy']); 
var sharedServicesModule = angular.module('sharedServices',['ui.router', 'authentication','cgBusy']); //servi REST lato server
var headerModule = angular.module('header',['authentication','sharedServices']);  //'http-auth-interceptor'

var dreamApp = angular.module('dreamApp', [ 'ui.bootstrap', 'underscore', 'ui.router', 'cgBusy', 'ngDialog' , 'sharedServices', 'header']);

dreamApp.constant("authUrl", "http://localhost:5500/users/login");

// Example of how to set default values for all dialogs
dreamApp.config(['ngDialogProvider', function (ngDialogProvider) {
  ngDialogProvider.setDefaults({
    className: 'ngdialog-theme-default',
    plain: false,
    showClose: false,
    closeByDocument: true,
    closeByEscape: true,
    appendTo: false
  });
}]);

dreamApp.config(function ($stateProvider, $urlRouterProvider, USER_ROLES) {
  
  $stateProvider.state('root', {
    url: '',
    abstract: true,
    parent: '',
    views : {
      'container': { templateUrl : "app/Home/partial-root.html" },
      'navbar': { templateUrl: "app/Header/navbar.html" },
      'footer' : { templateUrl: "app/Header/footer.html" } //templateUrl
    },
    data: {
      authorizedRoles: [USER_ROLES.all]
    }
  });

  $stateProvider.state('index', {
    url: '/',
    abstract: false,
    parent: 'root',
    views: {
      'container@root': { templateUrl : "app/Home/empty.html" },
    },
    data: {
      authorizedRoles: [USER_ROLES.all]
    }
  });

  $stateProvider.state('error', {
    url: '/error/?errMsg',
    abstract: false,
    views: {
      'container': { templateUrl : "app/Home/error.html" },
    },
    data: {
      authorizedRoles: [USER_ROLES.all]
    }
  });

  $stateProvider.state('home', {
    url: '/home',
    abstract: false,
    parent: 'root',
    views: {
      'container@root': { templateUrl : "app/Home/partial-home.html" },
    },
    data: {
      authorizedRoles: [USER_ROLES.all]
    }
  });

  $stateProvider.state('home.registration', {
    url: '/reg',
    abstract: false,
    parent: 'home',
    views: {
      'container@root': { templateUrl : "app/Registration/_registration.html" },
    },
    data: {
      authorizedRoles: [USER_ROLES.all]
    }
  });

  $stateProvider.state('home.registration.ok', {
    url: '/reg/ok?msg',
    abstract: false,
    parent: 'home',
    views: {
      'container@root': { templateUrl : "app/Registration/_success.html" },
    },
    data: {
      authorizedRoles: [USER_ROLES.all]
    }
  });

  $stateProvider.state('home.registration.wizard', {
    url: '/wizard?step&code',
    abstract: false,
    parent: 'home',
    views: {
      'container@root': { templateUrl : "app/Registration/_wizard.html" },
    },
    data: {
      authorizedRoles: [USER_ROLES.all]
    }
  });

  $stateProvider.state('jump', {
    url: '/j/{state}?code',
    templateUrl : "app/Home/empty.html" ,
    data: {
      authorizedRoles: [USER_ROLES.all]
    }
  });
  

  // when there is an empty route, redirect to /index   
  $urlRouterProvider.when('', '/');
  $urlRouterProvider.otherwise( function($injector,$location) {
    console.log($location.path());
    // $state.go('error');
    // DEVO IMPARE A GESTIRE GLI ERRORI
    $location.path('error');
  }) ;

});

dreamApp.config(function ($httpProvider) {
  $httpProvider.interceptors.push(['$injector', function ($injector) {
    return $injector.get('AuthInterceptor');
  }]);
});

dreamApp.run(['$rootScope','AUTH_EVENTS','AuthService','$state','$location', function($rootScope,AUTH_EVENTS, AuthService, $state, $location) {

    // $routeChangeStart (for ngRoute) or $stateChangeStart (for UI Router) 
  $rootScope.$on('$stateChangeStart', function (event, nextState, nextParams, fromState, fromParams) {

    var authorizedRoles = nextState.data.authorizedRoles;
    
    if (!AuthService.isAuthorized(authorizedRoles)) {
      event.preventDefault();
      if (AuthService.isAuthenticated()) {
        // user is not allowed
        $rootScope.$broadcast(AUTH_EVENTS.notAuthorized);
      } else {
        // user is not logged in
        $rootScope.$broadcast(AUTH_EVENTS.notAuthenticated);
      }
    }

    if ( nextState.name == 'jump' ) {
      event.preventDefault();
      console.log('vorrei andare: '+nextParams.state);
      $state.go(nextParams.state,nextParams);
      //router.goTo(nextParams.state,nextParams);
      // $rootScope
    } 

  });

}]);

