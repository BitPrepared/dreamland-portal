define(['angular','dreamApp'], function(angular,dreamApp){
    'use strict';

    var editorController = dreamApp.controller('EditorController', function ($scope, $rootScope, $http, $log,$state, $stateParams) {

        $scope.stato = "alert-info";
        $scope.result = ".";
        $scope.gruppo = null;
        $scope.readyGruppo = false;
        $scope.update = function(){
            if ($scope.gruppo > 0 && $scope.gruppo < 3327) { $scope.readyGruppo = true; }
            else { $scope.readyGruppo = false; }
        }

        $scope.creaEG = function() {
            var gruppo = {};
            gruppo.gruppo = $scope.gruppo;
            $http.post('./api/editor/eg',gruppo).
                success(function(data, status, headers, config) {
                    $log.info('Utente creato ',data);
                    $scope.result = data;
                    $scope.stato = "alert-info";
                }).
                error(function(data, status, headers, config) {
                    $log.error('Problema creazione utente');
                    $scope.result = data;
                    $scope.stato = "alert-danger";
                });
        }

        $scope.creaEG = function() {
            var gruppo = {};
            gruppo.gruppo = $scope.gruppo;
            $http.post('./api/editor/cc',gruppo).
                success(function(data, status, headers, config) {
                    $log.info('Utente creato ',data);
                    $scope.result = data;
                    $scope.stato = "alert-info";
                }).
                error(function(data, status, headers, config) {
                    $log.error('Problema creazione utente');
                    $scope.result = data;
                    $scope.stato = "alert-danger";
                });
        }

    });

    return editorController;

});

