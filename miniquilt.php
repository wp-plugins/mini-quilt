<?php
/*
Plugin Name: Mini Quilt
Plugin URI: http://www.ikirudesign.com/plugins/mini-quilt/
Description: A stand-alone implementation of the <a href="http://www.ikirudesign.com/themes/kaleidoscope/">Kaleidoscope theme</a>'s Mini Quilt sidebar element.
Author: David Hayes
Version: 0.5.0
Author URI: http://www.davidbhayes.com/
License: GPL 2.0 (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
*/

function mq_css() {
  mq_load_options();
  global $patches_across, $rows, $patch_width;
  $main_width = ($patch_width+4)*$patches_across;
//  $sheet_url = get_bloginfo('wpurl')."/wp-content/plugins/miniquilt/mqstyle.php";
//  echo '<link rel="stylesheet" type="text/css" href="'.$sheet_url.'" />';
/*
  Yes, I know that !important declarations are bad CSS. 
  But when you need to override styles while contending with an 
  unpredictable structure it's quite nearly the only option. 
*/
?>
<style>
ul.miniquiltbox {
  margin-left: auto !important;
  margin-right: auto !important;
  padding: 1px !important;
  background-color: white;
  overflow: auto;
  list-style: none inside none !important;
  letter-spacing: 1;
  width: <?php echo $main_width ?>px !important;
  border: 1px solid #999 !important;
}
ul.miniquiltbox li {
  float: left !important;
  margin: 0 !important;
  padding: 0 !important;
  list-style: none inside none !imporant;
  clear: none !important;
  border: none !important;
  background: none !important;
}
ul.miniquiltbox li:before {
  content: ""  !important; /*This is the override Kubrick's oddball list style*/
}
ul.miniquiltbox li a {
  display: block !important;
  width: <?php echo $patch_width; ?>px !important;
  height: <?php echo $patch_width; ?>px !important;
  border: 2px solid white !important;
  margin: 0 !important;
  padding: 0 !important;
}
ul ul.miniquiltbox li a:hover {
  border: 2px solid #222 !important;
    margin: 0 !important;
  padding: 0 !important;
}
</style>
<?php 
}
add_filter('wp_head', 'mq_css');

function mq_length_fix($x){
    if(strlen($x)<2){
        $x = sprintf("0%s",$x);
    }
    return $x;
}

function mq_cos_color ($pshift) {
  // the color's value
  $hbase=.82; //$hbase 0<$hsafe<2; ~2 bright/white, ~0 dark/black
  
  // the colors saturation
  global $postyear;
  $yeardiff = date(Y)-$postyear;  // $hshift 1-->0
  $hshift = .08*$yeardiff;          // $hshift is how much to change tint/shade /yr
  
  if ($hshift > 1) { //this assures that the colors are always valid (after years of darkening)
    $hshift = 1;
  }
  
  if ($hbase > 1) {     // assuring $hsafe is always <= 1, so that wave peaks are still less than 255 
    $hsafe = 2-$hbase;  // $hsafe assures that all colors will be valid 
  }
  if ($hbase <= 1) {  //when we don't seen to worry about height
    $hsafe = $hbase;
  }
   
  // change the first (and only the first) +/- to toggle fade/darken
  $coscol = dechex(127.5*(($hbase-($hsafe*$hshift))+(($hsafe-($hsafe*$hshift))*(cos((M_PI*($pshift))/180)))));
  return $coscol;
}

function mq_broaden_shift($indeg, $broaden=0, $shift=0) { //$range is roughly the length you want to spend on the peak
  //$newx = $x;  // it's best to avoid values for $range above 75, the curves get wonky
  $outdeg = $shift+$indeg-($broaden*sin((M_PI*($indeg+$shift))/180));
  return $outdeg;
}

function mq_date_to_color($day,$year) {
  global $postyear;
  $postyear = $year;

  $degrees = .986*$day; // beacuse there are 360, not 365.24, degrees in a circle
  if (get_option(southern_hemisphere)) {$degrees = $degrees+180;}
  $redshift = mq_broaden_shift($degrees, 18, 134); //Best Values: 18, 134
  $greenshift = mq_broaden_shift($degrees, 20, 240);//Best Values: 20, 240
  $blueshift = mq_broaden_shift($degrees, 10); //5, 0
  $redhex = mq_cos_color($redshift);
  $greenhex = mq_cos_color($greenshift);
  $bluehex = mq_cos_color($blueshift);
  $redfix = mq_length_fix($redhex);
  $greenfix = mq_length_fix($greenhex);
  $bluefix = mq_length_fix($bluehex);
  
  $bestrgb = sprintf("%s%s%s",$redfix,$greenfix,$bluefix);
  return $bestrgb;
}

function mq_mini_quilt ($args) { 
    extract($args);
    mq_load_options();
    global $patches_across, $rows, $patch_width;
    $total_patches = $patches_across*$rows;
    echo $before_widget;
    echo $before_title.'The Mini Quilt'.$after_title; ?>
      <ul class="miniquiltbox">
        <?php
            $recentPosts = new WP_Query();
            $query = 'showposts='.$total_patches.'&caller_get_posts=1';
            $recentPosts->query($query);
         while ($recentPosts->have_posts()) : $recentPosts->the_post(); 
          $test_id = get_the_ID(); ?>
          <li><a style="background: #<?php if (is_single($test_id)) {echo 'bbb';} else {echo mq_date_to_color(get_the_time(z), get_the_time(Y));}; ?> !important;" href="<?php the_permalink() ?>" rel="bookmark" title="&#8220;<?php the_title(); ?>&#8221; from <?php the_time('d M Y'); ?>"></a></li>
      	<?php endwhile; ?>
      </ul>
    <?php echo $after_widget;
}

function mq_control() {
    if (!get_option('mq_control')) {$options = array('patch_width'=>20,'patches_across'=>8,'rows'=>6);}
      else {$options = get_option('mq_control');}
    
    if ($_POST['mq_control-submit']) {
      $options = array('patch_width' => $_POST['mq_control-patch_width'], 'patches_across' => $_POST['mq_control-patches_across'], 'rows' => $_POST['mq_control-rows']);
      update_option('mq_control', $options);
    }
     
    echo '<p>Patch Size (in pxs):<br /><input type="text" name="mq_control-patch_width" value="'.$options['patch_width'].'" id="mq_quilt-patch_width" /></p>';
    echo '<p>Columns:<br /><input type="text" name="mq_control-patches_across" value="'.$options['patches_across'].'" id="mq_control-patches_across" /></p>';  
    echo '<p>Rows:<br /><input type="text" name="mq_control-rows" value="'.$options['rows'].'" id="mq_control-rows" /></p>';
    echo '<input type="hidden" id="mq_control-submit" name="mq_control-submit" value="1" />';
}

function mq_load_options() {
    $options = get_option('mq_control');
    global $patches_across, $rows, $patch_width;
    $patches_across = $options['patches_across'];
    $rows = $options['rows'];
    $patch_width = $options['patch_width'];
}
function widget_myuniquewidget_register() {
  register_sidebar_widget('Mini Quilt','mq_mini_quilt');
  register_widget_control('Mini Quilt','mq_control');
}
add_action('init', widget_myuniquewidget_register);

?>
