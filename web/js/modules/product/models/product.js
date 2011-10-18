define([
	'libs/underscore/underscore',
	'libs/backbone/backbone',
	'modules/product/collections/options'
], function(_, Backbone, ProductOptions){
	
	var Product = Backbone.Model.extend({
		urlRoot: '/plugin/shopping/run/getdata/type/product/id/',
		defaults: {
			name: '',
			sku: '',
			mpn: '',
			weight: 0,
			brand: '',
			shortDescription: '',
			fullDescription: '',
			enabled: true,
			price: 0,
			taxGroup: 1,
			pageTemplate: 0,
			options: null,
			categories: []
		},
		initialize: function (){
			if (this.get('options') === null){
				this.set({options: new ProductOptions});
			} else {
				//@todo loading optionlist from array
			}
		},
//		validate: function(attrs) {
//			if (attrs.hasOwnProperty('price') && isNaN(attrs.price)){
//				alert('price must be a number');
//			}
//		}
	});
	
	return Product;
});