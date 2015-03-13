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
            var priceSign = group.get('priceSign');
            var priceValue = group.get('priceValue');
            var priceType = group.get('priceType');
            var originalPrice = $('#group-products-price').val();
            var priceSymbol = $('#group-products-price-symbol').val();
            var resultPrice = 0;
            var priceModificationValue = 0;
            if(priceType == 'percent'){
                priceModificationValue = parseFloat(originalPrice)*parseFloat(priceValue)/100;
            }
            if(priceType == 'unit'){
                priceModificationValue = parseFloat(priceValue);
            }
            if(priceSign == 'minus'){
                resultPrice = parseFloat(originalPrice) - parseFloat(priceModificationValue);
            }
            if(priceSign == 'plus'){
                resultPrice = parseFloat(originalPrice) + parseFloat(priceModificationValue);
            }
            $('.group-id-'+group.get('id')+' .group-price-final').html('<span>= </span>'+priceSymbol+resultPrice.toFixed(2));
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
            var secureToken = $('.secure-token-group-price').val();
            var priceSymbol = $('#group-products-price-symbol').val();
            data += '&productId='+productId+'';
            data += '&secureToken='+secureToken+'';
            $.ajax({
                url: $('#website_url').val()+'api/store/groupsprice/',
                data: data,
                type: 'POST',
                dataType: 'json',
                success: function(response){
                    var originalPrice = $('#group-products-price').val();
                    var resultPrice = 0;
                    var priceModificationValue = 0;
                    if(response.priceType == 'percent'){
                        priceModificationValue = parseFloat(originalPrice)*parseFloat(response.priceValue)/100;
                    }
                    if(response.priceType == 'unit'){
                        priceModificationValue = parseFloat(response.priceValue);
                    }
                    if(response.priceSign == 'minus'){
                        resultPrice = parseFloat(originalPrice) - parseFloat(priceModificationValue);
                    }
                    if(response.priceSign == 'plus'){
                        resultPrice = parseFloat(originalPrice) + parseFloat(priceModificationValue);
                    }
                    $('.group-id-'+response.groupId+' .group-price-final').html('<span>= </span>'+priceSymbol+resultPrice.toFixed(2));
                }
            });
        }
    });

    return GroupsPriceView;
});