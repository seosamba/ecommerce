/*jslint nomen: true*/
/*global $, jQuery, _, Backbone */
$(function () {
    var $widgets = $('.plugin-filtering-widget');
    $widgets.on('click', 'button', function (e) {
        var $widget = $(e.currentTarget).closest('.plugin-filtering-widget'),
            data;
        data = $widget.find('input:checkbox:checked').serialize();

        $.ajax({
            url: $('#website_url').val() + 'plugin/filtering/run/filterSettings/filterId/' + $widget.data('filterid'),
            type: 'POST',
            data: data,
            success: function () {
                console.log(arguments);
            }
        });
    });
    $widgets.on('change', 'input.filter-cb', function (e) {
        var $subList = $(e.currentTarget).closest('li').find('ul');
        $subList.find('input:checkbox')
            .prop('checked', e.currentTarget.checked)
            .prop('disabled', e.currentTarget.checked);
    });
    $widgets.find('div.slider').each(function () {
        var min = Math.floor(parseFloat($(this).data('min'))),
            max = Math.ceil(parseFloat($(this).data('max')));
        $(this).slider({
            range: true,
            min: min,
            max: max,
            values: [ min, max ]
        });
    });
});