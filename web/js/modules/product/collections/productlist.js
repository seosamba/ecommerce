define([
	'underscore',
	'backbone',
	'../models/product'
], function(_, Backbone, ProductModel){

	var ProductList = Backbone.Collection.extend({
		model: ProductModel,
		urlOriginal:  'api/store/products/',
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
                return $('#website_url').val() + this.urlOriginal + 'offset/'+this.paginator.offset+'/limit/'+this.paginator.limit;
            }
            return $('#website_url').val() + this.urlOriginal;
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