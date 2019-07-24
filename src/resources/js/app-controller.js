var app = angular.module("wttDashboard", ['angular-ladda', 'ngRoute', 'cgBusy']);

app.config(['$routeProvider', '$httpProvider', '$interpolateProvider',
    function ($routeProvider, $httpProvider, $interpolateProvider) {

        $routeProvider.when("/account", {
            template: wtt_dashboard.accountTemplate
        }).when("/login", {
            template: wtt_dashboard.loginTemplate
        }).otherwise({
            redirectTo: '/account'
        });

        $httpProvider.interceptors.push('authInterceptor');
        $interpolateProvider.startSymbol('{[').endSymbol(']}');
    }]);

app.controller("appController", ['$scope', '$http', '$q', '$location',
    function ($scope, $http, $q, $location) {

        $scope.error = null;
        $scope.message = "Invalid Email or Password";
        $scope.loading = false;
        $scope.promiseMessage = "Please wait...";
        var WttApiBaseUrl = wtt_dashboard.wttApiBaseUrl;
        // $scope.WttAppUrl = WttApiBaseUrl.slice(0, -5) + "/#/";
        $scope.WttAppUrl = "https://app.textmetrics.com/#/";

        $scope.loginModel = {
            RememberMe: true
        };

        var getData = function (url) {
            var deffered = $q.defer();

            $http.get(url, {
                withCredentials: true
            })
                .success(function (data, status, headers, config) {
                    deffered.resolve(data);
                }).error(function (error, status) { // called asynchronously if an error occurs
                deffered.reject(error.Message);
            });
            return deffered.promise;
        };

        var postData = function (url, dataIn) {
            var deffered = $q.defer();

            $http.post(url, dataIn, {
                withCredentials: true
            })
                .success(function (data, status, headers, config) {
                    deffered.resolve(data);
                }).error(function (error, status) { // called asynchronously if an error occurs
                deffered.reject(error.Message);
                $scope.error = error.Message;
                $scope.loading = false;
            });
            return deffered.promise;
        };

        var csrfTokenName = window.Craft ? window.Craft.csrfTokenName : '';
        var csrfTokenValue = window.Craft ? window.Craft.csrfTokenValue : '';

        var sendMessageToServer = function (token) {
            var deffered = $q.defer();
            var userId = wtt_dashboard.currentUserId;
            var userRecordId = wtt_dashboard.userData !== null ? wtt_dashboard.userData.id : ""; //jQuery("#user-record-id").val();
            var headers = {};

            var params = {
                userId: userId,
                userRecordId: userRecordId,
                accessToken: token
            };

            if(csrfTokenName !== 'undefined' || csrfTokenValue !== 'undefined') {
                headers['X-CSRF-TOKEN'] = csrfTokenValue;
            }

            jQuery.ajax({
                url: Craft.getActionUrl('webtexttool/user/save-access-token'),
                type: 'POST',
                dataType: 'json',
                data: params,
                headers: headers,
                success: function (result) {
                    deffered.resolve(result);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    deffered.reject(errorThrown);
                    console.log(JSON.stringify(jqXHR));
                    console.log("AJAX error: " + textStatus + ' : ' + errorThrown);
                }
            });

            return deffered.promise;
        };

        $scope.login = function () {
            $scope.loading = true;

            postData(WttApiBaseUrl + "user/login", $scope.loginModel).then(function (response) {
                if (response) {
                    localStorage.setItem('wtt_token', response.access_token);
                    sendMessageToServer(response.access_token).then(function () {
                        window.location.reload();
                    }, function(result) {
                        $scope.loading = false;
                        $scope.error = result;
                    });
                } else {
                    $scope.loading = false;
                    $scope.error = $scope.message;
                }

            });
        };

        $scope.logout = function () {

            $scope.loading = true;

            getData(WttApiBaseUrl + "user/logout").then(function () {
                localStorage.removeItem('wtt_token');
                sendMessageToServer('').then(function () {
                    window.location.reload();
                }, function(result) {
                    $scope.loading = false;
                    $scope.error = result;
                });
            });
        };

        function init() {
            $scope.authCode = wtt_dashboard.userData !== null ? wtt_dashboard.userData.accessToken : "";
            $scope.apiKey = wtt_dashboard.wttApiKey;

            if (localStorage.getItem('wtt_token') === "" || localStorage.getItem('wtt_token') === null || $scope.authCode !== '') {
                localStorage.setItem('wtt_token', $scope.authCode);

                if ($scope.apiKey !== '') {
                    localStorage.setItem('wtt_token', $scope.apiKey);
                }
            }

            getData(WttApiBaseUrl + "user/authenticated").then(function (result) {
                $scope.auth = result;

                if ($location.path().indexOf('account') > 0 && !$scope.auth) {
                    $location.path('/login');
                } else if ($location.path().indexOf('login') > 0 && $scope.auth) {
                    $location.path('/account');
                }

                if (result) {
                    $scope.accountPromise = getData(WttApiBaseUrl + "user/info").then(function (userInfo) {
                        $scope.userInfo = userInfo;

                        $scope.displayTrialDays = function (days) {
                            if (days <= 0) return "0";
                            return days;
                        };
                    });
                }
            })
        }

        init();

    }
]);

app.factory('authInterceptor', ["$q",
    function ($q) {
        var sessionInjector = {
            request: function (config) {
                config.headers = config.headers || {};
                config.headers.Authorization = 'Bearer ' + localStorage.getItem('wtt_token');
                config.headers.WttSource = 'CraftCMS';
                return config;
            },
            response: function (response) {
                return response || $q.when(response);
            }
        };
        return sessionInjector;
    }
]);