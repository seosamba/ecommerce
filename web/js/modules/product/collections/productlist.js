define([
	'libs/underscore/underscore',
	'libs/backbone/backbone',
	'modules/product/models/product'
], function(_, Backbone, ProductModel){
	var ProductList = Backbone.Collection.extend({
		model: ProductModel,
		url: '/plugin/shopping/run/getdata/type/product/',
		initialize: function(){
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