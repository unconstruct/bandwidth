<?php
/**
 * The template for displaying the footer.
 *
 *
 * @package WordPress
 * @subpackage Ego
 * @since Ego 1.0
 */
?>
	<div class="clear" style="height:300px;"></div>

</div>        
<!--end wrapper--> 

<!--start footer-->
<div id="footer">
	<div class="footer_content_wrapper">
		<div class="content">
			<?php
			$networks['behance']['network_name']='Behance';
			$networks['behance']['network_class']='behance';

			$networks['deviantart']['network_name']='DeviantArt';
			$networks['deviantart']['network_class']='deviantart';

			$networks['dribbble']['network_name']='Dribbble';
			$networks['dribbble']['network_class']='dribbble';
			
			$networks['facebook']['network_name']='Facebook';
			$networks['facebook']['network_class']='facebook';
			
			$networks['flickr']['network_name']='Flickr';
			$networks['flickr']['network_class']='flickr';
			
			$networks['forrst']['network_name']='Forrst';
			$networks['forrst']['network_class']='forrst';
			
			$networks['googleplus']['network_name']='Google+';
			$networks['googleplus']['network_class']='googleplus';

			$networks['instagram']['network_name']='Instagram';
			$networks['instagram']['network_class']='instagram';
			
			$networks['linkedin']['network_name']='LinkedIn';
			$networks['linkedin']['network_class']='linkedin';
			
			$networks['myspace']['network_name']='Myspace';
			$networks['myspace']['network_class']='myspace';

			$networks['pinterest']['network_name']='Pinterest';
			$networks['pinterest']['network_class']='pinterest';
			
			$networks['rss']['network_name']='RSS';
			$networks['rss']['network_class']='rss';
			
			$networks['skype']['network_name']='Skype';
			$networks['skype']['network_class']='skype';
			
			$networks['tumblr']['network_name']='Tumblr';
			$networks['tumblr']['network_class']='tumblr';

			$networks['twitter']['network_name']='Twitter';
			$networks['twitter']['network_class']='twitter';
			
			$networks['vimeo']['network_name']='Vimeo';
			$networks['vimeo']['network_class']='vimeo';

			$networks['vine']['network_name']='Vine';
			$networks['vine']['network_class']='vine';

			$networks['youtube']['network_name']='YouTube';
			$networks['youtube']['network_class']='youtube';
			
			$snsCounter=0;
			
			$outputSns='<ul class="socialSmall">';
			
			if(returnOptionValue('sns_type_1')!='0') {
				if(isset($networks[returnOptionValue('sns_type_1')]['network_class'])) {
					$outputSns.='<li><a href="'.returnOptionValue('sns_url_1').'" title="'.$networks[returnOptionValue('sns_type_1')]['network_name'].'" class="'.$networks[returnOptionValue('sns_type_1')]['network_class'].'">'.$networks[returnOptionValue('sns_type_1')]['network_name'].'</a></li>';
					$snsCounter++;
				}
			}
			
			if(returnOptionValue('sns_type_2')!='0') {
				if(isset($networks[returnOptionValue('sns_type_2')]['network_class'])) {
					$outputSns.='<li><a href="'.returnOptionValue('sns_url_2').'" title="'.$networks[returnOptionValue('sns_type_2')]['network_name'].'" class="'.$networks[returnOptionValue('sns_type_2')]['network_class'].'">'.$networks[returnOptionValue('sns_type_2')]['network_name'].'</a></li>';
					$snsCounter++;
				}
			}
			
			if(returnOptionValue('sns_type_3')!='0') {
				if(isset($networks[returnOptionValue('sns_type_3')]['network_class'])) {
					$outputSns.='<li><a href="'.returnOptionValue('sns_url_3').'" title="'.$networks[returnOptionValue('sns_type_3')]['network_name'].'" class="'.$networks[returnOptionValue('sns_type_3')]['network_class'].'">'.$networks[returnOptionValue('sns_type_3')]['network_name'].'</a></li>';
					$snsCounter++;
				}
			}
			
			if(returnOptionValue('sns_type_4')!='0') {
				if(isset($networks[returnOptionValue('sns_type_4')]['network_class'])) {
					$outputSns.='<li><a href="'.returnOptionValue('sns_url_4').'" title="'.$networks[returnOptionValue('sns_type_4')]['network_name'].'" class="'.$networks[returnOptionValue('sns_type_4')]['network_class'].'">'.$networks[returnOptionValue('sns_type_4')]['network_name'].'</a></li>';
					$snsCounter++;
				}
			}
			
			if(returnOptionValue('sns_type_5')!='0') {
				if(isset($networks[returnOptionValue('sns_type_5')]['network_class'])) {
					$outputSns.='<li><a href="'.returnOptionValue('sns_url_5').'" title="'.$networks[returnOptionValue('sns_type_5')]['network_name'].'" class="'.$networks[returnOptionValue('sns_type_5')]['network_class'].'">'.$networks[returnOptionValue('sns_type_5')]['network_name'].'</a></li>';
					$snsCounter++;
				}
			}
			
			if(returnOptionValue('sns_type_6')!='0') {
				if(isset($networks[returnOptionValue('sns_type_6')]['network_class'])) {
					$outputSns.='<li><a href="'.returnOptionValue('sns_url_6').'" title="'.$networks[returnOptionValue('sns_type_6')]['network_name'].'" class="'.$networks[returnOptionValue('sns_type_6')]['network_class'].'">'.$networks[returnOptionValue('sns_type_6')]['network_name'].'</a></li>';
					$snsCounter++;
				}
			}
			
			$outputSns.='</ul>';
			
			if($snsCounter>0) {
				echo $outputSns;
			}
			?>
			
			<div class="logo">
				<a class="navigateTo" href="<?php echo home_url(); ?>" title="<?php bloginfo( 'name' ); ?>"><img src="<?php echo returnUploadedImageByID('footer_logo'); ?>" alt="<?php bloginfo( 'name' ); ?>" /></a>
			</div>
			<p class="copyright"><?php echo stripslashes(html_entity_decode(returnOptionValue('footer_copyright'),ENT_QUOTES,"UTF-8")); ?></p>
		</div>
	</div>
</div>
<!--end footer-->

<?php
/* Always have wp_footer() just before the closing </body>
 * tag of your theme, or you will break many plugins, which
 * generally use this hook to reference JavaScript files.
 */
wp_footer();
?>

<script type="text/javascript">

var isMobile = false, isiPad =false, iOS5=false;

if( navigator.userAgent.match(/Android/i) || 
	navigator.userAgent.match(/webOS/i) ||
	navigator.userAgent.match(/iPhone/i) || 
	navigator.userAgent.match(/iPad/i)|| 
	navigator.userAgent.match(/iPod/i) || 
	navigator.userAgent.match(/BlackBerry/i)){
		isMobile = true;
}

if(navigator.userAgent.match(/iPad/i)) {
	isiPad = true;
}

if (navigator.userAgent.match(/OS 5(_\d)+ like Mac OS X/i)) {
	iOS5=true;
}

jQuery(document).ready(function($){
	
	$(window).load(function() {

		<?php
			$s_easing=array('easeInQuad','easeInQuad','easeInOutQuad','easeInCubic','easeOutCubic','easeInOutCubic','easeInQuart','easeOutQuart','easeInOutQuart','easeInQuint','easeOutQuint','easeInOutQuint','easeInSine','easeOutSine','easeInOutSine','easeInExpo','easeOutExpo','easeInOutExpo','easeInCirc','easeOutCirc','easeInOutCirc');	
		?>
		epic_easing_str='<?php echo $s_easing[intval(returnOptionValue('epic_easing'))]; ?>';
		
		var use_epic_slideshow=false;
		var use_epic_autoplay=false;
		
		if(udt_global_vars.epic_slideshow=='1') {
			use_epic_slideshow=true;
		}
		if(udt_global_vars.epic_autoplay=='1') {
			use_epic_autoplay=true;
		}
		
		//call slider
		$('.epicSlider').epicSlider({
			loop : true,															//Boolean: whether slideshow should loop or not	
			slideShow: use_epic_slideshow,											//Boolean: use slideshow or not
			autoPlay: use_epic_autoplay,											//Boolean: autoplay uplon load or not
			slideShowInterval : parseInt(udt_global_vars.epic_slide_timer,10),			//Integer: slideshow cycling speed, in milliseconds
			transitionSpeed : parseInt(udt_global_vars.epic_slide_animation_speed,10),	//Integer: transitions speed, in milliseconds
			startSlide : 0,															//Integer: starts at 0
			shuffleSlides:false,													//Boolean: shuffle slides or not
			easing : epic_easing_str, 												//String: easing method - see http://jqueryui.com/demos/effect/easing.html
			fx : udt_global_vars.epic_slide_animation,									//String: none, fade, leftToRight, topToBottom 
			fxmobile : udt_global_vars.epic_mobile_slide_animation,						//String: mobile effect -  none, fade, leftToRight, topToBottom 
			pattern :true															//Boolean: add pattern or not
		});
	});
});

jQuery(document).ready(function($){
	/*call flexslider*/
	$('.flexslider').flexslider({
		animation: "fade",
		slideDirection: "horizontal",
		slideshow: parseInt(udt_global_vars.flexslider_autoplay),
		slideshowSpeed: parseInt(udt_global_vars.flexslider_slide_timer),
		animationDuration: parseInt(udt_global_vars.flexslider_slide_animation_speed),
		directionNav: true,
		controlNav: true
	});
});

</script>
<?php //SoundManager2 ?>
<script type="text/javascript">

soundManager.setup({
  url: '<?php echo get_template_directory_uri();?>/js/swf/',
  preferFlash: true
});
</script>
</body>
</html>