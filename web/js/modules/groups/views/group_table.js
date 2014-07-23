define([
	'backbone',
    '../collections/group',
    $('#website_url').val()+'system/js/external/jquery/plugins/DataTables/jquery.dataTables.min.js'
], function(Backbone,
            GroupCollection
            ){

    var GroupsTableView = Backbone.View.extend({
        el: $('#group-table'),
        events: {
            'click a[data-role=delete]': 'deleteGroup',
            'click a[data-role=edit]'  : 'editGroup'
        },
        templates: {},
        initialize: function(options){
            var aoColumnDefs = [
                { "bSortable": false, "aTargets": [ -1 ] }
            ];

            this.$el.dataTable({
                'sDom': 't<"clearfix"p>',
                "iDisplayLength": 12,
                "bPaginate": true,
                "bAutoWidth": false,
                "aoColumnDefs": aoColumnDefs
            });
            this.groups = new GroupCollection();

            this.groups.on('reset', this.renderGroups, this);
            this.groups.on('add', this.renderGroups, this);
            this.groups.on('destroy', this.renderGroups, this);
        },
        render: function(){
            this.groups.pager();
        },
        renderGroups: function(){
            this.$el.fnClearTable();
            this.groups.each(this.renderGroup, this);
           // $('#group-pricing .dataTables_paginate')[0].style.display = "none";
        },
        renderGroup: function(group){
            var priceType = $('.group-currency').val();
            var priceSign = '-';
            if(group.get('priceType') == 'percent'){
                priceType = '%';
            }
            if(group.get('priceSign') == 'plus'){
                priceSign = '+';
            }
            this.$el.fnAddData([
                '<span class="groupName-table">'+group.get('groupName')+'</span>',
                '<span>'+priceSign+' '+group.get('priceValue')+' '+priceType+'</span>',
                '<a class="ticon-pencil icon14" data-role="edit" data-cid="'+group.get('id')+'" href="javascript:;"></a> <a class="ticon-remove error icon14" data-role="delete" data-cid="'+group.get('id')+'" href="javascript:;"></a>',
            ]);
        },
        deleteGroup: function(e){
            var cid = $(e.currentTarget).data('cid');
            var model = this.groups.get(cid);
            if (model){
                model.destroy();
            }
        },
        editGroup: function(e){
            var cid = $(e.currentTarget).data('cid');
            $.ajax({
                url: $('#website_url').val() + 'api/store/groups/id/',
                data:{groupId:cid},
                type: 'GET',
                dataType: 'json'

            }).done(function(responce) {
                $('#groupName').val(responce[0].groupName);
                $('#group-price-type').val(responce[0].priceType).attr('selected',true);
                $('#group-sign').val(responce[0].priceSign).attr('selected',true);
                $('#priceValue').val(responce[0].priceValue);
                $('#priceValue').focus();
            })
        }
    });

    return GroupsTableView;
});