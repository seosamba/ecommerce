define([
	'underscore',
	'backbone',
	'../collections/selections',
	'../views/selection'
], function(_, Backbone, Selections, SelectionView){
	var ProductOptionView = Backbone.View.extend({
		template: $('#optionMainTemplate').template(),
		optionListTemplate: $('#optionListTemplate').template(),
        tagName: 'div',
        className: 'option-wrapper grid_12 alpha omega mt10px background',

		events: {
			'click .remove-option': 'kill',
			'click .add-selection-btn': 'addSelection',
			'change select.option-type-select': 'typeChange',
			'change input.option-title': 'titleChange',
			'change input[name=isTemplate]': 'toggleIsTemplate',
			'change input[name=templateName]': 'templateNameChange'
		},
		initialize: function(){
			this.model.bind('change:type', this.render, this);
			this.model.view = this;
			
			if (this.model.has('selection') && this.model.get('selection') instanceof Backbone.Collection){
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
                $(this.el).find('.option-content').html($.tmpl(this.optionListTemplate, this.model));
                this.renderAllSelections();
            }
            if (this.model.has('isTemplate')){
                this.$('input[name=isTemplate]').attr('checked', 'checked').parent().hide();
                this.$('input[name=templateName]').show();
            } else {
                this.$('input[name=isTemplate]').removeAttr('checked').parent().show();
                this.$('input[name=templateName]').hide();
            }
            checkboxRadioStyle();
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
            checkboxRadioStyle();
		},
		renderSelection: function(selection){
			if (!selection.has('_deleted')){
				selection.set({'_parent': this.cid});
				var view = new SelectionView({model: selection});
				this.$el.find('div.option-list-holder').append(view.render().el);
			}
			
		},
		renderAllSelections: function(){
			if (this.model.has('selection')){
				this.model.get('selection').each(this.renderSelection, this);
			}
		},
		toggleIsTemplate: function(e){
			var $tplNameInput   = $(e.target).closest('div').find('input[name=templateName]'),
                $tplLabel       = $(e.target).closest('div').find('label');
            if (e.target.checked) {
                this.model.set({isTemplate: true});
                $tplLabel.hide();
                $tplNameInput.show();
            } else {
                this.model.unset('isTemplate');
                $tplLabel.show();
                $tplNameInput.hide();
            }
		},
        templateNameChange: function(e){
            this.model.set({templateName: e.target.value});
        },
		kill: function(){
			this.model.collection.remove(this.model);
			this.remove();
		}
	});
	
	return ProductOptionView;
});