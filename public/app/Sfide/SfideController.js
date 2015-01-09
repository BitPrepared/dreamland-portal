define(['angular','dreamApp','underscore'], function(angular,dreamApp,_){
   'use strict';

  var sfideController = dreamApp.controller('SfideController', function ($scope, $q, $rootScope, $http, $state, ngDialog, $stateParams, Portal, $window) {

    $scope.sfidaid = angular.isDefined($stateParams.id) ? $stateParams.id : -1;
    $scope.squadriglia = null;
    $scope.sfida = null;

    $scope.tipiSfida = ['missione','impresa'];
    $scope.categoriaImpresa = [ {'desc' : 'Avventura' , 'code' : 0}, {'desc' : 'Originalita', 'code' : 1}, {'desc' : 'Traccia nel Mondo', 'code' : 2}, {'desc' : 'Grande Impresa', 'code' : 3} ];
    $scope.categoriaMissione = [ {'desc' : 'Avventura' , 'code' : 0}, {'desc' : 'Originalita', 'code' : 1}, {'desc' : 'Traccia nel Mondo', 'code' : 2} ];
    $scope.categoriaMissioneSpeciale = [ {'desc' : 'Avventura' , 'code' : 0}, {'desc' : 'Originalita', 'code' : 1}, {'desc' : 'Traccia nel Mondo', 'code' : 2} , {'desc' : 'Altro', 'code' : 3} ];

    $scope.iscr = {
      tipo : $scope.tipiSfida[1],
      categoriaSfida: $scope.categoriaMissione[0],
      descrizione: null,
      numerosquadriglieri : 0,
      specialitasquadriglierinuove : 0,
      brevettisquadriglierinuove : 0,
      numeroprotagonisti : 0,
      punteggiosquadriglia : 0
    };

    $scope.categoriaSfidaDesc = '';

    var descArrayCategorieDiSfida = [
    //Avventura:
    'il Sogno che si concretizzerà in un impresa o missione, dovrà caratterizzarsi per lo spirito d’avventura, dovrà essere avvincente, entusiasmante e sfidante, qualcosa che sia degno di un vero avventuriere',
    //Originalità:
    'il Sogno che si concretizzerà in un impresa o missione, dovrà caratterizzarsi per la sua originalità, non le solite idee per imprese e missioni, ma qualcosa che riesca a stupire, insomma che sia speciale, che abbia quel tocco di creatività e ingegno fuori dal comune.',
    //'Traccia nel Mondo':
    'il Sogno che si concretizzerà in un impresa o missione, dovrà caratterizzarsi per il segno lasciato, per la traccia, per il cambiamento apportato sia nel mondo, nella realtà, nel territorio, sia in sé stessi, negli altri, nelle persone incontrate, un cambiamento che sia sfida vera.',
    //Grande Impresa:
    'il Sogno che si concretizzerà in un impresa o missione, dovrà caratterizzarsi per il suo puntare in alto. Fondamentali saranno impegno, costanza e tenacia da parte di tutti. Fatica e sforzo e cura dei particolari, saranno i mezzi necessari per realizzare quella che verrà ricordata nel tempo come la grande Impresa (Missione) della Squadriglia.'
    ];

    $scope.step = angular.isDefined($stateParams.step) ? $stateParams.step : 1;

    $scope.caratteriMancanti = function() {
        var scritti = angular.element('#descrizione').val().length;
        return 50 - scritti > 0 ? 50 - scritti : 0 ;
    }

    $scope.setStep = function(step){
      $scope.step = step;
      $scope.update();
    };

    $scope.partecipa = function(){
      $scope.enableButton = false;

      Portal.updateSquadriglia($scope.squadriglia, function() {
        var newRequest = {};
        newRequest.specialitasquadriglierinuove = $scope.iscr.specialitasquadriglierinuove;
        newRequest.brevettisquadriglierinuove = $scope.iscr.brevettisquadriglierinuove;
        newRequest.obiettivopunteggio = $scope.iscr.punteggiosquadriglia;
        newRequest.categoriaSfida = $scope.iscr.categoriaSfida;
        newRequest.numeroprotagonisti = $scope.iscr.numeroprotagonisti;
        newRequest.descrizione = $scope.iscr.descrizione;
        newRequest.tipo = $scope.iscr.tipo;

        $http.put('./api/sfide/iscrizione/'+$scope.sfidaid, newRequest).
          success(function(data, status, headers, config) {
            // $state.go('home.registration.ok',{ msg : 'Iscrizione alla sfida completata con successo'},{reload : true});
            $('#spinnerdiv').show();
            $window.location.href = $scope.sfida.permalink+'?iscritto';
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
                                            + (3 * $scope.iscr.numeroprotagonisti) + 20; //20 mi auguro il successo
        if ( $scope.iscr.tipo != $scope.tipiSfida[0] ) {
          $scope.iscr.punteggiosquadriglia = $scope.iscr.punteggiosquadriglia 
                                            + 2 * $scope.iscr.specialitasquadriglierinuove
                                            + (6 * $scope.iscr.brevettisquadriglierinuove);
        }
      }
      if ( $scope.iscr.categoriaSfida != null ) {
        $scope.categoriaSfidaDesc = descArrayCategorieDiSfida[$scope.iscr.categoriaSfida.code];
      }

    };

    $scope.updateSfida = function() {
      if ( $scope.sfida.categoria != null ) {
        if ( $scope.iscr.tipo == $scope.tipiSfida[0] ) {
          //missione
          $scope.iscr.categoriaSfida = _.find($scope.categoriaMissione, function(cat){ return $scope.sfida.categoria.desc == cat.desc; });
        } else {
          //impresa
          $scope.iscr.categoriaSfida = _.find($scope.categoriaImpresa, function(cat){ return $scope.sfida.categoria.desc == cat.desc; });
        }
      } else {
        $scope.iscr.categoriaSfida = null;
      }
      $scope.categoriaSfidaDesc = '';
      $scope.update();
    }

    $scope.isMissione = function(){
      return $scope.iscr.tipo == $scope.tipiSfida[0];
    }

    $scope.isSfidaSpeciale = function(){
      return null != $scope.sfida && $scope.sfida.sfidaspeciale;
    }

    Portal.loadSquadriglia(function(squadriglia){
      // $scope.squadriglia = {
      //   componenti : parseInt(squadriglia.componenti),
      //   specialita : parseInt(squadriglia.specialita),
      //   brevetti : parseInt(squadriglia.brevetti)
      // }
      $scope.squadriglia = squadriglia;

      Portal.loadSfida($scope.sfidaid,function(sfida){
        $scope.sfida = sfida;
        if ( !sfida.sfidaspeciale ) {
          if ( $scope.iscr.tipo != $scope.tipiSfida[0] ) { //impresa
            if ( sfida.categoria == null ) $scope.iscr.categoriaSfida = null;
            else $scope.iscr.categoriaSfida = _.find($scope.categoriaImpresa, function(cat){ return sfida.categoria.desc == cat.desc; });
          } else { //missione
            $scope.iscr.categoriaSfida = _.find($scope.categoriaMissione, function(cat){ return sfida.categoria.desc == cat.desc; });
          }
        } else {
          $scope.iscr.categoriaSfida = _.find($scope.categoriaMissioneSpeciale, function(cat){ return sfida.categoria.desc == cat.desc; });
        }

        $scope.update();
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


  });

  return sfideController;

});


