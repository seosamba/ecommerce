define([
	'libs/underscore/underscore',
	'libs/backbone/backbone',
	'modules/product/models/product',
	'modules/product/collections/categories',
	'modules/product/views/category',
	'modules/product/collections/options',
	'modules/product/models/option',
	'modules/product/views/option'
], function(_, Backbone, ProductModel, Categories, CategoryView, OptionCollection, ProductOption, ProductOptionView){
	
	var AppView = Backbone.View.extend({
		el: $('#manage-product'),
		events: {
			"keypress input#new-category": "newCategory",
			"click #add-new-option-btn": "newOption",
			"click #submit": "saveProduct",
			"change #product-image-folder": "imageChange",
			"click div.box": "setProductImage",
			"change [data-reflection=property]": "setProperty",
			'click input[name^=category]': "toggleCategory"
		},
		websiteUrl: $('#websiteUrl').val(),
		initialize: function(){
			$(this.el).tabs();
			$('#description-box').tabs();
			
			this.initBrandAutocomplete();
			this.newCategoryInput = this.$('#new-category');
			
			// pre-loading necessary data
			Categories.bind('add', this.addCategory, this);
			Categories.bind('reset', this.addAllCategories, this);
			Categories.bind('reset', this.proccessCategories, this);
//			Categories.bind('all', this.render, this);
			Categories.fetch();
			
//			this.model.view = this;
			
			$(".ui-tabs-nav, .ui-tabs-nav > *" )
				.removeClass( "ui-corner-all" )
				.addClass( "ui-corner-top" );
		},
		setModel: function (model) {
			this.model = model;
			this.model.bind('change', this.render, this);
			this.model.bind('change:categories', this.proccessCategories, this);
			this.model.view = this.app;
			this.render();
		},
		addCategory: function(category){
			var view = new CategoryView({model: category});
			$('#product-categories').append(view.render().el);
		},
		addAllCategories: function(){
			Categories.each(this.addCategory);
		},
		newCategory: function(e){
			var name = this.newCategoryInput.val();
			if (e.keyCode == 13 && name !== '') {
			   Categories.create({name: name}, {
				   success: function(model, response){
					   $('#new-category').val('').blur();
				   },
				   error: function(model, response){
					   smoke.alert(response.responseText);
				   }
			   });
			}
		},
		toggleCategory: function(e){
			var checkedCategories = [];
		
			_.each($('input[name^=category]:checked'), function(el){
				checkedCategories.push(Categories.get( el.value ).toJSON());
			});
			
			this.model.set({categories: checkedCategories});
		},
		proccessCategories: function(){
			if (this.model.has('categories')){
				_.each(this.model.get('categories'), function(category, name){
					var el = Categories.get(category.id).view.el;
					$(el).find(':checkbox').attr('checked','checked');
				});
			}
		},
		newOption: function(){
			var newOption = new ProductOption();
			var optWidget = new ProductOptionView({model: newOption});
			this.model.attributes.options.add(newOption);
			$('#options-holder').append(optWidget.render().el);
			optWidget.addSelection();
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
			//setting model properties to view
			if (this.model.has('photo')){
				this.$('#product-image').attr('src', this.model.get('photo'));
			} else {
				this.$('#product-image').attr('src', this.websiteUrl+'system/images/noimage.png');
			}
			this.$('#product-name').val(this.model.get('name'));
			this.$('#product-sku').val(this.model.get('sku'));
			this.$('#product-mpn').val(this.model.get('mpn'));
			this.$('#product-weight').val(this.model.get('weight'));
			this.$('#product-brand').val(this.model.get('brand'));
			this.$('#product-price').val(this.model.get('price'));
			this.$('#product-taxClass').val(this.model.get('taxClass'));
			this.$('#product-pageTemplate').val(this.model.get('pageTemplate'));
			this.$('#product-shortDescription').val(this.model.get('shortDescription'));
			this.$('#product-fullDescription').val(this.model.get('fullDescription'));
			
			// loading option onto frontend
			$('#options-holder').empty();
			if (this.model.has('options')) {
				this.model.get('options').each(function(option){
					var optWidget = new ProductOptionView({model: option});
					$('#options-holder').append(optWidget.render().el);
				});
			}
			//populating selected categories
//			$('#product-categories').find('input:checkbox:checked').removeAttr('checked');
			
			
			$('#image-list').masonry({
				itemSelector : '.box',
				columnWidth : 120
			});
			$(this.el).show();
		},
		saveProduct: function(){
			//@todo: make messages translatable
			if (this.model.isNew()){
				this.model.save(null, {success: function(model, response){
					smoke.alert('Product added');
					appRouter.products.fetch();
					appRouter.navigate('edit/'+model.id, true);
				}});			
			} else {
				this.model.save(null, {success: function(model, response){
					smoke.alert('Product saved');
					appRouter.app.model.fetch({data: {id: model.id}});
				}});			
			}
		},
		initBrandAutocomplete: function(){
			$('#product-brand').autocomplete({
				minLength: 2,
				source: function (request, response){
					var elem = $('#product-brand'),
						xhrCache = {},
						lastXhr;
					if ( elem.data('xhrCache') === undefined){
						elem.data('xhrCache', xhrCache);
					} else {
						xhrCache = elem.data('xhrCache');					
					}

					if ( elem.data('lastXhr') === undefined){
						elem.data('lastXhr', lastXhr);
					} else {
						lastXhr = elem.data('lastXhr');					
					}

					var term = request.term ;

					if (xhrCache === undefined) {
						xhrCache = {};
						elem.data('xhrCache', xhrCache);
					}

					if ( term in xhrCache ){
						response(xhrCache[term]);
						return;
					}
					lastXhr = $.getJSON($('#websiteUrl').val()+'/plugin/shopping/run/getdata/type/brands/', request, function(data, status, xhr){
						xhrCache[ term ] = data;
						if ( xhr === lastXhr ){
							response(data);
						}
					});

				},
				select: function( event, ui ) {
					$('#product-brand').val(ui.item.name)
						.data('brandId', ui.item.id)
						.trigger('change');
					return false;
				}
			}).data( "autocomplete" )._renderItem = function( ul, item ) {
				return $( "<li></li>" )
					.data( "item.autocomplete", item )
					.append( "<a>" + item.name + "</a>" )
					.appendTo( ul );
			};
		}
	});
	
	return AppView;
});