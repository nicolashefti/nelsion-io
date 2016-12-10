angular.module('app').config(configureRoutes);

configureRoutes.$inject = ['$routeProvider'];

function configureRoutes($routeProvider) {
    $routeProvider
        .when('/nelsons', {
            template: '<nelsons></nelsons>'
        })
        .when('/nelson/:id', {
            template: '<nelson></nelson>'
        })
        .otherwise({
            redirectTo: '/nelsons'
        });
}
