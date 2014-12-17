define(['angular','dreamApp','moment'], function(angular,dreamApp,moment){
   'use strict';

  var registrationController = dreamApp.controller('RegistrationController', function ($scope, $rootScope, $http, $state, ngDialog, Portal, $stateParams, $window) {

    $scope.ruolisquadriglia = [ {'desc' : 'Altro' , 'code' : 3}, {'desc' : 'Capo Sq.', 'code' : 1}, {'desc' : 'Vice capo Sq.', 'code' : 2} ];

    $scope.caller = '';

    $scope.orig = {};

    $scope.completato = false;

    $scope.dateOptions = {
          changeYear: true,
          changeMonth: true,
          yearRange: '1900:-0',
          dateFormat: 'dd/mm/yy'
      };

    $scope.reg = {
      'token' : null,
      'datan' : null,
      'zona' : null,
      'regione' : null,
      'gruppo' : null,
      'nomecaporeparto': null,
      'cognomecaporeparto': null,
      'emailcaporeparto': null,
      'email' : null,
      'codcens' : null,
      'nome' : null,
      'cognome' : null,
      'nomesq': null,
      'ruolosq' : "Capo Sq.",
      'numerosquadriglieri' : 0,
      'specialitasquadriglieri' : 0,
      'brevettisquadriglieri' : 0,
      'specialitadisquadriglia' : false,
      'rinnovospecialitadisquadriglia' : false,
      'punteggiosquadriglia' : 0
    };

    $scope.currentError = '';

    $scope.enableButton = true;  

    $scope.step = angular.isDefined($stateParams.step) ? $stateParams.step : 1;
    
    if ( $state.current.name != 'home.registration' ) {
      $scope.reg.token = $stateParams.code;
      $http.get('./api/registrazione/info/'+$stateParams.code).
        success(function(data, status, headers, config) {
          $scope.caller = data.nome;
          $scope.reg.nome = data.nome;
          $scope.reg.cognome = data.cognome;

          $scope.reg.zona = data.zonaNome;
          $scope.reg.gruppo = data.gruppoNome;
          $scope.reg.regione = data.regioneNome;

          $scope.completato = data.completato;

          var cc = data.cc[0];

          $scope.orig.nomecaporeparto = cc.nome;
          $scope.orig.cognomecaporeparto = cc.cognome;
          $scope.orig.emailcaporeparto = cc.email;

          $scope.reg.nomecaporeparto = cc.nome;
          $scope.reg.cognomecaporeparto = cc.cognome;
          $scope.reg.emailcaporeparto = cc.email;

          //    PER AVERE LE SQ. QUI BISOGNA RENDERLE VISIBILI ESTERNAMENTE
          //Portal.loadSquadriglia(function(squadriglia){
          //    debugger;
          //    //squadriglia.
          //}, function(err){
          //    debugger;
          //    $rootScope.lastErrMsg = err;
          //    $state.go('error',null, {reload: true});
          //});

        }).
        error(function(data, status, headers, config) {
          $rootScope.lastErrMsg = 'impossibile trovare token registrazione';
          $state.go('error',null, {reload: true});
        });
      }

    $scope.setStep = function(step){
      // $state.go('home.registration.wizard',{ 'code' : $stateParams.code , 'step' : step }); //<-- cosi perdo lo scope
        $scope.step = step;
      // debugger;
    }

    $scope.sendRegistrationRequest = function () {
        $scope.enableButton = false;
        var newRequest = {};
        newRequest.email = $scope.email;
        newRequest.codicecensimento = $scope.codcens;
        var datanascita = moment($scope.datan);
        newRequest.datanascita = datanascita.format('YYYYMMDD');

        $rootScope.remoteLoad = $http.post('./api/registrazione/step1', newRequest).
          success(function(data, status, headers, config) {
            $state.go('home.registration.ok',{ msg : 'riceverai in breve tempo una mail per proseguire con la registrazione'},{reload : true});
          }).
          error(function(data, status, headers, config) {
            $scope.currentError = data;
            ngDialog.open({template:'modalDialogId', scope: $scope });
            $scope.enableButton = true;
          });

    };

    $scope.sendRegistrationCC = function() {
        $scope.enableButton = false;
        var newRequest = {};
        newRequest.codicecensimento = $scope.codcens;

        $rootScope.remoteLoad = $http.post('./api/registrazione/stepc/'+$stateParams.code, newRequest).
            success(function(data, status, headers, config) {
                //$window.location.href = data;
                $state.go('home.registration.ok',{ msg : 'Registrazione completata con successo. Riceverai una mail con le nuove credenziali.'},{reload : true});
            }).
            error(function(data, status, headers, config) {
                $scope.currentError = data;
                ngDialog.open({template:'modalDialogId', scope: $scope });
                $scope.enableButton = true;
            });
    }

    $scope.update = function() {
      $scope.reg.punteggiosquadriglia = $scope.reg.numerosquadriglieri + $scope.reg.specialitasquadriglieri + (4 * $scope.reg.brevettisquadriglieri);

      if ( $scope.reg.specialitadisquadriglia ) {
        $scope.reg.punteggiosquadriglia += 10;
      }
      if ( $scope.reg.rinnovospecialitadisquadriglia ) {
        $scope.reg.punteggiosquadriglia += 7;
      }

    };

    $scope.registraSquadriglia = function() {
      var token = $scope.reg.token;
      $scope.enableButton = false;
      $rootScope.remoteLoad = $http.post('./api/registrazione/step2/'+token, $scope.reg).
          success(function(data, status, headers, config) {
            //$window.location.href = data;
            $state.go('home.registration.ok',{ msg : 'Registrazione completata con successo. Riceverai una mail con le nuove credenziali.'},{reload : true});
          }).
          error(function(data, status, headers, config) {
            $scope.currentError = data;
            ngDialog.open({template:'modalDialogId', scope: $scope });
            $scope.enableButton = true;
          });
    }


  });

  return registrationController;

});