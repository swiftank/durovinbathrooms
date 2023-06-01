        require([
            'jquery',
            'domReady!'
        ], function ($) {
$('.thumbnail img').on('click',function(e){
	var loc = $(this).attr("src");
	$('#main').attr('src',loc);
})
});