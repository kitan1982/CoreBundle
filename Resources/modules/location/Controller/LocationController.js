export default class LocationController {
    constructor($http, LocationAPIService, $uibModal) {
        this.$http = $http
        this.LocationAPIService = LocationAPIService
        this.$uibModal = $uibModal
        this.locations = undefined

        this.columns = [
            {
                name: this.translate('name'),
                prop: 'name',
                canAutoResize: false
            },
            {
                name: this.translate('address'),
                cellRenderer: function() {
                    return '<div>{{ $row.street_number}}, {{ $row.street }}, {{ $row.pc }}, {{ $row.town }}, {{ $row.country }}</div>';
                }
            },
            {
                name: this.translate('actions'),
                cellRenderer: function() {
                    return '<button class="btn-primary btn-xs" ng-click="lc.editLocation($row)" style="margin-right: 8px;"><i class="fa fa-pencil-square-o"></i></button><button class="btn-danger btn-xs" ng-click="lc.removeLocation($row)"><i class="fa fa-trash"></i></button>';
                }
            },
            {
                name: this.translate('coordinates'),
                cellRenderer: function(scope) {
                    var gmaplink = '';
                    //var gmapurl = 'https://www.google.be/maps/@' + scope.$row.latitude + ',' + scope.$row.longitude;
                    //var gmaplink = '<a href="' + gmapurl + '"><i class="fa fa-globe"></i></a>';
                    //does not work yet

                    return '<div>' + translate('latitude') + ': {{ $row.latitude }} | ' + translate('longitude') + ': {{ $row.longitude }} |  ' + gmaplink + ' </div>'
                }
            }
        ];

        this.dataTableOptions = {
            scrollbarV: true,
            columnMode: 'force',
            headerHeight: 50,
            footerHeight: 50,
            columns: this.columns
        };

        this.LocationAPIService.findAll().then(d => this.locations = d.data)
    }  

    translate(key) {
        return window.Translator.trans(key, {}, 'platform');
    } 

    removeLocation(location) {
        this.LocationAPIService.delete(location.id).then(function(d) {
            this.removeLocationCallback(location);
        });
    }

    removeLocationCallback(location) {
        const index = this.locations.indexOf(location);
        if (index > -1 ) this.locations.splice(index, 1); 
    }

    createForm() {
        this.$uibModal.open({
            templateUrl: Routing.generate('api_get_create_location_form', {'_format': 'html'}),
            controller: 'CreateModalController',
            constrollerAs: 'clmc',
            resolve: {
                locations: () => { return this.locations }
            }
        });
    }

    editLocation(location) {
        this.$uibModal.open({
            //bust = no cache
            templateUrl: Routing.generate('api_get_edit_location_form', {'_format': 'html', 'location': location.id}) + '?bust=' + Math.random().toString(36).slice(2),
            controller: 'EditModalController',
            controllerAs: 'elmc',
            resolve: {
                locations: () => { return this.locations },
                location: () => { return location }
            }
        })
    }
}