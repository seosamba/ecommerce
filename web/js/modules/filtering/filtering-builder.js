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
            'keyup [name=new-attribute]': 'attachAttribute'
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

            return $('<p>', {'class': 'filtering-attribute-widget'})
                .append($('<label>').html(attr.label))
                .append(
                    $('<input>', {type: 'text', name: attr.name, value: _.unescape(attr.value)})
                        .data({aid: attr.attribute_id, tags: tags})
                )
                .appendTo(this.list);
        },
        saveAttributeValue: function (e) {
            var $input = $(e.currentTarget),
                data = {
                    product_id: this.productId,
                    attribute_id: $input.data('aid'),
                    tags: $input.data('tags'),
                    value: $input.val()
                };

            $.ajax({
                url: $('#website_url').val() + 'api/filtering/eav/',
                type: 'PUT',
                data: JSON.stringify(data),
                success: function (model) {
                    $input.val(_.unescape(model.value));
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