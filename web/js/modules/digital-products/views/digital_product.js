define([
    'backbone',
    '../collections/digital-product',
    'text!../templates/paginator.html',
    'i18n!../../../nls/'+$('input[name=system-language]').val()+'_ln'
], function (Backbone,
             DigitalProductCollection,
             PaginatorTmpl, i18n) {

    var DigitalProductView = Backbone.View.extend({
        el: $('#digital-products-block'),
        events: {
            'click a[data-digital-file=delete]': 'deleteDigitalProduct',
            'keyup .digital-product-data': 'changeAttributes',
            'click td.digital-product-paginator a.page': 'navigate'
        },
        templates: {
            paginator: _.template(PaginatorTmpl)
        },
        initialize: function (options) {

            this.digitalProducts = new DigitalProductCollection();

            this.digitalProducts.on('reset', this.renderDigitalProducts, this);
            this.digitalProducts.on('add', this.renderDigitalProducts, this);
            this.digitalProducts.on('destroy', this.renderDigitalProducts, this);
        },
        render: function () {
            this.digitalProducts.pager();
        },
        renderDigitalProducts: function () {
            this.$el.find('tbody').empty();
            this.digitalProducts.each(this.renderDigitalProduct, this);
            this.digitalProducts.info()['i18n'] = i18n;
            this.$('td.digital-product-paginator').html(this.templates.paginator(this.digitalProducts.information));
            var self = this;
            $('.start-date').datepicker({
                dateFormat: 'dd-M-yy',
                changeMonth: true,
                changeYear: true,
                yearRange: "c-5:c+5",
                onSelect: function () {
                    var newStartDate = $(this).val(),
                        id = $(this).closest('.digital-product-row').data('digital-product-id'),
                        currentModel = self.digitalProducts.get(id);

                        currentModel.set('start_date', newStartDate);
                        currentModel.save({});
                }
            });

            $('.end-date').datepicker({
                dateFormat: 'dd-M-yy',
                changeMonth: true,
                changeYear: true,
                yearRange: "c-5:c+5",
                minDate: 0,
                onSelect: function () {
                    var newStartDate = $(this).val(),
                        id = $(this).closest('.digital-product-row').data('digital-product-id'),
                        currentModel = self.digitalProducts.get(id);

                    currentModel.set('end_date', newStartDate);
                    currentModel.save({});
                }
            });
        },
        renderDigitalProduct: function (digitalProduct) {
            var endDate = digitalProduct.get('end_date'),
                downloadLimit = digitalProduct.get('download_limit');
            if (downloadLimit === '0') {
                downloadLimit = '';
            }
            if (endDate === '2038-01-19 00:00:00') {
                endDate = ''
            } else {
                endDate = $.datepicker.formatDate('dd-M-yy', new Date(endDate));
            }

            this.$el.find('tbody').append(
                '<tr class="digital-product-row" data-digital-product-id="'+ digitalProduct.get('id')+'"><td><input class="start-date digital-product-data" data-attribute-name="start_date" type="text" name="start-date" value="'+ $.datepicker.formatDate("dd-M-yy", new Date(digitalProduct.get("start_date")))+'" /></td>' +
                '<td><input data-attribute-name="end_date" class="text-center end-date digital-product-data" type="text" name="end-date" placeholder="∞" value="'+ endDate +'" /></td>' +
                '<td><input class="download-limit text-center digital-product-data" data-attribute-name="download_limit"  placeholder="∞"  type="text" name="download-limit" value="'+downloadLimit+'" /></td>' +
                '<td><input class="display-file-name text-center digital-product-data" data-attribute-name="display_file_name" type="text" name="display-file-name" value="'+digitalProduct.get('display_file_name')+'" /></td>' +
                '<td><a class="ticon-remove error grid_1 text-center" data-digital-file="delete" data-cid="'+ digitalProduct.get('id')+'" href="javascript:;"></a></td>'+
                '</tr>'
            );
        },
        deleteDigitalProduct: function (e) {
            var self = this,
                cid = $(e.currentTarget).data('cid'),
                productId = self.digitalProducts.get(cid).get('product_id');

            showConfirm('Do you want delete file?', function() {
                $.post($('#website_url').val() + 'plugin/shopping/run/checkDigitalProductUsage/', {'productId': productId}, function(response){
                    if(response.responseText.productSold) {
                        showConfirm(_.isUndefined(i18n['This product was sold. Do you really want to delete this file?'])?'This product was sold. Do you really want to delete this file?':i18n['This product was sold. Do you really want to delete this file?'], function () {
                            self.digitalProducts.get(cid).destroy({
                                success: function (model, response) {
                                    showMessage(response.message);
                                }
                            });
                        });
                    } else {
                        self.digitalProducts.get(cid).destroy({
                            success: function (model, response) {
                                showMessage(response.message);
                            }
                        });
                    }
                }, 'json');
            });
        },
        changeAttributes: function(e) {
            var target = $(e.currentTarget),
                id = target.closest('.digital-product-row').data('digital-product-id'),
                attributeName = target.data('attribute-name'),
                attributeValue = target.val(),
                currentModel = this.digitalProducts.get(id);

            currentModel.set(attributeName, attributeValue);
            currentModel.save({});
        },
        navigate: function(e){
            e.preventDefault();

            var page = $(e.currentTarget).data('page');
            if ($.isNumeric(page)){
                this.digitalProducts.goTo(page);
            } else {
                switch(page){
                    case 'first':
                        this.digitalProducts.goTo(this.digitalProducts.firstPage);
                        break;
                    case 'last':
                        this.digitalProducts.goTo(this.digitalProducts.totalPages);
                        break;
                    case 'prev':
                        this.digitalProducts.requestPreviousPage();
                        break;
                    case 'next':
                        this.digitalProducts.requestNextPage();
                        break;
                }
            }
        }
    });

    return DigitalProductView;
});