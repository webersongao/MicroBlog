jQuery(function ($) {

	$(document).on('click', '#micro-news-board .editB', function () {

		parent = $(this).parent();

		id = $(parent).attr('data-id');

		var title = $.trim($('#mn-title-' + id).html());
		var content = $.trim($('#mn-text-' + id).html());
		var cat = $('#mn-cat-' + id).html();
		var link = $('#mn-link-' + id).html();

		var fields = '<form action="" method="post" id="update-micro-news" class="update-micro-news-' + id + '">';
		fields += '<div class="row"><label for="nTitle">Title:</label><input type="text" name="nTitle" class="title-updated regular-text" value="' + title + '"/></div>';

		fields += '<div class="row"><label for="nContent">Content:</label><textarea class="text-updated" name="nContent">' + content + '</textarea></div>';


		if (cat != '' && cat != undefined && typeof cat != undefined) {
			//fields +='<div class="row"><label for="nCat">Category key:</label><input type="text" name="nCat" class="cat-updated regular-text" value="'+cat+'"/></div>';
			fields += '<div class="row"><label for="nCat">Category key:</label>';
			fields += '<select name="nCat" class="cat-updated regular-text">';
			fields += '<option value="default" ' + ((cat == "default") ? 'selected' : '') + ' >Default</option>';
			fields += '<option value="cata" ' + ((cat == "cata") ? 'selected' : '') + ' >CatA</option>';
			fields += '<option value="catb" ' + ((cat == "catb") ? 'selected' : '') + ' >CatB</option>';
			fields += '<option value="catc" ' + ((cat == "catc") ? 'selected' : '') + ' >CatC</option>';
			fields += '<option value="catd" ' + ((cat == "catd") ? 'selected' : '') + ' >CatD</option>';
			fields += '</select></div>';
		}

		fields += '<div class="row"><label for="nLink">Link:</label><input type="text" name="nLink" class="link-updated regular-text" value="' + link + '"/></div>';

		fields += '<input type="hidden" name="nId" value="' + id + '"/><div class="row"><input type="submit" class="button-primary submit-update"></div></form>';


		var checkopen = parent.hasClass('open');
		if (!checkopen) {
			parent.append(fields);
			parent.addClass('open');

			$(this).attr('value', 'Update');

			//show cross sign
			$(parent).children(".closeB").css('visibility', 'visible');
		}
		else {
			//hide cross sign
			$(parent).children(".closeB").css('visibility', 'hidden');

			parent.removeClass('open');
			$(this).attr('value', 'edit');

			title = $('#update-micro-news .title-updated').val();
			content = $('#update-micro-news .text-updated').val();
			link = $('#update-micro-news .link-updated').val();
			cat = $('#update-micro-news .cat-updated').val();

			if (cat == '' || cat == 'undefined' || typeof cat === undefined)
				cat = "NULL";

			$.ajax({
				url: location.href,
				cache: false,
				type: "post",
				data: { nId: id, nTitle: title, nContent: content, nCat: cat, nLink: link },
				success: function () {
					window.location.reload();
				}
			});

			$('#update-micro-news').remove();

		}

	});

	$(document).on('click', '#micro-news-board .delB', function () {
		parent = $(this).parent();

		id = $(parent).attr('data-id');

		$.ajax({
			url: location.href,
			cache: false,
			type: "post",
			data: { dId: id },
			success: function () {
				parent.hide('slow');
			}
		});

	});

	$(document).on('click', '#micro-news-board .closeB', function () {

		parent = $(this).parent();

		id = $(parent).attr('data-id');

		$('#micro-news-board .update-micro-news-' + id).remove();
		parent.removeClass('open');

		$("[data-id='mn-edit-" + id + "']").attr('value', 'Edit');

		//hiding itself
		$(this).css('visibility', 'hidden');

	});

	$(document).on('click', '#micro-news .load-nav .loadMore', function () {
		numofnews = $(this).attr('data-num');	//number of news to display
		loops = $(this).attr('data-loops'); //this will decide how many times we have already called
		totalnews = $(this).parent().attr('data-total');
		possible_loops = Math.floor(parseInt(totalnews) / parseInt(numofnews));
		category = $('#micro-news .data-holder').attr('data-cat');

		update_style = $("#micro-news .load-nav").attr('data-style');//how to update news, swap/append

		//decrease opacity to look like data is faded
		$("#micro-news .data-holder").animate({ opacity: 0.25 }, 500);

		$('#micro-news .load-nav .loadHome').css('visibility', 'visible');//show home button to go back

		//ajax request here		
		$.ajax({
			type: "POST",
			url: micronews_enqueue_ajax_object.ajaxurl,	//localized in wordpress
			data: { 'action': 'kush_micronews_ajaxcallback', what: "loadmore", numnews: numofnews, loop: loops, cat: category },
			success: function (result) {
				if (result == '0') {
					$('#micro-news .load-nav .loadMore').css('visibility', 'hidden');//hide myself because there is no news now
					$('#micro-news .load-nav .loadMore').attr('data-loops', '1'); //reset loop counter
				}
				else {
					//choose style to perform
					if (update_style == "swap") {
						//add opacity in header
						result = result.replace('<div class="data-holder">', '<div class="data-holder" style="opacity:0.25;">');

						$("#micro-news .data-holder").replaceWith(result);
					}
					else {
						//cutoff header
						result = result.replace('<div class="data-holder">', '');
						//cutoff last div
						result = result.replace(new RegExp('</div>$', 'i'), '');

						$("#micro-news .data-holder").append(result);
					}

					//reset opacity to look like new data is catched
					$("#micro-news .data-holder").animate({ opacity: 1 }, 300);

					$('#micro-news .load-nav .loadMore').attr('data-loops', ++loops); //increment loop counter						
				}

				//hide button if loops reach it limit
				if (loops >= possible_loops)
					$('#micro-news .load-nav .loadMore').css('visibility', 'hidden');//hide myself because there is no news now

			}
		});//ajax ends


	});

	$(document).on('click', '#micro-news .load-nav .loadHome', function () {
		$('#micro-news .load-nav .loadMore').css('visibility', 'visible');// show myself
		$('#micro-news .load-nav .loadMore').attr('data-loops', '1'); //reset loop counter

		numofnews = $(this).attr('data-num');	//number of news to display
		category = $('#micro-news .data-holder').attr('data-cat');

		//decrease opacity to look like data is faded
		$("#micro-news .data-holder").animate({ opacity: 0.25 }, 500);

		//ajax request here		
		$.ajax({
			type: "POST",
			url: micronews_enqueue_ajax_object.ajaxurl,	//localized in wordpress
			data: { 'action': 'kush_micronews_ajaxcallback', what: "loadhome", numnews: numofnews, cat: category },
			success: function (result) {

				//add opacity in header
				result = result.replace('<div class="data-holder"', '<div class="data-holder" style="opacity:0.25;"');
				//replacing head start
				$("#micro-news .data-holder").replaceWith(result);

				$('#micro-news .load-nav .loadHome').css('visibility', 'hidden');// hide myself
				//reset opacity to look like new data is catched
				$("#micro-news .data-holder").animate({ opacity: 1 }, 200);

			}
		});//ajax ends


	});

});