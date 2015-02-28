<?php

class UM_Account {

	function __construct() {
	
		add_shortcode('ultimatemember_account', array(&$this, 'ultimatemember_account'), 1);
		
		add_filter('um_account_page_default_tabs_hook', array(&$this, 'core_tabs'), 1);
		
		add_action('template_redirect', array(&$this, 'account'), 10001 );
		
		add_action('template_redirect', array(&$this, 'form_init'), 10002);
		
		$this->current_tab = 'general';

	}
	
	/***
	***	@get core account tabs
	***/
	function core_tabs() {
		
		$tabs[100]['general']['icon'] = 'um-faicon-user';
		$tabs[100]['general']['title'] = __('Account','ultimatemember');
		
		$tabs[200]['password']['icon'] = 'um-faicon-asterisk';
		$tabs[200]['password']['title'] = __('Change Password','ultimatemember');
		
		$tabs[300]['privacy']['icon'] = 'um-faicon-lock';
		$tabs[300]['privacy']['title'] = __('Privacy','ultimatemember');
		
		$tabs[400]['notifications']['icon'] = 'um-faicon-bell';
		$tabs[400]['notifications']['title'] = __('Notifications','ultimatemember');
		
		$tabs[9999]['delete']['icon'] = 'um-faicon-trash-o';
		$tabs[9999]['delete']['title'] = __('Delete Account','ultimatemember');
		
		return $tabs;
	}
	
	/***
	***	@account page form
	***/
	function form_init() {
		global $ultimatemember;

		if ( um_submitting_account_page() ) {
			
			$ultimatemember->form->post_form = $_POST;

			do_action('um_submit_account_errors_hook', $ultimatemember->form->post_form );
			
			if ( !isset($ultimatemember->form->errors) ) {
				
				if ( get_query_var('um_tab') ) {
					$this->current_tab = get_query_var('um_tab');
				}
				
				do_action('um_submit_account_details', $ultimatemember->form->post_form );

			}

		}

	}
	
	/***
	***	@can access account page
	***/
	function account(){
		global $ultimatemember;
		
		if ( um_is_core_page('account') && !is_user_logged_in() ) {
			um_redirect_home();
		}
		
		if ( um_is_core_page('account') ) {
			
			$ultimatemember->fields->set_mode = 'account';
			
			$ultimatemember->fields->editing = true;
			
			if ( get_query_var('um_tab') ) {
				$this->current_tab = get_query_var('um_tab');
			}
			
		}
		
	}
	
	/***
	***	@get tab link
	***/
	function tab_link( $id ) {
	
		if ( get_option('permalink_structure') ) {
		
			$url = trailingslashit( untrailingslashit( um_get_core_page('account') ) );
			$url = $url . $id . '/';
		
		} else {
			
			$url = add_query_arg( 'um_tab', $id, um_get_core_page('account') );
			
		}
		
		return $url;
	}
	
	/***
	***	@Add class based on shortcode
	***/
	function get_class( $mode ){
	
		global $ultimatemember;
		
		$classes = 'um-'.$mode;
		
		if ( is_admin() ) {
			$classes .= ' um-in-admin';
		}
		
		if ( $ultimatemember->fields->editing == true ) {
			$classes .= ' um-editing';
		}
		
		if ( $ultimatemember->fields->viewing == true ) {
			$classes .= ' um-viewing';
		}
		
		$classes = apply_filters('um_form_official_classes__hook', $classes);
		return $classes;
	}
	
	/***
	***	@get tab output
	***/
	function get_tab_output( $id ) {
		global $ultimatemember;
		
		$output = null;
		
		switch( $id ) {
			
			case 'notifications':
				
				$output = apply_filters("um_account_content_hook_{$id}", $output);
				return $output;
				
				break;

			case 'privacy':
				
				$args = 'profile_privacy,hide_in_members';
				
				$fields = $ultimatemember->builtin->get_specific_fields( $args );
				foreach( $fields as $key => $data ){
					$output .= $ultimatemember->fields->edit_field( $key, $data );
				}
				
				return $output;
				
				break;
				
			case 'delete':
				
				$args = 'single_user_password';
				
				$fields = $ultimatemember->builtin->get_specific_fields( $args );
				foreach( $fields as $key => $data ){
					$output .= $ultimatemember->fields->edit_field( $key, $data );
				}
				
				return $output;
				
				break;
				
			case 'general':
			
				$args = 'user_login,first_name,last_name,user_email';
				
				if ( !um_get_option('account_name') ) {
					$args = 'user_login,user_email';
				}
				
				$fields = $ultimatemember->builtin->get_specific_fields( $args );
				foreach( $fields as $key => $data ){
					$output .= $ultimatemember->fields->edit_field( $key, $data );
				}
				
				return $output;
				
				break;
				
			case 'password':
				
				$args = 'user_password';
				
				$fields = $ultimatemember->builtin->get_specific_fields( $args );
				foreach( $fields as $key => $data ){
					$output .= $ultimatemember->fields->edit_field( $key, $data );
				}
				
				return $output;
				
				break;
				
			default :
				
				$output = apply_filters("um_account_content_hook_{$id}", $output);
				return $output;
				
				break;

		}
	}
	
	/***
	***	@Shortcode
	***/
	function ultimatemember_account( $args = array() ) {
		return $this->load( $args );
	}
	
	/***
	***	@Load a module with global function
	***/
	function load( $args ) {
	
		global $ultimatemember;
		
		ob_start();

		$defaults = array(
			'template' => 'account',
			'mode' => 'account',
			'form_id' => 'um_account_id',
		);
		$args = wp_parse_args( $args, $defaults );
		
		if ( isset( $args['use_globals'] ) && $args['use_globals'] == 1 ) {
			$args = array_merge( $args, $this->get_css_args( $args ) );
		} else {
			$args = array_merge( $this->get_css_args( $args ), $args );
		}
		
		$args = apply_filters('um_account_shortcode_args_filter', $args);

		extract( $args, EXTR_SKIP );
		
		do_action("um_pre_{$mode}_shortcode", $args);
		
		do_action("um_before_form_is_loaded", $args);
		
		do_action("um_before_{$mode}_form_is_loaded", $args);
		
		$this->template_load( $template, $args );
		
		if ( !is_admin() && !defined( 'DOING_AJAX' ) ) {
			$this->dynamic_css( $args );
		}
		
		$output = ob_get_contents();
		ob_end_clean();
		return $output;

	}
	
	/***
	***	@Get dynamic css args
	***/
	function get_css_args( $args ) {
		$arr = um_styling_defaults( $args['mode'] );
		$arr = array_merge( $arr, array( 'form_id' => $args['form_id'], 'mode' => $args['mode'] ) );
		return $arr;
	}
	
	/***
	***	@Load dynamic css
	***/
	function dynamic_css( $args=array() ) {
		extract($args);
		$global = um_path . 'assets/dynamic_css/dynamic_global.php';
		$file = um_path . 'assets/dynamic_css/dynamic_'.$mode.'.php';
		include $global;
		if ( file_exists( $file ) )
			include $file;
	}

	/***
	***	@Loads a template file
	***/
	function template_load( $template, $args=array() ) {
		global $ultimatemember;
		if ( is_array( $args ) ) {
			$ultimatemember->shortcodes->set_args = $args;
		}
		$ultimatemember->shortcodes->load_template( $template );
	}

}