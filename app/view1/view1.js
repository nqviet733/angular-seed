'use strict';

angular.module('myApp.view1', ['ngRoute', 'ui.bootstrap', 'dialogs.main', 'pascalprecht.translate', 'dialogs.default-translations'])

    .config(['$routeProvider', function ($routeProvider) {
        $routeProvider
            .when('/view1', {
                templateUrl: 'view1/view1.html',
                controller: 'View1Ctrl'
            })
            .when('/details/:id', {
                templateUrl: 'view1/details.html',
                controller: 'DetailCtrl'
            });
    }])
    .config(['dialogsProvider','$translateProvider',function(dialogsProvider,$translateProvider){
        dialogsProvider.useBackdrop('static');
        dialogsProvider.useEscClose(false);
        dialogsProvider.useCopy(false);
        dialogsProvider.setSize('sm');

        $translateProvider.translations('es',{
            DIALOGS_ERROR: "Error",
            DIALOGS_ERROR_MSG: "Se ha producido un error desconocido.",
            DIALOGS_CLOSE: "Cerca",
            DIALOGS_PLEASE_WAIT: "Espere por favor",
            DIALOGS_PLEASE_WAIT_ELIPS: "Espere por favor...",
            DIALOGS_PLEASE_WAIT_MSG: "Esperando en la operacion para completar.",
            DIALOGS_PERCENT_COMPLETE: "% Completado",
            DIALOGS_NOTIFICATION: "Notificacion",
            DIALOGS_NOTIFICATION_MSG: "Notificacion de aplicacion Desconocido.",
            DIALOGS_CONFIRMATION: "Confirmacion",
            DIALOGS_CONFIRMATION_MSG: "Se requiere confirmacion.",
            DIALOGS_OK: "Bueno",
            DIALOGS_YES: "Si",
            DIALOGS_NO: "No"
        });

        $translateProvider.preferredLanguage('en-US');
    }])


    //Very important! We should inject dependencies even thought they're not
    .controller('View1Ctrl', ['$scope','$rootScope', '$timeout', '$translate', 'dialogs', function ($scope, $rootScope, $timeout, $translate, dialogs) {

        $scope.lang = 'en-US';
        $scope.language = 'English';

        var _progress = 33;

        $scope.name = '';
        $scope.confirmed = 'No confirmation yet!';

        $scope.custom = {
            val: 'Initial Value'
        };

        $scope.products = [
            {
                id: 1,
                title: "product1",
                imgUrl: "view1/img.jpg",
                price: "20$",
                detailPage: "detailURl"
            },
            {
                id: 2,
                title: "product2",
                imgUrl: "view1/img.jpg",
                price: "20$",
                detailPage: "detailURl"
            },
            {
                id: 3,
                title: "product3",
                imgUrl: "view1/img.jpg",
                price: "20$",
                detailPage: "detailURl"
            },
            {
                id: 4,
                title: "product1",
                imgUrl: "view1/img.jpg",
                price: "20$",
                detailPage: "detailURl"
            },
        ]
        $scope.sayHello = function () {
            console.log("Hello Everyone!");
        }
        $scope.popover = {
            "title": "Title",
            "content": "Hello Popover<br />This is a multiline message!",
            "saved": true
        };
        $scope.date = new Date();
        $scope.text = 'Click a date to check the binding.';
        $scope.launch = function(which) {
            switch(which) {
                case 'error':
                    dialogs.error();
                    break;
                case 'notify':
                    dialogs.notify();
                    break;
            }
        }
    }])


    .controller('DetailCtrl', ['$scope', '$routeParams', function ($scope, $routeParams) {
        $scope.id = $routeParams.id;
    }])