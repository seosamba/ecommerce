define([
    'backbone',
    '../models/pickup-location',
    'backbone.paginator'
], function(Backbone, PickupLocationModel){

    var PickupLocationCollection = Backbone.Paginator.requestPager.extend({
        model: PickupLocationModel,
        paginator_core: {
            type: 'GET',
            dataType: 'json',
            url: function(){
                return $('#website_url').val() + 'api/store/pickuplocations/';
            }
        },
        paginator_ui: {
            firstPage:    0,
            currentPage:  0,
            perPage:     5,
            totalPages:  10
        },
        server_api: {
            count: true,
            limit: function() { return this.perPage; },
            offset: function() { return this.currentPage * this.perPage },
            key: function(){ return this.key; },
            categoryId:function(){ return this.categoryId; }
        },
        parse: function(response, xhr){
            if (this.server_api.count){
                this.totalRecords = response.totalRecords;
            } else {
                this.totalRecords = response.length;
            }
            this.totalPages = Math.floor(this.totalRecords / this.perPage);
            return this.server_api.count ? response.data : response;
        }

    });

    return PickupLocationCollection;
});