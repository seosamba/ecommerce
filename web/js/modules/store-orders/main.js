define(['backbone',
    './collections/orders',
    './views/order'
], function(Backbone,
        OrdersCollection, OrdersView
    ){
    var MainView = Backbone.View.extend({
        el: $('#store-orders'),
        events: {

        },
        initialize: function(){
            this.orders = new OrdersCollection;
            this.orders.on('reset', this.renderOrders, this);
            this.orders.pager();
        },
        render: function(){

        },
        renderOrder: function(order){
            var view = new OrdersView({model: order});
            this.$('#orders-list').append(view.render().el);
        },
        renderOrders: function(){
            this.$('#orders-list').empty();
            this.orders.each(this.renderOrder.bind(this));
        }
    });

    return MainView;
});