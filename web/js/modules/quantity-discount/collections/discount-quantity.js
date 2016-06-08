define([
    'backbone',
    '../models/discount-quantity',
    'backbone.paginator'
], function(Backbone, DiscountQuantityModel){

    var DiscountQuantityCollection = Backbone.Paginator.requestPager.extend({
        model: DiscountQuantityModel,
        paginator_core: {
            type: 'GET',
            dataType: 'json',
            url: function(){
                return $('#website_url').val() + 'api/store/discounts/id/';
            }
        },
        paginator_ui: {
            firstPage: 1,
            currentPage: 1,
            perPage: 20,
            key: ''
        },
        server_api: {
            count: true,
            limit: function(){ return this.perPage; },
            offset: function(){ return (this.currentPage - this.firstPage) * this.perPage; },
            key: function(){ return this.key; }
        },
        parse: function(response, xhr){
            this.totalCount = _.has(response, 'totalCount') ? response.totalCount : response.length;
            this.totalPages = Math.ceil(this.totalCount / this.perPage);
            return _.has(response, 'data') ? response.data : response;
        }
    });

    return DiscountQuantityCollection;
});