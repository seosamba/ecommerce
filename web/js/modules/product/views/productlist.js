define([
	'underscore',
	'backbone'
], function(_, Backbone){
	
	var ProductView = Backbone.View.extend({
		tagName: 'div',
		className: 'productlisting',
		template: $('#productListingTemplate').template(),
        container: $('#product-list-holder'),
		events: {
            'change input.marker': 'mark',
            'click #product-list-holder a[href^=#edit]': 'runAction'
		},
		initialize: function(){
			this.model.on('change', this.render, this);
            this.model.on('remove', this.remove, this);
		},
		render: function(){
			var data = this.model.toJSON();
            data.websiteUrl = $('#website_url').val();
			if (this.options.hasOwnProperty('showDelete')){
				data.showDelete = this.options.showDelete;
			}
            if (!this.model.has('rendered')){
                data.lazy = true;
                this.model.set('rendered', true);
            }
			$(this.el).html($.tmpl(this.template, data));
			return this;
		},
        mark: function(e){
            if (e.target.checked){
                this.model.set({marked: true});
            } else {
                this.model.has('marked') && this.model.unset('marked');
            }
        },
        runAction: function(e){
            if ($('#product-list-holder').data('type') === 'related'){
                app.addRelated(this.model.get('id'));
                $('#product-list:visible').hide('slide');
                return false;
            }
        }
	});
	
	return ProductView;
});