jQuery(document).ready(function($) {
    $('#real-estate-filter-form').on('submit', function(e) {
        e.preventDefault();
        performSearch(1); // Пошук на першій сторінці
    });

    function performSearch(page) {
        var data = {
            action: 'reo_search',
            district: $('#district').val(),
            square: $('#square').val(),
            paged: page
        };

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: data,
            success: function(response) {
                $('.content-area').html(response);

                $('.pagination-btn').on('click', function() {
                    var page = $(this).data('page');
                    performSearch(page);
                });
            }
        });
    }
});
