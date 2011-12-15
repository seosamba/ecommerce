define([
	'libs/underscore/underscore',
	'libs/backbone/backbone'
], function(_, Backbone){
	
	var ProductView = Backbone.View.extend({
		tagName: 'div',
		className: 'productlisting',
		template: $('#productListingTemplate').template(),
		events: {
		
		},
		initialize: function(){
			this.model.bind('change', this.render, this);
			this.model.view = this;
		},
		render: function(){
			var data = this.model.toJSON();
            data.websiteUrl = $('#websiteUrl').val();
			if (this.options.hasOwnProperty('showDelete')){
				data['showDelete'] = this.options.showDelete;
			}
			$(this.el).html($.tmpl(this.template, data));
            this.$('img.lazy').lazyload({
                container: $('#product-list-holder'),
                effect: 'fadeIn'
            });
			return this;
		}
	});
	
	return ProductView;
});