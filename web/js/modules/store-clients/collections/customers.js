define([
	'backbone',
    '../models/customer_row',
    'backbone.paginator'
], function(Backbone, CustomerRowModel){

    var CustomersCollection =  Backbone.Paginator.requestPager.extend({
        model: CustomerRowModel,
        paginator_core: {
            dataType: 'json',
            url:  $('#website_url').val() + 'api/store/customers/for/dashboard/withcounter/1/'
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
            order: 'reg_date DESC',
            search: '',
            clientsFilter: '',
            roleId: ''
        },
        cached: {},
        parse: function(response){
            if (this.server_api.count){
                this.totalRecords = response.totalRecords;
            } else {
                this.totalRecords = response.length;
            }
            this.allClientsCount = response.allClientsCount;
            this.allAccountsCount = response.allAccountsCount;
            this.totalPages = Math.floor(this.totalRecords / this.perPage);
            return this.server_api.count ? response.data : response;
        },
        checked: function(){
            return this.filter(function(customer){ return customer.has('checked') && customer.get('checked'); });
        },
    });

	return CustomersCollection;
});
