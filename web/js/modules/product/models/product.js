define([
	'libs/underscore/underscore',
	'libs/backbone/backbone',
	'modules/product/collections/options'
], function(_, Backbone, ProductOptions){
	
	var Product = Backbone.Model.extend({
		urlRoot: '/plugin/shopping/run/getdata/type/product/',
		defaults: {
			name: '',
			sku: '',
			mpn: '',
			brand: '',
			shortDescription: '',
			fullDescription: '',
			enabled: 1,
			taxClass: 1,
			related: [],
            photo: null
		},
		initialize: function (){
			this.set({options: new ProductOptions()});
            this.bind('error', function(model, error) {
                smoke.alert(error);
            });
			this.bind('change:photo', this.setImage, this);
			this.bind('change:defaultOptions', function(){
				this.get('options').reset(this.get('defaultOptions'));
			}, this);
		},
		validate: function(attrs) {
			if (attrs.hasOwnProperty('price') && isNaN(attrs.price)){
				return 'Price must be a number, e.g: 12.95';
			}
			if (attrs.related){
				attrs.related = _.map(attrs.related, function(rel){
					return parseInt(rel);
				});
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