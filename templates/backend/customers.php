<?php

defined( 'ABSPATH' ) or exit;

/*
 *  Customers
 */

global $wpdb;

$search = isset( $_GET['s'] ) ? $_GET['s'] : '';
$page = isset( $_GET['p'] ) && $_GET['p'] > 1 ? $_GET['p'] : 1;
$results_per_page = get_option( 'yith-wcch-results_per_page' ) > 0 ? get_option( 'yith-wcch-results_per_page' ) : 50;
$users_offset = ( $page - 1 ) * $results_per_page;

$users = get_users( array( 'role' => 'customer' ) );
$num_users = count( $users );
$max_pages = ceil( $num_users / $results_per_page );

$users = get_users(
    array(
        'role' => 'customer',
        'offset' => $users_offset,
        'number' => $results_per_page,
        'search' => $search,
    )
);
if ( get_option('yith-wcch-hide_users_with_no_orders') ) {
    foreach ( $users as $key => $user) {
        $order_count = count( get_posts( array(
            'numberposts' => -1,
            'meta_key'    => '_customer_user',
            'meta_value'  => $user->ID,
            'post_type'   => 'shop_order',
            'post_status' =>  'any',
            'post_parent' => '0',
        ) ) );
        if ( ! $order_count > 0 ) { unset( $users[$key] ); }
    }
}

?>

<div id="yith-woocommerce-customer-history">
    <div id="customers" class="wrap">

        <h1><?php echo __( 'Customers', 'yith-woocommerce-customer-history' ); ?></h1>

        <form action="" class="search-box" style="float: right;">
            <label class="screen-reader-text" for="user-search-input"><?php echo __( 'Search Customer', 'yith-woocommerce-customer-history' ); ?>:</label>
            <input type="hidden" name="page" value="yith-wcch-customers.php">
            <input type="search" id="user-search-input" name="s" value="<?php echo $search; ?>">
            <input type="submit" id="search-submit" class="button" value="<?php echo __( 'Search Customer', 'yith-woocommerce-customer-history' ); ?>">
        </form>

        <p><?php echo __( 'Complete customers list.', 'yith-woocommerce-customer-history' ); ?></p>

        <div class="tablenav top">
            <ul class="subsubsub" style="margin-top: 4px;">
                <li class="customers"><a href="admin.php?page=yith-wcch-customers.php" class="current"><?php echo __( 'Customers', 'yith-woocommerce-customer-history' ); ?></a> |</li>
                <li class="users"><a href="admin.php?page=yith-wcch-users.php"><?php echo __( 'Other Users', 'yith-woocommerce-customer-history' ); ?></a></li>
            </ul>
            <div class="tablenav-pages">
                <div class="pagination-links">
                    <?php echo __( 'Total', 'yith-woocommerce-customer-history' ) . ': ' . $num_users; ?> &nbsp; | &nbsp;
                    <?php echo __( 'Page', 'yith-woocommerce-customer-history' ) . ': ' . $page . ' of ' . $max_pages; ?> &nbsp;
                    <?php if ( $page > 1 ) : ?>
                    <a class="prev-page" href="admin.php?page=yith-wcch-customers.php&p=1"><span aria-hidden="true">‹‹</span></a>
                    <a class="prev-page" href="admin.php?page=yith-wcch-customers.php&p=<?php echo $page - 1; ?>"><span aria-hidden="true">‹</span></a>
                    <?php else : ?>
                    <span class="tablenav-pages-navspan" aria-hidden="true">‹‹</span>
                    <span class="tablenav-pages-navspan" aria-hidden="true">‹</span>
                    <?php endif; ?>
                    <?php if ( $page < $max_pages ) : ?>
                    <a class="next-page" href="admin.php?page=yith-wcch-customers.php&p=<?php echo $page + 1; ?>"><span aria-hidden="true">›</span></a>
                    <a class="next-page" href="admin.php?page=yith-wcch-customers.php&p=<?php echo $max_pages; ?>"><span aria-hidden="true">››</span></a>
                    <?php else : ?>
                    <span class="tablenav-pages-navspan" aria-hidden="true">›</span>
                    <span class="tablenav-pages-navspan" aria-hidden="true">››</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <table class="wp-list-table widefat fixed striped posts">
            <tr>
                <th><?php echo __( 'User', 'yith-woocommerce-customer-history' ); ?></th>
                <th><?php echo __( 'Role', 'yith-woocommerce-customer-history' ); ?></th>
                <th><?php echo __( 'Orders', 'yith-woocommerce-customer-history' ); ?></th>
                <th><?php echo __( 'Pending Orders', 'yith-woocommerce-customer-history' ); ?></th>
                <th><?php echo __( 'Refund Orders', 'yith-woocommerce-customer-history' ); ?></th>
                <th><?php echo __( 'Orders average', 'yith-woocommerce-customer-history' ); ?></th>
                <th><?php echo __( 'Total Spent', 'yith-woocommerce-customer-history' ); ?></th>
                <th><?php echo __( 'Actions', 'yith-woocommerce-customer-history' ); ?></th>
            </tr>

            <?php

            foreach ( $users as $user ) :

                // $order_count = wc_get_customer_order_count( $user->ID );
                // $total_spent = wc_get_customer_total_spent( $user->ID );
                $total_spent = yith_ch_get_customer_total_spent( $user->ID );

                $order_count = count( get_posts( array(
                    'numberposts' => -1,
                    'meta_key'    => '_customer_user',
                    'meta_value'  => $user->ID,
                    'post_type'   => 'shop_order',
                    'post_status' =>  'any',
                    'post_parent' => '0',
                ) ) );

                $pending_orders_count = count( get_posts( array(
                    'numberposts' => -1,
                    'meta_key'    => '_customer_user',
                    'meta_value'  => $user->ID,
                    'post_type'   => 'shop_order',
                    'post_status' =>  array( 'pending', 'wc-pending'),
                    'post_parent' => '0',
                ) ) );

                $refunded_orders_count = count( get_posts( array(
                    'numberposts' => -1,
                    'meta_key'    => '_customer_user',
                    'meta_value'  => $user->ID,
                    'post_type'   => 'shop_order',
                    'post_status' =>  array( 'refunded', 'wc-refunded'),
                    'post_parent' => '0',
                ) ) );

                ?>

                <tr>
                    <td><a href="admin.php?page=yith-wcch-customer.php&user_id=<?php echo esc_html( $user->ID ); ?>"><?php echo esc_html( $user->display_name ); ?></a></td>
                    <td><?php

                        global $wp_roles;
                        $roles_array = array();
                        foreach ( $wp_roles->role_names as $role => $name ) {
                            if ( user_can( $user, $role ) ) {
                                $roles_array[] = $role;
                            }
                        }
                        echo implode( '<br />', $roles_array );

                    ?></td>
                    <td><?php echo $order_count; ?></td>
                    <td><?php echo $pending_orders_count; ?></td>
                    <td><?php echo $refunded_orders_count; ?></td>
                    <td><?php echo $order_count > 0 ? wc_price( $total_spent / $order_count ) : wc_price( $total_spent ); ?></td>
                    <td><?php echo wc_price( $total_spent ); ?></td>
                    <td>
                        <a href="admin.php?page=yith-wcch-customer.php&user_id=<?php echo esc_html( $user->ID ); ?>" class="button"><strong><i class="fa fa-eye" aria-hidden="true"></i> <?php echo __( 'View', 'yith-woocommerce-customer-history' ); ?></strong></a>
                        <a href="admin.php?page=yith-wcch-email.php&customer_id=<?php echo $user->ID; ?>" class="button"><strong><i class="fa fa-envelope" aria-hidden="true"></i> <?php echo __( 'Email', 'yith-woocommerce-customer-history' ); ?></strong></a>
                    </td>
                </tr>

            <?php endforeach; ?>

        </table>

        <div class="tablenav bottom">
            <div class="tablenav-pages">
                <div class="pagination-links">
                    <?php echo __( 'Total', 'yith-woocommerce-customer-history' ) . ': ' . $num_users; ?> &nbsp; | &nbsp;
                    <?php echo __( 'Page', 'yith-woocommerce-customer-history' ) . ': ' . $page . ' of ' . $max_pages; ?> &nbsp;
                    <?php if ( $page > 1 ) : ?>
                    <a class="prev-page" href="admin.php?page=yith-wcch-customers.php&p=1"><span aria-hidden="true">‹‹</span></a>
                    <a class="prev-page" href="admin.php?page=yith-wcch-customers.php&p=<?php echo $page - 1; ?>"><span aria-hidden="true">‹</span></a>
                    <?php else : ?>
                    <span class="tablenav-pages-navspan" aria-hidden="true">‹‹</span>
                    <span class="tablenav-pages-navspan" aria-hidden="true">‹</span>
                    <?php endif; ?>
                    <?php if ( $page < $max_pages ) : ?>
                    <a class="next-page" href="admin.php?page=yith-wcch-customers.php&p=<?php echo $page + 1; ?>"><span aria-hidden="true">›</span></a>
                    <a class="next-page" href="admin.php?page=yith-wcch-customers.php&p=<?php echo $max_pages; ?>"><span aria-hidden="true">››</span></a>
                    <?php else : ?>
                    <span class="tablenav-pages-navspan" aria-hidden="true">›</span>
                    <span class="tablenav-pages-navspan" aria-hidden="true">››</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>
