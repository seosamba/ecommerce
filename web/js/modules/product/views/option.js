define([
	'libs/underscore/underscore',
	'libs/backbone/backbone',
	'modules/product/collections/selections',
	'modules/product/views/selection'
], function(_, Backbone, Selections, SelectionView){
	var ProductOptionView = Backbone.View.extend({
		template: $('#optionMainTemplate').template(),
		optionListTemplate: $('#optionListTemplate').template(),

		events: {
			'click button.remove-option': 'kill',
			'click .add-selection-btn': 'addSelection',
			'change select.option-type-select': 'typeChange',
			'change input.option-title': 'titleChange',
			'change input[name=isTemplate]': 'toggleIsTemplate'
		},
		initialize: function(){
			this.model.bind('change', this.render, this);
			this.model.view = this;
			
			if (this.model.has('selection')){
				this.model.get('selection').bind('add', this.renderSelection, this);
				this.model.get('selection').bind('reset', this.renderAllSelections, this);
				this.model.get('selection').bind('remove', this.render, this);
				this.model.get('selection').bind('change:_deleted', this.render, this);
			}
		},
		render: function(){
			$(this.el).html($.tmpl(this.template, this.model.toJSON()));
			$(this.el).find('select.option-type-select').val(this.model.get('type'));
			
			if (this.model.get('type') == 'dropdown' || this.model.get('type') == 'radio'){
				$(this.el).find('div.option-content').html($.tmpl(this.optionListTemplate, this.model));
				this.renderAllSelections();
			}
			this.$('button.item-remove,button.remove-option').button({
				icons: {
                primary: 'ui-icon-closethick'
				},
				text: false
			}).find('span.ui-button-text').css({padding: '0'});
			this.$('button.add-selection-btn').button({
				icons: { primary: 'ui-icon-plus' }
			});
			return this;
		},
		typeChange: function(e){
			var type = e.target.value;
			this.model.set({type: type});
		},
		titleChange: function(e){
			this.model.set({title: $(e.target).val()});
		},
		addSelection: function(){
			var data = {}
			if (!this.model.get('selection').hasDefault()){
				data.isDefault = '1';
			}
			this.model.get('selection').add(data);
		},
		renderSelection: function(selection){
			if (!selection.has('_deleted')){
				selection.set({'_parent': this.cid});
				var view = new SelectionView({model: selection});
				$(this.el).find('div.option-list-holder').append(view.render().el);
			}
			
		},
		renderAllSelections: function(){
			if (this.model.has('selection')){
				this.model.get('selection').each(this.renderSelection, this);
			}
		},
		toggleIsTemplate: function(e){
			$(e.target).closest('div').find('input[name=templateName]').toggle();
		},
		kill: function(){
			this.model.collection.remove(this.model);
			this.remove();
		}
	});
	
	return ProductOptionView;
});