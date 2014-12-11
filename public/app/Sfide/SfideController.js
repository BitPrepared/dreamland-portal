dreamApp.controller('SfideController', function ($scope, $q, $rootScope, $http, $state, ngDialog, $stateParams, Portal, $window) {

  $scope.sfidaid = angular.isDefined($stateParams.id) ? $stateParams.id : -1;

  $scope.ready = false;
  $scope.isReady = function() {
    return $scope.ready;
  }

  var deferred = $q.defer();
  $scope.waitSfidaLoad = deferred.promise;

  $scope.squadriglia = null;
  $scope.sfida = null;

  Portal.loadSquadriglia(function(squadriglia){
    // $scope.squadriglia = {
    //   componenti : parseInt(squadriglia.componenti),
    //   specialita : parseInt(squadriglia.specialita),
    //   brevetti : parseInt(squadriglia.brevetti)
    // }
    $scope.squadriglia = squadriglia;
    Portal.loadSfida($scope.sfidaid,function(sfida){
      $scope.sfida = sfida;
      $scope.ready = true;
      $scope.update();
      deferred.resolve('Oh look we\'re done already.');
    },function(errore){
      $scope.currentError = errore;
      ngDialog.open({template:'modalDialogId', scope: $scope });
      $scope.enableButton = true;
    });

  },function(errore){
    $scope.currentError = errore;
    ngDialog.open({template:'modalDialogId', scope: $scope });
    $scope.enableButton = true;
  });

  $scope.tipiSfida = ['missione','impresa'];

  $scope.iscr = {
    tipo : 'impresa',
    'categoriaSfida': 'Avventura',
    numerosquadriglieri : 0,
    specialitasquadriglierinuove : 0,
    brevettisquadriglierinuove : 0,
    numeroprotagonisti : 0,
    punteggiosquadriglia : 0
  };

  $scope.step = angular.isDefined($stateParams.step) ? $stateParams.step : 1;

  $scope.setStep = function(step){
    $scope.step = step;
    $scope.update();
  };

  $scope.partecipa = function(){
    $scope.enableButton = false;

    deferred = $q.defer();
    $scope.waitSfidaLoad = deferred.promise;

    Portal.updateSquadriglia($scope.squadriglia, function() {
      var newRequest = {};
      newRequest.specialitasquadriglierinuove = $scope.iscr.specialitasquadriglierinuove;
      newRequest.brevettisquadriglierinuove = $scope.iscr.brevettisquadriglierinuove;
      newRequest.obiettivopunteggio = $scope.iscr.punteggiosquadriglia;

      $http.put('./api/sfide/iscrizione/'+$scope.sfidaid, newRequest).
        success(function(data, status, headers, config) {
          deferred.resolve('Oh look we\'re done already.');
          // DOVREI ANDARE SU WORDPRESS PER COMUNICARLO...
          // $state.go('home.registration.ok',{ msg : 'Iscrizione alla sfida completata con successo'},{reload : true});
          $window.location.href = '/wordpress/wp-json/portal/cs';
        }).
        error(function(data, status, headers, config) {
          $scope.currentError = data;
          ngDialog.open({template:'modalDialogId', scope: $scope });
          $scope.enableButton = true;
        });
    }, function(data){
      $scope.currentError = data;
      ngDialog.open({template:'modalDialogId', scope: $scope });
      $scope.enableButton = true;
    });

  }

  $scope.enableButton = true;

  $scope.update = function() {
    if ( null != $scope.squadriglia ) {
      $scope.iscr.punteggiosquadriglia = $scope.squadriglia.componenti 
                                          + 2 * $scope.squadriglia.specialita
                                          + (6 * $scope.squadriglia.brevetti)
                                          + (3 * $scope.iscr.numeroprotagonisti);
      if ( $scope.iscr.tipo != $scope.tipiSfida[0] ) {
        $scope.iscr.punteggiosquadriglia = $scope.iscr.punteggiosquadriglia 
                                          + 2 * $scope.iscr.specialitasquadriglierinuove
                                          + (6 * $scope.iscr.brevettisquadriglierinuove);
      }
    }
  };

  $scope.categoriaDisponibili = function() {

      if ( $scope.iscr.tipo == $scope.tipiSfida[0] ){
          // MISSIONE
          return [ {'desc' : 'Avventura' , 'code' : 0}, {'desc' : 'Originalità', 'code' : 1}, {'desc' : 'Traccia nel Mondo', 'code' : 2} ];
      } else {
          // IMPRESA
          return [ {'desc' : 'Avventura' , 'code' : 0}, {'desc' : 'Originalità', 'code' : 1}, {'desc' : 'Traccia nel Mondo', 'code' : 2}, {'desc' : 'Grande Impresa', 'code' : 2} ];
      }

  }

  $scope.isMissione = function(){
    return $scope.iscr.tipo == $scope.tipiSfida[0];
  }

  $scope.isSfidaSpeciale = function(){
    return null != $scope.squadriglia ? $scope.sfida.speciale : false;
  }

})


