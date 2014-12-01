authenticationModule.constant('USER_ROLES', {
  all: '*',
  admin: 'admin',
  editor: 'editor',
  guest: 'guest'
}); 

authenticationModule.factory('AuthService', function ($http, Session, USER_ROLES) {

  var authService = {};
 
  authService.login = function (credentials) {
    //FAKE
    Session.create(0, 'rtd@agesci.it', USER_ROLES.admin);

    // return $http
    //   .post('/login', credentials)
    //   .then(function (res) {
    //     Session.create(res.data.id, res.data.user.id,res.data.user.role);
    //     return res.data.user;
    //   });

    return $http
      .get('/login', credentials)
      .then(function (res) {
        // debugger;
        Session.create(res.data.id, res.data.user.id,res.data.user.role);
        return res.data.user;
      });

  };
 
  authService.isAuthenticated = function () {
    return !!Session.userId;
  };
 
  authService.isAuthorized = function (authorizedRoles) {
    if (!angular.isArray(authorizedRoles)) {
      authorizedRoles = [authorizedRoles];
    }

    // all oppure prima verifico che sia autenticato dopo verifico se il ruolo e' coerente
    return authorizedRoles.indexOf(USER_ROLES.all) !== -1 || (authService.isAuthenticated() &&
      authorizedRoles.indexOf(Session.userRole) !== -1);
  };
 
  return authService;

});


//   return {

//     login: function (credentials) {

//       //FAKE
//       Session.create(0, 'rtd@agesci.it', USER_ROLES.admin);

//       return $http
//         .post('/login', credentials)
//         .then(function (res) {
//           Session.create(res.id, res.userid, res.role);
//         });
//      },   

//     authService.login = function (credentials) {
//     return $http
//       .post('/login', credentials)
//       .then(function (res) {
//         Session.create(res.data.id, res.data.user.id,
//                        res.data.user.role);
//         return res.data.user;
//       });
//     };

//     isAuthenticated: function () {
//       debugger;
//       return !!Session.userId;
//     },

//     isAuthorized: function (authorizedRoles) {
//       if (!angular.isArray(authorizedRoles)) {
//         authorizedRoles = [authorizedRoles];
//       }
      
//       // all oppure prima verifico che sia autenticato dopo verifico se il ruolo e' coerente
//       return authorizedRoles.indexOf(USER_ROLES.all) !== -1 || this.isAuthenticated() && authorizedRoles.indexOf(Session.userRole) !== -1;
//     }

//   };

// });
