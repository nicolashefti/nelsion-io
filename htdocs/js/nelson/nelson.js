angular.module('app').component('nelson', {
    templateUrl: 'js/nelson/nelson.html',
    controller: function ($http, $routeParams, ENV) {
        var $ctrl = this;
        $ctrl.nelson = {};
        $ctrl.getNelson = getNelson;
        $ctrl.updateNelson = updateNelson;
        $ctrl.$onInit = $onInit;

        function $onInit (){
            getNelson();
        }

        function getNelson() {
            $http.get(ENV.apiEndpoint + '/nelson/' + $routeParams.id)
                .then(function (response) {
                        $ctrl.nelson = response.data;

                    },
                    function () {
                        alert('Something went wrong loading Nelson');
                    });
        }

        function updateNelson() {
            $http.post(ENV.apiEndpoint + '/nelson/' + $routeParams.id, $ctrl.nelson)
                .then(function (response) {
                        alert('Ok')
                    },
                    function () {
                        alert('Something went wrong loading the beers');
                    });
        }
    }
});
