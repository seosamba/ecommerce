define([
	'backbone',
    '../collections/productlist',
    '../../product/collections/brands',
    '../../companies/collections/company',
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
    'text!../templates/company_product_dialog.html',
    'text!../templates/quantity_dialog.html',
    'text!../templates/negative-stock_dialog.html',
    'text!../templates/promo_dialog.html',
    'i18n!../../../nls/'+$('input[name=system-language]').val()+'_ln'
], function(Backbone, ProductsCollection, BrandsCollection, CompaniesCollection, TagsCollection, TemplatesCollection,
            ProductRowView,
            PaginatorTmpl, TaxDialogTmpl, BrandsDialogTmpl, TagsDialogTmpl, TemplateDialogTmpl, ToggleDialogTmpl, DeleteDialogTmpl,
            FreeShippingDialogTmpl, CompanyProductDialogTmpl, QuantityDialogTmpl, NegativeStockDialogTmpl, PromoDialogTmpl, i18n){
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
            this.products.on('reset', this.loadSuppliersCompanies, this);
            this.products.on('reset', this.loadCompanies, this);
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
            this.brands = new BrandsCollection();
            this.brands.fetch({async: false});

            var applyButton  = _.isUndefined(i18n['Apply']) ? 'Apply':i18n['Apply'];
            var brandButtons = {};

            brandButtons[applyButton] = function() {
                var brand = $.trim($(this).find('input[name=newbrand]').val()),
                    brandValidation = new RegExp(/[^\u0080-\uFFFF\w\s-]+/gi);

                brand = !!brand ? brand : $.trim($(this).find('select[name=brands]').val());
                if(brandValidation.test(brand)){
                    showMessage(_.isUndefined(i18n['Brand name should contain the following characters only: a-z, A-Z, 0-9, -(dash), _(underscore) and space.'])?'Brand name should contain the following characters only: a-z, A-Z, 0-9, -(dash), _(underscore) and space.':i18n['Brand name should contain the following characters only: a-z, A-Z, 0-9, -(dash), _(underscore) and space.'], true, 3000);
                    return false;
                } else {
                    if(brand == '' || typeof brand === 'undefined') {
                        showMessage(_.isUndefined(i18n['Can\'t assign brand. Please select brand or provide new!']) ? 'Can\'t assign brand. Please select brand or provide new!':i18n['Can\'t assign brand. Please select brand or provide new!'], true, 3000);
                        return false;
                    }
                    self.products.batch('PUT', {'brand': brand}, $(this).find('input[name="applyToAll"]').attr('checked') );
                    $(this).dialog('close');
                }
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
        companyAction: function() {
            if (!this.companies){
                this.companies = new CompaniesCollection();
                this.companies.fetch({async: false});
            }

            var checked = this.products.where({checked: true}),
                productIds     = _.pluck(checked, 'id'),
                self = this;

            if (!productIds.length){
                return false;
            }

            var applyButton  = _.isUndefined(i18n['Apply']) ? 'Apply':i18n['Apply'];
            var companyProductButtons = {};

            companyProductButtons[applyButton] = function() {

                var companies = [];
                $(this).find('input[name=company]:checked').each(function(){
                    companies.push($(this).val());
                });

                $.ajax({
                    url: $('#website_url').val() + 'api/store/companyproducts/',
                    type: 'POST',
                    data: {'companies': companies, productIds:productIds, removeOldCompanies: '1'},
                    dataType: 'json',
                    success: function(response){
                        showMessage(response.responseText, false, 5000);
                    }
                });

                $(this).dialog('close');
            };

            $.ajax({
                url: $('#website_url').val() + 'api/store/companyproducts/',
                type: 'GET',
                data: {'groupByCompany': 1, productIds:productIds.join(',')},
                context: this,
                dataType: 'json',
                success: function(response){
                    var usedCompanyIds = [];
                    if (_.isObject(response)) {
                        $.each(response, function(key, val) {
                            usedCompanyIds[val.companyId] = val.companyId;
                        });
                    }

                    var dialog = _.template(CompanyProductDialogTmpl, {
                        companies: this.companies.toJSON(),
                        companyProducts:this.companyProducts,
                        totalProducts: this.products.totalRecords,
                        products:this.products,
                        usedCompanyIds:usedCompanyIds,
                        i18n:i18n
                    });
                    $(dialog).dialog({
                        dialogClass: 'seotoaster',
                        buttons: companyProductButtons,
                        close: function (event, ui) {
                            $(this).dialog('destroy');
                            self.products.pager();
                        }
                    });

                }
            });
            return false;

        },
        quantityAction: function(products){
            var self = this,
                applyButton  = _.isUndefined(i18n['Apply']) ? 'Apply':i18n['Apply'],
                quantityButtons = {};

            quantityButtons[applyButton] = function() {
                var productQuantity = $('.custom-quantity').val();

                if($('input.infinite-param').is(':checked')){
                    $('.custom-quantity').val('');
                    $('.custom-quantity-block').hide();

                    productQuantity = '';
                }else{
                    $('.custom-quantity-block').show();
                }
                var positivNumber = Math.sign(productQuantity);

                if(positivNumber == -1) {
                    showMessage(_.isUndefined(i18n['Please enter valid number']) ? 'Please enter valid number':i18n['Please enter valid number'], true, 5000);
                    return false;
                }

                self.products.batch('PUT', {'inventory': productQuantity});
                $(this).dialog('close');
            };

            var dialog = _.template(QuantityDialogTmpl, {
                i18n:i18n
            });

            $(dialog).dialog({
                dialogClass: 'seotoaster',
                buttons: quantityButtons,
                open: function(event, ui) {
                    $('.infinite-param').on('change',  function(e){
                        e.preventDefault();

                        if($('input.infinite-param').is(':checked')){
                            $('.custom-quantity-block').hide();
                        }else{
                            $('.custom-quantity-block').show();
                        }
                    });
                }
            });
            return false;
        },
        negativeStockAction:function (products){
            var self = this;

            var applyButton  = _.isUndefined(i18n['Apply']) ? 'Apply':i18n['Apply'];
            var negativeStockButtons = {};

            negativeStockButtons[applyButton] = function() {
                var negativeStock = $(this).find($("select option:selected")).val();
                if(negativeStock == -1){
                    return false;
                }

                self.products.batch('PUT', {'negativeStock': negativeStock}, $(this).find('input[name="applyToAll"]').attr('checked') );
                $(this).dialog('close');
            };

            var dialog = _.template(NegativeStockDialogTmpl, {
                totalProducts: this.products.totalRecords,
                i18n:i18n
            });

            $(dialog).dialog({
                dialogClass: 'seotoaster',
                buttons: negativeStockButtons
            });
            return false;
        },
        promoAction:function (products){
            var selectedProducts = products,
                dialog = _.template(PromoDialogTmpl, {
                totalProducts: this.products.totalRecords,
                currencyUnit: $('input[name=currency-unit]').val(),
                i18n:i18n
            });

            $(dialog).dialog({
                dialogClass: 'seotoaster',
                width: '480',
                height: '360',
                open: function(event, ui) {
                    var promoFrom = $(document).find('#promo-from'),
                        promoDue = $(document).find('#promo-due');

                    promoFrom.datepicker({
                        dateFormat: 'd-M-yy',
                        defaultDate: "+1w",
                        changeMonth: true,
                        changeYear: true,
                        yearRange: "c-5:c+5",
                        onSelect: function(selectedDate){
                            promoDue.datepicker("option", "minDate", selectedDate);
                        }
                    });
                    promoDue.datepicker({
                        dateFormat: 'd-M-yy',
                        defaultDate: "+1w",
                        changeMonth: true,
                        changeYear: true,
                        yearRange: "c-5:c+5",
                        onSelect: function(selectedDate){
                            promoFrom.datepicker("option", "maxDate", selectedDate);
                        }
                    });

                    $('#assign-promo-dialog').on('submit',  function(e) {
                        e.preventDefault();
                        var promoPrice = $('#promo-price').val(),
                            promoFrom = $('#promo-from').val(),
                            promoDue = $('#promo-due').val(),
                            productIds  = _.pluck(selectedProducts, 'id');

                        if(promoFrom == '' || promoDue == '') {
                            return showMessage(_.isUndefined(i18n['Wrong date format'])?'Wrong date format':i18n['Wrong date format'], true, 5000);
                        }

                        if(_.isEmpty(productIds)) {
                            return showMessage(_.isUndefined(i18n['Please select product'])?'Please select product':i18n['Please select product'] , true, 5000);
                        }

                        $.ajax({
                            url: $('#website_url').val()+'plugin/promo/run/assignPromoMass/',
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                'promo-price' : promoPrice,
                                'promo-from'  : promoFrom,
                                'promo-due'   : promoDue,
                                'productIds'  : productIds,
                                'secureToken' : $('#shopping-token').val()
                            }
                        }).done(function (response) {
                            if(response.error == 1) {
                                showMessage(response.responseText, true, 5000);
                            } else {
                                showMessage(response.responseText, false, 3000);
                            }
                        });
                    });
                },
                close: function(event, ui){
                    $(this).dialog('close').remove();
                }
            });
            return false;
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
        },
        loadSuppliersCompanies: function() {
            var self = this;
            $.ajax({
                url: $('#website_url').val()+'api/store/companyproducts/',
                data: {productIds: this.products.pluck('id').join(','), 'groupByCompany':true},
                success: function(response){
                    if (_.isArray(response)){
                        var suppliersCompanies = _.groupBy(response, function(r){ return r.productId; });
                        self.products.each(function(prod){
                            prod.set('suppliersCompanies', _.isUndefined(suppliersCompanies[prod.get('id')]) ? [] : suppliersCompanies[prod.get('id')] );
                        });
                    }
                }
            });
        },
        loadCompanies: function(){
            var self = this;
            $.ajax({
                url: $('#website_url').val()+'api/store/companies/',
                data: {},
                success: function(response){
                    if (_.isArray(response)){
                        var suppliersCompanies = _.groupBy(response, function(r){ return r.id; });
                        self.products.each(function(prod){
                            prod.set('companyInfo', suppliersCompanies);
                        });
                    }
                }
            });
        }
    });

    return MainView;
});
