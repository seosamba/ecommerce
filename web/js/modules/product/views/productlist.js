define([
	'underscore',
	'backbone',
    'text!../templates/product-listing-template.html',
    'i18n!../../../nls/'+$('input[name=system-language]').val()+'_ln'
], function(_, Backbone, productListingTmpl, i18n){
	
	var ProductView = Backbone.View.extend({
		tagName: 'div',
		className: 'productlisting',
		template: {},
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
                showDelete: _.has(this.options, 'showDelete') ? this.options.showDelete : false,
                i18n: i18n
            };
            if (!this.model.has('rendered')){
                data.lazy = true;
                this.model.set({rendered: true}, {silent: true});
            }
            var products = _.extend(data, this.model.toJSON());

			$(this.el).html(_.template(productListingTmpl, products));
			return this;
		}
	});
	
	return ProductView;
});