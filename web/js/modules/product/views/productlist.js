define([
	'libs/underscore/underscore',
	'libs/backbone/backbone'
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
			this.model.bind('change', this.render, this);
            this.model.bind('remove', this.remove, this);
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
                container: this.container,
                effect: 'fadeIn'
            });
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