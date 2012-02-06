define([
	'libs/underscore/underscore',
	'libs/backbone/backbone'
], function(_, Backbone){
	
	var CategoryView = Backbone.View.extend({
		tagName: 'div',
		className: 'category-widget',
		template: $('#categoryTemplate').template(),
		nameInput: null,
		events: {
			"click span.ui-icon-closethick": "kill",
			"dblclick span.category-editable": "edit",
			"keypress span.category-editable": "preventLineBreak",
			"blur span.category-editable": "save"
		},
		initialize: function(){
			this.model.bind('change', this.render, this);
			this.model.view = this;
		},
		render: function(){
			$(this.el).html($.tmpl(this.template, this.model.toJSON()));
			this.nameInput = this.$el.children('span.category-editable');
			return this;
		},
		kill: function(){
			var confirmMsg = $('#new-category').data('confirmmsg').replace('%cat%', this.model.get('name'));
			var modelHolder = this.model;
			
			showConfirm(confirmMsg, function(){
				modelHolder.destroy({success: function(model, response) {
                    model.view.remove();
                }});
			});
		},
		edit: function(){
            this.buffer = this.nameInput.text();
            this.nameInput.attr('contenteditable', true).css({border: '1px solid #999'}).focus();
		},
		preventLineBreak: function(e){
			if (e.keyCode == 13) {
				return false;
			}
		},
		save: function(){
            if (this.buffer !== this.nameInput.text()){
                this.model.save({name: this.nameInput.text()});
            }
            this.nameInput.css({border: 'none'}).removeAttr('contenteditable');
		}
		
	});
	
	return CategoryView;
});