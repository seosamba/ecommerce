define([
	'Underscore',
	'Backbone',
    'modules/zones/collections/zones',
    'modules/zones/views/zone'
], function(_, Backbone, ZonesCollection, ZoneView){

    var appView = Backbone.View.extend({
        el: $('#manage-zones'),
        zoneHolder: $('#zones'),
        events: {
            'keyup': 'hotkey',
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
        hotkey: function(e){
            console.log(e);
        },
        newZone: function(){
            this.zonesCollection.add();
        },
        deleteZone: function(){
            var index = this.zoneHolder.tabs('option', 'selected');
                model = this.zonesCollection.at(index);
            if (model){
                model.destroy();
                this.zoneHolder.tabs('remove', index);
            }
        },
        renderZone: function(zone){
            console.log(zone.toJSON())
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