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
    'text!../templates/freeShipping_dialog.html'
], function(Backbone, ProductsCollection, BrandsCollection, TagsCollection, TemplatesCollection,
            ProductRowView,
            PaginatorTmpl, TaxDialogTmpl, BrandsDialogTmpl, TagsDialogTmpl, TemplateDialogTmpl, ToggleDialogTmpl, DeleteDialogTmpl, FreeShippingDialogTmpl){
    var MainView = Backbone.View.extend({
        el: $('#store-products table.products-table'),
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
            this.$el.find('#paginator').html(this.templates.paginator(this.products.info()));
            this.$el.find('tbody').empty();
            productCollection.each(this.renderProductRow, this);
        },
        renderProductRow: function(product){
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
                dialog  = _.template(TaxDialogTmpl, { totalProducts: this.products.totalRecords });
            $(dialog).dialog({
                dialogClass: 'seotoaster',
                buttons: {
                    "Apply": function(){
                        var taxClass = $(this).find('select[name=taxes]').val();
                        self.products.batch('PUT', {taxClass: taxClass}, $(this).find('input[name="applyToAll"]').attr('checked') );
                        $(this).dialog("close");
                    }
                }
            });
            return false;
        },
        brandAction: function(products){
            var self = this;
            if (!this.brands){
                this.brands = new BrandsCollection();
                this.brands.fetch({async: false});
            }
            var dialog = _.template(BrandsDialogTmpl, {
                brands: this.brands.toJSON(),
                totalProducts: this.products.totalRecords
            });
            $(dialog).dialog({
                dialogClass: 'seotoaster',
                buttons: {
                    "Apply": function(){
                        var brand = $.trim($(this).find('input[name=newbrand]').val());
                        brand = !!brand ? brand : $.trim($(this).find('select[name=brands]').val());

                        self.products.batch('PUT', {'brand': brand}, $(this).find('input[name="applyToAll"]').attr('checked') );

                        $(this).dialog('close');
                    }
                }
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
            var dialog = _.template(TagsDialogTmpl, {
                tags: this.tags.toJSON(),
                used: used,
                prodCount: products.length,
                totalProducts: this.products.totalRecords
            });
            $(dialog).on('change', 'input.partial', function(e){
                if (e.currentTarget.checked === false){
                    $(e.currentTarget).removeClass('partial').parent('label.partial').removeClass('partial');
                }
                return false;
            });
            $(dialog).dialog({
                dialogClass: 'seotoaster',
                buttons: {
                    "Apply": function(){
                        var idTags = _.pluck($(this).find('input.tags').serializeArray(),'value');
                        var newTags = [];

                        _.each(idTags, function(id){
                            newTags.push(self.tags.get(id).toJSON());
                        });

                        if (!_.isEmpty(newTags)){
                            self.products.batch('PUT', {tags: newTags}, $(this).find('input[name="applyToAll"]').attr('checked') );
                        }
                        $(this).dialog("close");
                    }
                }
            });
            return false;
        },
        deleteAction: function(products){
            var self = this,
                dialog = _.template(DeleteDialogTmpl, { totalProducts: this.products.totalRecords });
            $(dialog).dialog({
                width: 350,
                dialogClass: 'seotoaster',
                buttons: {
                    "No": function(){
                        $(this).dialog("close");
                    },
                    "Yes": function(){
                        self.products.batch('DELETE', null, $(this).find('input[name="applyToAll"]').attr('checked'));
                        $(this).dialog("close");
                    }
                }
            });
        },
        freeshippingAction:function (products){
            var self = this;

            var dialog = _.template(FreeShippingDialogTmpl, {
                totalProducts: this.products.totalRecords
            });
            $(dialog).dialog({
                dialogClass: 'seotoaster',
                buttons: {
                    "Apply": function(){
                        var freeShipping = $(this).find($("select option:selected")).val();
                        if(freeShipping == -1){
                            return false;
                        }

                        self.products.batch('PUT', {'freeShipping': freeShipping}, $(this).find('input[name="applyToAll"]').attr('checked') );
                        $(this).dialog('close');
                    }
                }
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
                showMessage('No product page templates found', true);
                return false;
            }

            var dialog = _.template(TemplateDialogTmpl, {
                templates: this.pageTemplates.toJSON(),
                totalProducts: this.products.totalRecords
            });

            $(dialog).dialog({
                width: 350,
                dialogClass: 'seotoaster',
                buttons: {
                    "Cancel": function(){
                        $(this).dialog('destroy');
                    },
                    "Apply": function(){
                        var template = $(this).find('select[name=page-tmpl]').val();
                        if (!!template){
                            self.products.batch('PUT', {pageTemplate: template}, $(this).find('input[name="applyToAll"]').attr('checked') );
                        }
                        $(this).dialog('close');
                    }
                }
            });
        },
        toggleAction: function(products){
            var self = this;
            var dialog = _.template(ToggleDialogTmpl, { totalProducts: this.products.totalRecords });
            $(dialog).dialog({
                width: 350,
                height: 140,
                dialogClass: 'seotoaster',
                buttons: {
                    "Enable": function(){
                        self.products.batch('PUT', {enabled: 1}, $(this).find('input[name="applyToAll"]').attr('checked') );
                    },
                    "Disable": function(){
                        self.products.batch('PUT', {enabled: 0}, $(this).find('input[name="applyToAll"]').attr('checked') );
                    }
                }
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