define([
	'libs/underscore/underscore',
	'libs/backbone/backbone'
], function(_, Backbone){
	var SelectionView = Backbone.View.extend({
		tagName: 'div',
		className: 'clearfix',
		template: $('#listItemTemplate').template(),
		events: {
			"click button.item-remove": 'remove',
			'change :input': 'updateModel'
		},
		inputs: {},
		updateModel: function(){
			var data = {
				title:				 this.inputs.title.val(),
				priceModifierSign:	 this.inputs.priceModifierSign.val(),
				priceModifierType:	 this.inputs.priceModifierType.val(),
				priceModifierValue:	 this.inputs.priceModifierValue.val(),
				weightModifierSign:  this.inputs.weightModifierSign.val(),
				weightModifierValue: this.inputs.weightModifierValue.val(),
				isDefault:			 this.inputs.isDefault.attr('checked') && 1
			}
			console.log(data);
			this.model.set(data);
		},
		initialize: function(){
			this.model.bind('change', this.render, this);
			this.model.view = this;
		},
		render: function(){
			console.log(this.model.toJSON());
			$(this.el).html($.tmpl(this.template, this.model.toJSON()));
			this.inputs = {
				title: this.$('input[name="title"]'),
				priceModifierSign: this.$('select[name="priceModifierSign"]'),
				priceModifierType: this.$('select[name="priceModifierType"]'),
				priceModifierValue: this.$('input[name="priceModifierValue"]'),
				weightModifierSign: this.$('select[name="weightModifierSign"]'),
				weightModifierValue: this.$('input[name="weightModifierValue"]'),
				isDefault: this.$('input:radio[name^="isdefault"]')
			}
			
			return this;
		}
	});
	return SelectionView;
});