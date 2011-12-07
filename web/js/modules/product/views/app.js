define([
	'libs/underscore/underscore',
	'libs/backbone/backbone',
	'modules/product/models/product',
	'modules/product/views/category',
//	'modules/product/collections/options',
	'modules/product/models/option',
	'modules/product/views/option',
	'modules/product/views/productlist'
], function(_, Backbone, ProductModel, CategoryView, /*OptionCollection,*/ ProductOption, ProductOptionView, ProductListView){

	var AppView = Backbone.View.extend({
		el: $('#manage-product'),
		events: {
			'keypress input#new-category': 'newCategory',
			'click #add-new-option-btn': 'newOption',
			'click #submit': 'saveProduct',
			'change #product-image-folder': 'imageChange',
			'click div.box': 'setProductImage',
			'change [data-reflection=property]': 'setProperty',
			'change #product-enabled': 'toggleEnabled',
			'click input[name^=category]': 'toggleCategory',
			'click #delete': 'deleteProduct',
			'click #related-holder span.ui-icon-closethick': 'removeRelated',
            'keypress input#new-brand': 'newBrand',
            'click a#brandlanding-link': 'gotoBrandPage',
            'keyup #product-list-search': 'filterProductList'
		},
		websiteUrl: $('#websiteUrl').val(),
		initialize: function(){
			//initializing jQueryUI elements
			$(this.el).tabs();
			$('#description-box').tabs();
			$('#delete,#add-new-option-btn').button();
			$('#add-related').autocomplete({
				minLength: 3,
				select: this.addRelated,
				source: this.relatedAutocomplete
			}).data( "autocomplete" )._renderItem = this.renderAutocomplete;

			this.newCategoryInput = this.$('#new-category');


			$(".ui-tabs-nav, .ui-tabs-nav > *" )
				.removeClass( "ui-corner-all" )
				.addClass( "ui-corner-top" );
		},
		setModel: function (model) {
			this.model = model;
			this.model.bind('change', this.render, this);
			this.model.view = this;
		},
		toggleEnabled: function(e){
			this.model.set({enabled: this.$('#product-enabled').prop('checked') ? 1 :0 });
		},
		newCategory: function(e){
			var name = this.newCategoryInput.val();
			if (e.keyCode == 13 && name !== '') {
			   appRouter.categories.create({name: name}, {
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
				checkedCategories.push(appRouter.categories.get( el.value ).toJSON());
			});

			this.model.set({categories: checkedCategories});
		},
		newOption: function(){
			var newOption = new ProductOption();
			var optWidget = new ProductOptionView({model: newOption});
			this.model.get('options').add(newOption);
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
			var imgName = $(e.currentTarget).find('img').data('name');
            var fldrName = this.$('#product-image-folder').val();
			this.model.set({photo: fldrName+'/'+imgName });
            this.$('#image-select-dialog').hide('slide');
        },
		setProperty: function(e){
			var propName = e.currentTarget.id.replace('product-', '');
			var data = {};
			data[propName] = e.currentTarget.value;
			this.model.set(data);
		},
		render: function(){
            console.log('render');
            $('#quick-preview').empty();

            //hiding delete button if product is new
            if (!this.model.isNew()){
                $('#delete').show();
            } else {
                $('#delete').hide();
            }

			//setting model properties to view
			if (this.model.has('photo')){
				this.$('#product-image').attr('src', this.websiteUrl+'media/'+this.model.get('photo').replace('/', '/small/'));
			} else {
				this.$('#product-image').attr('src', this.websiteUrl+'system/images/noimage.png');
			}
			this.$('#product-name').val(this.model.get('name'));
			this.$('#product-sku').val(this.model.get('sku'));
			this.$('#product-mpn').val(this.model.get('mpn'));
			this.$('#product-weight').val(this.model.get('weight'));
			this.$('#product-brand').val(this.model.get('brand')).trigger('change');
			this.$('#product-price').val(this.model.get('price'));
			this.$('#product-taxClass').val(this.model.get('taxClass'));
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

            //render related products
            this.renderRelated();

            //populating selected categories
			$('#product-categories').find('input:checkbox:checked').removeAttr('checked');
			if (this.model.has('categories')){
				_.each(this.model.get('categories'), function(category, name){
					var el = appRouter.categories.get(category.id).view.el;
					$(el).find(':checkbox').attr('checked','checked');
				});
			}
			//toggle enabled flag
			if (this.model.get('enabled')){
				this.$('#product-enabled').attr('checked', 'checked');
			} else {
				this.$('#product-enabled').removeAttr('checked');
			}

			if (this.model.has('pageTemplate')){
				this.$('#product-pageTemplate').val(this.model.get('pageTemplate'));
			} else {
                if (this.model.has('page')){
                    $('<a></a>', {href: $('#websiteUrl').val()+this.model.get('page').url, target: '_blank'})
                        .html(this.model.get('page').h1)
                        .appendTo('#quick-preview');
                    this.$('#product-pageTemplate').val(this.model.get('page').templateId);
                } else {
                    this.$('#product-pageTemplate').val('-1');
                }
			}
			$('#image-list').masonry({
				itemSelector : '.box',
				columnWidth : 120
			});
			$(this.el).show();
		},
		saveProduct: function(){
			//@todo: make messages translatable
            if (!this.validateProduct()) {
                smoke.alert('Missing some required fields');
                return false;
            }
			if (!this.model.get('options').isEmpty()){
				var list = this.model.get('options').toJSON();
				this.model.set({defaultOptions: list});
			}
			if (!this.model.has('pageTemplate')){
				var templateId = this.$('#product-pageTemplate').val();
				if (templateId !== '-1') {
                    this.model.set({pageTemplate: templateId});
                } else {
                    smoke.alert('Please, select product page template before saving');
                    this.$('#product-pageTemplate').focus();
                    return false;
                }
			}

            if ($('#new-brand').val()){
                this.addNewBrand($('#new-brand').val()).$('#new-brand').val('');
            }

			if (this.model.isNew()){
				this.model.save(null, {success: function(model, response){
					smoke.alert('Product added');
					appRouter.products.add(model);
					appRouter.navigate('edit/'+model.id, true);
				}, error: this.processSaveError});
			} else {
				this.model.save(null, {success: function(model, response){
					smoke.alert('Product saved');
					appRouter.app.model.fetch({data: {id: model.id}});
				}, error: this.processSaveError});
			}
		},
        processSaveError: function(model, response){
            smoke.alert(response.responseText);
        },
		deleteProduct: function(){
			var model  = this.model;
			if (model.isNew()){
				smoke.alert('Product is not saved yet');
				return false;
			}
			smoke.confirm('Dragons ahead! Are you sure?', function(e){
				if (e){
					model.destroy({
						success: function(){
							appRouter.products.fetch().done(function(){
                                appRouter.brands.fetch()
                                appRouter.navigate('list', true);
                            });
						}
					});
				}
			});
		},
        validateProduct: function(){
            var error   = false,
                name    = trim(this.model.get('name')),
                sku     = trim(this.model.get('sku')),
                price   = trim(this.model.get('price')),
                brand   = trim(this.model.get('brand'));


            if (name === ''){
                this.$('#product-name').addClass('highlight');
                error = true || error;
            } else {
                this.$('#product-name').removeClass('highlight');
            }

            if (sku === ''){
                this.$('#product-sku').addClass('highlight');
                error = true || error;
            } else {
                this.$('#product-sku').removeClass('highlight');
            }

            if (price === ''){
                this.$('#product-price').addClass('highlight');
                error = true || error;
            } else {
                this.$('#product-price').removeClass('highlight');
            }

            return !error;
        },
		relatedAutocomplete: function(request, response){
			var list = appRouter.products.search(request.term.toLowerCase()).filter(function(prod){
                var id = prod.get('id'),
                    model = appRouter.app.model;
                return (model.get('id') !== prod.get('id') && !_(model.get('related')).include(prod.get('id'))) ;
            });

			response(list);
		},
		renderAutocomplete: function( ul, item ) {
			return $( "<li></li>" )
				.data( "item.autocomplete", item )
				.append( "<a><img style='float:right;max-width: 80px' src=" +
                (item.has('photo')?'/media/'+item.get('photo').replace('/','/product/'):'/system/images/noimage.png') +
                " /><div>" + item.get('name').toUpperCase() + "<br>SKU:" + item.get('sku') + "<br />" +item.get('brand')+"</div></a>"
				).appendTo( ul );
		},
		addRelated: function( event, ui ) {
			var related = _(appRouter.app.model.get('related')).toArray(),
				id	= ui.item.get('id');
			if (related.indexOf(id) === -1){
				related.push(id);
				appRouter.app.model.set({related: related});
			}
			//return false;
		},
		removeRelated: function(el){
			var id = $(el.target).closest('div.productlisting').find('a').attr('href').replace('#edit/',''),
				related = _.without(_(this.model.get('related')).toArray(), parseInt(id));
			this.model.set({related: related});
		},
		renderRelated: function() {
            $('#related-holder').empty();
            if (this.model.has('related')) {
                _(this.model.get('related')).each(function (productId) {
                    var product = appRouter.products.get(parseInt(productId)),
                        view = new ProductListView({model:product, showDelete:true});

                    $('#related-holder').append(view.render().el);
                });
            }
            return false;
        },
        newBrand: function(e){
            var newBrand = this.$('#new-brand').val();
            if (e.keyCode === 13 && newBrand !== '') {
                var current = appRouter.brands.pluck('name').map(function(item){ return item.toLowerCase()});
                this.addNewBrand(newBrand)
                    .$('#new-brand').val('');
                this.$('#product-brand').focus();
            }
            return this;
        },
        addNewBrand: function(newBrand){
            newBrand = trim(newBrand);
            var current = appRouter.brands.pluck('name').map(function(item){ return item.toLowerCase()});
            if (!_.include(current, newBrand.toLowerCase())){
                appRouter.brands.add({name: newBrand});
            } else {
                var brand = appRouter.brands.find(function(item){
                    return item.get('name').toLowerCase() == newBrand.toLowerCase();
                });
                newBrand = brand.get('name');
            }
            appRouter.app.model.set({brand: newBrand});
            return this;
        },
        gotoBrandPage: function(e){
            e.preventDefault();
            var el    = $(e.target),
                msg   = el.data('msg'),
                brand = appRouter.brands.find(function(item){ return item.get('name') === $('#product-brand').val() });
            if (brand && brand.has('url')) {
                window.open(this.websiteUrl+brand.get('url'),'_blank');
            } else {
                smoke.alert(_.template(msg, brand.toJSON()));
            }
        },
        filterProductList: function(e){
            var search = e.target.value.toLowerCase();
            if (search.length){
                $('#product-list-holder > .productlisting:visible').hide();
                appRouter.products.search(search).map(function(prod){
                    $(prod.view.el).show();
                });
            } else {
                $('#product-list-holder > .productlisting').show();
            }
        }
	});

	return AppView;
});