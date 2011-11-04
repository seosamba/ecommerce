define([
	'libs/underscore/underscore',
	'libs/backbone/backbone',
	'modules/product/collections/options'
], function(_, Backbone, ProductOptions){
	
	var Product = Backbone.Model.extend({
		url: '/plugin/shopping/run/getdata/type/product/',
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
			taxClass: 1,
			pageTemplate: 0
		},
		initialize: function (){
			this.set({options: new ProductOptions()});
			this.bind('change:photo', this.setImage, this);
			this.bind('change:defaultOptions', function(){
				this.attributes.options.reset(this.get('defaultOptions'));
			}, this);
		},
		initOptions: function() {
			this.set({options: optList});
		},
		validate: function(attrs) {
			if (attrs.hasOwnProperty('price') && isNaN(attrs.price)){
				smoke.alert('Price must be a number, e.g: 12.95');
			}
		},
		setImage: function(){
			var photo = this.get('photo');
			if (photo instanceof Object){
				this.set({photo: '/media/'+photo.folder+'/product/'+photo.name});
			}
		}
	});
	
	return Product;
});