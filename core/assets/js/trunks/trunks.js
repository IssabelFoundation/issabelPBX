up.compiler('.content', function(element,data) {})
    $('.sortable').each(function() {
        Sortable.create(this, {
            animation: 150,
            onEnd: function(ui) {
                $(ui.item).find('input').val($(ui.item).index());
            }
        });
    });
});

