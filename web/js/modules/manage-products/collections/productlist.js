define([
	'Underscore',
	'Backbone',
    'libs/backbone/backbone.paginator',
    'modules/product/models/product'
], function(_, Backbone, Paginator, ProductModel){

    var ProductCollection = Paginator.requestPager.extend({
        model: ProductModel,
        paginator_core: {
            type: 'GET',
            dataType: 'json',
            url: $('#website_url').val() + 'storeapi/v1/product/'
        },
        paginator_ui: {
            firstPage: 0,
            currentPage: 0,
            perPage: 30
//            totalPages: 2
        },
        server_api: {
            'key': function() { return $('input[name="productsearch"]').val(); },
            'limit': function() { return this.perPage },
            'offset': function() { return this.currentPage * this.perPage },
            'order': '',
            'ftag': function() { return $('select[name="filter-tag"]').val() || ''; },
            'fbrand': function() { return $('select[name="filter-brand"]').val() || ''; }
        },
        parse: function (response) {
            var resultsCount = _.size(response);
            if (resultsCount < this.perPage){
                this.totalPages = this.currentPage;
            }
            return response;
        }
    });

	return ProductCollection;
});