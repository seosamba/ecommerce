define([
	'backbone',
	'../models/product',
    '../models/option',
    '../collections/productlist',
    '../collections/tags_lazy',
    '../collections/options',
    './tag',
	'./option',
	'./productlist'
], function(Backbone,
            ProductModel, ProductOption,
            ProductsCollection, TagsCollection, OptionsCollection,
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
			'click div.tag-widget input[name^=tag]': 'toggleTag',
			'click #delete': 'deleteProduct',
            'keypress input#new-brand': 'newBrand',
            'keypress #product-list-search': 'filterProducts',
            'mouseover #option-library': 'fetchOptionLibrary',
            'submit form.binded-plugin': 'formSubmit',
            'click #massaction': 'massAction',
            'click #product-list-back-link': 'hideProductList',
            'click a[data-role=editProduct]': function(e){
                var pid = $(e.currentTarget).data('pid');
                this.setProduct(pid);
                if (window.history && window.history.pushState){
                    var loc = window.location;
                    window.history.pushState({}, document.title, loc.href.replace(/product.*$/, 'product/id/'+pid) );
                }
                return false;
            }
		},
        products: null,
        tags: null,
        brands: null,
        searchIndex: null,
		websiteUrl: $('#website_url').val(),
		initialize: function(){
            var self = this;
            this.setProduct();
            $('#add-new-option-btn').button();

            $('#product-list-search').ajaxStart(function(){
                $(this).attr('disabled', 'disabled');
            }).ajaxStop(function(){
                $(this).removeAttr('disabled');
            });

            this.quickPreviewTmpl = $('#quickPreviewTemplate').template();

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
		},
        initProducts: function(){
            if (this.products === null) {
                this.products = new ProductsCollection();
                this.products.bind('add', this.renderProduct, this);
                this.products.bind('reset', this.renderAllProducts, this);
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
        setProduct: function (productId) {
            this.model = new ProductModel();

            if (productId) {
                if (this.products !== null) {
                    this.model = this.products.get(productId);
                    this.render();
                } else {
                    this.model.fetch({data: {id: productId}})
                        .success(this.render.bind(this));
                }
            }

            this.model.on('change:tags', this.renderProductTags, this);
            this.model.on('change:related', this.renderRelated, this);
            this.model.on('error', this.processSaveError, this);

            $('#manage-product').tabs("select" , 0);
            return this;
		},
        newProduct: function(e) {
            e.preventDefault();
            this.setProduct().render();
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
			var optWidget = new ProductOptionView({model: newOption});
			this.model.get('options').add(newOption);
			$('#options-holder').append(optWidget.render().el);
			optWidget.addSelection();
		},
        addOption: function(){
            var optId = this.$('#option-library').val();
            if (optId > 0 ){
                var option = this.optionLibrary.get(optId).toJSON();

                var newOption = new ProductOption({
                    title: option.title,
                    parentId: option.id,
                    type: option.type
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
            $.post('/backend/backend_media/getdirectorycontent', {folder: folder}, function(response){
				var $box = $('#image-list');
				$box.empty();
				if (response.hasOwnProperty('imageList') && response.imageList.length ){
					var $images = $('#imgTemplate').tmpl(response);
					$box.append($images).imagesLoaded(function(){
                        $(this).masonry('reload')
                            .find('img.lazy').lazyload();
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
            this.$('#product-image').attr('src', '/media/'+this.model.get('photo').replace('/', '/small/'));
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

            $('#product-list:visible').hide();
            $("#manage-product").tabs( "option", "ajaxOptions",
                { data: {productId: this.model.get('id') } }
            );

            $('#quick-preview').empty(); //clening preview content

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
					optWidget.render().$el.appendTo('#options-holder');
				});
			}

            //populating selected tags
			$('#product-tags-available:not(:empty)').find('div.tag-widget').show();

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
                $('#quick-preview').html($.tmpl(this.quickPreviewTmpl, this.model.toJSON()));
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
                view.$el.hide();
            }
        },
        renderTags: function(){
            this.tags.each(this.renderTag, this);
            this.tagWaypoint();
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
            return false;
        },
        tagWaypoint: function(){
            var self = this;

            $('#product-tags-available div.tag-widget:last').waypoint(function(){
                $(this).waypoint('remove');

                if ( self.tags.currentPage != self.tags.lastPage ){
                    self.tags.requestNextPage();
                }
            }, {context: '#product-tags-available', offset: '170%' });
        },
        renderProductTags: function(){
            console.log('renderProductTags');
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
                            $('div.tagid-'+id+':hidden', '#product-tags-available').show();
                        }
                    });
                    view.render().$el
                        .find('span.ui-icon-closethick').remove().end()
                        .find('input:checkbox').attr('checked', 'checked').end()
                        .appendTo(container);
//                    $('#product-tags input:checkbox[name^=tag][value='+tag.id+']').attr('checked', 'checked');

                    $('div.tagid-'+tag.id+':visible', '#product-tags-available').effect("transfer", {
                        to: view.$el,
                        className: 'ui-effects-transfer'
                    }, 500).hide();
                });

                var ids = _.map(_.pluck(this.model.get('tags'), 'id'), function(id){
                    return '.tagid-'+id;
                });
                console.log($(ids));
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
            if (this.$('#product-list-holder').children().size() === this.products.size()){
                this.$('#product-list-holder').find('img.lazy').lazyload({
                    container: this.$('#product-list-holder'),
                    effect: 'fadeIn'
                }).removeClass('lazy');
            }
        },
        renderAllProducts: function(){
            this.$('#product-list-holder').empty();
            this.products.each(this.renderProduct, this);
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

			if (this.model.isNew()){
				this.model.save(null, {success: function(model, response){
                    if (self.products !== null) {
                        self.products.add(model);
                    }
                    showMessage('Product saved.<br/> Go to your search engine optimized product landing page here.');
                }, error: this.processSaveError});
			} else {
				this.model.save(null, {success: function(model, response){
					showMessage('Product saved.<br/> Go to your search engine optimized product landing page here.');
                    self.render();
				}, error: this.processSaveError});
			}

            if (newInLibrary && self.hasOwnProperty('optionLibrary')){
                self.optionLibrary.fetch();
            }
		},
        processSaveError: function(model, response){
            showMessage(response.responseText, true);
        },
		deleteProduct: function(){
			var self = this;
                model  = this.model;
			if (model.isNew()){
                showMessage('Product is not saved yet', true);
				return false;
			}
            showConfirm('Dragons ahead! Are you sure?', function(){
                model.destroy({
                    success: function(model, response){
                        self.brands.fetch()
                        self.navigate('new', true);
                    },
                    error: function(model, response){
                        showMessage('Oops! Something went wrong!', true);
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
		addRelated: function( ids ) {
            if (_.isNull(ids) || _.isUndefined(ids)) return false;

            var relateds = _(this.model.get('related')).map(function(id){ return parseInt(id) });
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
                this.products.data.key = e.target.value;
                this.products.reset().load([
                    this.waypointCallback.bind(this),
                    function(response){ if (response.length === 0) { $('#product-list-holder').html('<p class="nothing">'+$('#product-list-holder').data('emptymsg')+'</p>')} ; }
                ]);
                $(e.target).autocomplete('close');
            }
        },
        fetchOptionLibrary: function(){
            if (!_.has(this, 'optionLibrary')){
                this.optionLibrary = new OptionsCollection();
                this.optionLibrary.on('reset', function(collection){
                    $('#option-library')
                        .html('<option value="-1" disabled="disabled" selected="selected">select from library</option>')
                        .append($.tmpl('<option value="${id}" >${title}</option>', collection.toJSON()));
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
        massAction: function() {
            var type = $('#product-list-holder').data('type'),
                prodlist = this.products.filter(function(prod){ return prod.has('marked'); }),
                ids = _.pluck(prodlist, 'id');

            switch (type){
                case 'edit':
                    this.massDelete(ids);
                    break;
                case 'related':
                    this.addRelated(ids);
                    $('#product-list').hide('slide');
                    _.each(prodlist, function(prod){
                        prod.unset('marked');
                    })
                    break;
            }

            return false;
        },
        massDelete: function(ids){
            var self = this;
            showConfirm('Oh man... Really?', function(){
                if (!_.isEmpty(ids)) {
                    $.ajax({
                        url: self.products.urlOriginal +'id/'+ids.join(','),
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
            $.getJSON('/plugin/shopping/run/searchindex', function(response){
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

            var listtype = $(e.target).data('listtype');

            var callback = function(){
                $('#product-list').show('slide');
                $('#product-list-holder').data({type: listtype}).trigger('scroll');
                var labels = $('#massaction').data('labels');
                $('#massaction').text(labels[listtype]);
            }

            if (this.products === null) {
                return this.initProducts().load([
                    this.waypointCallback.bind(this),
                    callback
                ]);
            }
            callback();
        },
        waypointCallback: function(){
            var self = this;
            $('.productlisting:last', '#product-list-holder').waypoint(function(){
                $(this).waypoint('remove');

                if (!self.products.paginator.last){
                    self.products.load(self.waypointCallback.bind(self));
                }
            }, {context: '#product-list-holder', offset: '130%' } );
        },
        hideProductList: function(){
            $('#product-list').hide('slide');
            var term = $('#product-list-search').val();
            if (term != this.products.data.key){
                if (term == ''){
                    $('#product-list-search').trigger('keypress', true);
                } else {
                    $('#product-list-search').val(this.products.data.key);
                }
            }
        }
	});

	return AppView;
});