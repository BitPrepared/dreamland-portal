dreamApp.controller('IscrizioneController', function($scope, $rootScope, $http, $state, ngDialog, $stateParams) {

    $scope.reg = {
        'codcens' : null,
        'nomesq': 'Aquile',
        'squadriglieriaggiunti' : 0,
        'squadriglieripersi' : 0,

        'specialitasquadriglieri' : 0,
        'brevettisquadriglieri' : 0,
        'obiettivoraggiunto' : false,
        'punteggiosquadriglia' : 0
    };

    //+1 per ogni nuovo componete della Squadriglia
    //-1 per ogni componente in meno
    //+2 per ogni specialità individuale conquistata in aggiunta
    //+6 per ogni Brevetto individuale conquistato in aggiunta
    //-1 per ogni Specialità persa con l’abbandono di un componente della Squadriglia
    //-4 per ogni brevetto perso con l’abbandono di un componente della Squadriglia
    //+3 per ogni componente che si sentirà protagonista
    //+20 per il raggiungimento stimato dell'obiettivo



});
