'use strict';

var $baseUrl = window.baseUrl;

require.config({
    baseUrl: $baseUrl+'app/',
    paths: {
      'angular': $baseUrl+'assets/js/dist/angular.min',
      'moment' : $baseUrl+'assets/js/dist/moment-with-locales.min',
      'underscore': $baseUrl+'assets/js/dist/underscore-min',

      //JQUERY
      'jquery' : $baseUrl+'assets/js/dist/jquery.min',
      'jquery-ui' : $baseUrl+'assets/js/dist/jquery-ui.min',

      // ANGULARJS
      'ngDialog': $baseUrl+'assets/js/dist/ngDialog.min',
      'angular-ui-router': $baseUrl+'assets/js/dist/angular-ui-router.min',
      'angular-sanitize': $baseUrl+'assets/js/dist/angular-sanitize.min',
      'ui-bootstrap': $baseUrl+'assets/js/dist/ui-bootstrap.min',
      'angular-locale-it': $baseUrl+'assets/js/dist/locale/angular-locale_it-it',
      'angular-animate': $baseUrl+'assets/js/dist/angular-animate.min',
      'angular-date': $baseUrl+'assets/js/dist/angular-date',

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
  'jquery', 'jquery-ui', 'angular', 'underscore',
  'dreamApp', 
  'Portal/PortalService',
  'ApplicationController',
  'Header/HeaderController',
  'Authentication/LoginController',
  'Registration/RegistrationController',
  'Registration/CalculatorController',
  'Sfide/SfideController',
  'Sfide/ChiusuraController',
  'Editor/EditorController',
  'Flash/FlashController'
  ]
  , function(jQuery,jQueryui,angular,_,dreamApp,portal,appController,header,registration,calc,sfide,sfidechiuse,editor,flash) {
    angular.element('#spinnerdiv').hide();
    angular.bootstrap(document, ['dreamApp']);  
    // var even = _.find([1, 2, 3, 4, 5, 6], function(num){ return num % 2 == 0; });
    // console.log(even);
});