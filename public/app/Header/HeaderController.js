'use strict';

headerModule.controller('HeaderController' , function HeaderController($scope, $location){

		$scope.isActive = function (viewLocation) { 
			return viewLocation === $location.path();
		};

		$scope.isAvailable = function (viewLocation) { 
			return false;
		};

	}
);