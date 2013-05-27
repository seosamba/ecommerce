define([
	'underscore',
	'backbone',
    '../collections/zones',
    './zone',
    '../models/zone'
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
            this.zonesCollection.on('destroy', this.destroyZone, this);
        },
        newZone: function(){
            var model = new ZoneModel();
            this.zonesCollection.add(model);
            var lastIndex = this.zoneHolder.find('ul.ui-tabs-nav li').size()-1;
            this.zoneHolder.tabs('option', 'active', lastIndex);
        },
        deleteZone: function(){
            var zoneHolder = this.zoneHolder;
                index = zoneHolder.tabs('option', 'active');
                model = this.zonesCollection.at(index);
            if (model){
                showConfirm('Are you sure?', function(){
                    model.destroy();
                });
            } else {
                console.log('No zone to remove');
            }
        },
        destroyZone: function(zone){
            this.zoneHolder.find('ul.ui-tabs-nav li[aria-controls='+zone.cid+'], div#'+zone.cid).remove()
                .end().tabs('refresh');
        },
        renderZone: function(zone){
            var view = new ZoneView({model: zone}),
                id   = '#zone-'+zone.cid;

            this.zoneHolder.find('ul.ui-tabs-nav').append('<li><a href="#'+zone.cid+'">'+zone.get('name')+'</a></li>')
            view.render().$el.appendTo(this.zoneHolder);
            this.zoneHolder.tabs('refresh');
        },
        resetZones: function(){
            this.zoneHolder.find('ul.ui-tabs-nav li:not(.add-new-zone), div.ui-tabs-panel').remove();
            this.zonesCollection.each(this.renderZone, this);
            this.zoneHolder.tabs('option', 'active', 0);
        },
        saveZones: function(){
            $('#ajax_msg').show('fade');
            $.post(this.zonesCollection.url, {zones: this.zonesCollection.toJSON()}, function(){
                app.view.zonesCollection.fetch();
                $('#ajax_msg').hide('fade');
            });
        },
        openDialog: function(e){
            var id = '#'+$(e.target).data('name')+'-dialog';
            $(id).dialog('open');
        },
        render: function(){
            return this;
        }
    })
	
	return appView;
});