<?php

if(!defined('ABSPATH')) exit;

/*
Plugin Name:  Tag Suggest
Plugin URI:   https://www.arkkod.com/
Description:  Automatic Tag Suggestion Plugin on Google Recommended Searches
Version:      1.0
Author:       Orçun Tuna
Author URI:   https://www.arkkod.com
Text Domain:  tag-suggest
*/

$imgurl = plugins_url('assets/img/',__FILE__ );
$apiurl = 'http://clients1.google.com/complete/search?hl=en&output=toolbar&q=';

/* Register meta box */
function tag_suggest_box() {
    add_meta_box( 'meta-box-id', __( 'Tag Suggest', 'tag-suggest' ), 'tag_suggest_box_html', 'post' );
}
add_action( 'add_meta_boxes', 'tag_suggest_box' );
 
/* Meta box display callback */
function tag_suggest_box_html( $post ) {
	global $imgurl;
	?>

		<div class="tsdiv">
			<div class="tssearch">
				<input type="text" class="form-input-tip" id="ts_form_input" name="tskeyword" placeholder="<?php echo __('Keyword', 'tag_suggest')?>">
				<a href="javascript:void(0)" class="button-primary" id="ts_form_button" value="submit"><?php echo __('Show', 'tag_suggest')?></a>
				<img src="<?php echo $imgurl; ?>spinner.gif">
			</div>
			<div class="tsresults" id="tsresults"><ul></ul></div>
		</div>

	<?php
}
 
add_action( 'admin_footer', 'ts_ajax_script' ); 

function ts_ajax_script() { 
	global $imgurl;
	?>
	<script type="text/javascript" >

	jQuery("#ts_form_button").click(function() {

		$(".tssearch img").show();
		$(".tssearch img").attr('src', '<?php echo $imgurl ?>spinner.gif');

		var data = {
			'action': 'my_action',
			'keyword': $("#ts_form_input").val()
		};

		jQuery.post(ajaxurl, data, function(response) {
			//alert(response);
			var list = JSON.parse(response);
			$(list).each(function(index,value){
				var nowhtml = $("#tsresults ul").html();
				var valuehtml = '<li><a href="javascript:void(0)" onclick="ts_add(\''+value+'\')">'+value+'</a></li>';
				$("#tsresults ul").html(nowhtml + valuehtml);
			});
			$(".tssearch img").attr('src', '<?php echo $imgurl ?>ok.png');
		});

		
	});
	function ts_add(value){

		value = value.trim();
		$("#new-tag-post_tag").val(value);
		$("#tagsdiv-post_tag input.tagadd").click();
		$("#tsresults li").filter(function() {
		    return $(this).text() === value;
		}).addClass("selected");
		
	}

	</script> <?php
}

add_action( 'wp_ajax_my_action', 'ts_ajax_action' );

function ts_ajax_action() {
	global $wpdb;
	global $apiurl;

	$keyword = trim($_POST['keyword']);
	$keyword_output = urlencode($keyword);

	$html = file_get_contents($apiurl . $keyword_output);
	preg_match_all ('/data="(.*)"\/>/U', $html, $_output);
	$output = $_output[0];
	foreach ($output as $key => $value) {
		$output[$key] = str_replace('data="','',$output[$key]);
		$output[$key] = str_replace('"/>','',$output[$key]);
		$output[$key] = utf8_encode($output[$key]);
	}
	$json = json_encode($output);
	echo $json;

	wp_die();
}

function ts_assets() {
    wp_register_style('ts_assets', plugins_url('assets/css/tag-suggest.css',__FILE__ ));
    wp_enqueue_style('ts_assets');
}

add_action( 'admin_init','ts_assets');