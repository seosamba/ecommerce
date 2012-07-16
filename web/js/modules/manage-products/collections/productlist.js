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
            perPage: 20
        },
        server_api: {
            'key': function() { return $('input[name="productsearch"]').val(); },
            'limit': function() { return this.perPage },
            'offset': function() { return this.currentPage * this.perPage },
            'order': '',
            'count': true,
            'ftag': function() { return $('select[name="filter-tag"]').val() || ''; },
            'fbrand': function() { return $('select[name="filter-brand"]').val() || ''; }
        },
        parse: function (response, xhr) {
            console.log(response);
            var resultsCount = _.size(response),
                totalCount = xhr.getResponseHeader('X-Toasted-Total-Rows');
            if (!isNaN(totalCount)){
                this.totalPages = Math.floor(totalCount/this.perPage);
                this.totalCount = totalCount;
            }
            if (resultsCount < this.perPage){
                this.totalPages = this.currentPage;
            }
            return response;
        },
        getFilter: function(){
            var self = this,
                params = {};
            _.each(self.server_api, function(value, key){
                if( _.isFunction(value) ) {
                    value = _.bind(value, self);
                    value = value();
                }
                params[key] = value;
            });

            return params;
        }
    });

	return ProductCollection;
});