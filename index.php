<?php
/*
Plugin Name: User Deleter
Description: Deletes users, and their content, after a period of inactivity.
Author: Loud Dog
Version: 0.1
Author URI: http://www.louddog.com/
*/

if (!function_exists('debug')) {
	function debug($var) {
		echo "<pre>".print_r($var, true)."</pre>";
	}
}

new User_Deleter();
class User_Deleter {
	var $defaults = array(
		'enabled' => false,
		'roles' => array(
			'author',
			'contributor',
			'subscriber',
		),
	);
	
	function __construct() {
		register_deactivation_hook(__FILE__, array(&$this, 'deactivate'));
		add_action('wp', array(&$this, 'schedule'));		
		add_action('user_deleter', array(&$this, 'delete_inactive_users'));
		add_action('admin_init', array(&$this, 'admin_init'));
		add_action('admin_menu', array(&$this, 'admin_menu'));
		add_action('admin_notices', array(&$this, 'admin_notices'));
		$this->options = array_merge($this->defaults, get_option('user_deleter', array()));
	}
	
	function schedule() {
		if (!wp_next_scheduled('user_deleter')) {
			wp_schedule_event(mktime(0, 0, 0), 'daily', 'user_deleter');
		}
	}
	
	function deactivate() {
		wp_clear_scheduled_hook('user_deleter');
	}
	
	function delete_inactive_users() {
		// TODO: delete inactive users
	}
	
	function admin_init() {
		register_setting('user_deleter', 'user_deleter', array(&$this, 'validate'));
		add_settings_section('user_deleter', "User Deletion Settings", array(&$this, 'user_deletion_settings'), 'user_deleter');
		add_settings_field('user_deleter_enabled', "Enabled", array(&$this, 'show_field_enabled'), 'user_deleter', 'user_deleter');
		add_settings_field('user_deleter_roles', "Roles", array(&$this, 'show_field_roles'), 'user_deleter', 'user_deleter');
		
	}
	
	function validate($input) {
		$this->options['enabled'] = isset($input['enabled']) ? true : false;
		
		$this->options['roles'] = array();
		if (is_array($input['roles'])) {
			foreach (get_editable_roles() as $key => $role) {
				if (array_key_exists($key, $input['roles'])) {
					$this->options['roles'][] = $key;
				}
			}
		}
		
		return $this->options;
	}
	
	function user_deletion_settings() {
		echo "<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>";
	}
	
	function show_field_enabled() { ?>
		<input
			type="checkbox"
			name="user_deleter[enabled]"
			id="user_deleter_enabled"
			<?php if ($this->options['enabled']) echo 'checked'; ?>
		/>
		<label for="user_deleter_enabled">
			Enabled
		</label>
	<?php }
	
	function show_field_roles() {
		echo "<ul>";
		foreach (get_editable_roles() as $key => $role) { ?>
			<li>
				<input
					type="checkbox"
					name="user_deleter[roles][<?php echo $key; ?>]"
					id="user_deleter_roles_<?php echo $key; ?>"
					<?php if (in_array($key, $this->options['roles'])) echo 'checked'; ?>
				/>
				<label for="user_deleter_roles_<?php echo $key; ?>">
					<?php echo $role['name']; ?>
				</label>
			</li>
		<?php }
		echo "</ul>";
	}
	
	function admin_menu() {
		add_options_page(
			"User Deleter",
			"User Deleter",
			'manage_options',
			'user_deleter',
			array(&$this, 'settings')
		);
	}
	
	function settings() { ?>
		<form action="options.php" method="post">
			<?php
				settings_fields('user_deleter');
				do_settings_sections('user_deleter');
			?>
			<input type="submit" value="Save Settings" />
		</form>
	<?php }
	
	function admin_notices() {
		
	}
}