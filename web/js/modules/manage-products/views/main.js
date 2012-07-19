define([
	'Underscore',
	'Backbone',
    'modules/manage-products/collections/productlist',
    'modules/product/collections/brands',
    'modules/product/collections/tags',
    'modules/common/collections/templates',
    'modules/manage-products/views/productrow',
    'text!modules/manage-products/templates/paginator.html',
    'text!modules/manage-products/templates/tax_dialog.html',
    'text!modules/manage-products/templates/brands_dialog.html',
    'text!modules/manage-products/templates/tags_dialog.html',
    'text!modules/manage-products/templates/template_dialog.html',
    'text!modules/manage-products/templates/toggle_dialog.html',
    'text!modules/manage-products/templates/delete_dialog.html'
], function(_, Backbone, ProductsCollection, BrandsCollection, TagsCollection, TemplatesCollection,
            ProductRowView,
            PaginatorTmpl, TaxDialogTmpl, BrandsDialogTmpl, TagsDialogTmpl, TemplateDialogTmpl, ToggleDialogTmpl, DeleteDialogTmpl){
    var MainView = Backbone.View.extend({
        el: $('#manage-products table.products-table'),
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
            this.products.pager();
            $.extend($.ui.dialog.prototype.options, {
                modal: true,
                resizable: false,
//                draggable: false,
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
                dialog  = $.tmpl(TaxDialogTmpl, {});
            dialog.dialog({
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
            var dialog = $.tmpl(BrandsDialogTmpl, {
                brands: this.brands.toJSON()
            });
            dialog.dialog({
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
            var dialog = $.tmpl(TagsDialogTmpl, {
                tags: this.tags.toJSON(),
                used: used,
                prodCount: products.length
            });
            dialog.on('change', 'input.partial', function(e){
                if (e.currentTarget.checked === false){
                    $(e.currentTarget).removeClass('partial').parent('label.partial').removeClass('partial');
                }
                return false;
            });
            dialog.dialog({
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
                dialog = $.tmpl(DeleteDialogTmpl, {});
            dialog.dialog({
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

            var dialog = $.tmpl(TemplateDialogTmpl, {templates: this.pageTemplates.toJSON()});
            dialog.dialog({
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
            var dialog = $.tmpl(ToggleDialogTmpl, {});
            dialog.dialog({
                width: 350,
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
        }
    });

    return MainView;
});