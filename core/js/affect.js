$(function() {
	
	//3rd Party Emoticons Acknowledgement
	$('emot').attr('data-toggle', 'tooltip');
	$('emot').attr('data-placement', 'right');
	$('emot').attr('data-title', 'POPO Emoticons<br>Copyright &amp; Reserved by Netease<br>Author: Rokey<br>Website: www.rokey.net');

	$('[data-toggle="tooltip"]').tooltip({html: true});

	$('.divLink').on('click', function(){
		window.location.href = $(this).data('link-url');
	});

	$('.closeExibeoBoxBtn').on('click', function(){
		closeExibeoBox();
	});

	$('#photoCaptionBox').on('click', function(){
		if($('#editCaptions').prop('checked') == false) $(this).css('display', 'none');
	});

	$('[data-toggle="exibeoBox"]').on('click', function(){
		$refAlbum = $(this).data('album'); //we also use these for our admin control
		$refPhoto = $(this).data('photo'); //we also use these for our admin control
		exibeoBox( $(this).data('master-dir'), $refAlbum, $refPhoto );
	});

	$('#showCaptions').on('click', function(){
		if($(this).prop('checked')) $.cookie("showCaptions", 1);
		else $.cookie("showCaptions", 0);
	});
	
	if($.cookie("showCaptions") == null || $.cookie("showCaptions") == 1) $('#showCaptions').attr('checked', true);
	else $('#showCaptions').attr('checked', false);

	$('#saveCaption').on('click', function(){
		saveCaption($refAlbum, $refPhoto);
	});

	//stickSections;
	$(window).on("scroll.sectionWrapper", function() {
		stickSections();
	});
});

function exibeoBox($masterDir, $album, $photoFile)
{
	$('.exibeoBox').css('display', 'block');


	var jqxhr = $.get( '/ajax/' + $album + '/' + $photoFile)
		.done(function($dataStr) {
			$dataStr = decodeURIComponent($dataStr.replace(/\+/g, '%20'));
			$dataArray = Array();
			$dataArray = $dataStr.split('{!}');

			//unbind any previous events
			$('#lastPhotoBtn').unbind();
			$('#nextPhotoBtn').unbind();

			//last photo button
			if($dataArray[0] != ''){
				$('#lastPhotoBtn').on('click', function() {
					$('#exibeoBoxImg').css('display', 'none');
					$('#loadingImg').css('display', 'block');
					
					//turn the buttons off while the image is loading
					$('#nextPhotoBtn').css('display', 'none');
					$('#lastPhotoBtn').css('display', 'none');

					$refAlbum = $album; //we also use these for our admin control
					$refPhoto = $dataArray[0]; //we also use these for our admin control
					exibeoBox($masterDir, $album, $dataArray[0]);
				});
			}
			else $('#lastPhotoBtn').css('display', 'none');

			//next photo button
			if($dataArray[1] != ''){
				$('#nextPhotoBtn').on('click', function() {
					$('#exibeoBoxImg').css('display', 'none');
					$('#loadingImg').css('display', 'block');

					//turn the buttons off while the image is loading
					$('#nextPhotoBtn').css('display', 'none');
					$('#lastPhotoBtn').css('display', 'none');

					$refAlbum = $album; //we also use these for our admin control
					$refPhoto = $dataArray[1]; //we also use these for our admin control
					exibeoBox($masterDir, $album, $dataArray[1]);
				});
			}
			else $('#nextPhotoBtn').css('display', 'none');

			//generate caption box
			if($('#showCaptions').prop('checked')){
				$('#photoCaptionBox').html($dataArray[2]);
				if($dataArray[2] != "" || $('#editCaptions').prop('checked')){
					$('#photoCaptionBox').css('display', 'block');
					
					if($('#editCaptions').prop('checked')){
						getCaption($album, $photoFile);
						$('#editCaption').css('display', 'block');
					}
				}
				else{
					$('#photoCaptionBox').css('display', 'none');
					$('#editCaption').css('display', 'none');
				}
			}
			else{
				$('#photoCaptionBox').css('display', 'none');
				$('#editCaption').css('display', 'none');
			}

			$('#exibeoBoxImg').on('load', function(){
				//turn the buttons back on after image is loaded
				if($dataArray[1] != '') $('#nextPhotoBtn').css('display', 'block');
				if($dataArray[0] != '')$('#lastPhotoBtn').css('display', 'block');

				$('#loadingImg').css('display', 'none'); //hide the loading gif
				$('#exibeoBoxImg').css('display', 'block');//show the image
				$('#exibeoBoxImg').unbind('load'); //unbind this listener
			});
			
			
			$('#exibeoBoxImg').attr('src', '/' + $masterDir + '/' + $album + '/' + $photoFile);
			$('#blurEffect').attr('src', '/' + $masterDir + '/' + $album + '/' + $photoFile);
			//$('#exibeoBoxImg').css('background-image', 'url("/' + $masterDir + '/' + $album + '/thumbs/' + $photoFile + '")');

		})

		.fail(function() { alert('Oops, something went wrong'); });
}

function getCaption($album, $photoFile)
{
	var jqxhr = $.get( '/get-caption/' + $album + '/' + $photoFile)
		.done(function($captionTxt){
			$('#caption').val($captionTxt);
		})
		.fail(function() { alert('Oops, something went wrong'); });
}

function saveCaption($album, $photoFile)
{
	var jqxhr = $.post( '/save-caption/' + $album + '/' + $photoFile, { caption: $('#caption').val() })
		.done(function($status){
			$('#caption').val($status);
		})
		.fail(function() { alert('Oops, something went wrong'); });

}

function closeExibeoBox()
{
	$('.exibeoBox').css('display', 'none');
	$('#exibeoBoxImg').attr('src', '');
	$('#blurEffect').attr('src', '');
	$('#exibeoBoxImg').css('background-image', 'none');
}


function stickSections()
{
	//detach the listener
	$(window).off("scroll.sectionWrapper");

	$scrollPos = $(window).scrollTop();
	$currentSticky = null;

	$('.sectionWrapper').each(function(){
		//global reset of removing any sectionFixed class
		$(this).children().removeClass('sectionFixed');

		//assign our object currentSticky
		if($scrollPos >= ($(this).offset().top - ($(this).height() - $(this).children().height() - 6)) ) $currentSticky = $(this);
		//lets apply a little fade effect
		else if($(this).offset().top - $(this).height() > 200 && $scrollPos >= ($(this).offset().top - $(this).height()  - 200) ){
			$currentSticky.children().css('opacity', (($(this).offset().top - $scrollPos) / 205) );
		}
		//reset the opacity when it is outside any effect zone
		else if($(this).offset().top - $scrollPos < 300) if($currentSticky) $currentSticky.children().css('opacity', 1);
		//use our default class
		else $(this).children().addClass('sectionEmbed');

	});

	//apply the fixed class to our scrollPos match
	if($currentSticky) $currentSticky.children().addClass('sectionFixed');;

	//reattach the listener
	$(window).on("scroll.sectionWrapper", function() {
		stickSections();		
	});
}
