define(['angular','dreamApp','underscore'], function(angular,dreamApp,_){
    'use strict';

    var chiusuraController = dreamApp.controller('ChiusuraController', function ($scope, $q, $rootScope, $http, $state, ngDialog, $stateParams, Portal, $window, $log) {

        $scope.sfidaid = angular.isDefined($stateParams.id) ? $stateParams.id : -1;
        $scope.squadriglia = null;
        $scope.sfida = null;

        $scope.tipiSfida = ['missione','impresa'];
        $scope.categoriaImpresa = [ {'desc' : 'Avventura' , 'code' : 0}, {'desc' : 'Originalita', 'code' : 1}, {'desc' : 'Traccia nel Mondo', 'code' : 2}, {'desc' : 'Grande Impresa', 'code' : 3} ];
        $scope.categoriaMissione = [ {'desc' : 'Avventura' , 'code' : 0}, {'desc' : 'Originalita', 'code' : 1}, {'desc' : 'Traccia nel Mondo', 'code' : 2} ];
        $scope.categoriaMissioneSpeciale = [ {'desc' : 'Avventura' , 'code' : 0}, {'desc' : 'Originalita', 'code' : 1}, {'desc' : 'Traccia nel Mondo', 'code' : 2} , {'desc' : 'Altro', 'code' : 3} ];

        $scope.risultato = {
            protagonisti : 0,
            nuovespecialita : 0,
            nuovibrevetti : 0,
            punteggiosquadriglia : 0,
            provasuperata : true
        }

        $scope.iscr = {
            descrizione: null,
            numerosquadriglieri : 0,
            specialitasquadriglierinuove : 0,
            brevettisquadriglierinuove : 0,
            numeroprotagonisti : 0,
            punteggiosquadriglia : 0,
            obiettivo : 0
        };

        $scope.enableButton = true;

        $scope.isMissione = function(){
            return $scope.iscr.tipo == $scope.tipiSfida[0];
        }

        $scope.isSfidaSpeciale = function(){
            return null != $scope.sfida && $scope.sfida.sfidaspeciale;
        }

        Portal.loadSquadriglia(function(squadriglia){
            $scope.squadriglia = squadriglia;

            $log.info('Sfida '+$scope.sfidaid);

            Portal.loadSfida($scope.sfidaid,function(sfida){
                $scope.sfida = sfida;
                if ( !sfida.sfidaspeciale ) {
                    if ( $scope.iscr.tipo != $scope.tipiSfida[0] ) { //impresa
                        $scope.iscr.categoriaSfida = _.find($scope.categoriaImpresa, function(cat){ return sfida.categoria.desc == cat.desc; });
                    } else { //missione
                        $scope.iscr.categoriaSfida = _.find($scope.categoriaMissione, function(cat){ return sfida.categoria.desc == cat.desc; });
                    }
                } else {
                    $scope.iscr.categoriaSfida = _.find($scope.categoriaMissioneSpeciale, function(cat){ return sfida.categoria.desc == cat.desc; });
                }

                $scope.iscr.obiettivo = sfida.obiettivopunteggio;

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

        $scope.update = function() {
            if ( null != $scope.squadriglia ) {
                $scope.risultato.punteggiosquadriglia = $scope.squadriglia.componenti
                                            + 2 * ($scope.squadriglia.specialita + $scope.risultato.nuovespecialita)
                                            + (6 * ($scope.risultato.nuovibrevetti + $scope.squadriglia.brevetti ) )
                                            + (3 * $scope.risultato.protagonisti);
                if ( $scope.risultato.provasuperata ) $scope.risultato.punteggiosquadriglia = $scope.risultato.punteggiosquadriglia + 20;
                if ( $scope.risultato.punteggiosquadriglia >= $scope.iscr.obiettivo  ) {
                    $scope.valutaSuccesso = 'label-success';
                } else {
                    $scope.valutaSuccesso = 'label-warning';
                }

                // in area tolleranza : label-default
            }
        };

        $scope.valutaSuccesso = 'label-info';

        $scope.chiudi = function(){

            var sfida = $scope.risultato;
            Portal.chiudiSfida($scope.sfidaid,function(sfida){



            },function(errore){
                $scope.currentError = errore;
                ngDialog.open({template:'modalDialogId', scope: $scope });
                $scope.enableButton = true;
            });

        }

    });

    return chiusuraController;

});