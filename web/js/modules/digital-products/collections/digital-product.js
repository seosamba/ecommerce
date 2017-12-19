define([
    'backbone',
    '../models/digital-product',
    'backbone.paginator'
], function (Backbone, DigitalProductModel) {

    var DigitalProductCollection = Backbone.Paginator.requestPager.extend({
        model: DigitalProductModel,
        paginator_core: {
            type: 'GET',
            dataType: 'json',
            url: function () {
                return $('#website_url').val() + 'api/store/digitalproducts/id/';
            }
        },
        paginator_ui: {
            firstPage:    0,
            currentPage:  0,
            perPage:     7,
            totalPages:  10
        },
        server_api: {
            count: true,
            limit: function() { return this.perPage; },
            offset: function() { return this.currentPage * this.perPage },
            key: function(){ return this.key; },
            productId: function () {
                return this.productId;
            }
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

    return DigitalProductCollection;
});