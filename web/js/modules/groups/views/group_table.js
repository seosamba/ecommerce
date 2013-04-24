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
            'click a[data-role=delete]': 'deleteGroup'
        },
        templates: {},
        initialize: function(options){
            var aoColumnDefs = [
                { "bSortable": false, "aTargets": [ -1 ] }
            ];

            this.$el.dataTable({
                'sDom': 't<"clearfix"p>',
                "iDisplayLength": 7,
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
        },
        renderGroup: function(group){
            this.$el.fnAddData([
                group.get('groupName'),
                '<a data-role="delete" data-cid="'+group.get('id')+'" href="javascript:;">[x]</a>'
            ]);
        },
        deleteGroup: function(e){
            var cid = $(e.currentTarget).data('cid');
            var model = this.groups.get(cid);
            if (model){
                model.destroy();
            }
        }
    });

    return GroupsTableView;
});