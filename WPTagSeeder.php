<?php
/*
Plugin Name: WP Tag Seeder
Plugin URI: https://teknikforce.com/
Description: With the help of keyword you can add Tag on Post. 
Author: TEKNIKFORCE
Version: 1.0.0
*/
if(!function_exists('add_action')){
//someone is trying to execute the plugin outside of wordpress
die();
}

//add_action('admin_menu', 'tag_adder_menu');
add_option("tag_addor_sensetive", '0', 'Tag Addor Case sensitivity', 'yes');
add_option("tag_addor_mwworld", '1', 'Tag Addor For Maching Whole World', 'yes');
add_option("tag_addor_scanposttitle", '1', 'Tag For Scan Post Titles For Keywords', 'yes');
add_option("tag_addor_autotag", '1', 'Tag Addor For Auto Tag New Posts', 'yes');
add_option("tag_addor_tags", "Music, singer ,song, instruments, chartbuster\nLanguage,French, German, Russian, Lebanese, English, Chinese", 'Tag addor tags', 'yes');
add_option("totalmatchintitle", "0","Total matches in title" , 'yes');
add_option("total_matched_inbody", "1","Total matches in body" , 'yes');
add_action('publish_post', 'tagseeder_tnf_tagativator');
add_action('admin_head', 'tagseeder_head_tag');
add_action('ajax_call', '0');



add_action('admin_menu','tagseeder_menu');

    add_action('admin_enqueue_scripts','tagseeder_AdminScript');
if(!function_exists('tagseeder_AdminScript'))
{

function tagseeder_AdminScript()
    {
                    wp_enqueue_script('jquery');

      wp_register_style('tagseeder_admin_bootstrap',plugins_url('assets/bootstrap/css/bootstrap.min.css',__FILE__));
      wp_enqueue_style('tagseeder_admin_bootstrap');
      
      wp_register_script('tagseeder_admin_bootstrap_js',plugins_url('assets/bootstrap/js/bootstrap.min.js',__FILE__),array('jquery'));
      wp_enqueue_script('tagseeder_admin_bootstrap_js');  


     
        }
    }



if(!function_exists('tagseeder_tnf_tagativator')){

function tagseeder_tnf_tagativator($post_ID){
  
    if(get_option('tag_addor_autotag')){
    @$res=tagseeder_tnf_autotag($post_ID);   
   }
    
}
}

if(!function_exists('tagseeder_head_tag')){

function tagseeder_head_tag(){
    if (@$_POST["starts1"] && wp_verify_nonce($_POST['tagseedercsrfval'],'tagseedercsrfval')) 
    {
        $option = 1000;
        update_option('ajax_call', '0');
        echo <<< TNF

<script type="text/javascript" >
var int=self.setInterval("ajax_tag_adder()",$option);

function ajax_tag_adder(){
jQuery(document).ready(function($) {
     
	var data = {
		action: 'ajax_sender'
	};
   
	jQuery.post(ajaxurl, data, function(response) {
		if((response=="-1") || (response=="fail"))
     int=window.clearInterval(int);
          
        var ce = document.getElementById("ajax_show_count").innerHTML;
        
        if((response !== '-1'))
            ce =  response;
            if((response == '-1'))
            ce =  "<br />The process of tag addition is finished!";
            //alert(response);
       document.getElementById("ajax_show_count").innerHTML = ce;
        
	});
});
}
</script>
TNF;
    }
}
}
add_action('wp_ajax_ajax_sender', 'tagseeder_ajaxtag_adder');

if(!function_exists('tagseeder_ajaxtag_adder')){

function tagseeder_ajaxtag_adder(){
    
    global $wpdb;
    @$option = get_option("ajax_call") ;
    //$pfx = $wpdb->prefix;
     @$ajax_posts = $wpdb->get_col("SELECT id FROM $wpdb->posts WHERE `post_status` = 'publish' AND id > $option limit 10");
     
     if(count($ajax_posts) == 0){
         echo '-1';
         die();
     }
             @$total_no_of_matchs = 0;
            
            foreach ($ajax_posts as $postid) {
                               
             @$total_no_of_matchs = $total_no_of_matchs +   tagseeder_tnf_autotag($postid);
                
            }
             update_option('ajax_call', $postid );
             
             echo  "Processing for Post ID $postid";
              die();

    
}
}


if(!function_exists('tagseeder_tnf_autotag')){


function tagseeder_tnf_autotag($post_ID){
   

    @$tag_addor_keys = get_option('tag_addor_tags');
    @$tag_addor_casesensetive = get_option('tag_addor_sensetive');
    @$tag_addor_mwholeworld = get_option('tag_addor_mwworld');
    @$tag_addor_scanposttitle = get_option('tag_addor_scanposttitle');
    
    
    @$tag_addor_keys1 = explode("\n", $tag_addor_keys);
    
     global $post;
    global $wpdb;
    $table_name = $wpdb->prefix . "posts";
    
    @$maches_tag = array();
       @$total_matched_inbody = get_option('total_matched_inbody');
       @$total_match_in_title = get_option('totalmatchintitle');
    $tagset = 0;
    foreach($tag_addor_keys1 as $tag_addor_keys_val){
        @$tag_addor_keys_val = explode(",", $tag_addor_keys_val);
        @$tag_name=$tag_addor_keys_val[0];
        $allbody=0;
        $alltitle=0;  
        foreach ($tag_addor_keys_val as $tag_addor_keys_val1){
            @$tag_addor_keys_val1 = trim($tag_addor_keys_val1);
           // wp_set_post_tags($post_ID,$tag_name,TRUE);
            if($tag_addor_keys_val1 != ''){
                @$tag_id=$post_ID;
                @$searchable_post = get_post($post_ID);
               // wp_set_post_tags($tag_id,$tag_name,TRUE);
                if(@$tag_addor_scanposttitle){
                    
                @$key_search = "  " . $searchable_post->post_title . " " . $searchable_post->post_content;     
                    
                }     
                 else {
                     
                @$key_search = "  " . $searchable_post->post_content;   
                     
                 }
                 
                @$ttag=$tag_addor_keys_val1;
                @$tag_addor_keys_val1= tagseeder_normalize_string($tag_addor_keys_val1);
                //@$tag_addor_keys_val1=strtolower($tag_addor_keys_val1);
                
                if($tag_addor_casesensetive){
                    @$allbody =  $allbody + (preg_match_all("/\b$tag_addor_keys_val1\b/u", (tagseeder_normalize_string($searchable_post->post_content)),$maches_tag));
                    
                    @$alltitle = $alltitle + (preg_match_all("/\b$tag_addor_keys_val1\b/u", (tagseeder_normalize_string($searchable_post->post_title)),$maches_tag));
                   
                    //wp_set_post_tags($tag_id,"$tag_name",TRUE);
                }
                else {
                    @$allbody = $allbody + (preg_match_all("/\b$tag_addor_keys_val1\b/iu", (tagseeder_normalize_string($searchable_post->post_content)),$maches_tag)); 
                    @$alltitle = $alltitle + (preg_match_all("/\b$tag_addor_keys_val1\b/iu", (tagseeder_normalize_string($searchable_post->post_title)),$maches_tag)); 
                }
          
                //wp_set_post_tags('1528',$searchable_post->post_title,TRUE);
                if (($tag_addor_mwholeworld && ( $allbody >= $total_matched_inbody) && ( $alltitle >= $total_match_in_title))
                        || (!$tag_addor_mwholeworld && (!$tag_addor_casesensetive && (strpos(strtolower($key_search),
           strtolower($tag_addor_keys_val1))) || ($tag_addor_mwholeworld && ($tag_addor_casesensetive && strpos(($key_search), ($tag_addor_keys_val1))))))                       
                        ) {
                    
                @$tag_name=trim($tag_name);
               
@$tagset=$tagset+1;


wp_set_post_tags($tag_id,$tag_name,TRUE);
//echo  $tagset;
break;
        }                 
            }
        }
    }//echo  $tagset;
   return $tagset; 
}


}

if(!function_exists('tagseeder_menu')){

function tagseeder_menu(){
    add_options_page("WP Tag Seeder", 'WP Tag Seeder', '8', 'tnf_tag_adder_slug', 'tagseeder_tag_adder');
}
}

if(!function_exists('tagseeder_tag_adder')){

function tagseeder_tag_adder(){
    global $wpdb;
    echo "<div class='wrap'>";
   if(isset($_POST['tnf_tag_adder_save']) && $_POST["user_tag_addor"] && wp_verify_nonce($_POST['tagseedercsrf'],'tagseedercsrf'))

   {
     @$tag_addor_sensetive = sanitize_text_field($_POST["tnf_case_sensetive"]);
     @$tag_addor_mwworld = sanitize_text_field($_POST["tnf_mwworld"]);
     @$tag_addor_scanposttitle = sanitize_text_field($_POST["tnf_scan_posttitle"]);
     @$tag_addor_autotag = sanitize_text_field($_POST["tnf_autotag"]);
     @$total_matched_keyword_inbody =  sanitize_text_field($_POST["total_matched_keyword_inbody"]);
     @$total_matched_keyword_title =  sanitize_text_field($_POST["total_matched_keyword_title"]);
     update_option("tag_addor_sensetive", $tag_addor_sensetive);
     update_option("tag_addor_mwworld", $tag_addor_mwworld);
     update_option("tag_addor_scanposttitle", $tag_addor_scanposttitle);
     update_option("tag_addor_autotag", $tag_addor_autotag);
     update_option("totalmatchintitle", $total_matched_keyword_title);
     update_option("total_matched_inbody", $total_matched_keyword_inbody);
     
       
        @$user_tag_addor = stripslashes(sanitize_text_field($_POST["user_tag_addor"]));
        update_option('tag_addor_tags', $user_tag_addor);
        
        echo "<div class='updat ed'>You have saved WP Tag seeder setting.</div>";
   }
   
   if (isset($_POST["addtag"])) {
       // ini_set('max_execution_time', 300); // 
        @$myposts = $wpdb->get_col("SELECT id FROM `$wpdb->posts` WHERE `post_status` = 'publish'");
        @$nofm = 0;
            //Music,singer,song,instruments Jangal,forest,tree
            foreach ($myposts as $pst) {//echo tnf_autotag($pst);//echo $pst;
            @$nofm = $nofm +   tagseeder_tnf_autotag($pst);//echo $nofm;
                
            }//echo $nofm;
            //@$nofm1 = $nofm == 0 ?0:$nofm-2<0?$nofm:$nofm==2 || $nofm==3?$nofm-1:$nofm-2;
            if($nofm == 0){
                echo "<div class='updated'>0 tag added to your posts by WP Tag Seeder</div>";
            }
            elseif($nofm-2<0){
                 echo "<div class='updated'>".esc_html($nofm)." tag added to your posts by WP Tag Seeder</div>";
            }
            elseif($nofm==2 || $nofm==3) {
                $nofm12 = $nofm-1;
                 echo "<div class='updated'>".esc_html($nofm12)." tag added to your posts by WP Tag Seeder</div>";    
        }
        else{
            $nofm13 = $nofm-2;
            echo "<div class='updated'>".esc_html($nofm13)." tag added to your posts by WP Tag Seeder</div>";
        }
        
        
        } 
   
    @$action_uri = str_replace('%7E', '~', $_SERVER['REQUEST_URI']);
    @$tag_addor_tags = stripslashes(get_option('tag_addor_tags'));
    @$totalmatchintitle = get_option('totalmatchintitle');
    @$total_matched_inbody = get_option('total_matched_inbody');//echo get_option("tag_addor_sensetive");
    if(get_option("tag_addor_sensetive")){
        @$chk1 = "checked=='checked'";
    }  else {
     @$chk1 = '';    
    }
    if(get_option("tag_addor_mwworld")){
        $chk2 = "checked=='checked'";
    }  else {
     $chk2 = '';    
    }
    if(get_option("tag_addor_scanposttitle")){
        $chk3 = "checked=='checked'";
    }  else {
     $chk3 = '';    
    }
    if(get_option("tag_addor_autotag")){
        $chk4 = "checked=='checked'";
    }  else {
     $chk4 = '';    
    }
    if (isset($_POST["applytagtoall"])) {
            $vis="visiblehidden";
        }else{
            $vis="hidden"; 
        }
    $tagseedercsrf=wp_create_nonce('tagseedercsrf');
    $tagseedercsrfval = wp_create_nonce('tagseedercsrfval');
    echo "<div id='icon-edit' class='icon32'></div><h2>WP Tag Seeder</h2><table><tr><td>";
    echo tagadder_handler('WP Tag Seeder Setting',"<Form method ='post' action='".esc_url($action_uri)."' > 
            <textarea style='overflow: scroll;' name='user_tag_addor' cols='80' rows='10'>".esc_textarea($tag_addor_tags)."</textarea><br/>
             <span style='font-weight:bold;font-size: 13px;color: #666666;'>Each line is a keyword set which can be separated by using comma. The first element in the line is the tag.</span>   
            <br/><br/> 
            <input type='checkbox' name='tnf_case_sensetive' ".esc_attr($chk1)."> Activate for Case Sensetive <br/><br/>
            <input type='checkbox' name='tnf_mwworld' $chk2> Activate For Maching Whole Word <br/><br/>
            <input type='checkbox' name='tnf_scan_posttitle' ".esc_attr($chk3)."> Activate For Scan Post Titles For Keywords <br/><br/>
            <input type='checkbox' name='tnf_autotag' ".esc_attr($chk4)."> Activate For Auto Tag New Posts <br/><br/>
            <span style='font-weight: 700;'>Minimum Matches in Content :</span> <input type='text' size= '6' name= 'total_matched_keyword_inbody' value = '".esc_attr($total_matched_inbody)."' /><br/><br/> <div style='font-weight: 900;padding-left: 15%;'> and </div> <br/> <span style='font-weight: 700;padding-right: 2.7%;'>Minimum Matches in Title : </span>  <input type='text' size= '6' name= 'total_matched_keyword_title' value = '".esc_attr($totalmatchintitle)."' /><br />(This feature is only valid with the maching whole word option) <br /><br />
            <input type='hidden' name='tagseedercsrf' value='".$tagseedercsrf."'>
            <input type='submit' name='tnf_tag_adder_save' class='button-primary widget-control-save' value='SUBMIT' style='width:30%'> </form>");
    echo tagadder_handler('Apply Tag to All Posts',"<Form method ='post' action='' >
            <input class='button-primary widget-control-save' type='submit' name='addtag' id='addtag' value='Add Tags' style='width:250px'></form><br/>
            <Form method ='post' action='' >
            <input type='submit' id='applytagtoall' name='applytagtoall' value='Add Tags (client side)' style='width:250px;margin: 1% 1% 1% 0%;' class='button-primary widget-control-save' >
            <br/><Span style='font-size:15px;color:gray'>
            (The process of tagging all posts is controlled by AJAX which decreases server load while using large number of posts and tags.)
              <input type='hidden' name='tagseedercsrf' value='".$tagseedercsrfval."'>
            <input type='hidden' value='starts1' name='starts1' id='starts1' />
            </form><br /><span id='ajax_show_count' style='visibility:".esc_attr($vis).";align:center;font-size:15px;text-align: center;'></span>");
    echo "</td></tr></table><span style='right:0px;bottom:40px;position:absolute;'><a href='https://teknikforce.com' target='_BLANK'><img src='".plugins_url('assets/img/logo.png',__FILE__)."'style='max-height:40px;max-width:150px'></a></span></div>";
}

}

if(!function_exists('tagadder_handler')){

function tagadder_handler($title,$html){
    $ret_res = <<< TNF
<div class="metabox-holder" />
<div  class="postbox gdrgrid frontleft" style='width:100%'>
<h3 class="hndle">
<span>$title</span>
</h3>
<div class="inside">
<div class="table">
<table>
<tbody>
<tr class="first">
<td class="first b">$html </td></tr></tbody></table></div></div></div>
TNF;
    return $ret_res;
}
}


if(!function_exists('tagseeder_normalize_string')){

function tagseeder_normalize_string ($string) {
    $table = array(
        'Š'=>'S', 'š'=>'s', '�?'=>'Dj', 'Ž'=>'Z', 'ž'=>'z', 'C'=>'C', 'c'=>'c', 'C'=>'C', 'c'=>'c',
        'À'=>'A', '�?'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
        'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', '�?'=>'I', 'Î'=>'I', '�?'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
        'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', '�?'=>'Y', 'Þ'=>'B', 'ß'=>'Ss',
        'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e',
        'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o',
        'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b',
        'ÿ'=>'y', 'R'=>'R', 'r'=>'r',
    );

    return str_replace(array_keys($table), array_values($table), $string);
}
}