define([
	'Underscore',
	'Backbone',
	'modules/product/models/product',
	'modules/product/views/tag',
	'modules/product/models/option',
	'modules/product/views/option',
	'modules/product/views/productlist'
], function(_, Backbone, ProductModel, TagView, ProductOption, ProductOptionView, ProductListView){

	var AppView = Backbone.View.extend({
		el: $('#manage-product'),
		events: {
            'click .show-list': 'toggleList',
			'keypress input#new-tag': 'newTag',
			'click #add-new-option-btn': 'newOption',
            'change select#option-library': 'addOption',
			'click #submit': 'saveProduct',
			'change #product-image-folder': 'imageChange',
			'click div.box': 'setProductImage',
			'change [data-reflection=property]': 'setProperty',
			'change #product-enabled': 'toggleEnabled',
			'click input[name^=tag]': 'toggleTag',
			'click #delete': 'deleteProduct',
            'keypress input#new-brand': 'newBrand',
            'keypress #product-list-search': 'filterProducts',
            'mouseover #option-library': 'fetchOptionLibrary',
            'submit form.binded-plugin': 'formSubmit',
            'click #massaction': 'massAction'
		},
		websiteUrl: $('#website_url').val(),
		initialize: function(){
			$('#add-new-option-btn').button();
            var self = this;
            $('#product-list-search').ajaxStart(function(){
                    $(this).attr('disabled', 'disabled');
                }).ajaxStop(function(){
                    $(this).removeAttr('disabled');
                })
            $.getJSON(this.websiteUrl + 'plugin/shopping/run/getdata/type/index', function(response){
                $('#product-list-search').autocomplete({
                    minLength: 2,
                    source: response,
                    select: function(event, ui){
                        $('#product-list-search').val(ui.item.value).trigger('keypress', true);
                    }
                });
            });
            this.quickPreviewTmpl = $('#quickPreviewTemplate').template();
		},
		setModel: function (model) {
			this.model = model;
            this.model.view = this;
            this.model.bind('change', this.render, this);
            this.model.trigger('change');
            $('#manage-product').tabs("select" , 0);
		},
		toggleEnabled: function(e){
			this.model.set({enabled: this.$('#product-enabled').prop('checked') ? 1 :0 });
		},
		newTag: function(e){
			var name = this.$('#new-tag').val();
			if (e.keyCode == 13 && name !== '') {
			   appRouter.tags.create({name: name}, {
				   success: function(model, response){
					   $('#new-tag').val('').blur();
				   },
				   error: function(model, response){
					   showMessage(response.responseText, true);
				   }
			   });
			}
		},
		toggleTag: function(e){
			var checkedTags = [];

			_.each($('input[name^=tag]:checked'), function(el){
				checkedTags.push(appRouter.tags.get( el.value ).toJSON());
			});

			this.model.set({tags: checkedTags});
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

            //render related products
            this.renderRelated();

            //populating selected tags
			$('#product-tags').find('input:checkbox:checked').removeAttr('checked');
			if (this.model.has('tags')){
				_.each(this.model.get('tags'), function(tag, name){
					var el = appRouter.tags.get(tag.id).view.el;
					$(el).find(':checkbox').attr('checked','checked');
				});
			}

			//toggle enabled flag
			if (parseInt(this.model.get('enabled'))){
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
                this.$('#product-pageTemplate').val(this.model.get('page').templateId);
            }

            if (!this.model.isNew()){
                $('#quick-preview').html($.tmpl(this.quickPreviewTmpl, this.model.toJSON()));
            }

			$('#image-list').masonry({
				itemSelector : '.box',
				columnWidth : 118
			});

            $('div#ajax_msg:visible').hide('fade');
		},
		saveProduct: function(){
			//@todo: make messages translatable
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

            if ($('#new-brand').val()){
                this.addNewBrand($('#new-brand').val()).$('#new-brand').val('');
            }

			if (this.model.isNew()){
				this.model.save(null, {success: function(model, response){
                    if (appRouter.products !== null) {
                        appRouter.products.add(model);
                    }
                    appRouter.navigate('edit/'+model.id, true);
                    showMessage('Product added');
                }, error: this.processSaveError});
			} else {
				this.model.save(null, {success: function(model, response){
					showMessage('Product saved');
				}, error: this.processSaveError});
			}

            if (newInLibrary && appRouter.hasOwnProperty('optionLibrary')){
                appRouter.optionLibrary.fetch();
            }
		},
        processSaveError: function(model, response){
            showMessage(response.responseText, true);
        },
		deleteProduct: function(){
			var model  = this.model;
			if (model.isNew()){
                showMessage('Product is not saved yet', true);
				return false;
			}
            showConfirm('Dragons ahead! Are you sure?', function(){
                model.destroy({
                    success: function(model, response){
                        appRouter.brands.fetch()
                        appRouter.navigate('new', true);
                    },
                    error: function(model, response){
                        showMessage('Oops! Something went wrong!', true);
                        console.log($.parseJSON(response.responseText));
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

            var relateds = _(appRouter.app.model.get('related')).map(function(id){ return parseInt(id) });
                relateds = _.union(relateds, ids);

            appRouter.app.model.set({related: _.without(relateds, this.model.get('id'))});
		},
		removeRelated: function(id){
            var relateds = _(appRouter.app.model.get('related')).map(function(id){ return parseInt(id) });
			this.model.set({related: _.without(relateds, parseInt(id))});
		},
		renderRelated: function() {
            $('#related-holder').empty();

            if (this.model.has('related') && this.model.get('related').length) {
                var relateds = this.model.get('related');

                _(relateds).each(function (pid) {
                    pid = parseInt(pid);

                    if (appRouter.products !== null){
                        var model = appRouter.products.get(pid);
                    }
                    if (!model) {
                        var model = new ProductModel();
                        model.fetch({data: {id: pid}});
                    }
                    var view = new ProductListView({model: model, showDelete:true});
                    view.render().$el.css({cursor: 'default'}).appendTo('#related-holder');
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
            var currentList = appRouter.brands.pluck('name').map(function(item){ return item.toLowerCase()});
            if (!_.include(currentList, newBrand.toLowerCase())){
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
        filterProducts: function(e, forceRun) {
            if (e.keyCode === 13 || forceRun === true) {
                appRouter.products.reset().load([
                    appRouter.app.waypointCallback,
                    function(response){ if (response.length === 0) { $('#product-list-holder').html('<p class="nothing">'+$('#product-list-holder').data('emptymsg')+'</p>')} ; }
                ], {key: e.target.value});
                $(e.target).autocomplete('close');
            }
        },
        fetchOptionLibrary: function(){
            if (!appRouter.hasOwnProperty('optionLibrary')){
                var optionsLibrary = Backbone.Collection.extend({
                    url: this.websiteUrl + 'plugin/shopping/run/getdata/type/options/',
                    model: ProductOption,
                    initialize: function(){
                        this.bind('reset', function(collection){
                            $('#option-library').html('<option value="-1" disabled="disabled">select from library</option>')
                                .append($.tmpl('<option value="${id}" >${title}</option>', collection.toJSON()));
                        }, this);
                    }
                });
                appRouter.optionLibrary = new optionsLibrary();
                appRouter.optionLibrary.fetch();
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
                    console.log(response);
                    if (response.hasOwnProperty('result')) {
                        smoke.alert(response.result);
                    }
                }
            });
            return false;
        },
        massAction: function() {
            var type = $('#product-list-holder').data('type'),
                prodlist = appRouter.products.filter(function(prod){ return prod.has('marked'); }),
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
            showConfirm('Oh man... Really?', function(){
                if (!_.isEmpty(ids)) {
                    $.ajax({
                        url: appRouter.products.url() + ids.join('/'),
                        type: 'DELETE',
                        dataType: 'json',
                        statusCode: {
                            409: function() {
                                showMessage("Can't remove products", true);
                            }
                        }
                    }).done(function(){
                        appRouter.products.remove(ids);
                        showMessage('Products removed');
                    });
                }
            });
        },
        toggleList: function(e) {
            e.preventDefault();

            var element = $(e.target),
                listtype = element.data('listtype');

            var callback = function(){
                $('#product-list').show('slide');
                $('#product-list-holder').data({type: listtype}).trigger('scroll');
                var labels = $('#massaction').data('labels');
                $('#massaction').text(labels[listtype]);
            }

            if (appRouter.products === null) {
                appRouter.initProductlist().load([
                    this.waypointCallback,
                    callback
                ]);
                return;
            }
            callback();
        },
        waypointCallback: function(){
            $('.productlisting:last', '#product-list-holder').waypoint(function(){
                $(this).waypoint('remove');
                appRouter.products.load(appRouter.app.waypointCallback);
            }, {context: '#product-list-holder', offset: '130%' } );
        }
	});

	return AppView;
});