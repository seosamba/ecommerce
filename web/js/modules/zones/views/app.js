define([
	'Underscore',
	'Backbone',
    'modules/zones/collections/zones',
    'modules/zones/views/zone',
    'modules/zones/models/zone'
], function(_, Backbone, ZonesCollection, ZoneView, ZoneModel){

    var appView = Backbone.View.extend({
        el: $('#manage-zones'),
        zoneHolder: $('#zones'),
        events: {
            'click #new-zone-btn': 'newZone',
            'click #delete-zone': 'deleteZone',
            'click #save-btn': 'saveZones',
            'click .open-dialog': 'openDialog'
        },
        initialize: function(){
            this.zonesCollection = new ZonesCollection;
            this.zonesCollection.on('add', this.renderZone, this);
            this.zonesCollection.on('reset', this.resetZones, this);
        },
        newZone: function(){
            var model = new ZoneModel();
            this.zonesCollection.add(model);
        },
        deleteZone: function(){
            var zoneHolder = this.zoneHolder;
                index = zoneHolder.tabs('option', 'selected');
                model = this.zonesCollection.at(index);
            if (model){
                showConfirm('Are you sure?', function(){
                    model.destroy();
                    zoneHolder.tabs('remove', index);
                });
            } else {
                console.log('No zone to remove');
            }
        },
        renderZone: function(zone){
            console.log(zone);
            var view = new ZoneView({model: zone}),
                id = '#zone-'+zone.cid;
            this.zoneHolder.tabs('add', id, zone.get('name'));
            view.render().$el.appendTo(id);
        },
        resetZones: function(){
            this.zonesCollection.each(this.renderZone, this)
        },
        saveZones: function(){
            $('#ajax_msg').show('fade');
            $.post(this.zonesCollection.url, {zones: this.zonesCollection.toJSON()}, function(){
                app.view.zoneHolder.tabs('destroy').tabs();
                app.view.zonesCollection.fetch();
                $('#ajax_msg').hide('fade');
            });
        },
        openDialog: function(e){
            var id = '#'+$(e.target).data('name')+'-dialog';
            $(id).dialog('open');
        },
        render: function(){

        }
    })
	
	return appView;
});