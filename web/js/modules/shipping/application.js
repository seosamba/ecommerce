define(['backbone'], function(Backbone){
    var ShipperModel = Backbone.Model.extend({
        idAttribute: 'name',
        parse: function(data){
            return _.pick(data, 'name', 'enabled');
        }
    });

    var ShipperCollection = Backbone.Collection.extend({
        model: ShipperModel,
        url: $('#website_url').val()+'api/store/shippers/name/'
    });

    var AppView = Backbone.View.extend({
        el: $('#shippers'),
        events: {
            'click #submit': 'saveShipperConfig',
            'click span[role=switch]': function (e){
                var name =  $(e.currentTarget).prev('a').data('plugin'),
                    plugin = this.shippers.get(name);
                if (_.isUndefined(plugin)){
                    plugin = new ShipperModel({name: name, enabled: 0, config: null});
                    this.shippers.add(plugin);
                }
                var status = plugin.get('enabled') === 1 ? 0 : 1;
                if(status){
                    $(e.currentTarget).parent('li').removeClass('disabled').addClass('enabled').find('span.icon-minus').removeClass('icon-minus').addClass('icon-checkmark');
                }else{
                    $(e.currentTarget).parent('li').removeClass('enabled').addClass('disabled').find('span.icon-checkmark').removeClass('icon-checkmark').addClass('icon-minus');
                }
                plugin.set('enabled', status);
                console.log(plugin.save());
                $(e.currentTarget).replaceWith(_.template(this.templates.button, plugin.toJSON()));
            }
        },
        templates: {
            button: '<span class="unit-over" role="switch"><% if (!!enabled) {%>Disable<% } else { %>Enable<% } %></span>'
        },
        initialize: function(){
            this.shippers = new ShipperCollection();
            this.shippers.on('reset', this.render, this);
            this.shippers.fetch();
        },
        render: function(){
            var self = this;
            $('ul.ui-tabs-nav a[data-plugin]').each(function(){
                var plugin = self.shippers.get($(this).data('plugin'));
                if (plugin) {
                    $(this).after(_.template(self.templates.button, plugin.toJSON())).closest('li').addClass(!!plugin.get('enabled')?'enabled':'disabled');
                } else {
                    $(this).after('<span class="unit-over" role="switch">Enable</span>').closest('li').addClass('disabled');
                }
                $(this).closest('li.enabled').prepend('<span class="icon-checkmark"></span>');
                $(this).closest('li.disabled').prepend('<span class="icon-minus"></span>');
            });
        },
        saveShipperConfig: function(){
            var index = this.$el.tabs( "option", "selected" ),
                currentPane = $('#pane-container div.ui-tabs-panel:eq('+index+')');
            if (currentPane){
                var form = currentPane.find('form');
                $.ajax({
                    url: form.attr('action'),
                    data: form.serialize(),
                    dataType: 'json',
                    type: form.attr('method'),
                    complete: function(response){
                        form.trigger('formsave', response)
                    }
                });
            }
        }
    });

    return AppView;
});