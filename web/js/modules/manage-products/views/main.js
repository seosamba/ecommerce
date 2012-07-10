define([
	'Underscore',
	'Backbone',
    'modules/manage-products/collections/productlist',
    'modules/product/collections/brands',
    'modules/product/collections/tags',
    'modules/common/collections/templates',
    'modules/manage-products/views/productrow',
    'text!modules/manage-products/templates/tax_dialog.html',
    'text!modules/manage-products/templates/brands_dialog.html',
    'text!modules/manage-products/templates/tags_dialog.html',
    'text!modules/manage-products/templates/template_dialog.html'
], function(_, Backbone, ProductsCollection, BrandsCollection, TagsCollection, TemplatesCollection,
            ProductRowView,
            TaxDialogTmpl, BrandsDialogTmpl, TagsDialogTmpl, TemplateDialogTmpl){
    var MainView = Backbone.View.extend({
        el: $('#manage-products table.products-table'),
        events: {
            'change select[name="product-mass-action"]': 'massAction',
            'click tfoot a.table-nav': 'navigate'
        },
        initialize: function(){
            this.products = new ProductsCollection();
            this.products.on('reset', this.renderProducts, this);
            this.products.pager();
            $.extend($.ui.dialog.prototype.options, {
                modal: true,
                resizable: false,
                draggable: false,
                minWidth: 300,
                close: function(){
                    $(this).remove();
                }
            });
        },
        renderProducts: function(productCollection){
            this.$el.find('a.table-nav').hide();
            if (this.products.currentPage < this.products.totalPages){
                this.$el.find('a.table-nav[data-dir="next"]').show();
            }
            if (this.products.currentPage > this.products.firstPage){
                this.$el.find('a.table-nav[data-dir="prev"]').show();
            }
            this.$el.find('tbody').empty();
            productCollection.each(this.renderProductRow, this);
        },
        renderProductRow: function(product){
            var view = new ProductRowView({model: product});
            this.$el.find('tbody').append(view.render().$el);
        },
        navigate: function(e){
            switch($(e.currentTarget).data('dir')){
                case 'prev':
                    this.products.requestPreviousPage();
                    break;
                case 'next':
                    this.products.requestNextPage();
                    break;
            }
            return false;
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
            var dialog = $(TaxDialogTmpl);
            dialog.dialog({
                dialogClass: 'seotoaster',
                buttons: {
                    "Apply": function(){
                        var taxClass = $(this).find('select[name=taxes]').val();
                        _.each(products, function(prod){
                            prod.set('taxClass', taxClass).save();
                        });
                        $(this).dialog("close");
                    }
                }
            });
            return false;
        },
        brandAction: function(products){
            if (!this.brands){
                this.brands = new BrandsCollection();
                this.brands.fetch({async: false});
            }
            var dialog = $.tmpl(BrandsDialogTmpl, {brands: this.brands.toJSON()});
            dialog.dialog({
                dialogClass: 'seotoaster',
                buttons: {
                    "Apply": function(){
                        var brand = $.trim($(this).find('input[name=newbrand]').val());
                        brand = !!brand ? brand : $.trim($(this).find('select[name=brands]').val());

                        _.each(products, function(prod){
                            prod.set('brand', brand).save();
                        });

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
            var dialog = $.tmpl(TagsDialogTmpl, {tags: this.tags.toJSON(), used: used, prodCount: products.length});
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
                            _.each(products, function(prod){
                               prod.set('tags', newTags);
                            });
                        }
                        $(this).dialog("close");
                    }
                }
            });
            return false;
        },
        deleteAction: function(products){
            var self = this,
                url = $('#website_url').val()+this.products.urlOriginal;
            if (showConfirm('Are you sure?<br>This operation cannot be undone', function(){
                var ids = _.pluck(products, 'id');
                $.ajax({
                    url: url+ids.join('/'),
                    type: 'DELETE',
                    dataType: 'json',
                    success: function(response){
                        var deleted = _.reject(ids, function(id) {
                           return response[id] === true;
                        });
                        console.log(deleted);
                       //self.products.remove();
                    }
                });
            }));
        },
        templateAction: function(products){
            var self = this;

            if (!this.templates){
                this.templates = new TemplatesCollection();
                this.templates.fetch({async: false, data: {filter : 'typeproduct' }});
            }

            var dialog = $.tmpl(TemplateDialogTmpl, {templates: this.templates.toJSON()});
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
                            _.each(products, function(prod){
                                prod.set('pageTemplate', template).save();
                            });
                        }
                        $(this).dialog('close');
                    }
                }
            });
        },
        toggleAction: function(products){
            var self = this;
            var dialog = $('<div>@TODO Some nice and long explanation of this feature</div>'),
                callback = function(status){
                    var ids = _.pluck(products, 'id');
                    $.ajax({
                        url: $('#website_url').val()+'storeapi/v1/product/id/'+ids.join(',')+'/',
                        type: 'PUT',
                        data: JSON.stringify({enabled: status}),
                        dataType: 'json',
                        success: function(response){
                            console.log(response);
                        }
                    });
                }
            dialog.dialog({
                width: 350,
                dialogClass: 'seotoaster',
                buttons: {
                    "Enable all": function(){ callback(1); },
                    "Disable all": function(){ callback(0); }
                }
            })
        }
    });

    return MainView;
});