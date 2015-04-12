'use strict';

angular.module('myApp.view1', ['ngRoute', 'ui.bootstrap', 'dialogs.main', 'pascalprecht.translate', 'dialogs.default-translations', 'ngResource'])

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

    .service('getFileService', ['$http', '$q', function ($http, $q) {

        this.getData = function () {
            var q = $q.defer();
            $http.get('view1/data.json', {headers: {accept: "application/json"}})
                .success(function (result) {
                    q.resolve(result);
                })
                .error(function (err) {
                    q.reject(err);
                });
            return q.promise;
        };
    }])

    //Very important! We should inject dependencies even thought they're not
    .controller('View1Ctrl', ['$scope','$rootScope', '$timeout', '$translate', 'dialogs', '$filter', 'getFileService', function ($scope, $rootScope, $timeout, $translate, dialogs, $filter, getFileService) {

        $scope.lang = 'en-US';
        $scope.language = 'English';

        /*getFileService.getData().then(function(result){
            console.log(result);
        })*/

        var _progress = 33;

        $scope.name = '';
        $scope.confirmed = 'No confirmation yet!';

        $scope.custom = {
            val: 'Initial Value'
        };

        $scope.products = [
            {
                id: 1,
                title: "Dâu Tây Đà Lạt",
                imgUrl: "view1/hatgiong/dautay/1.jpg",
                price: 20000,
                detailPage: "Xem Chi Tiết",
                lifeTime: "20 ngày",
                seedsCount: 30
            },
            {
                id: 2,
                title: "Dâu Tây Đà Lạt",
                imgUrl: "view1/hatgiong/dautay/2.jpg",
                price: 30000,
                detailPage: "Xem Chi Tiết",
                lifeTime: "20 ngày",
                seedsCount: 30
            },
            {
                id: 3,
                title: "Dâu Tây Đà Lạt",
                imgUrl: "view1/hatgiong/dautay/3.jpg",
                price: 30000,
                detailPage: "Xem Chi Tiết",
                lifeTime: "20 ngày",
                seedsCount: 30
            },
            {
                id: 4,
                title: "Dâu Tây Đà Lạt",
                imgUrl: "view1/hatgiong/dautay/4.jpg",
                price: 50000,
                detailPage: "Xem Chi Tiết",
                lifeTime: "20 ngày",
                seedsCount: 30
            },
            {
                id: 5,
                title: "Dâu Tây Đà Lạt",
                imgUrl: "view1/hatgiong/dautay/5.jpg",
                price: 50000,
                detailPage: "Xem Chi Tiết",
                lifeTime: "20 ngày",
                seedsCount: 30
            },
            {
                id: 6,
                title: "Hướng Dương Nhật Bản",
                imgUrl: "view1/hatgiong/huongduong/1.jpg",
                price: 50000,
                detailPage: "Xem Chi Tiết",
                lifeTime: "20 ngày",
                seedsCount: 30
            },
            {
                id: 7,
                title: "Hướng Dương Nhật Bản",
                imgUrl: "view1/hatgiong/huongduong/2.jpg",
                price: 50000,
                detailPage: "Xem Chi Tiết",
                lifeTime: "20 ngày",
                seedsCount: 30
            },
            {
                id: 8,
                title: "Hướng Dương Nhật Bản",
                imgUrl: "view1/hatgiong/huongduong/3.jpg",
                price: 50000,
                detailPage: "Xem Chi Tiết",
                lifeTime: "20 ngày",
                seedsCount: 30
            },
            {
                id: 9,
                title: "Hướng Dương Nhật Bản",
                imgUrl: "view1/hatgiong/huongduong/4.jpg",
                price: 50000,
                detailPage: "Xem Chi Tiết",
                lifeTime: "20 ngày",
                seedsCount: 30
            },
            {
                id: 1,
                title: "Dâu Tây Đà Lạt",
                imgUrl: "view1/hatgiong/dautay/1.jpg",
                price: 20000,
                detailPage: "Xem Chi Tiết",
                lifeTime: "20 ngày",
                seedsCount: 30
            },
            {
                id: 2,
                title: "Dâu Tây Đà Lạt",
                imgUrl: "view1/hatgiong/dautay/2.jpg",
                price: 30000,
                detailPage: "Xem Chi Tiết",
                lifeTime: "20 ngày",
                seedsCount: 30
            },
            {
                id: 3,
                title: "Dâu Tây Đà Lạt",
                imgUrl: "view1/hatgiong/dautay/3.jpg",
                price: 30000,
                detailPage: "Xem Chi Tiết",
                lifeTime: "20 ngày",
                seedsCount: 30
            },
            {
                id: 4,
                title: "Dâu Tây Đà Lạt",
                imgUrl: "view1/hatgiong/dautay/4.jpg",
                price: 50000,
                detailPage: "Xem Chi Tiết",
                lifeTime: "20 ngày",
                seedsCount: 30
            },
            {
                id: 5,
                title: "Dâu Tây Đà Lạt",
                imgUrl: "view1/hatgiong/dautay/5.jpg",
                price: 50000,
                detailPage: "Xem Chi Tiết",
                lifeTime: "20 ngày",
                seedsCount: 30
            },
            {
                id: 6,
                title: "Hướng Dương Nhật Bản",
                imgUrl: "view1/hatgiong/huongduong/1.jpg",
                price: 50000,
                detailPage: "Xem Chi Tiết",
                lifeTime: "20 ngày",
                seedsCount: 30
            },
            {
                id: 7,
                title: "Hướng Dương Nhật Bản",
                imgUrl: "view1/hatgiong/huongduong/2.jpg",
                price: 50000,
                detailPage: "Xem Chi Tiết",
                lifeTime: "20 ngày",
                seedsCount: 30
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
        $scope.getProductsByPrice = function(item) {
            //item is object that we want to compare to
            //$scope.from & $scope.to are get from the inputs
            //console.log($scope.from);
            //console.log($scope.to);
            return item.price >= ($scope.from === undefined || $scope.from === null ? 0 : $scope.from)
                && item.price <= ($scope.to === undefined || $scope.to === null ? 100000 : $scope.to) ;
        };
        $scope.toggleSearchBar = true;
    }])


    .filter('findProductPrice', function () {
        return function (items, from, to) {
            //items are objects that we want to compare to
            //from & to are get from the inputs
            //console.log(items);
            var newItems = [];
            for (var i = 0; i < items.length; i++) {
                if (items[i].price >= (from === undefined || from === null ? 0 : from)
                    && items[i].price <= (to === undefined || to === null ? 100000 : to)) {
                    newItems.push(items[i]);
                }
            };

            return newItems;
        }
    })

    .controller('DetailCtrl', ['$scope', '$routeParams', 'getFileService', 'Data', function ($scope, $routeParams, getFileService, Data) {
        $scope.id = $routeParams.id;
        $scope.data = [];
        getFileService.getData().then(function(result){
            ///console.log(result);
        })
        $scope.data = Data.get({id: $scope.id}, function(data) {
            console.log(data);
        });

    }])
    .factory('Data', ['$resource', function ($resource) {
        return $resource('view1/:id.json', {}, {
            query: {method: 'GET', params: {id: 'details'}, isArray: true}
        });
    }])

