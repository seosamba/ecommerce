define([
	'libs/underscore/underscore',
	'libs/backbone/backbone'
], function(_, Backbone){
	
	var ProductView = Backbone.View.extend({
		tagName: 'div',
		className: 'productlisting grid_2 ui-corner-all ui-widget-content',
		template: $('#productListingTemplate').template(),
		events: {
		
		},
		initialize: function(){
			this.model.bind('change', this.render, this);
			this.model.view = this;
		},
		render: function(){
			$(this.el).html($.tmpl(this.template, this.model.toJSON()));
			return this;
		}
	});
	
	return ProductView;
});