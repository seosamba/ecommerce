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
	'./productlist',
    '../../coupons/views/coupon_form',
    '../../coupons/views/coupons_table',
    '../../groups/views/group_price',
    'i18n!../../../nls/'+$('input[name=system-language]').val()+'_ln'
], function(Backbone,
            ProductModel,  ProductOption,
            ProductsCollection, TagsCollection, OptionsCollection, ImagesCollection,
            TagView, ProductOptionView, ProductListView, CouponFormView, CouponGridView, GroupsPriceView, i18n){

	var AppView = Backbone.View.extend({
		el: $('#manage-product'),
		events: {
            'click #new-product': 'newProduct',
            'click .show-list': 'toggleList',
			'keyup input#new-tag': 'newTag',
			'click #add-new-option-btn': 'newOption',
            'change #option-library': 'addOption',
			'click #submit': 'saveProduct',
			'click #product-image-folder': 'imageChange',
			'click .box': 'setProductImage',
			'change :input[data-reflection]': 'setProperty',
			'change #product-enabled': 'toggleEnabled',
            'change #free-shipping': 'toggleFreeShipping',
			'change #product-tags-available .tag-widget input[name^=tag]': 'toggleTag',
			'click #delete': 'deleteProduct',
            'keypress input#new-brand': 'newBrand',
            'keypress #product-list-search': 'filterProducts',
            'click a[href="#options-tab"]': 'fetchOptionLibrary',
            'submit form.binded-plugin': 'formSubmit',
            'change #product-list-holder input.marker': 'markProducts',
            'click #massaction': 'massAction',
            'click #product-list-back-link': 'hideProductList',
            'click a[data-role=editProduct]': 'productAction',
            'click #toggle-current-tags': function(e){
                e.preventDefault();
                checkboxRadioStyle();
                $('#product-tags-current, #product-tags-available, .paginator', '#tag-tab').toggle();
            },
            'click .paginator a.page': 'paginatorAction',
            'change #automated-set-price': 'toggleSetPriceConfig'
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

            $(document).ajaxStart(function(){
                $('#product-list-search').attr('disabled', 'disabled');
            }).ajaxStop(function(){
                $('#product-list-search').removeAttr('disabled');
            });

            this.quickPreviewTmpl = _.template($('#quickPreviewTemplate').html());

            $('#product-list').hide("slide", { direction: "right"});
			hideSpinner();
            this.$el.tabs({
                beforeLoad: function(event, ui){
                    ui.ajaxSettings.url += '?'+$.param({productId : self.model.get('id')}); // TODO find a right way
                }
            });
            this.$el.on('tabsbeforeactivate', function(event, ui){
                switch (ui.newPanel.selector){
                    case '#tag-tab':
                        self.initTags();
                    break;
                    case '#coupon-tab':
                    case '#group-pricing-tab':
                        if (self.model.isNew()){
                            showMessage(_.isUndefined(i18n['Please save product information first'])?'Please save product information first':i18n['Please save product information first'], true);
                            return false;
                        }
                    break;
                }
            }).show();

            this.images =  new ImagesCollection(),
            this.images.on('reset', this.renderImages, this);

            this.render();

            this.couponForm = new CouponFormView();
            this.couponGrid = new CouponGridView({hideProductColumn: true});
            this.couponForm.$el.on('coupon:created', _.bind(this.couponGrid.render, this.couponGrid));
            this.couponForm.render();

            this.groupsPrice = new GroupsPriceView();

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
                showSpinner('#tag-tab');
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
            this.model.on('change:id', this.setProductIdForCouponAndGroup, this);

            this.model.on('sync', function(){
                if (this.model.has('options')){
                    this.model.get('options').on('add', this.renderOption, this);
                    this.model.get('options').on('reset', this.renderOptions, this);
                }
                if (this.products !== null){
                    this.products.pager();
                }
                this.render();
                var productSavedMessage = _.isUndefined(i18n['Product saved.'])?'Product saved.':i18n['Product saved.'];
                var gotoMesssage = _.isUndefined(i18n['Go to your search engine optimized product landing page here.'])?'Go to your search engine optimized product landing page here.':i18n['Go to your search engine optimized product landing page here.'];
                var messageSaved = productSavedMessage+'</br>'+gotoMesssage;
                showMessage(messageSaved);
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
        toggleFreeShipping: function(e){
            this.model.set({freeShipping: this.$('#free-shipping').prop('checked') ? 1 :0 });
        },
		newTag: function(e){
			var name = $.trim(e.currentTarget.value),
                tagValidation = new RegExp(/[^\u0080-\uFFFF\w\s-]+/gi);
            if (e.keyCode == 13 && name !== '') {
                if(tagValidation.test(name)){
                    showMessage(_.isUndefined(i18n['Tag name should contain only letters, digits and spaces'])?'Tag name should contain only letters, digits and spaces':i18n['Tag name should contain only letters, digits and spaces'], true);
                    $(e.currentTarget).blur();
                    return false;
                } else {
                    this.tags.create({name: name}, {
                        wait: true,
                        success: function(model, response){
                            $('#new-tag').val('').blur();
                        },
                        error: function(model, response){
                            showMessage(response.responseText, true);
                        }
                    });
                    // Reset tag collection
                    this.tags.nameTag     = '';
                    this.tags.currentPage = 1;
                    this.tags.fetch();
                }
			}
            else {
                // Search tag
                this.tags.nameTag     = name;
                this.tags.currentPage = 1;
                this.tags.fetch();
            }
		},
		newOption: function(){
			var newOption = new ProductOption();
            newOption.get('selection').add({isDefault: 1});
			this.model.get('options').add(newOption);
            this.renderOptions();
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
            $('#image-select-dialog').show("slide", { direction: "left"});
			var folder = $(e.target).val();
			if (folder == '0') {
				return;
            }
            var self = this;
            this.images.server_api.folder = folder;
            this.images.flush().fetch({ success: function(){ self.images.pager(); hideSpinner();}, silent: true});
            //$('#image-select-dialog').show("slide", { direction: "right"});
        },
        renderImages: function(){
            $('#image-list').html(_.template($('#imgTemplate').html(), {images: this.images.toJSON()}))
            $('.paginator', '#image-select-dialog').replaceWith(_.template($('#paginatorTemplate').html(), _.extend(
                this.images.paginator_ui,
                this.images.info(),
                {collection: 'images', cssClass: ''}
            )));
        },
        setProductImage: function(e){
            var imgName = $(e.currentTarget).find('img').data('name');
            var fldrName = this.$('#product-image-folder').val();
            this.model.set({photo: fldrName+'/'+imgName });
            this.$('#product-image').attr('src', $('#website_url').val() + this.mediaPath + fldrName +'/small/'+ imgName);
            this.$('#image-select-dialog').hide("slide", { direction: "left"});
            this.$('#product-image-folder').val('0');
            this.$('#image-list, .paginator').empty();
        },
		setProperty: function(e){
			var propName = $(e.currentTarget).data('reflection');
			this.model.set(propName, _.isNaN(e.currentTarget.value) ? null : e.currentTarget.value) ;
		},
		render: function(){
            console.log('render: app.js', this.model.changedAttributes());
            this.$el.tabs({ active: 0 });

            $('#product-list:visible').hide("slide", { direction: "right"});

            $('#quick-preview').empty(); //clening preview content

            //hiding delete button if product is new
            if (!this.model.isNew()){
                $('#delete').show();
            } else {
                $('#delete').hide();
            }

			//setting model properties to view
            var photoUrl = $('#website_url').val()+'system/images/noimage.png';
			if (!this.model.has('photo')){
                this.$('#product-image').attr('src', $('#website_url').val()+'system/images/noimage.png');
            } else {
                photoUrl = this.model.get('photo');
                if (!/^https?:\/\/.*/.test(photoUrl)){
                    photoUrl = $('#website_url').val()+ this.mediaPath + photoUrl.replace('/', '/small/');
                }
            }
            this.$('#product-image').attr('src', photoUrl);

            this.$('#product-brand').val(-1); //reseting brand field

            var self = this;
            _.each(this.model.toJSON(), function(value, name){
                self.$('[data-reflection='+name+']').val(value);
            });

            if (this.model.has('related')){
                _.isEmpty(this.model.get('related')) && this.$('#related-holder').find('.spinner').remove();
            }

			// loading option onto frontend
//			$('#options-holder').empty();
//			if (this.model.has('options')) {
            this.renderOptions();
//			}

			//toggle enabled flag
			if (parseInt(this.model.get('enabled'))){
				this.$('#product-enabled').attr('checked', 'checked');
			} else {
				this.$('#product-enabled').removeAttr('checked');
			}

            //toggle free-shipping flag
            if (parseInt(this.model.get('freeShipping'))){
                this.$('#free-shipping').attr('checked', 'checked');
            } else {
                this.$('#free-shipping').removeAttr('checked');
            }

			if (this.model.has('pageTemplate')){
				this.$('#product-pageTemplate').val(this.model.get('pageTemplate'));
			} else if (this.model.has('page')){
                this.$('#product-pageTemplate').val(this.model.get('page').templateId);
            } else {
                this.$('#product-pageTemplate').val('-1');
			}
            if(this.model.isNew()){
                this.$('#product-price').val('');
                this.$('#product-weight').val('');
            }
            if (!this.model.isNew()){
                $('#quick-preview').html(this.quickPreviewTmpl({
                    product: this.model.toJSON(),
                    websiteUrl: $('#website_url').val(),
                    currency: this.$('#currency-unit').text(),
                    photoUrl: photoUrl
                }));
            }

			hideSpinner();
		},
        renderTag: function(tag, index){
            var view = new TagView({model: tag});
                view.render();
            if (index instanceof Backbone.Collection){
				$('#product-tags-available').prepend(view.$el);
            } else {
                $('#product-tags-available').append(view.$el);
            }
            if ($('.tagid-'+tag.get('id'), '#product-tags-current').size()){
                view.$el.addClass('tag-current').find('input:checkbox').prop({
                    disabled: true,
                    checked: true
                });
            }
        },
        renderTags: function(){
            showSpinner('#tag-tab');
            $('#product-tags-available').empty();
            this.tags.each(this.renderTag, this);
            var paginatorData = {
                pages: 2,
                collection : 'tags',
                cssClass: 'fl-right ml-grid mt5px'
            };

            $('.paginator', '#tag-tab').replaceWith(_.template($('#paginatorTemplate').html(), _.extend(paginatorData, this.tags.info())));
            hideSpinner();
        },
        toggleTag: function(e){
            if (e.currentTarget.checked){
                var tag = {
                    id: e.currentTarget.value,
                    name: $(e.currentTarget).closest('.tag-widget').find('.tag-editable').text()
                };
                var current = this.model.get('tags') || [];
                this.model.set('tags', _.union(current, tag));
            }
            $(e.currentTarget).attr({
                disabled: 'disabled'
            }).closest('.tag-widget').effect("transfer", {
               to: '#toggle-current-tags',
               className: 'ui-effects-transfer'
            }, 500);
        },
        renderProductTags: function(){
            console.log('render product tags');
            $('.tag-widget input:checkbox', '#product-tags-available').prop({
                disabled: false,
                checked: false
            }).closest('.tag-widget').removeClass('tag-current');

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
                            $('.tagid-'+id+' input:checkbox', '#product-tags-available').prop({
                                disabled: false,
                                checked: false
                            }).closest('.tag-widget').removeClass('tag-current');
                        }
                    });
                    $('.tagid-'+tag.id+' input:checkbox', '#product-tags-available').prop({
                        disabled: true,
                        checked: true
                    }).closest('.tag-widget').addClass('tag-current');

                    view.render().$el
                        .find('.ticon-remove').remove().end()
                        .find('input:checkbox').prop('checked', true).end()
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
                productView.$el.find('input.marker').prop({
                    checked: true
                });
            }
        },
        renderProducts: function(){
            if (this.products.size()){
                this.$('#product-list-holder').empty();
                this.products.each(this.renderProduct, this);
                var paginatorData = {
                    collection : 'products',
                    cssClass: ''
                };
                paginatorData = _.extend(paginatorData, this.products.info());
                $('.paginator', '#product-list').replaceWith(_.template($('#paginatorTemplate').html(), paginatorData));
            } else {
                $('#product-list-holder').html('<p class="nothing">'+$('#product-list-holder').data('emptymsg')+'</p>');
            }
        },
		saveProduct: function(){
            showSpinner();
            var self = this;

            if (!this.validateProduct()) {
                hideSpinner();
                showMessage(_.isUndefined(i18n['Missing some required fields'])?'Missing some required fields':i18n['Missing some required fields'], true);
                $('#manage-product').tabs({active : 0});
                return false;
            }

            if (this.model.has('options')){
                var newInLibrary = !_.isEmpty(_.compact(this.model.get('options').pluck('isTemplate')));
			    this.model.set({defaultOptions: this.model.get('options').toJSON()});
            }

            var newBrandName = $('#new-brand').val();
            if (newBrandName){
                this.addNewBrand(newBrandName).$('#new-brand').val('');
            }

            this.model.save();

            if (newInLibrary && self.hasOwnProperty('optionLibrary')){
                self.optionLibrary.fetch();
            }
		},
        processSaveError: function(model, response){
            hideSpinner();
            showMessage(response.responseText, true);
        },
		deleteProduct: function(){
			var self = this;
			if (this.model.isNew()){
                showMessage(_.isUndefined(i18n['Product is not saved yet'])?'Product is not saved yet':i18n['Product is not saved yet'], true);
				return false;
			}
            showConfirm('Dragons ahead! Are you sure?', function(){
                self.model.destroy({
                    success: function(model, response){
                        self.products && self.products.pager();
                        $('#new-product').trigger('click');
                        showMessage(_.isUndefined(i18n['Product deleted'])?'Product deleted':i18n['Product deleted']);
                        location.reload();
                    }
                });
			});
		},
        validateProduct: function(){
            var error   = false;
            if (!this.$('#product-pageTemplate').val()){
                this.$('#product-pageTemplate').addClass('error');
                $('.missing-template').addClass('error');
                error = true || error;
            } else {
                var templateId = this.$('#product-pageTemplate').val();
                this.model.set({pageTemplate: templateId});
                this.$('#product-pageTemplate').removeClass('error');
            }

            if (!this.model.has('name') || $.trim(this.model.get('name')) === ''){
                this.$('#product-name').addClass('error');
                error = true || error;
            } else {
                this.$('#product-name').removeClass('error');
            }

            if (!this.model.has('sku') || $.trim(this.model.get('sku')) === ''){
                this.$('#product-sku').addClass('error');
                error = true || error;
            } else {
                this.$('#product-sku').removeClass('error');
            }

            if (!this.model.has('price')){
                this.$('#product-price').addClass('error');
                error = true || error;
            } else {
                this.$('#product-price').removeClass('error');
            }

            if (!this.model.has('brand') && $.trim($('#new-brand').val()) === '') {
                this.$('#product-brand').addClass('error');
                error = true || error;
            } else {
                this.$('#product-brand').removeClass('error');
            }

            if (!this.model.has('photo')) {
                this.$('.product-preview').addClass('error');
                error = true || error;
            } else {
                this.$('.product-preview').removeClass('error');
            }

            if (!this.model.has('shortDescription') || $.trim(this.model.get('shortDescription')) === ''){
                this.$('#product-shortDescription').addClass('error');
                error = true || error;
            } else {
                this.$('#product-shortDescription').removeClass('error');
            }

            return !error;
        },
        productAction: function(e){
            var pid = $(e.currentTarget).data('pid');
            var type = $('#product-list-holder').data('type');
            switch (type){
                case 'edit':
                    this.model.clear({silent:true}).set(this.products.get(pid).toJSON());
                    this.model.get('options').on('add', this.renderOption, this);
                    this.render();
                    if (window.history && window.history.pushState){
                        var loc = window.location;
                        window.history.pushState({}, document.title, loc.href.replace(/product.*$/, 'product/id/'+pid) );
                    }
                    break;
                case 'related':
                    this.addRelated(pid);
                    break;
                case 'set':
                    this.addPart(pid);
                    break;
            }
            $('#product-list').hide("slide", { direction: "right"});
            return false;
        },
		addRelated: function( ids ) {
            if (_.isNull(ids) || _.isUndefined(ids)) return false;

            var relateds = _.map(this.model.get('related'), function(id){ return parseInt(id) });
                relateds = _.union(relateds, ids);

            this.model.set({related: _.without(relateds, this.model.get('id'))});
		},

        toggleSetPriceConfig: function(e) {
            var checked = ($(e.currentTarget).prop('checked')) ? 1 : 0;
            $.post($('#website_url').val() + 'plugin/shopping/run/setConfig/', {config: {autocalculateSetPrice: checked, secureToken: $('.secure-token-tax').val()}});
        },

        removeRelated: function(id){
            var relateds = _(this.model.get('related')).map(function(id){ return parseInt(id) });
			this.model.set({related: _.without(relateds, parseInt(id))});
		},
		renderRelated: function() {
            $('#related-holder').find('.productlisting:not(.show-list)').remove();
            showSpinner();

            if (this.model.has('related') && this.model.get('related').length) {
                var relateds = this.model.get('related'),
                    self = this;

                $.ajax({
                    url: this.model.urlRoot(),
                    data: {id: relateds.join(',')},
                    success: function(response){
                        if (!response) return false;
                        if (response && !_.isArray(response)){
                            response = [response];
                        }
                        $('#related-holder').find('.productlisting:not(.show-list)').remove();
                        showSpinner();
                        _.each(response, function(related){
                            var view = new ProductListView({model: new ProductModel(related), showDelete: true});
                            view.delegateEvents({
                                'click .delete': function(){
                                    self.removeRelated(this.model.get('id'));
                                }
                            })
                            view.render().$el.css({cursor: 'default'}).appendTo('#related-holder');
                        });
                        hideSpinner();
                    }
                });
            }else{
                hideSpinner();
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
            checkboxRadioStyle();
        },
        renderOptions: function(){
            $('#options-holder').empty();
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
                    $('#product-list').hide("slide", { direction: "right"});
                    break;
                case 'set':
                    this.addPart(this.products.checked);
                    $('#product-list').hide("slide", { direction: "right"});
                    break;
            }
            $('div.productlisting input.marker:checked', '#product-list-holder').prop({
                checked: false
            });
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
                            403: function() { showMessage(_.isUndefined(i18n['Forbidden action'])?'Forbidden action':i18n['Forbidden action'], true) },
                            409: function() { showMessage(_.isUndefined(i18n['Can\'t remove products'])?'Can\'t remove products':i18n['Can\'t remove products'], true); }
                        }
                    }).done(function(){
                        self.products.remove(ids);
                        showMessage(_.isUndefined(i18n['Products removed'])?'Products removed':i18n['Products removed']);
                    });
                }
            });
        },
        initSearchIndex: _.once(function(){
            var self = this;
            $.getJSON($('#website_url').val() + 'plugin/shopping/run/searchindex', function(response){
                self.searchIndex = response;
                $('#product-list-search').autocomplete({
                    minLength: 2,
                    source: self.searchIndex,
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

            $('#product-list').show("slide", { direction: "right"});
            $('#product-list-holder').data('type', listtype);
            var labels = {
                "related": "<span class='success'>[ Add as related ]</span>",
                "edit": "<span class='error'>[ Delete selected ]</span>",
                "set": "[ add to set ]"
            };
            $('#massaction').html(labels[listtype]);

            if (this.products === null) {
                $('#product-list-holder').html('<div class="spinner"></div>');
                return this.initProducts().pager();
            }
        },
        hideProductList: function(){
            $('#product-list').hide("slide", { direction: "right"});
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
        },
        setProductIdForCouponAndGroup: function(){
            var productId = this.model.get('id');
            var productPrice = this.model.get('price');
            this.couponForm.$el.find('input#data-products').val(productId);
            this.couponGrid.coupons.server_api.productId = productId;
            this.couponGrid.render();
            this.groupsPrice.$el.find('input#group-products-price').val(productPrice);
            this.groupsPrice.$el.find('input#group-products-id').val(productId);
            this.$el.find('#group-regular-price').html(' '+$('#group-products-price-symbol').val()+parseFloat(productPrice).toFixed(2));
            this.groupsPrice.groups.server_api.productId = productId;
            this.groupsPrice.render();
        }
	});

	return AppView;
});