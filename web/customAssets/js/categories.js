$(document).ready(function() {
    $('.category-toggle').on('click', function(e) {
        e.stopPropagation();
        var $categoryItem = $(this).closest('.category-item');
        $categoryItem.toggleClass('active');

    });
    $('.subcategory-toggle').on('click', function(e) {
        e.stopPropagation();
        var $subcategoryItem = $(this).closest('.subcategory-item');
        $subcategoryItem.toggleClass('active');

    });

});