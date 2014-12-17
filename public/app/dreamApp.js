define(['angular','ngDialog','angular-ui-router','ui-bootstrap', 'angular-date', 'spinnerService' , 'authService','sharedServices'], 
  function(angular,ngDialog,ui_router,ui_bootstrap,angular_date,spinner,auth_service,services){
   'use strict';

  var dreamApp = angular.module('dreamApp', [ 'ngDialog', 'ui.router' , 'ui.date', 'ui.bootstrap', 'sharedServices' ]);

  dreamApp.constant("authUrl", "http://localhost:5500/users/login");

  dreamApp.constant("WORDPRESS_URL", window.wordpressUrl);

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
        'container': { templateUrl : window.baseUrl+"app/Home/partial-root.html" },
        'navbar': { templateUrl: window.baseUrl+"app/Header/navbar.html" },
        'footer' : { templateUrl: window.baseUrl+"app/Header/footer.html" } //templateUrl
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
        'container@root': { templateUrl : window.baseUrl+"app/Home/empty.html" }
      },
      data: {
        authorizedRoles: [USER_ROLES.all]
      }
    });

    $stateProvider.state('error', {
      url: '/error/?errMsg',
      parent: 'root',
      abstract: false,
      views: {
        'container': { 
          templateUrl : window.baseUrl+"app/Home/error.html", 
          controller: function($scope,$stateParams) { $scope.lastErrorMsg = $stateParams.errMsg; } 
        }
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
        'container@root': { templateUrl : window.baseUrl+"app/Home/partial-home.html" }
      },
      data: {
        authorizedRoles: [USER_ROLES.all]
      }
    });

    $stateProvider.state('sfide', {
      url: '/sfide',
      abstract: false,
      parent: 'root',
      views: {
        'container@root': { templateUrl : window.baseUrl+"app/Home/partial-home.html" }
      },
      data: {
        authorizedRoles: [USER_ROLES.all]
      }
    });

    $stateProvider.state('sfide.iscrizione', {
      url: '/iscr?step&id',
      abstract: false,
      parent: 'sfide',
      views: {
        'container@root': { templateUrl : window.baseUrl+"app/Sfide/_iscrizione.html" }
      },
      data: {
        authorizedRoles: [USER_ROLES.utente_eg]
      }
    });

    $stateProvider.state('home.registration', {
          url: '/reg',
          abstract: false,
          parent: 'home',
          views: {
              'container@root': { templateUrl : window.baseUrl+"app/Registration/_registration.html" }
          },
          data: {
              authorizedRoles: [USER_ROLES.guest]
          }
      });

      $stateProvider.state('home.registrationcc', {
          url: '/reg/cc?code',
          abstract: false,
          parent: 'home',
          views: {
              'container@root': { templateUrl : window.baseUrl+"app/Registration/_caporeparto.html" }
          },
          data: {
              authorizedRoles: [USER_ROLES.guest]
          }
      });

    $stateProvider.state('home.registration.ok', {
      url: '/reg/ok?msg',
      abstract: false,
      parent: 'home',
      views: {
        'container@root': { templateUrl : window.baseUrl+"app/Registration/_success.html" }
      },
      data: {
        authorizedRoles: [USER_ROLES.guest]
      }
    });

  $stateProvider.state('home.registration.ko', {
      url: '/reg/ko?msg',
      abstract: false,
      parent: 'home',
      views: {
          'container@root': { templateUrl : window.baseUrl+"app/Registration/_error.html" }
      },
      data: {
          authorizedRoles: [USER_ROLES.guest]
      }
  });

    $stateProvider.state('home.registration.wizard', {
      url: '/wizard?step&code',
      abstract: false,
      parent: 'home',
      views: {
        'container@root': { templateUrl : window.baseUrl+"app/Registration/_wizard.html" }
      },
      data: {
        authorizedRoles: [USER_ROLES.guest]
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

  dreamApp.run(['$rootScope','AUTH_EVENTS','AuthService','$state','$location', '$log' ,'USER_ROLES', function($rootScope,AUTH_EVENTS, AuthService, $state, $location, $log, USER_ROLES) {

    var cses = AuthService.createSession();

    var onevent = function() {
      // $routeChangeStart (for ngRoute) or $stateChangeStart (for UI Router) 
        $rootScope.$on('$stateChangeStart', function (event, nextState, nextParams, fromState, fromParams) {

          var authorizedRoles = nextState.data.authorizedRoles;
          $log.info(nextState.name);
          $log.info(nextState.url);

          if (!AuthService.isAuthorized(authorizedRoles)) {
            event.preventDefault();
            if (AuthService.isAuthenticated()) {
              // user is not allowed
              $rootScope.$broadcast(AUTH_EVENTS.notAuthorized);

              if ( authorizedRoles.indexOf(USER_ROLES.guest) !== -1 ) {
                $state.go('error',{ errMsg : 'Questa pagina e\' solo per gli utenti ancora non autenticati' })
              } else {
                $state.go('error',{ errMsg : 'Non possiedi sufficienti permessi per accedere alla pagina' })
              }
              
            } else {
              // user is not logged in
              $rootScope.$broadcast(AUTH_EVENTS.notAuthenticated);
              $state.go('error',{ errMsg : 'Non sei loggato. Autenticaticati per favore.' })
            }
          } else {
            $log.log('tutto ok');
          }

          if ( nextState.name == 'jump' ) {
            event.preventDefault();
            $log.log('vorrei andare: '+nextParams.state);
            $state.go(nextParams.state,nextParams);
          } 

        }); //on event
    }

    cses.then(function(result) {
      onevent();
    }, function(result){
      onevent();
    }); //cses

  }]);

  return dreamApp;
});