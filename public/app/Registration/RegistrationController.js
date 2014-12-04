dreamApp.controller('RegistrationController', function ($scope, $rootScope, $http, $state, ngDialog, $stateParams) {

  $scope.ruolisquadriglia = [ {'desc' : 'Altro' , 'code' : 3}, {'desc' : 'Capo Sq.', 'code' : 1}, {'desc' : 'Vice capo Sq.', 'code' : 2} ];

  $scope.caller = '';

  $scope.orig = {};

  $scope.reg = {
    'token' : null,
    'datan' : null,
    'zona' : null,
    'regione' : null,
    'gruppo' : null,
    'nomecaporeparto': null,
    'cognomecaporeparto': null,
    'emailcaporeparto': null,
    'codicecenscaporeparto': null,
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

        var cc = data.cc[0];

        $scope.orig.nomecaporeparto = cc.nome;
        $scope.orig.cognomecaporeparto = cc.cognome;
        $scope.orig.emailcaporeparto = cc.email;

        $scope.reg.nomecaporeparto = cc.nome;
        $scope.reg.cognomecaporeparto = cc.cognome;
        $scope.reg.emailcaporeparto = cc.email;

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

  $scope.isRequiredCodiceCensimento = function() {
    if ( $scope.orig.nomecaporeparto != $scope.reg.nomecaporeparto ) return true;
    if ( $scope.orig.cognomecaporeparto != $scope.reg.cognomecaporeparto ) return true;
    if ( $scope.orig.emailcaporeparto != $scope.reg.emailcaporeparto ) return true;
    return false;
  }

  $scope.sendRegistrationRequest = function () {
      $scope.enableButton = false;
      var newRequest = {};
      newRequest.email = $scope.email;
      newRequest.codicecensimento = $scope.codcens;
      $datanascita = moment($scope.datan);
      newRequest.datanascita = $datanascita.format('YYYYMMDD');

      $rootScope.remoteLoad = $http.post('./api/registrazione/step1', newRequest).
        success(function(data, status, headers, config) {
          $state.go('home.registration.ok',{ msg : 'riceverai in brevete tempo una mail per proseguire con la registrazione'},{reload : true});
        }).
        error(function(data, status, headers, config) {
          $scope.currentError = data;
          ngDialog.open({template:'modalDialogId', scope: $scope });
          $scope.enableButton = true;
        });

  };

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
          $state.go('home.registration.ok',{ msg : 'Registrazione completata con successo. Riceverai una mail con le nuove credenziali.'},{reload : true});
        }).
        error(function(data, status, headers, config) {
          $scope.currentError = data;
          ngDialog.open({template:'modalDialogId', scope: $scope });
          $scope.enableButton = true;
        });
  }


})
