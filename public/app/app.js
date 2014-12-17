'use strict';

require.config({
    baseUrl: 'app/', //assets/js/dist/
    paths: {
      'dist' : '../assets/js/dist',
      'angular': '../assets/js/dist/angular.min',
      'moment' : '../assets/js/dist/moment-with-locales.min',
      'underscore': '../assets/js/dist/underscore-min',

      // ANGULARJS
      'ngDialog': '../assets/js/dist/ngDialog.min',
      'angular-ui-router': '../assets/js/dist/angular-ui-router.min',
      'angular-sanitize': '../assets/js/dist/angular-sanitize.min',
      'ui-bootstrap': '../assets/js/dist/ui-bootstrap.min',
      'angular-locale-it': '../assets/js/dist/locale/angular-locale_it-it',
      'angular-animate': '../assets/js/dist/angular-animate.min',
      'angular-date': '../assets/js/dist/angular-date',

      // SERVICE 
      'spinnerService': 'Spinner/SpinnerService',
      'authService': 'Authentication/AuthService',
      'sharedServices': 'Service/sharedService'
    },
    shim: {
      'angular': {
        exports: 'angular'
      },
      "underscore": {
        exports: "_"
      },
      'ngDialog': {
        deps : [ 'angular' ],
        exports: 'ngDialog'
      },
      'angular-ui-router' : {
          deps : [ 'angular' ],
          exports : 'angular-ui-router'
      },
      'ui-bootstrap' : {
          deps : [ 'angular-ui-router' ],
          exports : 'ui-bootstrap'
      },
      'angular-sanitize' : {
          deps : ['angular'],
          exports : 'angular-sanitize'
      },
      'angular-locale-it' : {
          deps : ['angular'],
          exports : 'angular-locale-it'
      },
      'angular-animate': {
          deps : ['angular'],
          exports : 'angular-animate' 
      },
      'angular-date': {
          deps : ['angular'],
          exports : 'angular-date' 
      }
    }
});

// Start the main app logic.
requirejs([
  'dist/jquery.min', 'dist/jquery-ui.min', 'angular', 'underscore', 
  'dreamApp', 
  'Portal/PortalService',
  'ApplicationController',
  'Header/HeaderController',
  'Authentication/LoginController',
  'Registration/RegistrationController',
  'Registration/CalculatorController',
  'Sfide/SfideController',
  'Flash/FlashController'
  ]
  , function(jQuery,jQueryui,angular,_,dreamApp,portal,appController,header,registration,calc,sfide,flash) {
    angular.element('#spinnerdiv').hide();
    angular.bootstrap(document, ['dreamApp']);  
    // var even = _.find([1, 2, 3, 4, 5, 6], function(num){ return num % 2 == 0; });
    // console.log(even);
});