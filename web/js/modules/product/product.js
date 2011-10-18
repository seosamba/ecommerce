$(function(){
	
	// model for categories
	window.Category = Backbone.Model.extend({
		defaults: function(){
			return {
				name: ''
			};
		}		
	});
	
	window.CategoryList = Backbone.Collection.extend({
		model: Category,
		url: '/plugin/shopping/run/getdata/type/categories/id/',
		exists: function(name){
			return this.find(function(category){return category.get('name').toLowerCase() == name.toLowerCase();});
		}
	});
	
	window.Categories = new CategoryList;
	
	window.CategoryView = Backbone.View.extend({
		tagName: 'div',
		className: 'category-widget ui-corner-all ui-widget-content',
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
			var modelHolder = this.model;
			if (confirm('remove cat "'+ modelHolder.get('name')+'"?')){
				modelHolder.destroy({success: function(model, response) {
					model.view.remove();
				}});
			}
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
	
	window.ProductOption = Backbone.Model.extend({
		defaults: function(){
			return {
				title: '',
				type: 'dropdown'
			}
		}
	});
	
	window.ProductOptions = Backbone.Collection.extend({
		model: ProductOption
	})
	
	window.ProductOptionView = Backbone.View.extend({
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
			
	window.Selection = Backbone.Model.extend({
		defaults: function(){
			return {
				title: '',
				priceModifierSign: '+',
				priceModifierType: 'percent',
				priceModifierValue: null,
				weightModifierSign: '+',
				weightModifierValue: null,
				isDefault: 0
			}
		}
	});
	
	window.Selections = Backbone.Collection.extend({
		model: Selection
	});
	
	window.SelectionView = Backbone.View.extend({
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
	
	window.Product = Backbone.Model.extend({
		urlRoot: '/plugin/shopping/run/getdata/type/product/id/',
		defaults: {
			name: '',
			sku: '',
			mpn: '',
			weight: 0,
			brand: '',
			shortDescription: '',
			fullDescription: '',
			enabled: true,
			price: 0,
			taxes: 1,
			options: null
		},
		initialize: function (){
			if (this.get('options') === null){
				this.set({options: new ProductOptions});
			} else {
				//@todo loading optionlist from array
			}
		}
	});
	
	var AppView = Backbone.View.extend({
		el: $('#manage-product'),
		events: {
			"keypress input#new-category": "newCategory",
			"click #add-new-option-btn": "newOption",
			"click #submit": "saveProduct"
		},
		initialize: function(){
			$(this.el).tabs();
			this.newCategoryInput = this.$('#new-category');
			// pre-loading necessary data
			Categories.bind('add', this.addCategory, this);
			Categories.bind('reset', this.addAllCategories, this);
			Categories.bind('all', this.render, this);
			
			Categories.fetch();
			
			this.model.bind('all', this.render, this );
			this.model.view = this;
			
			$('#description-box').tabs();
		},
		addCategory: function(category){
			var view = new CategoryView({model: category});
			this.$('#product-categories').append(view.render().el);
		},
		addAllCategories: function(){
			Categories.each(this.addCategory);
		},
		newCategory: function(e){
			var name = this.newCategoryInput.val();
			if (e.keyCode == 13) {
				if (!Categories.exists(name)){
				   Categories.create({name: name}, {success: function(){App.newCategoryInput.val('').blur();}});
				} else {
					//@todo change next notification
					alert('category already exists');
				}
			}
		},
		newOption: function(){
			var newOption = new ProductOption();
			var optWidget = new ProductOptionView({model: newOption});
			$('#options-holder').append(optWidget.render().el);
		},
		render: function(){
			this.$('#product-name').val(this.model.get('name'));
			this.$('#product-sku').val(this.model.get('sku'));
			this.$('#product-mpn').val(this.model.get('mpn'));
			this.$('#product-weight').val(this.model.get('weight'));
			this.$('#product-brand').val(this.model.get('brand'));
			
			if (this.model.get('options')) {
				 _.each(this.model.get('options'), function(option){option.render()});
			}
		},
		saveProduct: function(){
			//@todo serialize product and then save
			this.model.save();
		}
	});
	
	var product = new Product({name: 'demo product'});
	window.App = new AppView({model: product});
	
	var Router = Backbone.Router.extend({
		routes: {
			'': 'newProduct',
			'new': 'newProduct',
			'edit/:id': 'editProduct'
		},
		newProduct: function(){
			App.model.clear();
		},
		editProduct: function(productId){
			App.model.fetch({data: {id: productId}});
		}
	});
	
	window.controller = new Router;
	
	Backbone.history.start();
});