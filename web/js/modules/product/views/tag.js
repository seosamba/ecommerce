define([
	'backbone'
], function(Backbone){
	
	var TagView = Backbone.View.extend({
		tagName: 'li',
		className: 'tag-widget',
		template: _.template($('#tagTemplate').html()),
		nameInput: null,
		events: {
			"click .ticon-close": "kill",
			"dblclick .tag-editable": "edit",
			"keypress .tag-editable": "preventLineBreak",
			"blur .tag-editable": "save"
		},
		initialize: function(){
			this.model.on('change', this.render, this);
            this.model.on('destroy', this.remove, this);
		},
		render: function(){
			$(this.el).addClass('tagid-'+this.model.get('id')).html(this.template({tag: this.model}));
            this.nameInput = this.$el.children('.tag-editable');
			return this;
		},
		kill: function(){
			var confirmMsg = $('#new-tag').data('confirmmsg').replace('%tag%', this.model.get('name')),
                model = this.model;
			showConfirm(confirmMsg, function(){ model.destroy(); });
		},
		edit: function(){
            this.buffer = this.nameInput.text();
            this.nameInput.attr('contenteditable', true).focus();
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