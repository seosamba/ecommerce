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
			"keypress span.category-editable": "updateOnEnterPressed",
			"blur span.category-editable": "disableEdit"
		},
		initialize: function(){
			this.model.bind('change', this.render, this);
			this.model.view = this;
		},
		render: function(){
			$(this.el).html($.tmpl(this.template, this.model.toJSON()));
			this.nameInput = $(this.el).children('span.category-editable'); 
			return this;
		},
		kill: function(){
			var confirmMsg = $('#new-category').data('confirmmsg').replace('%cat%', this.model.get('name'));
			var modelHolder = this.model;
			
			smoke.confirm(confirmMsg, function(e){
				if (e){
					modelHolder.destroy({success: function(model, response) {
						model.view.remove();
					}});
				}
			}, {ok:"Do it", cancel:"No way"});	
		},
		edit: function(){
			this.nameInput.attr('contenteditable', true).focus();
		},
		disableEdit: function(){
			this.nameInput.removeAttr('contenteditable');
		},
		updateOnEnterPressed: function(e){
			if (e.keyCode == 13) {
				this.save();
				return false;
			}
		},
		save: function(){
			this.model.save({name: this.nameInput.text()});
			this.disableEdit();
		}
		
	});
	
	return CategoryView;
});