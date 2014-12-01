authenticationModule.controller('FlashController', function ($scope, $rootScope, $http, $state, $stateParams) {

	$scope.flashmessage = angular.isDefined($stateParams.msg) ? $stateParams.msg : 'tutto ok';

});