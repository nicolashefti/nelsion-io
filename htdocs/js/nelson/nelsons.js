angular.module('app').component('nelsons', {
    templateUrl: 'js/nelson/nelsons.html',
    controller: function ($http, ENV) {
        var $ctrl = this;

        $http.get(ENV.apiEndpoint + '/nelsons')
            .then(function (response) {
                    $ctrl.nelsons = response.data;
                },
                function () {
                    alert('Something went wrong loading the nelsons');
                });
    }
});
