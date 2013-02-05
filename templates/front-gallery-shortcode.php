<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
  <script type="text/javascript">
  $(function() {
    $('img.image1').data('ad-desc', 'Whoa! This description is set through elm.data("ad-desc") instead of using the longdesc attribute.<br>And it contains <strong>H</strong>ow <strong>T</strong>o <strong>M</strong>eet <strong>L</strong>adies... <em>What?</em> That aint what HTML stands for? Man...');
    $('img.image1').data('ad-title', 'Title through $.data');
    $('img.image4').data('ad-desc', 'This image is wider than the wrapper, so it has been scaled down');
    $('img.image5').data('ad-desc', 'This image is higher than the wrapper, so it has been scaled down');
    var galleries = $('.ad-gallery').adGallery();
    setTimeout(function() {
      galleries[0].addImage("images/thumbs/t7.jpg", "images/7.jpg");
    }, 1000);
    setTimeout(function() {
      galleries[0].addImage("images/thumbs/t8.jpg", "images/8.jpg");
    }, 2000);
    setTimeout(function() {
      galleries[0].addImage("images/thumbs/t9.jpg", "images/9.jpg");
    }, 3000);
    setTimeout(function() {
      galleries[0].removeImage(1);
    }, 4000);
    
    $('#switch-effect').change(
      function() {
        galleries[0].settings.effect = $(this).val();
        return false;
      }
    );
    $('#toggle-slideshow').click(
      function() {
        galleries[0].slideshow.toggle();
        return false;
      }
    );
    $('#toggle-description').click(
      function() {
        if(!galleries[0].settings.description_wrapper) {
          galleries[0].settings.description_wrapper = $('#descriptions');
        } else {
          galleries[0].settings.description_wrapper = false;
        }
        return false;
      }
    );
  });
  </script>

<div id="gallery" class="ad-gallery">
	<div class="ad-image-wrapper">
	</div>
	<div class="ad-controls">
	</div>
	<div class="ad-nav">
		<div class="ad-thumbs">
			<ul class="ad-thumb-list">
				<li>
					<a href="images/1.jpg">
						<img src="images/thumbs/t1.jpg" class="image0">
					</a>
				</li>
				<li>
					<a href="images/2.jpg" id="t2">
						<img src="images/thumbs/t2.jpg" title="A title for 2.jpg" alt="This is a nice, and incredibly descriptive, description of the image 2.jpg" class="image6">
					</a>
				</li>
				<li>
					<a href="images/3.jpg">
						<img src="images/thumbs/t3.jpg" title="A title for 3.jpg" alt="This is a nice, and incredibly descriptive, description of the image 3.jpg" class="image7">
					</a>
				</li>
			</ul>
		</div>
	</div>
</div>
