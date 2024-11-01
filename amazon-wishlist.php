<?php
/*
Plugin Name: Amazon Wishlist
PLugin URI: http://jfoucher.com/2009/05/19/amazon-wishlist-plugin-for-wordpress-27
Description: Add an amazon wishlist widget with all the sorting options to your blog.
Author: Jonathan Foucher
Version: 0.2
Author URI: http://jfoucher.com/


Copyright 2009 Jonathan Foucher

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

*/


if (!class_exists('Jfoucher_rss')) {
	class Jfoucher_rss {

		// Class initialization
		function Jfoucher_rss() {
			if (isset($_GET['show_jfoucher_widget'])) {
				if ($_GET['show_jfoucher_widget'] == "true") {
					update_option( 'show_jfoucher_widget', 'noshow' );
				} else {
					update_option( 'show_jfoucher_widget', 'show' );
				}
			} 
		
			// Add the widget to the dashboard
			add_action( 'wp_dashboard_setup', array(&$this, 'register_widget') );
			add_filter( 'wp_dashboard_widgets', array(&$this, 'add_widget') );
		}

		// Register this widget -- we use a hook/function to make the widget a dashboard-only widget
		function register_widget() {
			wp_register_sidebar_widget( 'jfoucher_rss', __( 'Jfoucher.com', 'jfoucher-rss' ), array(&$this, 'widget'), array( 'all_link' => 'http://yoast.com/', 'feed_link' => 'http://jfoucher.com/feed/' ) );
		}

		// Modifies the array of dashboard widgets and adds this plugin's
		function add_widget( $widgets ) {
			global $wp_registered_widgets;
			if ( !isset($wp_registered_widgets['jfoucher_rss']) ) return $widgets;
			array_splice( $widgets, 2, 0, 'jfoucher_rss' );
			return $widgets;
		}

		function widget($args = array()) {
			$show = get_option('show_jfoucher_widget');
			if ($show != 'noshow') {
				if (is_array($args))
					extract( $args, EXTR_SKIP );
				echo $before_widget.$before_title.$widget_name.$after_title;
				echo '<a href="http://jfoucher.com/"><img style="margin: 0 0 5px 5px;" src="http://jfoucher.com/img/mangatar-sm.jpg" align="right" alt="Jfoucher.com"/></a>';
				include_once(ABSPATH . WPINC . '/rss.php');
				$rss = fetch_rss('http://jfoucher.com/feed/');
				if ($rss) {
					$items = array_slice($rss->items, 0, 3);
					if (empty($items)) 
						echo '<li>No items</li>';
					else {
						foreach ( $items as $item ) { ?>
						<a style="font-size: 14px; font-weight:bold;" href='<?php echo $item['link']; ?>' title='<?php echo $item['title']; ?>'><?php echo $item['title']; ?></a><br/> 
						<p style="font-size: 10px; color: #aaa;"><?php echo date('j F Y',strtotime($item['pubdate'])); ?></p>
						<p><?php echo $item['description']; ?></p>
						<?php }
					}
				}
				echo $after_widget;
			}
		}
	}

	// Start this plugin once all other plugins are fully loaded
	add_action( 'plugins_loaded', create_function( '', 'global $Jfoucher_rss; $Jfoucher_rss = new Jfoucher_rss();' ) );
}


if (!extension_loaded('curl')) {
 dl('curl.' . PHP_SHLIB_SUFFIX);
}
function amazon_wishlist($args){
		extract($args);
 
		// These are our own options
		$options = get_option('widget_az');
		$title = empty( $options['title'] ) ? __( 'Amazon Wishlist' ) : apply_filters('widget_title', $options['title']);   // Title in sidebar for widget
		$max_items = $options['show']+1;  // # of Posts we are showing
		$tri = $options['tri'];  // Tri
		$wishlistid=$options['wishlistid'];
		$thsize=$options['thsize'];
if (file_exists($_SERVER['DOCUMENT_ROOT']."/wp-content/cache/amazon.html")){
$filedate=filemtime($_SERVER['DOCUMENT_ROOT']."/wp-content/cache/amazon.html");
}else{
$filedate=0;
 if (!@touch($_SERVER['DOCUMENT_ROOT']."/wp-content/cache/amazon.html")){
 echo '<p class="error">Your "wp-content/cache" folder is not writable<br />Please correct this before continuing.</p>';
 }
		
}
$cacheage=time()-$filedate;
if ($cacheage >= 3600*24){
$file=fopen($_SERVER['DOCUMENT_ROOT']."/wp-content/cache/amazon.html","w");
$ch = curl_init();

// Configuration de l'URL et d'autres options
curl_setopt($ch, CURLOPT_URL, "http://www.amazon.com/gp/registry/registry.html?ie=UTF8&type=wishlist&id=$wishlistid&sort=$tri");
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
// Récupération de l'URL et affichage sur le naviguateur


$page = curl_exec($ch);

$page=ereg_replace("<html>(.*)<form method=\"post\" name=\"editItems\"([^<]*)","",$page);
$page=ereg_replace("</table>
</form>(.*)","</table>",$page);
$page=trim(utf8_encode($page));

$items=split("</tbody>
<tbody name[^<]*",$page,$max_items);

fwrite ($file,"<div class=\"amazon-wishlist\">");
for ($i=0;$i<count($items)-1;$i++){
$item=$items[$i];
$item=ereg_replace("<input type=\"hidden\"([^<]*)>","",$item);
$item=ereg_replace("</?tbody([^<]*)>","",$item);
$item=ereg_replace("</?table([^<]*)>","",$item);
$item=ereg_replace("</?td([^<]*)>","",$item);
$item=ereg_replace("</?tr([^<]*)>","",$item);
ereg("EUR ([^<]*)",$item,$prix);
ereg("\$([^<]*)",$item,$prix);
$prix=$prix[1];



ereg("<strong>[^<]*<a href=\"([^\"]*)\">([^<]*)</a>[^<]*</strong>",$item,$data);
$url=$data[1];
$booktitle=$data[2];
$encurl="http://www.amazon.com/gp/redirect.html?ie=UTF8&location=".urlencode($url)."&tag=lesenever-21&linkCode=ur2&camp=1642&creative=6746";

ereg("\t de ([^\(]*) \(Auteur\)",$item,$aut);
ereg("\t by ([^\(]*) \(Author\)",$item,$aut);
$auteur=$aut[1];

ereg("<img src=\"([^\"]*)\" ([^>]*)>",$item,$im);
$imgurl=$im[1];
$imgurl=ereg_replace("_SL110_","_SL500_AA240_",$imgurl);
ereg("dp/([^/]*)/",$url,$id);
$prodid=$id[1];


fwrite($file,"
<a href=\"$encurl\" title=\"See $booktitle on Amazon\"><img src=\"/wp-content/plugins/amazon-wishlist/imgsquare.php?thsize=$thsize&imgurl=$imgurl&prodid=$prodid\" alt=\"$booktitle\" height=\"$thsize\" width=\"$thsize\" class=\"flickr\" /></a><img src=\"http://www.assoc-amazon.fr/e/ir?t=lesenever-21&l=as2&o=8&a=$prodid\" width=\"1\" height=\"1\" border=\"0\" alt=\"\" style=\"border:none !important; margin:-1px !important; padding:0 !important\" />");

/*
fwrite($file,"
<a href=\"http://www.amazon.com/gp/product/$prodid?ie=UTF8&tag=lesenever-21&linkCode=as2&camp=1642&creative=6746&creativeASIN=$prodid\" title=\"Voir $booktitle chez amazon.fr\"><img src=\"/wp-content/plugins/amazon-wishlist/imgsquare.php?thsize=$thsize&imgurl=$imgurl&prodid=$prodid\" alt=\"$booktitle\" height=\"$thsize\" width=\"$thsize\" class=\"flickr\" /></a><img src=\"http://www.assoc-amazon.fr/e/ir?t=lesenever-21&l=as2&o=8&a=$prodid\" width=\"1\" height=\"1\" border=\"0\" alt=\"\" style=\"border:none !important; margin:-1px !important; padding:0 !important\" />");
*/
}

fwrite($file,"</div>");
echo file_get_contents($_SERVER['DOCUMENT_ROOT']."/wp-content/cache/amazon.html");
}else{
echo file_get_contents($_SERVER['DOCUMENT_ROOT']."/wp-content/cache/amazon.html");
}
}

function widget_az($args) {
 
		// "$args is an array of strings that help widgets to conform to
		// the active theme: before_widget, before_title, after_widget,
		// and after_title are the array keys." - These are set up by the theme
		extract($args);
 
		// These are our own options
		$options = get_option('widget_az');
		$title = empty( $options['title'] ) ? __( 'Amazon Wishlist' ) : apply_filters('widget_title', $options['title']); // Title in sidebar for widget
		$show = $options['show'];  
		$tri = $options['tri'];  
		$wishlistid=$options['wishlistid'];

                      if ($show<1) $show = 1;
		if ($exclude=="") $exclude = "0";
		$before_title='<a href="http://www.amazon.fr/gp/registry/wishlist/'.$wishlistid.'?reveal=unpurchased&filter=all&sort='.$tri.'&layout=standard&x=14&y=18">';
		$after_title='</a>';
echo $before_widget . $before_title . $title . $after_title;
echo amazon_wishlist($args);
echo $after_widget;

}

function widget_az_control() {
 if (!@touch($_SERVER['DOCUMENT_ROOT']."/wp-content/cache/amazon.html")){
 echo '<p class="error">Your "wp-content/cache" folder is not writable<br />Please correct this before continuing.</p>';
 }
		// Get options
		$options = get_option('widget_az');
		// options exist? if not set defaults
		if ( !is_array($options) )
			$options = array('title'=>'Amazon Wishlist', 'show'=>'5', 'tri'=>'priority');
					if ( $_POST['az-submit'] ) {
 
			// Remember to sanitize and format use input appropriately.
			$options['title'] = strip_tags(stripslashes($_POST['az-title']));
			$options['show'] = strip_tags(stripslashes($_POST['az-show']));
			$options['tri'] = strip_tags(stripslashes($_POST['az-tri']));
$options['wishlistid'] = strip_tags(stripslashes($_POST['az-wishlistid']));
$options['thsize'] = strip_tags(stripslashes($_POST['az-thsize']));
			update_option('widget_az', $options);
		}
		
		// Get options for form fields to show
		$title = htmlspecialchars($options['title'], ENT_QUOTES);
		$show = htmlspecialchars($options['show'], ENT_QUOTES);
		$tri = htmlspecialchars($options['tri'], ENT_QUOTES);
$wishlistid = htmlspecialchars($options['wishlistid'], ENT_QUOTES);
		$thsize = htmlspecialchars($options['thsize'], ENT_QUOTES);
		// The form fields
		echo '<p style="text-align:right;">
				<label for="az-title">' . __('Title:') . '
				<input style="width: 200px;" id="az-title" name="az-title" type="text" value="'.$title.'" />
				</label></p>';
		echo '<p style="text-align:right;">
				<label for="az-show">' . __('Show:') . '
				<input style="width: 200px;" id="az-show" name="az-show" type="text" value="'.$show.'" />
				</label></p>';
		echo '<p style="text-align:right;">
				<label for="az-wishlistid">' . __('Wishlist ID :') . '
				<input style="width: 200px;" id="az-wishlistid" name="az-wishlistid" type="text" value="'.$wishlistid.'" />
				</label></p>';
		echo '<p style="text-align:right;">
				<label for="az-thsize">' . __('Thumbnail size:') . '
				<input style="width: 200px;" id="az-thsize" name="az-thsize" type="text" value="'.$thsize.'" />
				</label></p>';
		echo '<p style="text-align:right;">
				<label for="az-tri">' . __('Tri :') . '
				<select style="width: 200px;" id="az-tri" name="az-tri">
				';
				?>
  <option value="price" <?php if ($tri =="price") echo "selected=\"selected\""; ?>>Prix (du plus petit au plus grand)</option>
  <option value="price-desc" <?php if ($tri =="price-desc") echo "selected=\"selected\""; ?>>Prix (du plus grand au plus petit)</option>
  <option value="title" <?php if ($tri =="title") echo "selected=\"selected\""; ?>>Titre</option>
  <option value="priority" <?php if ($tri =="priority") echo "selected=\"selected\""; ?>>Priorit&eacute;</option>
  <option value="date-added" <?php if ($tri =="date-added") echo "selected=\"selected\""; ?>>Date d'ajout</option>
  <option value="last-updated" <?php if ($tri =="last-updated") echo "selected=\"selected\""; ?>>Mise &agrave; jour</option>

</select>
			
				</label></p>
				<?php

		echo '<input type="hidden" id="az-submit" name="az-submit" value="1" />';

			
			
			}
			function widget_az_init(){
// Register widget for use
	register_sidebar_widget(array('Amazon Wishlist', 'widgets'), 'widget_az');
 
	// Register settings for use, 300x100 pixel form
	register_widget_control(array('Amazon Wishlist', 'widgets'), 'widget_az_control', 300, 200);
}
 
// Run code and init
add_action('widgets_init', 'widget_az_init');

?>
