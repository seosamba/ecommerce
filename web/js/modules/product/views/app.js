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
            'change select#option-library': 'addOption',
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
            'keyup #product-list-search': 'filterProductList',
            'mouseover #option-library': 'fetchOptionLibrary'
		},
		websiteUrl: $('#websiteUrl').val(),
		initialize: function(){
			$('#delete,#add-new-option-btn').button();
			$('#add-related').autocomplete({
				minLength: 3,
				select: this.addRelated,
				source: this.relatedAutocomplete
			}).data( "autocomplete" )._renderItem = this.renderAutocomplete;

			this.newCategoryInput = this.$('#new-category');
		},
		setModel: function (model) {
			this.model = model;
            this.model.view = this;
            this.model.bind('change', this.render, this);
            this.model.trigger('change');
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
        addOption: function(){
            var optId = this.$('#option-library').val();
            if (optId > 0 ){
                var option = appRouter.optionLibrary.get(optId);
                    newOption = new ProductOption({
                        title: option.get('title'),
                        parentId: option.get('id'),
                        type: option.get('type')
                    });
                console.log(option.get('selection').map(function(item){ item.unset('id'); return item.toJSON(); }));
                newOption.get('selection').reset(option.get('selection').map(function(item){ item.unset('id'); return item.toJSON(); }));
                this.model.get('options').add(newOption);
                this.model.trigger('change');
            }
            $('#option-library').val('-1');
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
            this.$('#product-image-folder').val('0');
        },
		setProperty: function(e){
			var propName = e.currentTarget.id.replace('product-', '');
			var data = {};
			data[propName] = e.currentTarget.value;
			this.model.set(data);
		},
		render: function(){
            console.log('render app.js', this.model.changedAttributes());
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
            if (this.model.has('brand')){
                this.$('#product-brand').val(this.model.get('brand'));
            } else {
                this.$('#product-brand').val(-1);
            }
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
                this.$('#product-pageTemplate').val('-1');
			}

            if (this.model.has('page')){
                $('<a></a>', {href: $('#websiteUrl').val()+this.model.get('page').url, target: '_blank'})
                    .html(this.model.get('page').h1)
                    .appendTo('#quick-preview');
                this.$('#product-pageTemplate').val(this.model.get('page').templateId);
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

            if (this.model.has('options')){
			    this.model.set({defaultOptions: this.model.get('options').toJSON()});
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
                    if (appRouter.products === null) {
                        appRouter.initProductlist().fetch().done(
                            appRouter.products.add(model)
                        )
                    } else {
                        appRouter.products.add(model);
                    }
                    appRouter.navigate('edit/'+model.id, true);
                    smoke.alert('Product added');
                }, error: this.processSaveError});
			} else {
				this.model.save(null, {success: function(model, response){
					smoke.alert('Product saved');
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
            var error   = false;

            if (!this.model.has('name') || trim(this.model.get('name')) === ''){
                this.$('#product-name').addClass('highlight');
                error = true || error;
            } else {
                this.$('#product-name').removeClass('highlight');
            }

            if (!this.model.has('sku') || trim(this.model.get('sku')) === ''){
                this.$('#product-sku').addClass('highlight');
                error = true || error;
            } else {
                this.$('#product-sku').removeClass('highlight');
            }

            if (!this.model.has('price')){
                this.$('#product-price').addClass('highlight');
                error = true || error;
            } else {
                this.$('#product-price').removeClass('highlight');
            }

            if (!this.model.has('brand') && trim($('#new-brand').val()) === '') {
                this.$('#product-brand').addClass('highlight');
                error = true || error;
            } else {
                this.$('#product-brand').removeClass('highlight');
            }

            if (!this.model.has('photo')) {
                this.$('#product-image-holder').addClass('highlight');
                error = true || error;
            } else {
                this.$('#product-image-holder').removeClass('highlight');
            }

            if (!this.model.has('shortDescription') || trim(this.model.get('shortDescription')) === ''){
                this.$('#product-shortDescription').addClass('highlight');
                error = true || error;
            } else {
                this.$('#product-shortDescription').removeClass('highlight');
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

            if (this.model.has('related') && this.model.get('related').length) {
                var relateds = this.model.get('related');
                if (appRouter.products === null) {
                    appRouter.initProductlist().fetch({async: false})
                }
                _(relateds).each(function (pid) {
                    var view = new ProductListView({model:appRouter.products.get(pid), showDelete:true});
                    $('#related-holder').append(view.render().el);
                });
            }
            return false;
        },
        newBrand: function(e){
            var newBrand = trim(this.$('#new-brand').val());
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
                appRouter.products.search(search, ['name', 'brand', 'sku', 'mpn']).map(function(prod){
                    $(prod.view.el).show();
                });
            } else {
                $('#product-list-holder > .productlisting').show();
            }
            $('#product-list-holder').trigger('scroll');
        },
        fetchOptionLibrary: function(){
            if (!appRouter.hasOwnProperty('optionLibrary')){
                var optionsLibrary = Backbone.Collection.extend({
                    url: this.websiteUrl + 'plugin/shopping/run/getdata/type/options/',
                    model: ProductOption,
                    initialize: function(){
                        this.bind('reset', function(collection){
                            $('#option-library').html('<option value="-1" disabled="disabled">select from library</option>');
                            collection.each(function(item){
                                $.tmpl("<option value='${id}' >${title}</option>", item.toJSON()).appendTo('#option-library');
                            })
                        }, this);
                    }
                });
                appRouter.optionLibrary = new optionsLibrary();
                appRouter.optionLibrary.fetch();
            }
        }
	});

	return AppView;
});