define([
	'backbone',
    '../collections/group-price',
    'text!../templates/grouprow.html'
], function(Backbone,
            GroupsPriceCollection,
            GroupRowTemplate){

    var GroupsPriceView = Backbone.View.extend({
        el: $('#editing-grouping-price'),
        events: {
            'click a[data-role=delete]': 'deleteGroupPrice',
            'blur input': 'saveRowGroup',
            'change select': 'saveRowGroup'
        },
        templates: {
            row: _.template(GroupRowTemplate)
        },
        initialize: function(options){

            this.groups = new GroupsPriceCollection();

            this.groups.on('reset', this.renderGroupsPrice, this);
            this.groups.on('add', this.renderGroupsPrice, this);
            this.groups.on('destroy', this.renderGroupsPrice, this);

        },
        render: function(){
            this.groups.pager();
        },
        renderGroupsPrice: function(){
            $('#editing-grouping-price .group-price-row').remove();
            this.groups.each(this.renderGroup, this);
        },
        renderGroup: function(group){
            this.$el.append(this.templates.row({group:group}));
            $('.groupPriceType option[value=unit]').html($('#currency-unit').html());
        },
        deleteGroupPrice: function(e){
            var cid = $(e.currentTarget).data('cid');
            var productId = $('#group-products-id').val();
            $.ajax({
                url: $('#website_url').val()+'api/store/groupsprice/id/'+cid+'/productId/'+productId+'',
                data: {
                    id:cid,
                    productId:productId
                },
                type: 'DELETE',
                dataType: 'json',
                success: function(response){
                    $('.group-id-'+cid+' input[name=priceValue]').val('');
                },
                error: function(response){

                }
            });
        },
        saveRowGroup: function(e){
            var data = $(e.target).parent('.group-price-row').find('input, select').serialize();
            var productId = $('#group-products-id').val();
            data += '&productId='+productId+'';
            $.ajax({
                url: $('#website_url').val()+'api/store/groupsprice/',
                data: data,
                type: 'POST',
                dataType: 'json',
                success: function(response){

                }
            });
        }
    });

    return GroupsPriceView;
});