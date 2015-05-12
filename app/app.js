var app = angular.module('myApp', ['ngRoute']);
app.factory("services", ['$http', function($http) {
  var serviceBase = 'services/'
    var obj = {};
    obj.getLeads = function(){
        return $http.get(serviceBase + 'leads');
    }
    obj.getLead = function(leadID){
        return $http.get(serviceBase + 'lead?id=' + leadID);
    }

// till here arya//

    obj.insertLead = function (lead) {
    return $http.post(serviceBase + 'insertLead', lead).then(function (results) {
        return results;
    });
	};

	obj.updateLead = function (id,lead) {
	    return $http.post(serviceBase + 'updateLead', {id:id, lead:lead}).then(function (status) {
	        return status.data;
	    });
	};

	obj.deleteLead = function (id) {
	    return $http.delete(serviceBase + 'deleteLead?id=' + id).then(function (status) {
	        return status.data;
	    });
	};

    return obj;   
}]);

app.controller('listCtrl', function ($scope, services) {
    services.getLeads().then(function(data){
        $scope.leads = data.data;
    });
});

app.controller('editCtrl', function ($scope, $rootScope, $location, $routeParams, services, lead) {
    var leadID = ($routeParams.leadID) ? parseInt($routeParams.leadID) : 0;
   
    $rootScope.title = (leadID > 0) ? 'Edit Lead' : 'Add Lead';
    $scope.buttonText = (leadID > 0) ? 'Update Lead' : 'Add New Lead';

      var original = lead.data;
      original._id = leadID;
      $scope.lead = angular.copy(original);
      $scope.lead._id = leadID;

      $scope.isClean = function() {
        return angular.equals(original, $scope.lead);
      }

      $scope.deleteLead = function(lead) {
        $location.path('/');
        if(confirm("Are you sure to delete lead number: "+$scope.lead._id)==true)
        services.deleteLead(lead.leadNumber);
      };

      $scope.saveLead = function(lead) {
        $location.path('/');
        if (leadID <= 0) {
            services.insertLead(lead);
        }
        else {
            services.updateLead(leadID, lead);
        }
      };
});

app.config(['$routeProvider',
  function($routeProvider) {
    $routeProvider.
      when('/', {
        title: 'Leads',
        templateUrl: 'partials/leads.html',
        controller: 'listCtrl'
      })
      .when('/edit-lead/:leadID', {
        title: 'Edit Leads',
        templateUrl: 'partials/edit-lead.html',
        controller: 'editCtrl',
        resolve: {
          lead: function(services, $route){
            var leadID = $route.current.params.leadID;
            return services.getLead(leadID);
          }
        }
      })
      .otherwise({
        redirectTo: '/'
      });
}]);
app.run(['$location', '$rootScope', function($location, $rootScope) {
    $rootScope.$on('$routeChangeSuccess', function (event, current, previous) {
        $rootScope.title = current.$$route.title;
    });
}]);