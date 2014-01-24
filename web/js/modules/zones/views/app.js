define([
	'underscore',
	'backbone',
    '../collections/zones',
    './zone',
    '../models/zone'
], function(_, Backbone, ZonesCollection, ZoneView, ZoneModel){

    var appView = Backbone.View.extend({
        el: $('#manage-zones'),
        zoneHolder: $('#zone'),
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
            var lastIndex = this.zoneHolder.find('.ui-tabs-nav li').size()-1;
            this.zoneHolder.tabs('option', 'active', lastIndex);
        },
        deleteZone: function(){
            console.log('dfd');
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
            this.zoneHolder.find('.ui-tabs-nav li[aria-controls='+zone.cid+'], div#'+zone.cid).remove()
                .end().tabs('refresh');
        },
        renderZone: function(zone){
            var view = new ZoneView({model: zone}),
                id   = '#zone-'+zone.cid;

            this.zoneHolder.find('.ui-tabs-nav .add-new-zone').before('<li><a href="#'+zone.cid+'">'+zone.get('name')+'</a></li>')
            view.render().$el.appendTo(this.zoneHolder);
            this.zoneHolder.tabs('refresh');
        },
        resetZones: function(){
            this.zoneHolder.find('.ui-tabs-nav li:not(.add-new-zone), .ui-tabs-panel').remove();
            this.zonesCollection.each(this.renderZone, this);
            this.zoneHolder.tabs('option', 'active', 0);
        },
        saveZones: function(){
			showSpinner();
            $.post(this.zonesCollection.url, {zones: this.zonesCollection.toJSON()}, function(){
                app.view.zonesCollection.fetch();
				hideSpinner();
            });
        },
        openDialog: function(e){
            var id = '#'+$(e.target).data('name')+'-dialog';
            if($(e.target).data('name') == 'add-country'){
                var countryList = $(e.target).closest('.countries').find('.zone-countries li');
                $.each(countryList, function(zone){
                    var countryId = $(this).find('.remove-item').data('element-country');
                    $('#country-list').find('[data-element-country="'+countryId+'"]').closest('li').hide();
                });
            }
            if($(e.target).data('name') == 'add-state'){
                var statesList = $(e.target).closest('.states').find('.zone-states li');
                $.each(statesList, function(zone){
                    var countryId = $(this).find('.remove-item').data('element-state');
                    $('#state-list').find('[data-element-state="'+countryId+'"]').closest('li').hide();
                });
            }
            $(id).dialog('open');
            $(id).dialog({
                beforeClose: function(event) {
                    $('#state-list').find('li').show();
                    $('#country-list').find('li').show();
                }
            });
        },
        render: function(){
            return this;
        }
    })

	return appView;
});