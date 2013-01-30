/**
 * .
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
define(['backbone'], function(Backbone){

    var OrderModel = Backbone.Model.extend({
        urlRoot: function(){
            return $('#website_url').val() + 'api/store/orders/id/';
        }
    });

    return OrderModel;
});