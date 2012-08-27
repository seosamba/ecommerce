define([
	'Underscore',
	'Backbone',
    'modules/taxes/collections/rules',
    'modules/taxes/views/rule',
], function(_, Backbone, RulesCollection, RuleView){

    var rulesListView = Backbone.View.extend({
        el: $('#manage-taxes'),
        events: {
            'click #new-rule-btn': 'newRule',
            'click #save-btn': 'save'
        },
        initialize: function(){
            $('#price-inc-tax').on('change', this.changeTaxConfig)
            this.rulesCollection = new RulesCollection;
            this.rulesCollection.on('add', this.render, this);
            this.rulesCollection.on('remove', this.render, this);
            this.rulesCollection.on('reset', this.render, this);
        },
        render: function(){
            $('#rules').empty();
            this.rulesCollection.each(function(rule){
                var view = new RuleView({model: rule});
                view.render().$el.appendTo('#rules');
            });
        },
        save: function() {
            console.log(JSON.stringify(this.rulesCollection.toJSON()));

            $.post(this.rulesCollection.url, {rules: this.rulesCollection.toJSON()}, function(response){
                closePopup();
            });
        },
        newRule: function(){
            this.rulesCollection.add();
        },
        changeTaxConfig: function(e){
            $.post('/plugin/shopping/run/setConfig', {config: {showPriceIncTax: e.target.checked ? 1 : 0}}) ;
        }
    })
	
	return rulesListView;
});