/**
 * .
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
define([
    'backbone',
    'text!../templates/order.html',
    'i18n!../../../nls/'+$('input[name=system-language]').val()+'_ln',
    'accounting'
], function(Backbone, OrderTmpl, i18n, accounting){

    var OrderView = Backbone.View.extend({
        tagName: 'tr',
        template: _.template(OrderTmpl),
        events: {
            'mouseenter td.status-change': 'statusChange',
            'mouseleave td.status-change': 'statusChange',
            'click a.go-to-client': 'goToClient'
        },
        initialize: function(){
            this.model.on('change', this.render, this);
        },
        render: function(){
            this.model.set('accounting', accounting);
            this.model.set('moneyFormat', this.model.collection.moneyFormat);

            this.$el.html(this.template(this.model.toJSON()));
            return this;
        },
        statusChange: function(event) {
            var el = $(event.currentTarget),
                recurringId = this.model.get('recurring_id');
            if (recurringId === null) {
                if (event.type === "mouseleave") {
                    var status = this.model.get('status');
                    var translatedGateway = this.model.get('gateway');
                    var translatedStatus = 'cs_' + status;

                    if (translatedGateway == 'Quote' && status == 'pending') {
                        translatedStatus = 'New quote';
                    }

                    if (translatedGateway == 'Quote' && status == 'processing') {
                        translatedStatus = 'Quote Sent';
                    }

                    if (translatedGateway == 'Quote' && status == 'not_verified') {
                        translatedStatus = 'Quote Signed (Signature only quote)';
                    }

                    if (translatedGateway == 'Quote' && status == 'canceled') {
                        translatedStatus = 'Lost opportunity';
                    }

                    if (typeof i18n['' + translatedStatus + ''] !== "undefined") {
                        translatedStatus = i18n['' + translatedStatus + ''];
                    }
                    el.text(translatedStatus);
                    return true;
                }

                var refundPaymentTranslation = _.isUndefined(i18n['Refund payment']) ? 'Refund payment' : i18n['Refund payment'];
                var sendRequestPaymentTranslation = _.isUndefined(i18n['Send payment request']) ? 'Send payment request' : i18n['Send payment request'];
                var paidPaymentTranslation = _.isUndefined(i18n['Mark order paid']) ? 'Mark order paid' : i18n['Mark order paid'];
                var cancelOrderTranslation = _.isUndefined(i18n['Cancel order']) ? 'Cancel order' : i18n['Cancel order'];
                var deliveredTranslation = _.isUndefined(i18n['Delivered']) ? 'Delivered' : i18n['Delivered'];

                var buttons = {
                    'refunded': '<button class="change-status btn small blue-gradient" data-status="refunded" >' + refundPaymentTranslation + '</button>',
                    'completed': '<button class="change-status btn small green-gradient" data-status="completed" >' + paidPaymentTranslation + '</button>',
                    'canceled': '<button class="change-status btn small red-gradient" data-status="canceled" >' + cancelOrderTranslation + '</button>',
                    'delivered': '<button class="change-status btn small orange-gradient" data-status="delivered" >' + deliveredTranslation + '</button>',
                    'partial': '<button class="change-status btn small orange-gradient" data-status="partial" >' + sendRequestPaymentTranslation + '</button><button class="change-status btn small orange-gradient" data-status="partial" data-sub-status="completed">' + paidPaymentTranslation + '</button>'
                };

                var html = '';
                switch (this.model.get('status')) {
                    case 'completed':
                        html += buttons['refunded'];
                        break;
                    case 'pending':
                        html += buttons['completed'];
                        html += buttons['canceled'];
                        break;
                    case 'not_verified':
                        html += buttons['completed'];
                        html += buttons['canceled'];
                        break;
                    case 'shipped':
                        html += buttons['delivered'];
                        html += buttons['refunded'];
                        break;
                    case 'delivered':
                        html += buttons['refunded'];
                        break;
                    case 'partial':
                        html += buttons['partial'];
                        break;
                    default:
                        return false;
                        break;
                }

                el.html($('<div></div>').html(html).data('order-id', this.model.get('id')));
            }
        },
        goToClient: function(){
            var self = this,
                goToClientProfile = $('#website_url').val() + 'dashboard/clients/#client/'+self.model.get('user_id');

            window.open(goToClientProfile, '_blank');
        }
    });

    return OrderView;
});
