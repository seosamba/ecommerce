/**
 * .
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
define([
    'backbone',
    'text!../templates/order.html'
], function(Backbone, OrderTmpl){

    var OrderView = Backbone.View.extend({
        tagName: 'tr',
        template: _.template(OrderTmpl),
        events: {
            'mouseenter td.status-change': 'statusChange',
            'mouseleave td.status-change': 'statusChange'
        },
        initialize: function(){
            this.model.on('change', this.render, this);
        },
        render: function(){
            this.$el.html(this.template(this.model.toJSON()));
            return this;
        },
        statusChange: function(event){
            var el = $(event.currentTarget);
            if (event.type === "mouseleave"){
                el.text(this.model.get('status'));
                return true;
            }
            var buttons = {
                'refunded'  : '<button class="change-status btn btn-small blue-gradient" data-status="refunded" >Refund payment</button>',
                'completed' : '<button class="change-status btn btn-small green-gradient" data-status="completed" >Complete order</button>',
                'canceled'  : '<button class="change-status btn btn-small red-gradient" data-status="canceled" >Cancel order</button>',
                'delivered' : '<button class="change-status btn btn-small orange-gradient" data-status="delivered" >Delivered</button>'
            }

            var html = ''
            switch (this.model.get('status')){
                case 'completed':
                    html += buttons['refunded']
                    break;
                case 'pending':
                    html += buttons['completed'];
                    html += buttons['canceled'];
                    break;
                case 'shipped':
                    html += buttons['delivered'];
                    html += buttons['refunded'];
                    break;
                default:
                    return false;
                    break;
            }

            el.html($('<div></div>').html(html).data('order-id', this.model.get('id')));
        }
    });

    return OrderView;
});