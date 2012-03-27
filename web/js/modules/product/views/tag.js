define([
	'Underscore',
	'Backbone'
], function(_, Backbone){
	
	var TagView = Backbone.View.extend({
		tagName: 'div',
		className: 'tag-widget',
		template: $('#tagTemplate').template(),
		nameInput: null,
		events: {
			"click span.ui-icon-closethick": "kill",
			"dblclick span.tag-editable": "edit",
			"keypress span.tag-editable": "preventLineBreak",
			"blur span.tag-editable": "save"
		},
		initialize: function(){
			this.model.bind('change', this.render, this);
			this.model.view = this;
		},
		render: function(){
			$(this.el).html($.tmpl(this.template, this.model.toJSON()));
			this.nameInput = this.$el.children('span.tag-editable');
			return this;
		},
		kill: function(){
			var confirmMsg = $('#new-tag').data('confirmmsg').replace('%tag%', this.model.get('name'));
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
	
	return TagView;
});