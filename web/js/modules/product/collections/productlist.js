define([
	'libs/underscore/underscore',
	'libs/backbone/backbone',
	'modules/product/models/product'
], function(_, Backbone, ProductModel){
	var ProductList = Backbone.Collection.extend({
		model: ProductModel,
//		url: '/plugin/shopping/run/getdata/type/product/',
		urlOriginal: '/plugin/shopping/run/getdata/type/product/',
        paginator: {
            limit: 32,
            offset: 0,
            last: false
        },
        url: function() {
            if (!_.isEmpty(this.paginator)){
                return this.urlOriginal + 'offset/'+this.paginator.offset+'/limit/'+this.paginator.limit;
            }
            return this.urlOriginal;
        },
		initialize: function(){
		},
        load: function(callbacks) {
            if (!this.paginator.last) {
                var list = this;

                _.isFunction(callbacks) && (callbacks = [callbacks]);

                return this.fetch({add: true}).done( function(data){
                    list.paginator.offset += list.paginator.limit;
                    if (data.length < list.paginator.limit){
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
                            var categories = _.pluck(prod.get('categories'),'name');
                            return _.any(categories, function(cat){ return cat.toLowerCase().indexOf(term) !== -1 });
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