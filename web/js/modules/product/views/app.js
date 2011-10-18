define([
	'libs/underscore/underscore',
	'libs/backbone/backbone',
	'modules/product/models/product',
	'modules/product/collections/categories',
	'modules/product/views/category',
	'modules/product/models/option',
	'modules/product/views/option',
], function(_, Backbone, ProductModel, Categories, CategoryView, ProductOption, ProductOptionView){
	
	var AppView = Backbone.View.extend({
		el: $('#manage-product'),
		events: {
			"keypress input#new-category": "newCategory",
			"click #add-new-option-btn": "newOption",
			"click #submit": "saveProduct",
			"change #product-image-folder": "imageChange",
			"click div.box": "setProductImage",
			"change [data-reflection=property]": "setProperty"
		},
		initialize: function(){
			$(this.el).tabs();
			this.newCategoryInput = this.$('#new-category');
			// pre-loading necessary data
			Categories.bind('add', this.addCategory, this);
			Categories.bind('reset', this.addAllCategories, this);
			Categories.bind('all', this.render, this);
			
			Categories.fetch();
			
			this.model.bind('change', this.render, this);
			this.model.view = this;
			
			this.$('#description-box').tabs();
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
			if (e.keyCode == 13 && name !== '') {
				if (!Categories.exists(name)){
				   Categories.create({name: name}, {success: this.clearCategoryInput});
				} else {
					//@todo change next notification
					alert('category already exists');
				}
			}
		},
		clearCategoryInput: function(){
			this.$('#new-category').val('').blur();
		},
		newOption: function(){
			var newOption = new ProductOption();
			var optWidget = new ProductOptionView({model: newOption});
			$('#options-holder').append(optWidget.render().el);
		},
		imageChange: function(e){
			var folder = $(e.target).val();
			if (folder == '0') {
				return;
			}
			$.post('/backend/backend_media/getdirectorycontent', {folder: folder}, function(response){
				var $box = $('#image-list');
				$box.empty();
				if (response.hasOwnProperty('imageList') && response.imageList.length ){
					var $images = $('#imgTemplate').tmpl(response);
					$box.append($images).imagesLoaded(function(){
						$(this).masonry('reload')
					});
				} else {
					$box.append('<p>Empty</p>');
				}
				$('#image-select-dialog').show('slide');
			});
		},
		setProductImage: function(e){
			var src = $(e.currentTarget).find('img').attr('src');
			this.$('#image-select-dialog').hide('slide');
			this.$('#product-image-folder').val(0);
			this.model.set({photo: src});
		},
		setProperty: function(e){
			var propName = e.currentTarget.id.replace('product-', '');
			var data = {};
			data[propName] = e.currentTarget.value;
			this.model.set(data);
		},
		render: function(){
			console.log('render')
			//setting model properties to view
			this.$('#product-image').attr('src', this.model.get('photo'));
			this.$('#product-name').val(this.model.get('name'));
			this.$('#product-sku').val(this.model.get('sku'));
			this.$('#product-mpn').val(this.model.get('mpn'));
			this.$('#product-weight').val(this.model.get('weight'));
			this.$('#product-brand').val(this.model.get('brand'));
			this.$('#product-price').val(this.model.get('price'));
			this.$('#product-taxGroup').val(this.model.get('taxGroup'));
			this.$('#product-pageTemplate').val(this.model.get('pageTemplate'));
			
			if (this.model.get('options')) {
				 _.each(this.model.get('options'), function(option){option.render()});
			}
			$('#image-list').masonry({
				itemSelector : '.box',
				columnWidth : 120
			});
			$(this.el).show();
		},
		saveProduct: function(){
			//@todo serialize product and then save
			this.model.save();
		}
	});
	
	return AppView;
});