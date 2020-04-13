define([
	'underscore',
	'backbone',
    '../collections/zones',
    './zone',
    '../models/zone',
    'i18n!../../../nls/'+$('input[name=system-language]').val()+'_ln'
], function(_, Backbone, ZonesCollection, ZoneView, ZoneModel, i18n){

    var appView = Backbone.View.extend({
        el: $('#manage-zones'),
        zoneHolder: $('#zone'),
        events: {
            'click #new-zone-btn': 'newZone',
            'click #delete-zone': 'deleteZone',
            'click #save-btn': 'saveZones',
            'click .open-dialog': 'openDialog',
            'click .add-not-saved-countries' : 'addNotSavedCountries'
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
            var zoneHolder = this.zoneHolder;
                index = zoneHolder.tabs('option', 'active');
                model = this.zonesCollection.at(index);
            if (model){
                showConfirmCustom(_.isUndefined(i18n['Are you sure?'])?'Are you sure?':i18n['Are you sure?'], _.isUndefined(i18n['Yes'])?'Yes':i18n['Yes'], _.isUndefined(i18n['No'])?'No':i18n['No'], function(){
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

            this.zoneHolder.find('.ui-tabs-nav .add-new-zone').before('<li><a href="#'+zone.cid+'">'+zone.get('name')+'</a></li>');
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
        addNotSavedCountries : function(e) {
          e.preventDefault();
            showConfirmCustom(_.isUndefined(i18n['Are you sure want to add all not in use countries?'])?'Are you sure want to add all not in use countries?':i18n['Are you sure want to add all not in use countries?'], _.isUndefined(i18n['Yes'])?'Yes':i18n['Yes'], _.isUndefined(i18n['No'])?'No':i18n['No'], function(){
              var allCountries = app.views.countryList.collection;

                $.ajax({
                    'url': $('#website_url').val() + 'plugin/shopping/run/getUsedZoneCountries/',
                    'type' : 'GET',
                    'dataType': 'json',
                    'data': {}
                }).done(function(response){
                    var savedCountries = response.responseText.savedCounties;

                    if(response.error == 0) {
                        var notSavedCountries = {},
                            isSaved = false;

                        $.each(allCountries, function(key, country){
                            var idx = savedCountries.indexOf(country.id);

                            if(idx == -1) {
                                isSaved = true;
                                notSavedCountries[key] = allCountries[key];
                            }
                        });

                        if(isSaved) {
                            var index = app.view.zoneHolder.tabs('option', 'active'),
                                currentZone = app.view.zonesCollection.at(index),
                                currentListName = 'countries';

                            $.each(notSavedCountries, function(key, countryModel){
                                currentZone.addItem(currentListName, countryModel);
                            });

                            showMessage(_.isUndefined(i18n['Done. Don\'t forget to save zone']) ? 'Done. Don\'t forget to save zone' : i18n['Done. Don\'t forget to save zone'], false, 3000);
                        } else {
                            showMessage(_.isUndefined(i18n['Nothing to process. All countries are already added']) ? 'Nothing to process. All countries are already added' : i18n['Nothing to process. All countries are already added'], false, 3000);
                        }

                        $('.add-not-saved-countries').hide();
                    } else {
                        $('.add-not-saved-countries').show();
                        showMessage(_.isUndefined(i18n['Can\'t process countries']) ? 'Can\'t process countries' : i18n['Can\'t process countries'], true, 3000);
                    }
                }).fail(function(response) {
                    $('.add-not-saved-countries').show();
                    showMessage(_.isUndefined(i18n['Can\'t process countries']) ? 'Can\'t process countries' : i18n['Can\'t process countries'], true, 3000);
                });
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
    });

	return appView;
});
