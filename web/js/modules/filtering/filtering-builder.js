/*jslint nomen: true*/
/*global $, jQuery, _, Backbone, showMessage, TFilter */
if (_.isUndefined(TFilter)) {
    var TFilter = {};

    var AttributesCollection = Backbone.Collection.extend({
        url: function () {
            return $('#website_url').val() + 'api/filtering/filters/';
        }
    });

    TFilter.Attributes = new AttributesCollection();

    TFilter.MainView = Backbone.View.extend({
        tags: [],
        events: {
            'change .filtering-attribute-widget input': 'saveAttributeValue',
            'keyup [name=new-attribute]': 'attachAttribute',
            'click .filed-upgrade-select input': 'changeAttributeStatus'
        },
        initialize: function () {
            this.productId = this.$el.data('productid');

            this.$tabs = this.$el.find('div.plugin-filtering-builder-content');
            this.$tabs.tabs();
            this.$tabs.on('tabsactivate', _.bind(this.initTagsBlock, this));
            this.data = this.$el.data();
            var self = this;
            this.$el.find('input.typeahead').autocomplete({
                source: _.bind(this.autocomplete, this),
                select: function (event, ui) {
                    $('.filtering-attribute-widget').show();
                    $('.add-new-config').hide();
                    self.renderAttribute(ui.item).find('input:text').focus();
                    $(this).val('').blur();
                    return false;
                }
            });
        },
        setTags: function (tags) {
            if (!_.isEmpty(tags)) {
                this.tags = tags;
            }
            return this;
        },
        getTags: function () {
            return this.tags;
        },
        changeAttributeStatus: function(e){
            var checkBoxEl = $(e.currentTarget).closest('.filed-upgrade-select').find('.checkbox'),
                attributeEl = $(e.currentTarget).closest('.filtering-attribute-widget'),
                tagId = checkBoxEl.val(),
                attributeId = attributeEl.attr('data-attributeId'),
                attributeValue = attributeEl.attr('data-attributeValue'),
                productId = this.productId,
                checked = '';

            if(checkBoxEl.is(":checked")){
                checked = true;
            }else{
                checked = false;
            }
           var data = {
                product_id: productId,
                attribute_id: attributeId,
                tagId: tagId,
                attributeVal: attributeValue,
                checked: checked
            };

            $.ajax({
                url: $('#website_url').val() + 'api/filtering/eav/',
                type: 'PUT',
                data: JSON.stringify(data),
                success: function (model) {
                    showMessage(model.responseText.message, false, 2000);
                }

            });

        },
        attachAttribute: function (e) {
            e.preventDefault();
            if (e.keyCode === 13) {
                var self = this,
                    $el = $(e.currentTarget),
                    label = $el.val(),
                    tags = [],
                    attrExists;

                attrExists = TFilter.Attributes.find(function (a) {
                    return a.get('label') === label;
                });

                _.each(this.$el.find('.apply-to-tags input:checkbox:checked'), function (cb) {
                    tags.push($(cb).val());
                });

                if (!attrExists) {
                    TFilter.Attributes.create(
                        {
                            label: label
                        },
                        {
                            success: function (model, response) {
                                $el.val('').blur();
                                var attr = {
                                    attribute_id: model.get('id'),
                                    label: model.get('label'),
                                    name: model.get('name'),
                                    tags: tags
                                };
                                self.renderAttribute(attr).find('input:text').focus();
                            },
                            error: function (model, response) {
                                showMessage(response.responseText, true);
                            }
                        }
                    );
                } else {
                    $el.val('');
                    var $input = this.$el.find('.product-filters-list input[name="' + attrExists.get('name') + '"]');
                    if ($input.size()) {
                        $input.focus();
                    } else {
                        self.renderAttribute(_.extend({tags: tags}, attrExists.toJSON())).find('input:text').focus();
                    }
                }
            }
        },
        initTagsBlock: function () {
            var html = '';

            if (!_.isEmpty(this.tags)) {
                _.each(this.tags, function (tag) {
                    html += '<label><input type="checkbox" name="tags[]" value="' + tag.id + '" />' +
                        _.escape(tag.name) + '</label>';
                });
            } else {
                html = '<label>This product has no tags yet</label>';
            }

            this.$el.find('.apply-to-tags').html(html);
        },
        loadAttributes: function (attributes) {
            if (!_.isEmpty(attributes)) {
                _.each(attributes, this.renderAttribute, this);
            }
        },
        renderAttribute: function (attr, index) {
            // prevent duplicating attributes
            var $exists = this.$el.find('input[name="' + attr.name + '"]'),
                tags = [];
            if ($exists.size()) {
                return $exists.closest('p.filtering-attribute-widget');
            }

            // caching list element
            if (_.isUndefined(this.list)) {
                this.list = this.$el.find('.product-filters-list');
            }

            if (_.has(attr, 'tags')) {
                tags = attr.tags;
            }
            var html = '';
            _.each( attr.tags, function (k ,v) {
                if($.inArray(k, attr.checked_Tags) !== -1) {
                    html += '<label class="filed-upgrade-select"><input class="checkbox" type="checkbox" checked name="tags[]" value="' + k + '" />' +
                    _.escape(v) + '</label>';
                }else{
                    html += '<label class="filed-upgrade-select"><input class="checkbox" type="checkbox" name="tags[]" value="' + k + '" />' +
                    _.escape(v) + '</label>';
                }
            });
            var ticon = '';
            var ticonRemove = '';

            if(attr.product_id){
                ticon = 'ticon-cog';
                ticonRemove = 'ticon-remove-sign';
            }

            return $('<p>', {'class': 'filtering-attribute-widget', 'data-attributeId': attr.attribute_id, 'data-attributeValue': attr.value, 'data-productId': attr.product_id})
                .append($('<label>').html(attr.label+' ').append($('<span>', {'class': ticonRemove, 'style': 'color:#FF6347;'})))
                .append(
                    $('<input>', {type: 'text', name: attr.name, value: _.unescape(attr.value)})
                        .data({aid: attr.attribute_id, tags: tags})
                )
                .append($('<span>', {'class': ticon, 'style':'color:#228B22;'}).html(''))
                .append($('<div>', {'class': 'updateConfigBlock', 'style':"display:none;"}).append(html))
                .appendTo(this.list);
        },
        saveAttributeValue: function (e) {
            var $input = $(e.currentTarget),
                dataValue = {
                    product_id: this.productId,
                    attribute_id: $input.data('aid'),
                    tags: $input.data('tags'),
                    value: $input.val()
                };
            if(dataValue.value === ''){
                return;
            }

            $.ajax({
                url: $('#website_url').val() + 'api/filtering/eav/',
                type: 'PUT',
                data: JSON.stringify(dataValue),
                success: function (model) {
                    $input.val(_.unescape(model.value));
                    document.location.reload(true);


                }
            });
        },
        autocomplete: function (request, response) {
            var term = $.ui.autocomplete.escapeRegex(request.term).toLowerCase(),
                filteredAttrs = _.chain(TFilter.Attributes.toJSON())
                    .filter(function (attr) {
                        return attr.label.toLowerCase().search(term) !== -1;
                    }).map(function (attr) {
                        return {
                            attribute_id: attr.id,
                            name: attr.name,
                            label: attr.label,
                            value: null
                        };
                    })
                    .value();
            response(filteredAttrs);
        }
    });

    $(function () {
        TFilter.Attributes.fetch();
    });
}