define([
	'backbone',
	'../models/product',
    'backbone.paginator'
], function(Backbone, ProductModel){

    var ProductList = Backbone.Paginator.requestPager.extend({
        model: ProductModel,
        paginator_core: {
            type: 'GET',
            dataType: 'json',
            url: '/api/store/products/'
        },
        paginator_ui: {
            firstPage: 0,
            currentPage: 0,
            perPage: 24
        },
        server_api: {
            count: true,
            limit: function(){ return this.perPage; },
            offset: function(){ return this.currentPage * this.perPage; }
        },
        parse: function(response, xhr){
            this.totalCount = _.has(response, 'totalCount') ? response.totalCount : response.length;
            this.totalPages = Math.floor(this.totalCount / this.perPage);
            return _.has(response, 'data') ? response.data : response;
        }
    });

    return ProductList;

	var ProductList = Backbone.Collection.extend({
		model: ProductModel,
		urlOriginal: '/api/store/products/',
        paginator: {
            limit: 32,
            offset: 0,
            last: false
        },
        data: {
            key: ''
        },
        url: function() {
            if (!_.isEmpty(this.paginator)){
                return this.urlOriginal + 'offset/'+this.paginator.offset+'/limit/'+this.paginator.limit;
            }
            return this.urlOriginal;
        },
		initialize: function(){
            this.bind('reset', this.resetPaginator, this);
		},
        resetPaginator: function(){
            this.paginator = {limit: 32, offset: 0, last: false};
            return this;
        },
        load: function(callbacks, data) {
            if (!this.paginator.last) {
                var list = this;

                if (_.isObject(data)){
                    this.data = _.extend(this.data, data);
                }

                _.isFunction(callbacks) && (callbacks = [callbacks]);

                return this.fetch({add: true, data: this.data}).done( function(response){
                    list.paginator.offset += list.paginator.limit;
                    if (response.length < list.paginator.limit){
                        list.paginator.last = true;
                    }
                } ).done(callbacks);
            }
        },
        /**
         * Returns set of product that has given term in custom fields
         * @param term Search term
         * @param fields list of product properties to search in
         */
        search: function(term, fields) {
            term = term.toLowerCase();

            if (!fields) {
                fields = ['name', 'sku', 'mpn'];
            }

            return this.filter(function(prod){
                for (var i in fields){
                    if (prod.has(fields[i])) {
                        if(_.isArray(prod.get(fields[i]))) {
                            var tags = _.pluck(prod.get('tags'),'name');
                            return _.any(tags, function(tag){ return tag.toLowerCase().indexOf(term) !== -1 });
                        }
                        if(_.isString(prod.get(fields[i])) && prod.get(fields[i]).toLowerCase().indexOf(term) !== -1 ) {
                            return true;
                        }
                    }
                }
            });
        }
	});
	return ProductList;
});