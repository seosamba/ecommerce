define([
	'libs/underscore/underscore',
	'libs/backbone/backbone',
	'modules/product/collections/selections',
	'modules/product/views/selection'
], function(_, Backbone, Selections, SelectionView){
	var ProductOptionView = Backbone.View.extend({
		tagName: 'div',
		template: $('#optionMainTemplate').template(),
		optionListTemplate: $('#optionListTemplate').template(),
		collection: null,
		events: {
			'click button.remove-option': 'remove',
			'click .add-selection-btn': 'addSelection',
			'change select.option-type-select': 'typeChange',
			'change input.option-title': 'titleChange'
		},
		initialize: function(){
			this.model.bind('change', this.render, this);
			this.model.view = this;
			this.collection = new Selections;
			this.collection.bind('add', this.renderSelection, this);
			this.collection.bind('reset', this.renderAllSelections, this);
			
			if (this.model.get('type') == 'dropdown' || this.model.get('type') == 'radio'){
				this.model.set({params: this.collection});
			}
		},
		render: function(){
			$(this.el).html($.tmpl(this.template, this.model.toJSON()));
			this.$('select.option-type-select').val(this.model.get('type'));
			if (this.model.has('params')){
				this.$('div.option-content').html($.tmpl(this.optionListTemplate, this.model));
				this.renderAllSelections();
			}
			return this;
		},
		typeChange: function(e){
			var type = e.target.value;
			if (type == 'dropdown' || type == 'radio'){
				if (!this.model.has('params')){
					this.model.set({params: this.collection});
				}
			} else {
				if (this.model.has('params')){
					this.model.unset('params');
				}
			}
			this.model.set({type: type});
		},
		titleChange: function(e){
			this.model.set({title: $(e.target).val()});
		},
		addSelection: function(){
			this.model.get('params').add({_parent: this.cid });
		},
		renderSelection: function(selection){
			var view = new SelectionView({model: selection});
			this.$('div.option-list-holder').append(view.render().el);
		},
		renderAllSelections: function(){
			if (this.model.has('params')){
				this.model.get('params').each(this.renderSelection);
			}
		}
	});
	
	return ProductOptionView;
});