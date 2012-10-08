define([
	'backbone'
], function(Backbone){
	
	var TagView = Backbone.View.extend({
		tagName: 'div',
		className: 'tag-widget',
		template: _.template($('#tagTemplate').html()),
		nameInput: null,
		events: {
			"click span.ui-icon-closethick": "kill",
			"dblclick span.tag-editable": "edit",
			"keypress span.tag-editable": "preventLineBreak",
			"blur span.tag-editable": "save"
		},
		initialize: function(){
			this.model.on('change', this.render, this);
            this.model.on('destroy', this.remove, this);
		},
		render: function(){
			$(this.el).addClass('tagid-'+this.model.get('id')).html(this.template({tag: this.model}));
            this.nameInput = this.$el.children('span.tag-editable');
			return this;
		},
		kill: function(){
			var confirmMsg = $('#new-tag').data('confirmmsg').replace('%tag%', this.model.get('name')),
                model = this.model;
			showConfirm(confirmMsg, function(){ model.destroy(); });
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