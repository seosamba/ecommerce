define(['backbone',
    '../collections/orders',
    './order',
    'text!../templates/paginator.html',
    'text!../templates/export_dialog.html',
    'text!../templates/tracking_code.html',
    'text!../templates/refund_dialog.html',
    'text!../templates/shipping_labels_dates_dialog.html',
    'text!../templates/refund_shipment_dialog.html',
    'i18n!../../../nls/'+$('input[name=system-language]').val()+'_ln',
    'moment',
    'accounting'
], function(Backbone,
        OrdersCollection, OrdersView,
        PaginatorTmpl, ExportTemplate, TrackingCodeTemplate, RefundTemplate, ShippingLabelDates, RefundShipmentTmpl, i18n, moment, accounting
    ){
    var MainView = Backbone.View.extend({
        el: $('#store-orders'),
        events: {
            'click #extra-filters-switch': function(){ $('#extra-filters', this.el).slideToggle(); } ,
            'change input.filter': 'applyFilter',
            'change #orders-check-all': 'checkAllOrders',
            'click #orders-filter-apply-btn': 'applyFilter',
            'click td.paginator a.page': 'navigate',
            'click th.sortable': 'sort',
            'click button.change-status': 'changeStatus',
            'click td.shipping-service .setTracking': 'changeTracking',
            'click .sendInvoice': 'sendInvoice',
            'click #orders-filter-reset-btn': 'resetFilter',
            'change select[name="order-mass-action"]': 'massAction',
            'change input[name="check-order[]"]': 'toggleOrder',
            'change #filter-order-type': 'toggleRecurring',
            'click .generate-shipping-order-label' : 'generateShippingLabel',
            'click .refund-shipping-order-label' : 'refundShippingLabel'
        },
        templates: {
            paginator: _.template(PaginatorTmpl)
        },
        initialize: function(){
            this.orders = new OrdersCollection;
            this.orders.ordersChecked = [];
            this.orders.server_api = _.extend(this.orders.server_api, {
                'id': function() { return $('input[name=search]').val(); },
                'filter': function() {
                    return {
                        'product-key': $('input[name=filter-product-key]', '#store-orders form.filters').val(),
                        'status': $('#filter-status', '#store-orders form.filters').val(),
                        'country': $('select[name=filter-country]', '#store-orders form.filters').val(),
                        'state': $('select[name=filter-state]', '#store-orders form.filters').val(),
                        'carrier': $('select[name=filter-carrier]', '#store-orders form.filters').val(),
                        'date-from': $('input[name=filter-from-date]', '#store-orders form.filters').val(),
                        'date-to': $('input[name=filter-to-date]', '#store-orders form.filters').val(),
                        'amount-from': $('input[name=filter-from-amount]', '#store-orders form.filters').val(),
                        'amount-to': $('input[name=filter-to-amount]', '#store-orders form.filters').val(),
                        'user': $('input[name=user-name]', '#store-orders form.filters').val(),
                        'filter-order-type': $('select[name=filter-order-type]', '#store-orders form.filters').val(),
                        'filter-recurring-order-type': $('select[name=filter-recurring-order-type]', '#store-orders form.filters').val(),
                        'filter-by-coupon': $('input[name=filter-by-coupon-code]', '#store-orders form.filters').val()
                    };
                }
            });
            this.orders.on('reset', this.renderOrders, this);
            this.orders.pager();
        },
        massAction: function(e){
            var func = $(e.currentTarget).val()+'Action';

            if (_.isFunction(this[func])){
                var orders = this.orders;
                if (orders.length){
                    var self = this;
                    this[func].call(self, orders);
                }
            }
            $(e.currentTarget).val(0);
        },
        refundShippingLabel: function(e)
        {
            e.preventDefault();

            var orderId = $(e.currentTarget).data('order-id'),
                model = this.orders.get(orderId),
                self = this;

            $.ajax({
                url: $('#website_url').val()+'plugin/shopping/run/getRefundShipmentScreenInfo/',
                type: 'POST',
                dataType: 'json',
                data: {'orderId': orderId, 'secureToken': $('.orders-secure-token').val()}
            }).done(function(response){
                console.log(response);
                if (response.error == '1') {
                    showMessage(response.responseText, true, 5000);
                } else {
                    var shipmentRefundButton  = _.isUndefined(i18n['Refund']) ? 'Refund':i18n['Refund'],
                        assignShipmentRefundButtons = {};

                        assignShipmentRefundButtons[shipmentRefundButton] = function() {
                            $('.ui-button').css('zIndex',"101");
                        };

                    var dialog = _.template(RefundShipmentTmpl, {
                            orderId: orderId,
                            i18n:i18n,
                            accounting: accounting,
                            moneyFormat: self.orders.moneyFormat,
                            shippingTaxRate : self.orders.shippingTaxRate,
                            defaultTaxes: self.orders.defaultTaxes,
                            order: self.orders.get(orderId),
                            shipmentRefundButtonStatus: response.responseText.shipment_refund_button_status,
                            shipmentRefundScreenDescription: response.responseText.shipment_refund_screen_description
                        }),
                        availabilityMonths = [],
                        availableDateAndTime = [];


                    $(dialog).dialog({
                        dialogClass: 'seotoaster',
                        width: '50%',
                        resizable: false,
                        buttons: assignShipmentRefundButtons,
                        open: function (event, ui) {
                            if (response.responseText.shipment_refund_button_status === false) {
                                $(".ui-dialog-buttonset").remove();
                            }
                        },
                        close: function (event, ui) {
                            $(this).dialog('destroy');
                        }
                    });


                }
            });
        },
        generateShippingLabel: function(e)
        {
           var confirmMessage = _.isUndefined(i18n['Do you want to specify shipment date?'])?'Do you want to specify shipment date?':i18n['Do you want to specify shipment date?'],
               confirmMessageLabel = _.isUndefined(i18n['Do you want to create a label?'])?'Do you want to create a label?':i18n['Do you want to create a label?'],
               confirmMessageAvailabilityDate = _.isUndefined(i18n['Do you want to use this date for the shipping label?'])?'Do you want to use this date for the shipping label?':i18n['Do you want to use this date for the shipping label?'],
               orderId = $(e.currentTarget).data('order-id'),
               self = this,
               model = this.orders.get(orderId),
               assignAvailabilityDatesButtons = {},
               availabilityButton  = _.isUndefined(i18n['Create label']) ? 'Create label':i18n['Create label'],
               elRow = $(e.currentTarget).closest('tr');

            assignAvailabilityDatesButtons[availabilityButton] = function() {
                $('.ui-dialog').css('zIndex',"101");
                var availabilityDate = $('#shipment-availability-result-'+orderId).data('availability-date'),
                    availabilityTime = $('#shipment-availability-result-'+orderId).data('availability-time');

                if (!$('#shipment-availability-result-'+orderId).data('availability-date')) {
                    showMessage(_.isUndefined(i18n['Please specify shipment date'])?'Please specify shipment date':i18n['Please specify shipment date'], true, 5000);
                    return false;
                }

                if (!$('#shipment-availability-result-'+orderId).data('availability-time')) {
                    showMessage(_.isUndefined(i18n['Please specify shipment time'])?'Please specify shipment time':i18n['Please specify shipment time'], true, 5000);
                    return false;
                }
                smoke.confirm(confirmMessageAvailabilityDate, function (e) {
                    if (e) {
                        self.generateShippingLabelRequest(orderId, availabilityDate, availabilityTime, elRow);
                    }
                }, {
                    ok: _.isUndefined(i18n['Yes']) ? 'Yes' : i18n['Yes'],
                    cancel: _.isUndefined(i18n['No']) ? 'No' : i18n['No']
                });

            };

           smoke.confirm(confirmMessageLabel, function (e) {
               if(e) {
                   var shippingAvailabilityDays = JSON.parse(model.get('shipping_availability_days'));

                    if (_.isEmpty(shippingAvailabilityDays) || _.isNull(shippingAvailabilityDays)) {
                        showMessage(_.isUndefined(i18n['Shipping service does not support shipment label creation'])?'Shipping service does not support shipment label creation':i18n['Shipping service does not support shipment label creation'], true, 5000);
                        return false;
                    }

                   var dialog = _.template(ShippingLabelDates, {
                        orderId: orderId,
                        i18n:i18n,
                        shippingAvailabilityDays: shippingAvailabilityDays,
                        accounting: accounting,
                        moneyFormat: self.orders.moneyFormat,
                        shippingTaxRate : self.orders.shippingTaxRate,
                        defaultTaxes: self.orders.defaultTaxes,
                        order: self.orders.get(orderId)
                   }),
                       availabilityMonths = [],
                       availableDateAndTime = [];

                   _.each(shippingAvailabilityDays.availabilityDates, function(time, date){
                       if (typeof availabilityMonths[moment(date, 'YYYY-MM-DD').format("MM")] === 'undefined') {
                           availabilityMonths[moment(date, 'YYYY-MM-DD').format("MM")] = [parseInt(moment(date, 'YYYY-MM-DD').format("D"))];
                       } else {
                           availabilityMonths[moment(date, 'YYYY-MM-DD').format("MM")].push(parseInt(moment(date, 'YYYY-MM-DD').format("D")));
                       }
                       if (typeof availableDateAndTime[date] === 'undefined') {
                           availableDateAndTime[date] = [time];
                       } else {
                           availableDateAndTime[date].push(time);
                       }
                   });

                   $(dialog).dialog({
                        dialogClass: 'seotoaster',
                        width: '75%',
                        height: '400',
                        resizable: false,
                        buttons: assignAvailabilityDatesButtons,
                        open: function (event, ui) {
                            $('#availability-days-datepicker').datepicker({
                                beforeShowDay: function (date) {
                                    if (typeof availabilityMonths[date.getMonth() + 1] === 'undefined') {
                                        return [false, ''];
                                    }
                                    if (_.contains(availabilityMonths[date.getMonth() + 1], parseInt(date.getDate()))) {
                                        return [true, ''];
                                    }
                                    return [false, ''];
                                },
                                onSelect: function () {
                                    if (typeof availableDateAndTime[$.datepicker.formatDate("yy-mm-dd", $(this).datepicker('getDate'))] !== 'undefined') {
                                        $('#availability-shipment-time-'+orderId).empty();
                                        $('#shipment-availability-summary-'+orderId).find('.shipment-availability-date-summary').empty().text($.datepicker.formatDate("dd M yy", $(this).datepicker('getDate')));
                                        $('#shipment-availability-summary-'+orderId).find('.shipment-availability-time-summary').empty().text('');
                                        $('#shipment-availability-result-'+orderId).data('availability-date', $.datepicker.formatDate("yy-mm-dd", $(this).datepicker('getDate'))).data('availability-time', '');
                                        _.each(availableDateAndTime[$.datepicker.formatDate("yy-mm-dd", $(this).datepicker('getDate'))], function(time, date) {
                                            _.each(time, function(time){
                                                $('#availability-shipment-time-'+orderId).append('<button class="availability-shipment-time btn">'+time+'</button>');
                                            });
                                        });
                                    }
                                }
                            });

                            $('#availability-shipment-time-'+orderId).on('click', '.availability-shipment-time', function(e){
                                var el = $(e.currentTarget),
                                    switchTimeBlock = el.closest('div');

                                switchTimeBlock.find('.availability-shipment-time').removeClass('current');
                                el.addClass('current');
                                $('#shipment-availability-summary-'+orderId).find('.shipment-availability-time-summary').empty().text(el.text());
                                $('#shipment-availability-result-'+orderId).data('availability-time', el.text());
                            });
                        },
                        close: function (event, ui) {
                            $(this).dialog('destroy');
                        }
                   });

                   checkboxRadioStyle();
                   return false;
               }
           });

        },
        generateShippingLabelRequest: function(orderId, availabilityDate, availabilityTime, elRow, regenerate)
        {
            var self = this,
                model = self.orders.get(orderId);
            $.ajax({
                url: $('#website_url').val()+'plugin/shopping/run/shippingLabel/',
                type: 'POST',
                dataType: 'json',
                data: {'orderId': orderId, 'secureToken': $('.orders-secure-token').val(), 'availabilityDate': availabilityDate, availabilityTime: availabilityTime, 'regenerate' : regenerate}
            }).done(function(response) {
                if (response.error == '1') {
                    if (typeof response.responseText.regenerate !== 'undefined') {
                        smoke.confirm(response.responseText.message, function (e) {
                            if (e) {
                                self.generateShippingLabelRequest(orderId, availabilityDate, availabilityTime, elRow, true);
                            }
                        }, {
                            ok: _.isUndefined(i18n['Yes']) ? 'Yes' : i18n['Yes'],
                            cancel: _.isUndefined(i18n['No']) ? 'No' : i18n['No']
                        });
                    } else {
                        showMessage(response.responseText, true, 5000);
                    }
                } else {
                    showMessage(response.responseText.message, false, 5000);
                    if (response.responseText.shipping_label_link) {
                        elRow.find('.shipping-label-link').removeClass('hidden').val(response.responseText.shipping_label_link);
                        model.set({
                            'shipping_label_link': response.responseText.shipping_label_link
                        });

                    }
                    $('.ui-dialog-titlebar-close').trigger('click');
                }
            });
        },
        checkAllOrders: function(e) {
            var ordersIds = this.orders.ordersChecked;
            var orderIdsExclude = [];
            if(e.target.checked){
                this.orders.each(function(order){
                    ordersIds = _.union(ordersIds, [order.id]);
                    order.set({checked: true});
                });
            }else{
                this.orders.each(function(order){
                    orderIdsExclude = _.union(orderIdsExclude, [order.id]);
                    order.set({checked: false});
                });
                ordersIds = _.difference(ordersIds, orderIdsExclude);
            }
            this.orders.ordersChecked = ordersIds;
            if (typeof _checkboxRadio === "function")  {
                _checkboxRadio();
            }
        },
        toggleOrder: function(e) {
            var orderId = $(e.target).val();
            if(e.target.checked){
                this.orders.ordersChecked = _.union(this.orders.ordersChecked, [orderId]);
            }else{
                var filtered = _.filter(this.orders.ordersChecked, function(item) {
                    return item !== orderId
                });
                this.orders.ordersChecked = filtered;
            }
            if (typeof _checkboxRadio === "function")  {
                _checkboxRadio();
            }
        },
        exportOrdersAction: function(){
            var orders = this.orders.ordersChecked.join(',');
            var filters = this.orders.server_api.filter();
            var exportOrderButton  = _.isUndefined(i18n['Export']) ? 'Export':i18n['Export'];
            var exportOrderButtons = {};
            exportOrderButtons[exportOrderButton] = function() {
                $(this).dialog('close');
            };
            $.ajax({
                url: $('#website_url').val()+'plugin/shopping/run/getOrderExportConfig/',
                type: 'GET',
                dataType: 'json'

            }).done(function(response) {
                var dialog = _.template(ExportTemplate, {
                    ordersIds: orders,
                    filters: $.param(filters),
                    i18n:i18n,
                    'defaultConfig': response.responseText.defaultConfig,
                    'exportConfig': response.responseText.export_config
                });
                $(dialog).dialog({
                    dialogClass: 'seotoaster',
                    width: '75%',
                    height: '750',
                    resizable: false
                });
                return false;
            });
        },
        resetFilters: function(){
            this.$('form.filters > :input').val('');
        },
        render: function(){},
        renderOrder: function(order){
            order.set({useInvoice: $('#invoiceEnable').val()});
            order.set({i18n: i18n});
            var view = new OrdersView({model: order});
            this.$('#orders-list').append(view.render().el);
        },
        renderOrders: function(){
            this.$('#orders-list').empty();
            this.orders.each(this.renderOrder.bind(this));
            this.orders.info()['i18n'] = i18n;
            this.$('td.paginator').html(this.templates.paginator(this.orders.information));
        },
        applyFilter: function(e) {
            if(typeof e !== 'undefined'){
                e.preventDefault();
            }
            this.orders.ordersChecked = [];
            this.orders.currentPage = 0;
            this.orders.pager();
        },
        resetFilter: function(e){
            e.preventDefault();
            var $form = $(e.currentTarget).closest('form');
            $form.find('input:text').val('').end()
                 .find('select.filter').val('0').trigger('chosen:updated');
            this.applyFilter();
        },
        navigate: function(e){
            e.preventDefault();

            var page = $(e.currentTarget).data('page');
            if ($.isNumeric(page)){
                this.orders.goTo(page);
            } else {
                switch(page){
                    case 'first':
                        this.orders.goTo(this.orders.firstPage);
                        break;
                    case 'last':
                        this.orders.goTo(this.orders.totalPages);
                        break;
                    case 'prev':
                        this.orders.requestPreviousPage();
                        break;
                    case 'next':
                        this.orders.requestNextPage();
                        break;
                }
            }
        },
        sort: function(e){
            var $el = $(e.currentTarget),
                key = $el.data('sortkey');

            $el.siblings('.sortable').removeClass('sortUp').removeClass('sortDown');

            if (!!key) {
                if (!$el.hasClass('sortUp') && !$el.hasClass('sortDown')){
                    $el.addClass('sortUp');
                    key += ' ASC';
                } else {
                    if ($el.hasClass('sortUp')){
                        key += ' DESC';
                    }
                    if ($el.hasClass('sortDown')){
                        key += ' ASC';
                    }
                    $el.toggleClass('sortUp').toggleClass('sortDown');
                }
                this.orders.server_api.order = key;
                this.orders.pager();
            }
        },
        changeStatus: function(event){
            var self        = this,
                el          = $(event.currentTarget),
                id          = parseInt(el.closest('div').data('order-id')),
                confirmMessage = _.isUndefined(i18n['Are you sure you want to change status for this order?'])?'Are you sure you want to change status for this order?':i18n['Are you sure you want to change status for this order?'],
                status = el.data('status');

            var model = this.orders.get(id);

            if (status === 'refunded') {
                confirmMessage = _.isUndefined(i18n['Are you sure you want to refund this payment?'])?'Are you sure you want to refund this payment?':i18n['Are you sure you want to refund this payment?'];

                var refundButton  = _.isUndefined(i18n['Refund']) ? 'Refund':i18n['Refund'],
                    assignRefundButtons = {},
                    dialog = _.template(RefundTemplate, {
                        i18n:i18n,
                        orderId: id,
                        gateway: model.get('gateway'),
                        total: model.get('total')
                    });

                assignRefundButtons[refundButton] = function() {
                    $('.ui-dialog').css('zIndex',"101");
                    smoke.confirm(confirmMessage, function (e) {
                        if (e) {
                            $.ajax({
                                url: $('#website_url').val() + 'plugin/shopping/run/refundPayment/orderId/',
                                type: 'POST',
                                dataType: 'json',
                                data: {
                                    'orderId': $('#refund-order-id').val(),
                                    'refundAmount': $('.partial-refund-amount').val(),
                                    'refundInfo': $('.refund-info').val(),
                                    'secureToken': $('.orders-secure-token').val(),
                                    'paymentGateway': model.get('gateway'),
                                    'refundUsingPaymentGateway': $('input.use-refund-payment-gateway').is(':checked') ? 1 : 0,
                                    'refundTax': $('.refund-tax').val()
                                },
                                success: function (response) {
                                    if (response.error === 1) {
                                        showMessage(response.responseText, true, 5000);
                                    } else {
                                        showMessage(response.responseText.message, false, 5000);
                                        model.set('status', status);
                                        model.set('total', response.responseText.total);
                                        $('.ui-dialog-titlebar-close').trigger('click');
                                    }
                                }
                            });

                        }

                    }, {
                        ok: _.isUndefined(i18n['Yes']) ? 'Yes' : i18n['Yes'],
                        cancel: _.isUndefined(i18n['No']) ? 'No' : i18n['No']
                    });

                };

                $(dialog).dialog({
                    dialogClass: 'seotoaster',
                    width: '35%',
                    height: '450',
                    buttons: assignRefundButtons,
                    resizable: false,
                    open: function (event, ui) {

                    },
                    close: function (event, ui) {
                        $(this).dialog('destroy');
                    }
                });

            } else {
                smoke.confirm(confirmMessage, function (e) {
                    if (e) {
                        $.ajax({
                            url: $('#website_url').val() + 'plugin/shopping/run/order?id=' + id,
                            data: {status: status},
                            type: 'POST',
                            dataType: 'json',
                            beforeSend: function () {
                                el.closest('td').html('<img src="' + $('#website_url').val() + 'system/images/ajax-loader-small.gif" style="margin: 20px auto; display: block;">');
                            },
                            success: function (response) {
                                showMessage(_.isUndefined(i18n['Saved']) ? 'Saved' : i18n['Saved'], response.hasOwnProperty('error') && response.error);
                                if (!response.error && response.hasOwnProperty('responseText')) {
                                    model.set('status', response.responseText.status);
                                }
                            }
                        });
                    }
                }, {
                    ok: _.isUndefined(i18n['Yes']) ? 'Yes' : i18n['Yes'],
                    cancel: _.isUndefined(i18n['No']) ? 'No' : i18n['No']
                });
            }
        },
        changeTracking: function(e){
            var self    = this,
                el      = $(e.currentTarget),
                id      = parseInt(el.closest('tr').find('td.order-id').text());
            var model = this.orders.get(id);

            $.ajax({
                url: $('#website_url').val()+'plugin/shopping/run/fetchShippingUrlNames',
                type: 'GET',
                dataType: 'json'

            }).done(function(response) {
                var dialog = _.template(TrackingCodeTemplate, {
                    data:response.responseText.data,
                    defaultSelection: response.responseText.defaultSelection,
                    orderId: id,
                    i18n:i18n
                });

                $(dialog).dialog({
                    width: 600,
                    dialogClass: 'seotoaster',
                    resizable:false,
                    open: function(event, ui) {
                        $('.save-data').on('click', function(e){
                            e.preventDefault();
                            var  trackingUrlId =  $('#marketing-services').val(),
                                text =  $('#shippingTrackingId').val(),
                                data = {
                                    trackingUrlId: trackingUrlId,
                                    shippingTrackingId: text,
                                    id:id
                                };

                            $.ajax({
                                url: $('#website_url').val()+'plugin/shopping/run/order',
                                data: data,
                                type: 'POST',
                                dataType: 'json',
                                beforeSend: function(){
                                    el.closest('td').html('<img src="'+$('#website_url').val()+'system/images/ajax-loader-small.gif" style="margin: 20px auto; display: block;">');
                                },
                                success: function(response) {
                                    if (response.hasOwnProperty('error') && !response.error){
                                        showMessage(_.isUndefined(i18n['Saved'])?'Saved':i18n['Saved']);
                                    }
                                    if (response.hasOwnProperty('responseText')){
                                        model.set({
                                            'status': response.responseText.status,
                                            'shipping_tracking_id': response.responseText.shippingTrackingId
                                        });
                                    }
                                }
                            });
                        });

                    },
                    close: function(event, ui){
                        $(this).dialog('close').remove();
                    }
                });
            });
        },
        sendInvoice: function(event){
            var el = $(event.currentTarget),
                id = parseInt(el.closest('tr').find('td.order-id').text()),
                tdElement  = el.closest('td'),
                tdContent =  tdElement.html();
            $.ajax({
                url: $('#website_url').val()+'plugin/invoicetopdf/run/sendInvoiceToUser/',
                data: {
                    'cartId': id,
                    'dwn': 0
                },
                type: 'POST',
                dataType: 'json',
                beforeSend: function(){
                    tdElement.html('<img src="'+$('#website_url').val()+'system/images/ajax-loader-small.gif" style="margin: 20px auto; display: block;">');
                },
                success: function(response) {
                    if (response.hasOwnProperty('error')) {
                        if (!response.error) {
                            showMessage(_.isUndefined(i18n['Invoice has been sent']) ? 'Invoice has been sent' : i18n['Invoice has been sent']);
                        } else {
                            showMessage(response.responseText, false, 5000);
                        }
                    }
                    tdElement.html(tdContent);
                }
            });
        },
        toggleRecurring : function(e){
            var currentType = $(e.currentTarget).val();
            if (currentType === 'recurring_id') {
                $('.recurring-filters').removeClass('hidden');
            } else {
                $('.recurring-filters').addClass('hidden');
            }
        }
    });

    return MainView;
});