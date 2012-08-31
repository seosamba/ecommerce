/**
 * .
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
define(['backbone', '../models/order', 'backbone.paginator'],
    function(Backbone, OrderModel){

        var OrdersCollection = Backbone.Paginator.requestPager.extend({
            model: OrderModel,
            paginator_core: {
                dataType: 'json',
                url:  $('#website_url').val() + 'api/store/orders/id/'
            },
            paginator_ui: {
                firstPage:  0,
                currentPage: 0,
                perPage: 20,
                totalPages: 10
            },
            parse: function(response){
                this.totalPages = Math.floor(response.length / this.perPage);
                return response;
            }
        });

        return OrdersCollection;
    }
);