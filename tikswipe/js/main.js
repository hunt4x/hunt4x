jQuery(document).ready(function () {

	jQuery('#menu-mobile-icon').on('click', function (e) {
		e.preventDefault();
		// jQuery('.menu ul').animate({
		// 	width: "toggle"
		// });
		jQuery('.menu ul').fadeIn();
		jQuery(this).hide();
		jQuery('#close-mobile-nav').fadeIn();
	});

	jQuery('#close-mobile-nav').on('click', function (e) {
		e.preventDefault();
		jQuery(this).hide();
		jQuery('.menu ul').hide();
		jQuery('#menu-mobile-icon').show();
	});

	jQuery(document).on('click', '.tab-link', function () {
		var tabID = jQuery(this).attr('data-tab');
		jQuery(this).addClass('active').siblings().removeClass('active');
		jQuery('#tab-' + tabID).addClass('active').siblings().removeClass('active');
		jQuery('.content-wrapper').scrollTop(0);
	});

	// Footer nav menu
	jQuery('.wpst-login').click(function (e) {
		e.preventDefault();
		var videos = document.querySelectorAll('video');
		Array.prototype.forEach.call(videos, function (video) {
			video.pause();
		});
		jQuery('.dark-bg').show();
		jQuery('.comment-box').hide();
		jQuery('#main-nav').show();
		if (jQuery(this).hasClass('comment-opened')) {
			jQuery(this).removeClass('comment-opened');
		}
	});

	jQuery('.close-main-nav').click(function (e) {
		e.preventDefault();
		jQuery('.dark-bg').hide();
		jQuery('#main-nav').hide();
	});

	jQuery('.dark-bg').click(function (e) {
		jQuery(this).hide();
		jQuery('.slideup-box').hide();
		jQuery('.open-main-nav').show();
	});

});
