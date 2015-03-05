define([
	'backbone',
    '../../product/models/product',
    'backbone.paginator'
], function(Backbone, ProductModel){

    var ProductCollection = Backbone.Paginator.requestPager.extend({
        model: ProductModel,
        paginator_core: {
            type: 'GET',
            dataType: 'json',
            url: $('#website_url').val() + 'api/store/products/'
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
            'ftag': function() { return $('select[name="filter-tag"]').val() || []; },
            'fbrand': function() { return $('select[name="filter-brand"]').val() || []; }
        },
        parse: function (response, xhr) {
            var resultsCount = _.has(response, 'count') ? response.count : _.size(response.data);
            var totalCount = response.totalCount;
            if (!isNaN(totalCount)){
                this.totalPages = Math.floor(totalCount/this.perPage);
                this.totalRecords = totalCount;
            }
            return response.data;
        },
        getFilter: function(){
            var self = this,
                params = {};
            _.each(_.pick(this.server_api, 'ftag', 'fbrand', 'key'), function(value, key){
                if( _.isFunction(value) ) {
                    value = _.bind(value, self);
                    value = value();
                }
                params[key] = !_.isNull(value) && value;
            });

            return params;
        },
        batch: function(method, data, useFilter){
            var self = this,
                url  = this.paginator_core.url;

            if (!_.isBoolean(useFilter)) {
                useFilter = !!useFilter;
            }

            if (useFilter === false){
                var checked = this.where({checked: true}),
                    ids     = _.pluck(checked, 'id');
                console.log(ids);
                if (!ids.length){
                    return false;
                }
                url += 'id/'+ids.join(',')+'/';
            } else {
                url += '?'+$.param(this.getFilter());
            }
            $.ajax({
                url: url,
                type: method,
                data: JSON.stringify(data),
                dataType: 'json',
                success: function(response){
                    self.pager();
                },
                error: function(){
                    console.log('Batch action error:', arguments);
                    showMessage('Error occured. Contact support', true);
                }
            });
        }
    });

	return ProductCollection;
});