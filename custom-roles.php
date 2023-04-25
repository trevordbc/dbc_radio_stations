<?php
    if (!current_user_can('manage_options')) {
        wp_die('You do not have sufficient permissions to access this page.');
    }
	
	function get_all_capabilities() {
		$all_roles = wp_roles()->roles;
		$capabilities = [];

		foreach ($all_roles as $role) {
			foreach ($role['capabilities'] as $capability => $value) {
				if (!in_array($capability, $capabilities)) {
					$capabilities[] = $capability;
				}
			}
		}

		sort($capabilities);

		return $capabilities;
	}

    if (isset($_POST['action']) && $_POST['action'] == 'add_role' && isset($_POST['role_name']) && isset($_POST['display_name'])) {
        $role_name = sanitize_text_field($_POST['role_name']);
        $display_name = sanitize_text_field($_POST['display_name']);
        $capabilities = isset($_POST['capabilities']) ? array_map('sanitize_text_field', $_POST['capabilities']) : [];

        add_role($role_name, $display_name, array_fill_keys($capabilities, true));
    }

    echo '<h2>Add New Role</h2>';
    echo '<form method="POST" action="">';
    echo '<input type="hidden" name="action" value="add_role">';
    echo '<label>Role Name (slug)<br /><input type="text" name="role_name"></label><br>';
    echo '<label>Display Name<br /><input type="text" name="display_name"></label><br>';
    echo '<label>Capabilities</label><br />';
    $capabilities = get_all_capabilities();
	echo '<select name="capabilities[]" multiple>';
    foreach ($capabilities as $capability) {
        echo '<option value="' . esc_html($capability) . '">' . esc_html($capability) . '</option>';
    }
	echo '</select><br />';
    echo '<input type="submit" value="Add Role">';
    echo '</form>';

    echo '<hr>';
	
	// Confirmation prompt for deleting role
	if (isset($_POST['action']) && $_POST['action'] == 'delete_role' && isset($_POST['role_name']) && isset($_POST['new_role'])) {
		$role_name = sanitize_text_field($_POST['role_name']);
		$new_role = sanitize_text_field($_POST['new_role']);

		if ($role_name !== $new_role) {
			$users = get_users(['role' => $role_name]);

			if (count($users) > 0) {
				wp_die('You cannot delete a role that has users assigned to it.');
			}

			if (remove_role($role_name)) {
				echo '<div class="notice notice-success"><p>The role has been successfully deleted.</p></div>';
			} else {
				echo '<div class="notice notice-error"><p>There was an error deleting the role. Please try again.</p></div>';
			}
		}
	}

	// Existing Roles table
	$all_roles = wp_roles()->roles;

	echo '<h2>Existing Roles</h2>';
	echo '<table>';
	echo '<tr><th>Role Name</th><th>Display Name</th><th>Capabilities</th><th>Actions</th></tr>';

	foreach ($all_roles as $role_name => $role_data) {
		echo '<tr>';
		echo '<td>' . esc_html($role_name) . '</td>';
		echo '<td>' . esc_html($role_data['name']) . '</td>';
		echo '<td>' . implode(', ', array_keys($role_data['capabilities'])) . '</td>';
		echo '<td>';

		// Delete Role button
		echo '<button type="button" class="button" onclick="if (confirm(\'Are you sure you want to delete this role?\')) { document.getElementById(\'delete_role_' . esc_attr($role_name) . '\').submit(); }">Delete</button>';

		// Delete Role form
		echo '<form id="delete_role_' . esc_attr($role_name) . '" method="POST" action="' . admin_url('admin.php?page=custom-user-roles') . '" style="display:none">';
		echo '<input type="hidden" name="action" value="delete_role">';
		echo '<input type="hidden" name="role_name" value="' . esc_attr($role_name) . '">';
		echo '<input type="hidden" name="new_role" value="">';
		echo '</form>';

		echo '</td>';
		echo '</tr>';
	}

	echo '</table>';
