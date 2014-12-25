define(['angular','sharedServices'], function(angular,sharedServicesModule){
   'use strict';

  var portal = sharedServicesModule.service('Portal', function ($http) {
    
    this.loadSquadriglia = function (x,y) {
        $http.get('./api/squadriglia/').
          success(function(data, status, headers, config) {
            x(data);
          }).
          error(function(data, status, headers, config) {
            y(data);
          });
    };

    this.loadSfida = function (id,x,y) {
        $http.get('./api/sfide/'+id).
          success(function(data, status, headers, config) {
            x(data);
          }).
          error(function(data, status, headers, config) {
            y(data);
          });
    };

      this.chiudiSfida = function (id,x,y) {
          $http.put('./api/sfide/chiusura/'+id).
              success(function(data, status, headers, config) {
                  x(data);
              }).
              error(function(data, status, headers, config) {
                  y(data);
              });
      };

    this.updateSquadriglia = function (squadriglia,x,y) {
      $http.put('./api/squadriglia/',squadriglia).
          success(function(data, status, headers, config) {
            x(data);
          }).
          error(function(data, status, headers, config) {
            y(data);
          });
    };  

    return this;
  })

  return portal;

});