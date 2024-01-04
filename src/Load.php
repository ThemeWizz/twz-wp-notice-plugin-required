<?php

/**
 * QuadLayers WP Notice Plugin Required
 *
 * @package   quadlayers/wp-notice-plugin-required
 * @link      https://github.com/quadlayers/wp-notice-plugin-required
 */

namespace themewizz\twz_wp_notice_plugin_required;

/**
 * Class Load
 *
 * @package QuadLayers\WP_Notice_Plugin_Required
 */
class Load
{

	/**
	 * Required Plugins.
	 *
	 * @var array
	 */
	protected $plugins;
	/**
	 * Current Plugin name.
	 *
	 * @var string
	 */
	protected $current_plugin_name = '';

	/**
	 * Load constructor.
	 *
	 * @param string $current_plugin_name Current Plugin name.
	 * @param array  $plugins             Required Plugins.
	 */
	public function __construct($current_plugin_name, array $plugins = array())
	{
		$this->current_plugin_name = $current_plugin_name;
		$this->plugins = $plugins;

		load_plugin_textdomain('twz-wp-notice-plugin-required', false, dirname(dirname(plugin_basename(__FILE__))) . '/languages/');

		if (isset($plugins['server_url'])) {

			wp_enqueue_script('plugin-installer', plugin_dir_url(__FILE__) . 'assets/twz/js/installer.js', ['jquery'], '1.0.0', false);
			wp_localize_script(
				'plugin-installer',
				'cnkt_installer_localize',
				[
					'ajax_url'      => $plugins['server_url'] . '/wp-admin/admin-ajax.php',
					'admin_nonce'   => wp_create_nonce('twz-plugin-manager-client'),
					'install_now'   => __('Are you sure you want to install this plugin?', 'twz-plugin-manager-client'),
					'install_btn'   => __('Install Now', 'twz-plugin-manager-client'),
					'activate_btn'  => __('Activate', 'twz-plugin-manager-client'),
					'installed_btn' => __('Activated', 'twz-plugin-manager-client'),
				]
			);
			wp_enqueue_style('plugin-installer',  plugin_dir_url(__FILE__) . 'assets/twz/css/installer.css', array(), '1.0.0');
		}
		add_action('admin_notices', array($this, 'admin_notices'));
	}

	/**
	 * Add admin notice.
	 *
	 * @return void
	 */
	public function admin_notices()
	{

		$screen = get_current_screen();

		if (isset($screen->parent_file) && 'plugins.php' === $screen->parent_file && 'update' === $screen->id) {
			return;
		}

		foreach ($this->plugins as $plugin) {

			if (!isset($plugin['slug'], $plugin['name'])) {
				continue;
			}

			$plugin = Plugin::get_instance($plugin['slug'], $plugin['name']);

			$notice = $this->add_notice($plugin);

			/**
			 * If notice is added then return.
			 * This will prevent multiple notices for same plugin.
			 */
			if ($notice) {
				return;
			}
		}
	}

	/**
	 * Add notice.
	 *
	 * @param Plugin $plugin Plugin.
	 *
	 * @return bool
	 */
	private function add_notice(Plugin $plugin)
	{

		if ($plugin->is_plugin_activated()) {
			return false;
		}

		if ($plugin->is_plugin_installed()) {
			if (!current_user_can('activate_plugins')) {
				return false;
			}

?>
			<div class="error">
				<p>
					<?php if (!isset($this->plugins['server_url'])) { ?>
						<a href="<?php echo esc_url($plugin->get_plugin_activate_link()); ?>" class='button button-secondary'><?php printf(esc_html__('Activate % s', 'twz-wp-notice-plugin-required'), esc_html($plugin->get_plugin_name())); ?></a>
					<?php } else { ?>
						<a href="#" class='button button-secondary activate'><?php printf(esc_html__('Activate % s', 'twz-wp-notice-plugin-required'), esc_html($plugin->get_plugin_name())); ?></a>
					<?php } ?>
					<?php printf(esc_html__('The %1$s is not working because you need to activate the %2$s plugin. ', 'twz-wp-notice-plugin-required'), esc_html($this->current_plugin_name), esc_html($plugin->get_plugin_name())); ?>
				</p>
			</div>
		<?php
			return true;
		}

		if (!current_user_can('install_plugins')) {
			return false;
		}

		?>
		<div class="error">
			<p>
				<?php if (!isset($this->plugins['server_url'])) { ?>
					<a href="<?php echo esc_url($plugin->get_plugin_install_link()); ?>" class='button button-secondary'><?php printf(esc_html__('Install % s', 'twz-wp-notice-plugin-required'), esc_html($plugin->get_plugin_name())); ?></a>
				<?php } else { ?>
					<a href="#" class='button button-secondary install'><?php printf(esc_html__('Install % s', 'twz-wp-notice-plugin-required'), esc_html($plugin->get_plugin_name())); ?></a>
				<?php } ?>
				<?php printf(esc_html__('The %1$s is not working because you need to install the %2$s plugin. ', 'twz-wp-notice-plugin-required'), esc_html($this->current_plugin_name), esc_html($plugin->get_plugin_name())); ?>
			</p>
		</div>
<?php
		return true;
	}
}
