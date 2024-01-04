var twz_installer = twz_installer || {};

jQuery(document).ready(function ($) {
	("use strict");

	var is_loading = false;

	/**
	 * Install the plugin.
	 *
	 * @param {Element} el    Button element.
	 * @param {string} plugin The plugin slug.
	 */
	twz_installer.install_plugin = function (el, plugin) {
		// Confirm activation.
		var r = confirm(twz_installer_localize.install_now);
		if (r) {
			is_loading = true;
			el.addClass("installing");

			$.ajax({
				type: "POST",
				url: twz_installer_localize.ajax_url,
				data: {
					action: "cnkt_plugin_installer",
					plugin: plugin,
					nonce: twz_installer_localize.admin_nonce,
					dataType: "json",
				},
				success: function (data) {
					if (data) {
						if (data.status === "success") {
							el.attr("class", "activate btn btn-primary btn-sm");
							el.html(twz_installer_localize.activate_btn);
						} else {
							el.removeClass("installing");
						}
					} else {
						el.removeClass("installing");
					}
					is_loading = false;
				},
				error: function (status) {
					console.log(status);
					el.removeClass("installing");
					is_loading = false;
				},
			});
		}
	};

	/**
	 * Activate the plugin
	 *
	 * @param {Element} el    Button element.
	 * @param {string} plugin The plugin slug.
	 */
	twz_installer.activate_plugin = function (el, plugin) {
		$.ajax({
			type: "POST",
			url: twz_installer_localize.ajax_url,
			data: {
				action: "cnkt_plugin_activation",
				plugin: plugin,
				nonce: twz_installer_localize.admin_nonce,
				dataType: "json",
			},
			success: function (data) {
				if (data) {
					if (data.status === "success") {
						el.attr("class", "installed button disabled");
						el.html(twz_installer_localize.installed_btn);
					}
				}
				is_loading = false;
			},
			error: function (xhr, status, error) {
				console.log(status);
				is_loading = false;
			},
		});
	};

	/**
	 * Install/Activate Button Click.
	 *
	 * @since 1.0
	 */
	$(document).on("click", ".twz-quiz-plugin-installer a.button, a.btn", function (e) {
		var el = $(this),
			plugin = el.data("slug");

		e.preventDefault();

		if (!el.hasClass("disabled")) {
			if (is_loading) return false;

			// Installation
			if (el.hasClass("install")) {
				twz_installer.install_plugin(el, plugin);
			}

			// Activation
			if (el.hasClass("activate")) {
				twz_installer.activate_plugin(el, plugin);
			}
		}
	});
});
