define([
	'underscore',
	'backbone',
	'../collections/options'
], function(_, Backbone, ProductOptions){
	
	var Product = Backbone.Model.extend({
		urlRoot: '/api/store/products/id/',
		defaults: {
			name: '',
			sku: '',
			mpn: '',
			brand: null,
			shortDescription: '',
			fullDescription: '',
			enabled: 1,
			taxClass: 1,
			related: [],
            photo: null,
            options: new ProductOptions()
		},
		initialize: function (){
            this.bind('error', function(model, error) {
                showMessage(error, true);
            });
        },
		validate: function(attrs) {
			if (attrs.hasOwnProperty('price') && isNaN(attrs.price)){
				return 'Price must be a number, e.g: 12.95';
			}
		},
        parse: function(data) {
            data.options = new ProductOptions(!_.isEmpty(data.defaultOptions) ? data.defaultOptions : []);
            return data;
        }
	});
	
	return Product;
});