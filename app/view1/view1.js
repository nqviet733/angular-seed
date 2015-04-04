'use strict';

angular.module('myApp.view1', ['ngRoute', 'ui.bootstrap'])

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



    .controller('View1Ctrl', ['$scope', function ($scope) {
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
    }])


    .controller('DetailCtrl', ['$scope', '$routeParams', function ($scope, $routeParams) {
        $scope.id = $routeParams.id;
    }])