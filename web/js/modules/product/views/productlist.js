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
            'click a': 'runItemAction',
            'change input.marker': 'mark',
            'click span.ui-icon-closethick': 'removeRelated'
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
        runItemAction: function(e){
            if (this.container.attr('id') !== this.$el.parent().attr('id')){
                return false;
            }
            var type =  this.container.data('type');

            if (type === 'related'){
                appRouter.app.addRelated(this.model.get('id'));
                $('#product-list').hide('slide');
                return false;
            }
        },
        removeRelated: function(){
            appRouter.app.removeRelated(this.model.get('id'));
        }
	});
	
	return ProductView;
});