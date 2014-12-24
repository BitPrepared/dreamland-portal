define(['angular','dreamApp','underscore'], function(angular,dreamApp,_){
    'use strict';

    var chiusuraController = dreamApp.controller('ChiusuraController', function ($scope, $q, $rootScope, $http, $state, ngDialog, $stateParams, Portal, $window) {

        $scope.sfidaid = angular.isDefined($stateParams.id) ? $stateParams.id : -1;
        $scope.squadriglia = null;
        $scope.sfida = null;

        $scope.tipiSfida = ['missione','impresa'];
        $scope.categoriaImpresa = [ {'desc' : 'Avventura' , 'code' : 0}, {'desc' : 'Originalita', 'code' : 1}, {'desc' : 'Traccia nel Mondo', 'code' : 2}, {'desc' : 'Grande Impresa', 'code' : 3} ];
        $scope.categoriaMissione = [ {'desc' : 'Avventura' , 'code' : 0}, {'desc' : 'Originalita', 'code' : 1}, {'desc' : 'Traccia nel Mondo', 'code' : 2} ];
        $scope.categoriaMissioneSpeciale = [ {'desc' : 'Avventura' , 'code' : 0}, {'desc' : 'Originalita', 'code' : 1}, {'desc' : 'Traccia nel Mondo', 'code' : 2} , {'desc' : 'Altro', 'code' : 3} ];

        $scope.iscr = {
            descrizione: null,
            numerosquadriglieri : 0,
            specialitasquadriglierinuove : 0,
            brevettisquadriglierinuove : 0,
            numeroprotagonisti : 0,
            punteggiosquadriglia : 0
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

    });

    return chiusuraController;

});