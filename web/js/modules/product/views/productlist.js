define([
	'underscore',
	'backbone'
], function(_, Backbone){
	
	var ProductView = Backbone.View.extend({
		tagName: 'div',
		className: 'productlisting',
		template: _.template($('#productListingTemplate').html()),
        container: $('#product-list-holder'),
		events: {},
		initialize: function(){
			this.model.on('change', this.render, this);
            this.model.on('remove', this.remove, this);
		},
		render: function(){
            var data = {
                websiteUrl: $('#website_url').val(),
                mediaPath: $('#media-path').val(),
                showDelete: _.has(this.options, 'showDelete') ? this.options.showDelete : false
            };
            if (!this.model.has('rendered')){
                data.lazy = true;
                this.model.set({rendered: true}, {silent: true});
            }
			$(this.el).html(this.template(_.extend(data, this.model.toJSON())));
			return this;
		}
	});
	
	return ProductView;
});