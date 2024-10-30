<?php
/*
Plugin Name: CB Storefront
Plugin URI: http://www.cbengine.com
Description: ClickBank Storefront Plugin for WordPress
Version: 1.0
Author: CBengine.com
Author URI: http://www.cbengine.com
License:

  Copyright 2012 CBengine.com

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as 
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.
  
*/

class CBStoreFront {

	public static $options, $defaults, $notification;

	var $optname  = 'cb-storefront';
	var $weburl = '';
	var $cat = '';

	function delete_options() {
		global $wpdb;
		$opt_tbl = $wpdb->prefix."options";
		$r = $wpdb->query("DELETE FROM $opt_tbl WHERE option_name = '{$this->optname}'");
	}

	static function array_minimize($myarray=array(),$defaults=array(), $valuecheck=true) {

			// ADD MISSING KEYS, OR REMOVE IDENTICAL IF THEY MATCH DEFAULT VALUE
			foreach($defaults as $k => $v) {
				if(!isset($myarray[$k]))  $myarray[$k] = $v;

				if($valuecheck) if($myarray[$k] == $v) unset($myarray[$k]);
			}

			// DELETE OPTIONS NOT IN DEFAULTS
			foreach($myarray as $k => $v) {
				if(! isset($defaults[$k]) )unset($myarray[$k]);
			}

		return $myarray;
	}
	static function get_include($filename) {
		if (is_file($filename)) {
			ob_start();
			include $filename;
			$contents = ob_get_contents();
			ob_end_clean();
			return $contents;
		}
		return false;
	}

	public function get_options() {
		return get_option('cb-storefront');
	}
	function load_options() {
		$dat = $this->get_options();
		$dat = empty($dat) ? array() : $dat;

		return wp_parse_args($dat, $this->defaults);
	}

	function getter($k){
		return (isset($this->options[$k])) ? $this->options[$k] : $this->def($k);
	}
	function setter($k,$v){
		$this->options[$k] = $v;
	}

	function opt($k){
		$v = $this->getter($k);
		echo $v;
	}
	function def($id) {
		return (isset($this->defaults[$id])) ? $this->defaults[$id] : null;
	}
	function save($resetFields='') {
		// filter_options
		$this->options = CBStoreFront::array_minimize($this->options, $this->defaults);
		wp_cache_delete($this->optname, 'options');
		wp_cache_delete('notoptions', 'options');
		add_option($this->optname,$this->options);  // add if not exists.
		update_option($this->optname,$this->options); //  update because above does nothing if exists
		$this->options = $this->load_options();
	}

	function delete(){
		delete_option($this->optname);
	}




	public function template_redirect() {

		$vendor = @$_REQUEST['vendor'];
		if(!empty($vendor)){
			$link = "http://" . $this->getter('cbid') . "." . $vendor . ".hop.clickbank.net/";

			wp_redirect($link);
			exit();
			
			// $baseurl = plugins_url('', __FILE__).'/';
			// $back = '';
			// $back .= '<link rel="stylesheet" type="text/css" href="'.$baseurl.'media/display.css" />';
			// $back .= '<a href="'.$link.'" class="button">Click here to go directly to the product page</a> ...';
			// $back .= '<br/><br/><br/><br/><div><a href="http://www.cbengine.com/id/'.$vendor.'" class="cbenginelogo" title="'.$vendor.' - Read review on CBengine"><span>cbengine</span></a></div>';
			// wp_die('<h3>Please wait while we redirect your browser...</h3>' . $back, 'Loading...');

		}
		// exit();

	}


	function show_ident() {

		$ident = '<div><a href="http://www.cbengine.com" class="cbenginelogo" title="CBengine"><span>cbengine.com</span></a></div>';
		echo $ident;
	}


	// function loadResources($posts) {
	// 		return $posts;
	// }


	function __construct() {


		$this->defaults = array(
			'cbid' 		=> 'cbengine', 
			'nada' 		=> 'Your search did not return any products', 
			'limit' 	=> 10,
			'paged' 	=> 7,
			'maincat' 	=> ''
		);

		$this->options = $this->load_options();


		$this->weburl = $this->geturl();

		if ( isset( $_POST['save-storefront-options'] ) && $_GET['page'] == 'cb-storefront' ) {
			// $refer = wp_get_referer();
			foreach($this->defaults as $k => $v) {
				if(isset($_POST['cb-storefront_' . $k])){
					$this->setter($k, $_POST['cb-storefront_' . $k]);
				}
			}
			$this->save();
			$this->notification = '<div class="updated"><p><strong>' . __( 'Storefront options saved', 'cb-storefront' ) . '</strong></p></div>';
		}

		if(! is_admin()) {

    			add_action( 'template_redirect', array( &$this, 'template_redirect' ) );
		}


		if( !is_admin() ){

			// add_filter( 'the_posts', array(&$this, 'loadResources'), 11 );


		}


		
		add_shortcode( 'cb-storefront' , array( &$this, 'load_storefront' ) );
		// add_filter('cbstorefront-shortcode-defaults', array(&$this, 'shortcode_defaults'));

		add_action('init', array(&$this, 'init'));



		// Register admin styles and scripts
			add_action( 'admin_print_styles', array( &$this, 'register_admin_styles' ) );
			add_action( 'admin_enqueue_scripts', array( &$this, 'register_admin_scripts' ) );
	
		// Register site styles and scripts
			add_action( 'wp_print_styles', array( &$this, 'register_plugin_styles' ) );
			add_action( 'wp_enqueue_scripts', array( &$this, 'register_plugin_scripts' ) );

		
		add_action('admin_menu', array(&$this,'admin_menu'));

		// add_action( 'cb-store-front', array( $this, 'action_method_name' ) );
		// add_filter( 'cb-store-front', array( $this, 'filter_method_name' ) );

	} 


	function admin_menu() {
		$p = add_options_page( 'CB Storefront', 'CB Storefront', 'administrator', 'cb-storefront',  array(&$this,'admin_page') );
	}

	function get_include_contents($filename) {
		if (is_file($filename)) {
			ob_start();
			include $filename;
			$contents = ob_get_contents();
			ob_end_clean();
			return $contents;
		}
		return false;
	}

	function geturl($data='') {

		global $wp_query, $post;

			// TO DO: fix for non permalinks
			// $post = get_post($post_id);
			// if(count($wp_query->posts)){
			// $out = add_query_arg('mall',1,$out);


		$out = $_SERVER['REQUEST_URI'];
		$out = remove_query_arg( array('pg','cat','kw','mall','submit.x','submit.y','submit_x','submit_y'), $out );


		$pageid = $wp_query->post->ID;
		$out = get_permalink($pageid);

		$sep = (strpos($out, '?') === false) ? '?' : '&';

		if(strlen($data)){
			$out = $out . $sep . $data;
		}

		return $out;

	}

	function getsearchbox() {
		$out = '';
		$plugin_file = __FILE__;
		$out .= '<div id="cb_search">';
		$out .= '<form action="' . $this->geturl() . '" method="post" id="cb-search-form">';
		$out .= '<input name="kw" type="text" placeholder="Search for Products" />';
		$out .= '<input type="submit" value="Search" />';
		$out .= '</form>';
		$out .= '</div>';
		return $out;
	}


	function init() {

	}


	function get_inc_path() {
		return dirname( __FILE__ ) . '/inc/';
	}


	function remoteInfo($maincat='') {

		$p = array();
		$c = $this->getter('maincat');

		if ($maincat != '') $c = $maincat;

		$req_q = @$_REQUEST['kw'];
		$req_c = @$_REQUEST['cat'];

		if(!empty($req_q)){
			$p[] = "q=".urlencode(stripslashes($req_q)); 
			$this->weburl = add_query_arg('kw',urlencode($req_q),$this->weburl);
		}
		if(!empty($req_c)){
			$c = $req_c;
			$this->cat = $c;
			$this->weburl = add_query_arg('cat',urlencode($c),$this->weburl);

		}
		if ($c != '') $p[] = "c=".urlencode(stripslashes($c)); 

		$this->params = $p;
	}

	function getPageNum() {

		$pg = '';
		if (isset($_GET["pg"])) $pg = $_GET['pg']; 
		if(empty($pg) || $pg == '') $pg = 1; 
		return $pg;
	}

	function load_rss($url) {
		$uafunc = create_function('', "return 'cbstorefront-wp';");
		add_filter('http_headers_useragent', $uafunc);
		require_once (ABSPATH . WPINC . '/rss.php');
		$rss = fetch_rss($url);
		remove_filter('http_headers_useragent', $uafunc);
		return $rss;
	}



	function set_wp_title($title) {
		global $wp_query;
		$title = esc_attr($title);
		if(count($wp_query->posts)){
			$wp_query->queried_object->post_title .= ' : ' . $title;
			$wp_query->posts[0]->post_title = $title;
		}
	}
	function load_storefront($attr) {

		if ( !is_page() ) {
			return false;
		}

		$attr = shortcode_atts( $this->options, $attr );
		extract( $attr );



		$this->remoteInfo($maincat);

		$pg 	= $this->getPageNum();
		$found 	= '';
		$limit 	= $this->getter('limit');
		$paged 	= $this->getter('paged');
		$ww 	= $this->weburl;
		$nn 	= '';
		$searched = 0;


		// HEADER

		echo $this->getsearchbox();
		print ('<div id="store">'."\n"); 
		print ('<div class="storeItems">'."\n"); 


		// PAGE

		if(count($this->params) > 0){

			// GRAB DATA FEED

			$searched = 1;
			$f = "http://www.cbengine.com/search/cbstorefront.cfm?" . implode('&',$this->params);
			$result = $this->load_rss($f);
			$items = $result->items;
			if ($items) {
				if(count($items)){
					$result->channel = (object) $result->channel;

					$found 		= count($items);
					$limitvalue 	= $pg * $limit - ($limit);
					$endrow		= min(($pg*$limit),$found);
					$startrow 	= ($pg*$limit)-($limit);
					$aItems 	= array_slice($items, $startrow, $limit);
					$bulletStart 	= $startrow+1;

					$page_label = '';
					if ($this->cat != '') { 
						$catname = trim($result->channel->title);
						$page_title = '' . $catname . ' products';
						$page_keywords = $catname;
						$page_description = $catname;
						$backlink1 = '<a href="' . $this->geturl() . '">Main</a> &raquo; ';
						$backlink2 = '<a href="' . $this->weburl . '">' . $catname . '</a>';
						$page_label = "<h2 class=\"channelname\"  title=\"$catname : $found results\">".$backlink1.' '.$backlink2 ."</h2><br>";
					}

					echo $page_title;
					$this->set_wp_title($page_title);
				}
			}

		}

		if ($found > 0){

			print ($page_label."\n");


			$req_q = @$_REQUEST['kw'];
			if(!empty($req_q)){
				$backlink = '<a href="' . $this->geturl() . '">Main</a> &raquo; ';
				print ('<div class="total">'.$backlink.' '.$found." search results for ".$req_q."</div>"."\n");
			}

			// RENDER STORE ITEMS
			print ('<div id="products"><ol start="'.$bulletStart.'">'."\n");
			foreach ($aItems as $o) {
				$o = (object) $o;
				$hoplink = $this->geturl('vendor='.$o->vendor);
				$newtext = str_replace(',', ', ', $o->description);
				print ('<li class="item">'."\n");
				print ('<div class="itemtitle"><a href="'.$hoplink.'" rel="nofollow" target="_top">'.trim($o->title).'</a></div>'."\n");
				print ('<span class="itemdescription">'.trim($newtext).'</span>'."\n");
				print ("</li>"."\n");
			}
			print ("</ol></div>"."\n");

			// RENDER PAGED NAV 
			if ($found > $limit) {
				$numpages = ceil($found/$limit);
				$midpos = ceil($paged/2);
				if($pg <= $midpos){
					$startpage = 1;
				} else if ($pg >= $numpages-($midpos-1)){
					$startpage = max($numpages-($paged-1),1);
				}else{
					$startpage = $pg-($midpos-1);
				}
				$endpage = min($startpage+($paged-1),$numpages);

				$nn .= '<div class="nextn">';
				if($pg != 1){ 

					$i = $pg-1;
					$nn .= '<a href="' . $ww.'&pg='. $i . '">Back</a> '; 
				}
				for($i = $startpage; $i <= $endpage; $i++){ 
					$cc = ($i == $pg) ? ' class="currpage"' : '';
					$nn .= '<a href="'.$ww.'&pg='.$i.'"' . $cc . '>'.$i.'</a> ';
				}
				if(($found - ($limit * $pg)) > 0){ 
					$i = $pg+1;
					$nn .= '<a href="'.$ww.'&pg='. $i . '">Next</a> '; 
				}
				$nn .= '</div>';
				echo $nn;
			}

		} else if($searched == 1) { 

			// NO RESULTS

			echo '<div class="nada">' . $this->getter('nada') . '</div>';
		}else{

			// MAIN INDEX PAGE

			$this->topCategories();
		}


		// FOOTER
		$backlink = '<a href="' . $this->geturl() . '">Back to Storefront Categories</a>';
		print ('</div>'); // end css storeItems
			print ('<div style="clear: both;"></div>' . "\n"); 
			print ('<div class="storefooter">' . "\n"); 
			print ('<div class="cbs_back" >' . $backlink . '</div>' . "\n"); 
			print ('</div>' . "\n");
		print ('</div>'); // end css store class
	}


	function topCategories() {
		$catfile = $this->get_inc_path() . 'view_categories.php';

		$cathome = 'href="' . $this->geturl('cat=');


		if (file_exists($catfile)) {
			$string = $this->get_include_contents($catfile);
			$string = str_replace('href="',$cathome,$string);
			$string = str_replace('?&','?',$string);
			echo($string);
		}
	}

	function cleanUrl($dirty_url){
		list($clean_url)= explode('?',htmlspecialchars(strip_tags($dirty_url),ENT_NOQUOTES));
		return $clean_url;
	}

	function admin_page() {
		echo '<div class="wrap">';
		echo '<h2>CBengine ClickBank Storefront</h2>';
		if($this->notification !== ''){ echo $this->notification; }
		require_once dirname( __FILE__ ) . '/inc/view_admin.php';
		echo '</div>';
	}

	static function activation( $action ) {
		// ob_start();
		global $wp_version;
		$postinfo = array(
			'action' => $action,
			'wp_uri' => get_bloginfo('url'),
			'wp_ver' => $wp_version		
		);
		$uri = 'http://plugins.cbengine.com/cbstorefront/';
		$o = array('method' => 'POST', 'timeout' => 10, 'body' => $postinfo);
		$o['headers']= array(
			'Content-Type' => 'application/x-www-form-urlencoded; charset=' . get_option('blog_charset'),
			'Content-Length' => strlen(implode(',',$postinfo)),
			'user-agent' => 'WordPress/' . $wp_version,
			'referer'=> get_bloginfo('url')
		);			
		$raw_response = wp_remote_post($uri, $o);

		// trigger_error(ob_get_contents(),E_USER_ERROR);
	}
	static function install( $network_wide ) {
		CBStoreFront::activation('activate');

	}
	static function uninstall( $network_wide ) {
		CBStoreFront::activation('deactivate');
	}

	public function register_admin_styles() {
		wp_register_style( 'cb-storefront-admin-styles', plugins_url( 'cb-storefront/media/admin.css' ) );
		wp_enqueue_style( 'cb-storefront-admin-styles' );
	}
	public function register_admin_scripts() {
		wp_register_script( 'cb-storefront-admin-script', plugins_url( 'cb-storefront/media/admin.js' ) );
		wp_enqueue_script( 'cb-storefront-admin-script' );
	}
	public function register_plugin_styles() {
		wp_register_style( 'cb-storefront-plugin-styles', plugins_url( 'cb-storefront/media/display.css' ) );
		wp_enqueue_style( 'cb-storefront-plugin-styles' );
	}

	public function register_plugin_scripts() {
		wp_register_script( 'cb-storefront-plugin-script', plugins_url( 'cb-storefront/media/display.js' ) );
		wp_enqueue_script( 'cb-storefront-plugin-script' );
	}
	
	function action_method_name() {
	}
	function filter_method_name() {
	} 

}
function cbstorefront_main() {
	register_activation_hook( __FILE__, array('CBStoreFront', 'install')  );
	register_deactivation_hook( __FILE__, array('CBStoreFront', 'uninstall')  );
	new CBStoreFront();
}

add_action("plugins_loaded", 'cbstorefront_main');