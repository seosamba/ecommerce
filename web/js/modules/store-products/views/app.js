define([
	'backbone',
    '../collections/productlist',
    '../../product/collections/brands',
    '../../product/collections/tags',
    '../../common/collections/templates',
    './productrow',
    'text!../templates/paginator.html',
    'text!../templates/tax_dialog.html',
    'text!../templates/brands_dialog.html',
    'text!../templates/tags_dialog.html',
    'text!../templates/template_dialog.html',
    'text!../templates/toggle_dialog.html',
    'text!../templates/delete_dialog.html',
    'text!../templates/freeShipping_dialog.html',
    'i18n!../../../nls/'+$('input[name=system-language]').val()+'_ln'
], function(Backbone, ProductsCollection, BrandsCollection, TagsCollection, TemplatesCollection,
            ProductRowView,
            PaginatorTmpl, TaxDialogTmpl, BrandsDialogTmpl, TagsDialogTmpl, TemplateDialogTmpl, ToggleDialogTmpl, DeleteDialogTmpl, FreeShippingDialogTmpl, i18n){
    var MainView = Backbone.View.extend({
        el: $('#store-products'),
        events: {
            'change input[name="check-all"]': function(e) {
                this.products.each(function(prod){
                    prod.set('checked', e.currentTarget.checked);
                });
            },
            'change select[name="product-mass-action"]': 'massAction',
            'click #paginator a.page': 'navigate',
            'change .pfilter': function() {
                this.products.goTo(this.products.firstPage);
            }
        },
        templates: {
            paginator: _.template(PaginatorTmpl)
        },
        initialize: function(){
            this.products = new ProductsCollection();
            this.products.on('reset', this.renderProducts, this);
            this.products.on('reset', this.loadStats, this);
            this.products.pager();
            $.extend($.ui.dialog.prototype.options, {
                modal: true,
                resizable: false,
                minWidth: 300,
                close: function(){
                    $(this).remove();
                }
            });
            $('select.pfilter').chosen().closest('tr:hidden').fadeIn();
        },
        renderProducts: function(productCollection){
            this.products.info()['i18n'] = i18n;
            this.$el.find('#paginator').html(this.templates.paginator(this.products.information));
            this.$el.find('tbody').empty();
            productCollection.each(this.renderProductRow, this);
        },
        renderProductRow: function(product){
            product.set({i18n: i18n});
            var view = new ProductRowView({model: product});
            this.$el.find('tbody').append(view.render().$el);
        },
        navigate: function(e){
            e.preventDefault();

            var page = $(e.currentTarget).data('page');
            if ($.isNumeric(page)){
                this.products.goTo(page);
            } else {
                switch(page){
                    case 'first':
                        this.products.goTo(this.products.firstPage);
                        break;
                    case 'last':
                        this.products.goTo(this.products.totalPages);
                        break;
                    case 'prev':
                        this.products.requestPreviousPage();
                        break;
                    case 'next':
                        this.products.requestNextPage();
                        break;
                }
            }
        },
        massAction: function(e){
            var func = $(e.currentTarget).val()+'Action';

            if (_.isFunction(this[func])){
                var products = this.products.where({checked: true});
                if (products.length){
                    var self = this;
                    this[func].call(self, products);
                }
            }
            $(e.currentTarget).val(0);
        },
        taxAction: function(products){
            var self    = this,
                dialog  = _.template(TaxDialogTmpl, {
                    totalProducts: this.products.totalRecords,
                    i18n:i18n
                });

            var applyButton  = _.isUndefined(i18n['Apply']) ? 'Apply':i18n['Apply'];
            var taxButtons = {};
            taxButtons[applyButton] = function() {
                var taxClass = $(this).find('select[name=taxes]').val();
                self.products.batch('PUT', {taxClass: taxClass}, $(this).find('input[name="applyToAll"]').attr('checked') );
                $(this).dialog("close");
            }

            $(dialog).dialog({
                dialogClass: 'seotoaster',
                buttons: taxButtons
            });
            return false;
        },
        brandAction: function(products){
            var self = this;
            if (!this.brands){
                this.brands = new BrandsCollection();
                this.brands.fetch({async: false});
            }

            var applyButton  = _.isUndefined(i18n['Apply']) ? 'Apply':i18n['Apply'];
            var brandButtons = {};

            brandButtons[applyButton] = function() {
                var brand = $.trim($(this).find('input[name=newbrand]').val());
                brand = !!brand ? brand : $.trim($(this).find('select[name=brands]').val());

                self.products.batch('PUT', {'brand': brand}, $(this).find('input[name="applyToAll"]').attr('checked') );

                $(this).dialog('close');
            };


            var dialog = _.template(BrandsDialogTmpl, {
                brands: this.brands.toJSON(),
                totalProducts: this.products.totalRecords,
                i18n:i18n
            });
            $(dialog).dialog({
                dialogClass: 'seotoaster',
                buttons: brandButtons
            });
            return false;
        },
        tagAction: function(products){
            var self = this;
            if (!this.tags) {
                this.tags = new TagsCollection();
                this.tags.fetch({async: false});
            }
            var used = {};
            _.each(products, function(prod){
                if (prod.has('tags')){
                    _.each(prod.get('tags'), function(tag){
                        if (!_.has(used, tag.name)){
                            used[tag.name] = 1;
                        } else{
                            used[tag.name] += 1;
                        }
                    });
                }
            });

            var applyButton  = _.isUndefined(i18n['Apply']) ? 'Apply':i18n['Apply'];
            var tagButtons = {};

            tagButtons[applyButton] = function() {
                var idTags = _.pluck($(this).find('input.tags').serializeArray(),'value');
                var newTags = [];

                _.each(idTags, function(id){
                    newTags.push(self.tags.get(id).toJSON());
                });

                if (!_.isEmpty(newTags)){
                    self.products.batch('PUT', {tags: newTags}, $(this).find('input[name="applyToAll"]').attr('checked') );
                }
                $(this).dialog("close");
            };

            var dialog = _.template(TagsDialogTmpl, {
                tags: this.tags.toJSON(),
                used: used,
                prodCount: products.length,
                totalProducts: this.products.totalRecords,
                i18n:i18n
            });
            $(dialog).on('change', 'input.partial', function(e){
                if (e.currentTarget.checked === false){
                    $(e.currentTarget).removeClass('partial').parent('label.partial').removeClass('partial');
                }
                return false;
            });
            $(dialog).dialog({
                dialogClass: 'seotoaster',
                width: 960,
                maxHeight : ($(window).height()-$(window).height()*0.1),
                buttons: tagButtons
            });
            return false;
        },
        deleteAction: function(products){
            var self = this,
                dialog = _.template(DeleteDialogTmpl, {
                    totalProducts: this.products.totalRecords,
                    i18n:i18n
                });

            var noButton  = _.isUndefined(i18n['No']) ? 'No':i18n['No'];
            var yesButton  = _.isUndefined(i18n['Yes']) ? 'Yes':i18n['Yes'];
            var deleteButtons = {};

            deleteButtons[noButton] = function() { $(this).dialog("close");};

            deleteButtons[yesButton] = function() {
                self.products.batch('DELETE', null, $(this).find('input[name="applyToAll"]').attr('checked'));
                $(this).dialog("close");
            };


            $(dialog).dialog({
                width: 350,
                dialogClass: 'seotoaster',
                buttons: deleteButtons
            });
        },
        freeshippingAction:function (products){
            var self = this;

            var applyButton  = _.isUndefined(i18n['Apply']) ? 'Apply':i18n['Apply'];
            var freeShippingButtons = {};

            freeShippingButtons[applyButton] = function() {
                var freeShipping = $(this).find($("select option:selected")).val();
                if(freeShipping == -1){
                    return false;
                }

                self.products.batch('PUT', {'freeShipping': freeShipping}, $(this).find('input[name="applyToAll"]').attr('checked') );
                $(this).dialog('close');
            };

            var dialog = _.template(FreeShippingDialogTmpl, {
                totalProducts: this.products.totalRecords,
                i18n:i18n
            });

            $(dialog).dialog({
                dialogClass: 'seotoaster',
                buttons: freeShippingButtons
            });
            return false;
        },
        templateAction: function(products){
            var self = this;

            if (!this.pageTemplates){
                this.pageTemplates = new TemplatesCollection();
                this.pageTemplates.fetch({async: false, data: {filter : 'typeproduct' }});
            }

            if (this.pageTemplates.length === 0){
                this.pageTemplates = null;
                showMessage(_.isUndefined(i18n['No product page templates found']) ? 'No product page templates found':i18n['No product page templates found'], true);
                return false;
            }

            var dialog = _.template(TemplateDialogTmpl, {
                templates: this.pageTemplates.toJSON(),
                totalProducts: this.products.totalRecords,
                i18n:i18n
            });

            var cancelButton = _.isUndefined(i18n['Cancel']) ? 'Cancel':i18n['Cancel'];
            var applyButton  = _.isUndefined(i18n['Apply']) ? 'Apply':i18n['Apply'];
            var templateButtons = {};

            templateButtons[cancelButton] = function() { $(this).dialog('destroy');};

            templateButtons[applyButton] = function() {
                var template = $(this).find('select[name=page-tmpl]').val();
                if (!!template){
                    self.products.batch('PUT', {pageTemplate: template}, $(this).find('input[name="applyToAll"]').attr('checked') );
                }
                $(this).dialog('close');
            };

            $(dialog).dialog({
                width: 350,
                dialogClass: 'seotoaster',
                buttons: templateButtons
            });
        },
        toggleAction: function(products){
            var self = this;
            var dialog = _.template(ToggleDialogTmpl, {
                totalProducts: this.products.totalRecords,
                i18n:i18n
            });

            var enableButton  = _.isUndefined(i18n['Enable']) ? 'Enable':i18n['Enable'];
            var disableButton = _.isUndefined(i18n['Disable']) ? 'Disable':i18n['Disable'];
            var toogleButtons = {};

            toogleButtons[enableButton] = function() {
                self.products.batch('PUT', {enabled: 1}, $(this).find('input[name="applyToAll"]').attr('checked') );
            };

            toogleButtons[disableButton] = function() {
                self.products.batch('PUT', {enabled: 0}, $(this).find('input[name="applyToAll"]').attr('checked') );
            };

            $(dialog).dialog({
                width: 350,
                height: 140,
                dialogClass: 'seotoaster',
                buttons: toogleButtons
            })
        },
        loadStats: function(){
            var self = this;
            $.ajax({
                url: $('#website_url').val()+'api/store/stats',
                data: {id: this.products.pluck('id').join(',')},
                success: function(response){
                    if (_.isArray(response)){
                        var stats = _.groupBy(response, function(r){ return r.product_id; });
                        self.products.each(function(prod){
                           prod.set('stats', _.isUndefined(stats[prod.get('id')]) ? [] : stats[prod.get('id')] );
                        });
                    }
                }
            })
        }
    });

    return MainView;
});