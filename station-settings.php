<?php
// Include the Stripe PHP library
require_once(plugin_dir_path(__FILE__) . '/libraries/stripe-gateway/init.php'); // Adjust the path according to your project structure

// Set the Stripe API key from the saved settings
\Stripe\Stripe::setApiKey(get_option('stripe_api_key'));

// Update Roles Based On Subscription Status
function update_users_roles_based_on_stripe_status() {
    $args = array(
        'meta_key' => 'paygate_transaction_id',
        'meta_compare' => 'EXISTS',
    );

    $users = new WP_User_Query($args);

    if (!empty($users->results)) {
        foreach ($users->results as $user) {
            $transaction_id = get_user_meta($user->ID, 'paygate_transaction_id', true);
            $subscription_status = get_subscription_status($transaction_id);

            // Update user role based on subscription status
            if ($subscription_status === 'past_due') {
                $user->set_role('subscription_past_due');
            } elseif ($subscription_status === 'canceled') {
                $user->set_role('subscription_canceled');
            }
        }
    }
}

function station_settings_page() {
}

function get_subscription_status($transaction_id) {
    try {
        $subscription = \Stripe\Subscription::retrieve($transaction_id);
        $status = $subscription->status;
        return $status;
    } catch (Exception $e) {
        return '';
    }
}

function paygate_page() {
    // Get the current page number
    $paged = isset($_GET['paged']) ? intval($_GET['paged']) : 1;

    // Get the selected number of entries per page
    $per_page = isset($_GET['per_page']) ? intval($_GET['per_page']) : 25;

    // Update the $args in WP_User_Query based on the selected user role type
    $args = array(
        'meta_key' => 'paygate_transaction_id',
        'meta_compare' => 'EXISTS',
        'orderby' => 'meta_value',
        'order' => 'ASC',
        'number' => $per_page,
        'paged' => $paged,
    );

    $users = new WP_User_Query($args);

    // Pagination variables
    $total_users = $users->get_total();
    $num_pages = ceil($total_users / $per_page);
	
	if (isset($_POST['manual_update'])) {
        update_users_roles_based_on_stripe_status();
    }
	
	// Display manual update button
    echo '<div class="manual-update">';
    echo '<form method="post" action="">';
    echo '<input type="submit" name="manual_update" class="button" value="Manually Update User Roles">';
    echo '</form>';
    echo '</div>';

    // Display the table with user information
    echo '<table class="widefat">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>Email</th>';
    echo '<th>Subscription Status</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    foreach ($users->results as $user) {
        // Get the user email
        $email = $user->user_email;

        // Get the transaction ID from the user meta
        $transaction_id = get_user_meta($user->ID, 'paygate_transaction_id', true);

        // Fetch the subscription status using the transaction ID
        $subscription_status = get_subscription_status($transaction_id);

        echo '<tr>';
        echo '<td>' . esc_html($email) . '</td>';
        echo '<td>' . esc_html($subscription_status) . '</td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';

    // Display pagination
    if ($num_pages > 1) {
        echo '<div class="tablenav">';
        echo '<div class="tablenav-pages">';
        echo paginate_links(array(
            'base' => add_query_arg('paged', '%#%'),
            'format' => '',
            'prev_text' => __('&laquo;'),
            'next_text' => __('&raquo;'),
            'total' => $num_pages,
            'current' => $paged,
        ));
        echo '</div>';
    }

    // Display the "Load more" button
    if ($paged < $num_pages) {
        echo '<div class="load-more">';
        echo '<a href="' . add_query_arg('paged', $paged + 1) . '" class="button">Load more</a>';
        echo '</div>';
    }

    echo '</div>'; // Close the .wrap div
}

// Associate User With Stripe Payment ID
function associate_transaction_id_to_user($entry, $form)
{
    // Check if it's the form you want to process (use saved Gravity Form ID)
    if ($form['id'] != get_option('gravity_form_id')) {
        return;
    }

    $email = rgar($entry, get_option('email_field_id')); // Use saved email field ID
    $transaction_id = rgar($entry, 'transaction_id');

    // Get the user by email
    $user = get_user_by('email', $email);

    if ($user instanceof WP_User) {
        $existing_transaction_id = get_user_meta($user->ID, 'paygate_transaction_id', true);

        if (empty($existing_transaction_id)) {
            update_user_meta($user->ID, 'paygate_transaction_id', $transaction_id);
        }
    }
}

add_action('gform_after_submission', 'associate_transaction_id_to_user', 10, 2);

function paygate_settings_page() {
    // Save settings
    if (isset($_POST['submit_paygate_settings'])) {
        update_option('stripe_api_key', sanitize_text_field($_POST['stripe_api_key']));
        update_option('gravity_form_id', intval($_POST['gravity_form_id']));
        update_option('email_field_id', intval($_POST['email_field_id']));
    }

    // Display settings form
    ?>
    <div class="wrap">
        <h2>Paygate Settings</h2>
        <form method="post" action="">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Stripe API Key</th>
                    <td>
                        <input type="text" name="stripe_api_key" value="<?php echo esc_attr(get_option('stripe_api_key')); ?>"/>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Gravity Form ID</th>
                    <td>
                        <input type="number" name="gravity_form_id" value="<?php echo intval(get_option('gravity_form_id')); ?>"/>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Email Field ID</th>
                    <td>
                        <input type="number" name="email_field_id" value="<?php echo intval(get_option('email_field_id')); ?>"/>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" name="submit_paygate_settings" class="button-primary" value="<?php _e('Save Changes') ?>"/>
            </p>
        </form>
    </div>
    <?php
}

function custom_user_roles_menu() {
    add_menu_page('Station Settings', 'Station Settings', 'manage_options', 'station-settings', 'station_settings_page');
    add_submenu_page('station-settings', 'Paygate', 'Paygate', 'manage_options', 'paygate', 'paygate_page');
    add_submenu_page('station-settings', 'Paygate Settings', 'Paygate Settings', 'manage_options', 'paygate-settings', 'paygate_settings_page');
}

add_action('admin_menu', 'custom_user_roles_menu');
