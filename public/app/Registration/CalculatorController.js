define(['angular','dreamApp'], function(angular,dreamApp){
   'use strict';

  var calculator = dreamApp.controller('CalculatorController', function ($scope, $rootScope, $http, $state, $stateParams) {

    $scope.update = function() {
      debugger;
      $scope.parent.punteggiosquadriglia = $scope.parent.numerosquadriglieri + $scope.parent.specialitasquadriglieri + (4 * $scope.parent.brevettisquadriglieri);
      if ( $scope.parent.specialitadisquadriglia ) {
        $scope.parent.punteggiosquadriglia += 10;
      }
      if ( $scope.parent.rinnovospecialitadisquadriglia ) {
        $scope.parent.punteggiosquadriglia += 7;
      }

    };


  });

  return calculator;


});