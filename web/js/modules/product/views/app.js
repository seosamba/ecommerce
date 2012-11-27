define([
	'backbone',
	'../models/product',
    '../models/option',
    '../collections/products_pager',
    '../collections/tags_lazy',
    '../collections/options',
    '../collections/images',
    './tag',
	'./option',
	'./productlist'
], function(Backbone,
            ProductModel,  ProductOption,
            ProductsCollection, TagsCollection, OptionsCollection, ImagesCollection,
            TagView, ProductOptionView, ProductListView){

	var AppView = Backbone.View.extend({
		el: $('#manage-product'),
		events: {
            'click #new-product': 'newProduct',
            'click .show-list': 'toggleList',
			'keypress input#new-tag': 'newTag',
			'click #add-new-option-btn': 'newOption',
            'change select#option-library': 'addOption',
			'click #submit': 'saveProduct',
			'change #product-image-folder': 'imageChange',
			'click div.box': 'setProductImage',
			'change [data-reflection=property]': 'setProperty',
			'change #product-enabled': 'toggleEnabled',
			'click #product-tags-available div.tag-widget input[name^=tag]': 'toggleTag',
			'click #delete': 'deleteProduct',
            'keypress input#new-brand': 'newBrand',
            'keypress #product-list-search': 'filterProducts',
            'mouseover #option-library': 'fetchOptionLibrary',
            'submit form.binded-plugin': 'formSubmit',
            'change #product-list-holder input.marker': 'markProducts',
            'click #massaction': 'massAction',
            'click #product-list-back-link': 'hideProductList',
            'click a[data-role=editProduct]': 'productAction',
            'click #toggle-current-tags': function(e){
                e.preventDefault();
                $('#product-tags-current, #product-tags-available, div.paginator', '#tag-tab').slideToggle();
            },
            'click .paginator a.page': 'paginatorAction'
		},
        products: null,
        tags: null,
        brands: null,
        searchIndex: null,
		websiteUrl: $('#website_url').val(),
        mediaPath: $('#media-path').val(),
		initialize: function(){
            var self = this;
            this.initProduct();
            $('#add-new-option-btn').button();

            $('#product-list-search').ajaxStart(function(){
                $(this).attr('disabled', 'disabled');
            }).ajaxStop(function(){
                $(this).removeAttr('disabled');
            });

            this.quickPreviewTmpl = _.template($('#quickPreviewTemplate').html());

            $('#image-list').masonry({
                itemSelector : '.box',
                columnWidth : 118
            });

            $('#ajax_msg, #product-list').hide();
            this.$el.on('tabsselect', function(event, ui){
                if (ui.index === 1){
                    self.initTags();
                }
            }).show();

            this.images =  new ImagesCollection(),
            this.images.on('reset', this.renderImages, this);

            this.render()
		},
        initProducts: function(){
            if (this.products === null) {
                this.products = new ProductsCollection();
                this.products.bind('add', this.renderProduct, this);
                this.products.bind('reset', this.renderProducts, this);
            }

            return this.products;
        },
        initTags: function(){
            if (this.tags === null){
                this.tags = new TagsCollection();
                this.tags.template = _.template($('#tagTemplate').html());
                this.tags.on('add', this.renderTag, this);
                this.tags.on('reset', this.renderTags, this);
                this.tags.pager();
            }
        },
        initProduct: function () {
            this.model = new ProductModel();

            this.model.on('change:tags', this.renderProductTags, this);
            this.model.on('change:related', this.renderRelated, this);
            this.model.on('sync', function(){
                if (this.model.has('options')){
                    this.model.get('options').on('add', this.renderOption, this);
                    this.model.get('options').on('reset', this.renderOptions, this);
                }
                if (this.products !== null){
//                    var product = this.products.get(this.model.get('id'));
//                    !_.isUndefined(product) && product.set(this.model.toJSON());
                    this.products.pager();
    
                }
                this.render();
                showMessage('Product saved.<br/> Go to your search engine optimized product landing page here.');
            }, this);
            this.model.on('error', this.processSaveError, this);

//            if (this.model.has('options')){
            this.model.get('options').on('add', this.renderOption, this);
            this.model.get('options').on('reset', this.renderOptions, this);
//            }

            return this;
		},
        newProduct: function(e) {
            e.preventDefault();
            this.initProduct().render();
            if (window.history && window.history.pushState){
                var loc = window.location;
                window.history.pushState({}, document.title, loc.href.replace(/id.*$/, '') );
            }
        },
		toggleEnabled: function(e){
			this.model.set({enabled: this.$('#product-enabled').prop('checked') ? 1 :0 });
		},
		newTag: function(e){
			var name = $.trim(e.currentTarget.value);
			if (e.keyCode == 13 && name !== '') {
			   this.tags.create({name: name}, {
                   wait: true,
				   success: function(model, response){
					   $('#new-tag').val('').blur();
				   },
				   error: function(model, response){
                       showMessage(response.responseText, true);
				   }
			   });
			}
		},
		newOption: function(){
			var newOption = new ProductOption();
            newOption.get('selection').add({isDefault: 1});
			this.model.get('options').add(newOption);
		},
        addOption: function(){
            var optId = this.$('#option-library').val();
            if (optId > 0 ){
                var option = this.optionLibrary.get(optId);

                var newOption = new ProductOption({
                    title: option.get('title'),
                    parentId: option.get('id'),
                    type: option.get('type')
                });
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
            var self = this;

            $('#image-list').html('<div class="spinner"></div>');
            this.images.server_api.folder = folder;
            this.images.flush().fetch({success: function(){ self.images.pager(); }, silent: true});
            $('#image-select-dialog').show('slide');
        },
        renderImages: function(){
            $('#image-list').html(_.template($('#imgTemplate').html(), {images: this.images.toJSON()}))
                .imagesLoaded(function(){
                    $(this).masonry('reload');
                })
            $('div.paginator', '#image-select-dialog').replaceWith(_.template($('#paginatorTemplate').html(), _.extend(
                this.images.paginator_ui,
                this.images.info(),
                {collection: 'images', cssClass: ''}
            )));
        },
        setProductImage: function(e){
            var imgName = $(e.currentTarget).find('img').data('name');
            var fldrName = this.$('#product-image-folder').val();
            this.model.set({photo: fldrName+'/'+imgName });
            this.$('#product-image').attr('src', '/' + this.mediaPath + this.model.get('photo').replace('/', '/small/'));
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
            console.log('render: app.js', this.model.changedAttributes());
            this.$el.tabs("select" , 0).tabs( "option", "ajaxOptions",
                { data: {productId: this.model.get('id') } }
            );

            $('#product-list:visible').hide();

            $('#quick-preview').empty(); //clening preview content

            //hiding delete button if product is new
            if (!this.model.isNew()){
                $('#delete').show();
            } else {
                $('#delete').hide();
            }

			//setting model properties to view
			if (this.model.has('photo')){
				this.$('#product-image').attr('src', $('#website_url').val()+ this.mediaPath + this.model.get('photo').replace('/', '/small/'));
			} else {
				this.$('#product-image').attr('src', $('#website_url').val()+'system/images/noimage.png');
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
                this.renderOptions();
			}

            //populating selected tags
            if (!this.model.has('tags')) {
                $('#product-tags-current').empty();
                $('div.tag-widget input:checkbox', '#product-tags-available').removeAttr('checked').removeAttr('disabled');
            }
//			$('#product-tags-available:not(:empty)').find('div.tag-widget').show();

			//toggle enabled flag
			if (parseInt(this.model.get('enabled'))){
				this.$('#product-enabled').attr('checked', 'checked');
			} else {
				this.$('#product-enabled').removeAttr('checked');
			}

			if (this.model.has('pageTemplate')){
				this.$('#product-pageTemplate').val(this.model.get('pageTemplate'));
			} else if (this.model.has('page')){
                this.$('#product-pageTemplate').val(this.model.get('page').templateId);
            } else {
                this.$('#product-pageTemplate').val('-1');
			}

            if (!this.model.isNew()){
                $('#quick-preview').html(this.quickPreviewTmpl({
                    product: this.model.toJSON(),
                    websiteUrl: $('#website_url').val(),
                    currency: this.$('#currency-unit').text()
                }));
            }

			$('div#ajax_msg:visible').hide('fade');
		},
        renderTag: function(tag, index){
            var view = new TagView({model: tag});
                view.render();
            if (index instanceof Backbone.Collection){
                $('#product-tags-available').prepend(view.$el);
            } else {
                $('#product-tags-available').append(view.$el);
            }
            if ($('div.tagid-'+tag.get('id'), '#product-tags-current').size()){
                view.$el.find('input:checkbox').attr({
                    disabled: 'disabled',
                    checked: 'checked'
                });
            }
        },
        renderTags: function(){
            $('#product-tags-available').empty();
            this.tags.each(this.renderTag, this);
            var paginatorData = {
                collection : 'tags',
                cssClass: 'grid_12'
            };

            $('div.paginator', '#tag-tab').replaceWith(_.template($('#paginatorTemplate').html(), _.extend(paginatorData, this.tags.info())));
        },
        toggleTag: function(e){
            if (e.currentTarget.checked){
                var tag = {
                    id: e.currentTarget.value,
                    name: $(e.currentTarget).next('span.tag-editable').text()
                };
                var current = this.model.get('tags') || [];
                this.model.set('tags', _.union(current, tag));
            }
            $(e.currentTarget).attr({
                disabled: 'disabled'
            }).parent('.tag-widget').effect("transfer", {
               to: '#toggle-current-tags',
               className: 'ui-effects-transfer'
            }, 500);
        },
        renderProductTags: function(){
            if (this.model && this.model.has('tags')){
                var self = this,
                    container = $('#product-tags-current').empty();
                _.each(this.model.get('tags'), function(tag){
                    var view = new TagView({model: new Backbone.Model(tag)});
                    view.delegateEvents({
                        'change input:checkbox[name^=tag]': function(){
                            var id = this.model.get('id');
                            var newSet = _.reject(self.model.get('tags'), function(tag){
                                return tag.id === id;
                            });
                            self.model.set('tags', newSet);
                            $('div.tagid-'+id+' input:checkbox', '#product-tags-available').removeAttr('checked').removeAttr('disabled');
                        }
                    });
                    $('div.tagid-'+tag.id+' input:checkbox', '#product-tags-available').attr('checked', 'checked').attr('disabled', 'disabled');

                    view.render().$el
                        .find('span.ui-icon-closethick').remove().end()
                        .find('input:checkbox').attr('checked', 'checked').end()
                        .appendTo(container);

                });
            } else {
                $('#product-tags-current').html('<p class="nothing">'+$('#product-list-holder').data('emptymsg')+'</p>');
            }
        },
        renderBrands: function(brands){
            var tmpl = _.template("<% _.each(brands, function(brand){ %><option value='<%= brand %>'><%= brand %></option><% }); %>");

            $('#product-brand').html('<option value="-1" disabled>Select a brand</option>' +
                tmpl({brands: _.sortBy(brands, function(v){ return v.toLowerCase();}) })
            );

            if (this.model && this.model.has('brand')){
                this.$('#product-brand').val(this.model.get('brand'));
            } else {
                this.$('#product-brand').val(-1);
            }
        },
        renderProduct: function(product){
            var productView = new ProductListView({model: product});

            this.$('#product-list-holder').append(productView.render().el);
            if (_.has(this.products, 'checked') && _.contains(this.products.checked, product.get('id'))){
                productView.$el.find('input.marker').attr('checked', 'checked');
            }
//            disabled lazy load because don't needed for now
//            if (this.$('#product-list-holder').children().size() === this.products.size()){
//                this.$('#product-list-holder').find('img.lazy').lazyload({
//                    container: this.$('#product-list-holder'),
//                    effect: 'fadeIn'
//                }).removeClass('lazy');
//            }
        },
        renderProducts: function(){
            if (this.products.size()){
                this.$('#product-list-holder').empty();
                this.products.each(this.renderProduct, this);
                var paginatorData = {
                    collection : 'products',
                    cssClass: 'textright'
                };
                paginatorData = _.extend(paginatorData, this.products.info());
                $('div.paginator', '#product-list').replaceWith(_.template($('#paginatorTemplate').html(), paginatorData));
            } else {
                $('#product-list-holder').html('<p class="nothing">'+$('#product-list-holder').data('emptymsg')+'</p>');
            }
        },
		saveProduct: function(){
            var self = this;

            if (!this.validateProduct()) {
                showMessage('Missing some required fields', true);
                $('#manage-product').tabs("select" , 0);
                return false;
            }

            if (this.model.has('options')){
                var newInLibrary = !_.isEmpty(_.compact(this.model.get('options').pluck('isTemplate')));
			    this.model.set({defaultOptions: this.model.get('options').toJSON()});
            }

			if (!this.model.has('pageTemplate')){
				var templateId = this.$('#product-pageTemplate').val();
				if (templateId !== '-1') {
                    this.model.set({pageTemplate: templateId});
                } else {
                    showMessage('Please, select product page template before saving', true);
                    this.$('#product-pageTemplate').focus();
                    return false;
                }
			}

            var newBrandName = $('#new-brand').val();
            if (newBrandName){
                this.addNewBrand(newBrandName).$('#new-brand').val('');
            }

            this.model.save();
//			if (this.model.isNew()){
//				this.model.save(null, {success: function(model, response){
//                    if (self.products !== null) {
//                        self.products.add(model);
//                    }
//                    showMessage('Product saved.<br/> Go to your search engine optimized product landing page here.');
//                }, error: this.processSaveError});
//			} else {
//				this.model.save(null, {success: function(model, response){
//					showMessage('Product saved.<br/> Go to your search engine optimized product landing page here.');
//                    self.render();
//				}, error: this.processSaveError});
//			}

            if (newInLibrary && self.hasOwnProperty('optionLibrary')){
                self.optionLibrary.fetch();
            }
		},
        processSaveError: function(model, response){
            showMessage(response.responseText, true);
        },
		deleteProduct: function(){
			var self = this;
			if (this.model.isNew()){
                showMessage('Product is not saved yet', true);
				return false;
			}
            showConfirm('Dragons ahead! Are you sure?', function(){
                self.model.destroy({
                    success: function(model, response){
                        self.products && self.products.pager();
                        $('#new-product').trigger('click');
                        showMessage('Product deleted');
                    }
                });
			});
		},
        validateProduct: function(){
            var error   = false;

            if (!this.model.has('name') || $.trim(this.model.get('name')) === ''){
                this.$('#product-name').addClass('highlight');
                error = true || error;
            } else {
                this.$('#product-name').removeClass('highlight');
            }

            if (!this.model.has('sku') || $.trim(this.model.get('sku')) === ''){
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

            if (!this.model.has('brand') && $.trim($('#new-brand').val()) === '') {
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

            if (!this.model.has('shortDescription') || $.trim(this.model.get('shortDescription')) === ''){
                this.$('#product-shortDescription').addClass('highlight');
                error = true || error;
            } else {
                this.$('#product-shortDescription').removeClass('highlight');
            }

            return !error;
        },
        productAction: function(e){
            var pid = $(e.currentTarget).data('pid');
            var type = $('#product-list-holder').data('type');
            switch (type){
                case 'edit':
                    this.model.clear({silent:true}).set(this.products.get(pid).toJSON());
                    this.render();
                    if (window.history && window.history.pushState){
                        var loc = window.location;
                        window.history.pushState({}, document.title, loc.href.replace(/product.*$/, 'product/id/'+pid) );
                    }
                    break;
                case 'related':
                    this.addRelated(pid);
                    break;
            }
            $('#product-list').hide('slide');
            return false;
        },
		addRelated: function( ids ) {
            if (_.isNull(ids) || _.isUndefined(ids)) return false;

            var relateds = _.map(this.model.get('related'), function(id){ return parseInt(id) });
                relateds = _.union(relateds, ids);

            this.model.set({related: _.without(relateds, this.model.get('id'))});
		},
		removeRelated: function(id){
            var relateds = _(this.model.get('related')).map(function(id){ return parseInt(id) });
			this.model.set({related: _.without(relateds, parseInt(id))});
		},
		renderRelated: function() {
            $('#related-holder').empty();

            if (this.model.has('related') && this.model.get('related').length) {
                var relateds = this.model.get('related'),
                    self = this;

                $.ajax({
                    url: this.model.urlRoot,
                    data: {id: relateds.join(',')},
                    success: function(response){
                        if (!response) return false;
                        if (response && !_.isArray(response)){
                            response = [response];
                        }
                        $('#related-holder').empty();
                        _.each(response, function(related){
                            var view = new ProductListView({model: new ProductModel(related), showDelete: true});
                            view.delegateEvents({
                                'click span.ui-icon-closethick': function(){
                                    self.removeRelated(this.model.get('id'));
                                }
                            })
                            view.render().$el.css({cursor: 'default'}).appendTo('#related-holder');
                        });
                    }
                });
            }
            return false;
        },
        newBrand: function(e){
            var newBrand = $.trim(this.$('#new-brand').val());
            if (e.keyCode === 13 && newBrand !== '') {
                this.addNewBrand(newBrand)
                    .$('#new-brand').val('');
                this.$('#product-brand').focus();
            }
            return this;
        },
        addNewBrand: function(newBrand){
            newBrand = $.trim(newBrand);
            var brandsList = _.map($('#product-brand option'), function(opt){ return opt.value; });

            if (!_.include(_.map(brandsList, function(b){ return b.toLowerCase(); }), newBrand.toLowerCase())){
                brandsList.push(newBrand);
            } else {
                newBrand = _.find(brandsList, function(item){
                    return item.toLowerCase() == newBrand.toLowerCase();
                });
            }
            this.model.set({brand: newBrand});
            this.renderBrands(brandsList);
            return this;
        },
        filterProducts: function(e, forceRun) {
            if (e.keyCode === 13 || forceRun === true) {
                $('#product-list-holder').html('<div class="spinner"></div>');
                this.products.key = e.currentTarget.value;
                this.products.goTo(this.products.firstPage);
                $(e.target).autocomplete('close');
            }
        },
        renderOption: function(option){
            var optWidget = new ProductOptionView({model: option});
            optWidget.render().$el.appendTo('#options-holder');
        },
        renderOptions: function(){
            if (!this.model.has('options')) return false;
            this.model.get('options').each(this.renderOption, this);
        },
        fetchOptionLibrary: function(){
            if (!_.has(this, 'optionLibrary')){
                this.optionLibrary = new OptionsCollection();
                this.optionLibrary.on('reset', function(collection){
                    $('#option-library').html(_.template($('#optionLibraryTemplate').html(), {items: collection.toJSON()}));
                }, this);
                this.optionLibrary.fetch();
            }
        },
        formSubmit: function(e) {
            var $form = $(e.target);
            $.ajax({
                url: $form.attr('action'),
                type: $form.attr('method'),
                data: $form.serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.hasOwnProperty('result')) {
                        smoke.alert(response.result);
                    }
                }
            });
            return false;
        },
        markProducts:  function(e){
            var checked = _.has(this.products, 'checked') ? this.products.checked : [],
                pid = parseInt(e.currentTarget.value);
            if (e.currentTarget.checked){
                checked = _.union(checked, pid);
            } else {
                checked = _.without(checked, pid);
            }
            this.products.checked = checked;
            console.log(checked);
        },
        massAction: function() {
            var type = $('#product-list-holder').data('type');

            if (!_.has(this.products, 'checked') || _.isEmpty(this.products.checked)){
                return false;
            }

            switch (type){
                case 'edit':
                    this.massDelete(this.products.checked);
                    break;
                case 'related':
                    this.addRelated(this.products.checked);
                    $('#product-list').hide('slide');
                    break;
            }
            $('div.productlisting input.marker:checked', '#product-list-holder').removeAttr('checked');
            this.products.checked = [];

            return false;
        },
        massDelete: function(ids){
            var self = this;
            showConfirm('Oh man... Really?', function(){
                if (!_.isEmpty(ids)) {
                    $.ajax({
                        url: self.products.paginator_core.url()+'id/'+ids.join(','),
                        type: 'DELETE',
                        dataType: 'json',
                        statusCode: {
                            403: function() { showMessage("Forbidden action", true) },
                            409: function() { showMessage("Can't remove products", true); }
                        }
                    }).done(function(){
                        self.products.remove(ids);
                        showMessage('Products removed');
                    });
                }
            });
        },
        initSearchIndex: _.once(function(){
            $.getJSON($('#website_url').val() + '/plugin/shopping/run/searchindex', function(response){
                self.searchIndex = response;
                $('#product-list-search').autocomplete({
                    minLength: 2,
                    source: response,
                    select: function(event, ui){
                        $('#product-list-search').val(ui.item.value).trigger('keypress', true);
                    }
                });
            });
        }),
        toggleList: function(e) {
            e.preventDefault();

            this.initSearchIndex();

            var listtype = $(e.currentTarget).data('listtype');

            $('#product-list').show('slide');
            $('#product-list-holder').data('type', listtype);
            var labels = $('#massaction').data('labels');
            $('#massaction').text(labels[listtype]);

            if (this.products === null) {
                $('#product-list-holder').html('<div class="spinner"></div>');
                return this.initProducts().pager();
            }
        },
        hideProductList: function(){
            $('#product-list').hide('slide');
            var term = $.trim($('#product-list-search').val());
            if (term != this.products.key){
                if (term == ''){
                    $('#product-list-search').trigger('keypress', true);
                } else {
                    $('#product-list-search').val(this.products.key);
                }
            }
        },
        paginatorAction:  function(e){
            var page = $(e.currentTarget).data('page');
            var collection = $(e.currentTarget).parent('.paginator').data('collection');
            if (!collection) return false;
            if (_.has(this, collection)){
                collection = this[collection];
            }

            switch (page) {
                case 'first':
                    collection.goTo(collection.firstPage);
                    break;
                case 'prev':
                    if (collection instanceof Backbone.Paginator.requestPager){
                        collection.requestPreviousPage();
                    } else {
                        collection.previousPage();
                    }
                    break;
                case 'next':
                    if (collection instanceof Backbone.Paginator.requestPager){
                        collection.requestNextPage();
                    } else {
                        collection.nextPage();
                    }
                    break;
                case 'last':
                    collection.goTo(collection.totalPages);
                    break;
                default:
                    var pageId = parseInt(page);
                    !_.isNaN(pageId) && collection.goTo(pageId);
                    break;
            }
            return false;
        }
	});

	return AppView;
});