define([
	'underscore',
	'backbone'
], function(_, Backbone){
	var SelectionView = Backbone.View.extend({
		tagName: 'div',
		className: 'wrap mt5px',
		template: $('#listItemTemplate').template(),
		events: {
			"click button.item-remove": 'markToDelete',
			'change :input': 'updateModel',
			'change input[name^=isDefault]': 'changeDefault'
		},
		inputs: {},
		updateModel: function(){
			var data = {
				title:		 this.inputs.title.val(),
				priceSign:	 this.inputs.priceModifierSign.val(),
				priceType:	 this.inputs.priceModifierType.val(),
				priceValue:	 this.inputs.priceModifierValue.val(),
				weightSign:  this.inputs.weightModifierSign.val(),
				weightValue: this.inputs.weightModifierValue.val()
			}
			this.model.set(data);
		},
		initialize: function(){
//			this.model.bind('change:isDefault', this.render, this);
			this.model.view = this;
		},
		render: function(){
			$(this.el).html($.tmpl(this.template, this.model.toJSON()));
			this.inputs = {
				title: this.$('input[name="title"]'),
				priceModifierSign: this.$('select[name="priceModifierSign"]'),
				priceModifierType: this.$('select[name="priceModifierType"]'),
				priceModifierValue: this.$('input[name="priceModifierValue"]'),
				weightModifierSign: this.$('select[name="weightModifierSign"]'),
				weightModifierValue: this.$('input[name="weightModifierValue"]')
			}
			return this;
		},
		markToDelete: function(){
			this.model.set({isDefault: '0'});
			if (this.model.isNew()){
				this.model.collection.remove(this.model);
			} else {
				this.model.set({'_deleted': true});
			}
            checkboxRadioStyle();
			this.remove();
		},
		changeDefault: function(){
			var id = this.model.cid;
			this.model.collection.map(function(selection){
				if (selection.cid !== id){
					selection.set({isDefault: '0'});
				} else {
					selection.set({isDefault: '1'});
				}
			})
		}
	});
	return SelectionView;
});