var genericSearch = angular.module('genericSearch', ['ui.select']);
var translator = window.Translator;

//let's do some initialization first.
genericSearch.config(function ($httpProvider) {
	$httpProvider.interceptors.push(function ($q) {
		return {
			'request': function(config) {
				$('.please-wait').show();

				return config;
			},
			'requestError': function(rejection) {
				$('.please-wait').hide();

				return $q.reject(rejection);
			},	
			'responseError': function(rejection) {
				$('.please-wait').hide();

				return $q.reject(rejection);
			},
			'response': function(response) {
				$('.please-wait').hide();

				return response;
			}
		};
	});
});

genericSearch.controller('genericSearchCtrl', function(
	$scope,
	$log,
	$http,
	clarolineSearch
) {
	$scope.fields 	= [];
	$scope.$log   	= $log;
	$scope.searches = [];
	$scope.selected = [];
	$scope.options  = [];

	//init field list
	$http.get(Routing.generate(clarolineSearch.getFieldRoute())).then(function(d) {
		$scope.fields = d.data;
		$scope.options = generateOptions();
	})

	$scope.refreshOption = function($select) {		
		for (var i = 0; i < $scope.options.length; i++) {
			$scope.options[i].name = getOptionValue($scope.options[i].field, $select.search);
			$scope.options[i].value = $select.search;
		}
	}

	$scope.onSelect = function($item, $model, $select) {
		//angular and its plugins does not make any sense to me.
		$select.selected.pop();
		var cloned = angular.copy($item);
		$select.selected.push(cloned);
		$scope.options.push(getOptionValue($item.field));
		$scope.selected = $select.selected;
	}

	$scope.onRemove = function($item, $model, $select) {
		$scope.selected = $select.selected;
	}

	var getOptionValue = function(field, search) {
		search = !search ? '': search.trim();
		
		return translator.trans(field, {}, 'platform') + ': ' + search + ''; 
	}

	var generateOptions = function() {
		var options = [];

		for (var i = 0; i < $scope.fields.length; i++) {
			options.push(
				{
					id: i,
					name: getOptionValue($scope.fields[i]),
					field: $scope.fields[i],
					value: ''
				}
			);
		}

		return options;
	}
});

genericSearch.directive('clarolinesearch', [
	function clarolinesearch() {
		return {
			restrict: 'E',
			templateUrl: AngularApp.webDir + 'bundles/clarolinecore/js/angular/search/generic/views/search.html',
			replace: false
		}
	}
]);

genericSearch.provider("clarolineSearch", function() {
	var baseRoute = searchRoute = fieldRoute = '';

	this.setBaseRoute = function(route) {
		baseRoute = route;
	};
	
	this.setSearchRoute = function(route) {
		searchRoute = route;
	};

	this.setFieldRoute = function(route) {
		fieldRoute = route;
	};

	this.$get = function($http) {
		return {
			getBaseRoute: function() {
				return baseRoute;
			},
			getSearchRoute: function() {
				return searchRoute;
			},
			getFieldRoute: function() {
				return fieldRoute;
			},
			find: function(searches, page, limit) {
				if (searches.length > 0) {
					var qs = '?';

					for (var i = 0; i < searches.length; i++) {
						qs += searches[i].field +'[]=' + searches[i].value + '&';
					} 

					var route = Routing.generate(baseRoute, {'page': page, 'limit': limit});
					route += qs;

					return $http.get(route);
				} else {
					return $http.get(Routing.generate(searchRoute, {'page': page, 'limit': limit}));
				}
			}
		}
	}
});