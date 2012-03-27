define([
	'Underscore',
	'Backbone',
	'modules/product/collections/options'
], function(_, Backbone, ProductOptions){
	
	var Product = Backbone.Model.extend({
		urlRoot: '/plugin/shopping/run/getdata/type/product/',
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
            photo: null
		},
		initialize: function (){
//			this.set({options: new ProductOptions()});
            this.bind('error', function(model, error) {
                showMessage(error, true);
            });

//            this.bind('change:defaultOptions', function(){
//                this.get('options').reset(this.get('defaultOptions'));
//            }, this);
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