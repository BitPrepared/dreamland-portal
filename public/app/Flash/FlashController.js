define(['angular','dreamApp'], function(angular,dreamApp){
   'use strict';

	var flashController = dreamApp.controller('FlashController', function ($scope, $rootScope, $http, $state, $stateParams) {

		$scope.flashmessage = angular.isDefined($stateParams.msg) ? $stateParams.msg : 'tutto ok';

	});

return flashController;

});

