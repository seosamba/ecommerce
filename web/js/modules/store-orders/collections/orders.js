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
                url:  $('#website_url').val() + 'api/store/orders'
            },
            paginator_ui: {
                firstPage:    0,
                currentPage:  0,
                perPage:     10,
                totalPages:  10
            },
            server_api: {
                count: true,
                limit: function() { return this.perPage; },
                offset: function() { return this.currentPage * this.perPage },
                order: 'order.created_at DESC'
            },
            parse: function(response){
                if (this.server_api.count){
                    this.totalRecords = response.totalRecords;
                } else {
                    this.totalRecords = response.length;
                }
                this.totalPages = Math.floor(this.totalRecords / this.perPage);
                return this.server_api.count ? response.data : response;
            }
        });

        return OrdersCollection;
    }
);