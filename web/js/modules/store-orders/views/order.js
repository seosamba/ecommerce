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
        initialize: function(){

        },
        render: function(){
            this.$el.html(this.template(this.model.toJSON()));
            return this;
        }
    });

    return OrderView;
});