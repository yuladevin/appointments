<?
function create_recurring_appoinments_table(){
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();
$table_name = "{$wpdb->prefix}harel_recurring_appoinments";
    $sql = "
    DROP $table_name IF EXISTS;
    CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name VARCHAR(35) NOT NULL,
        day_of_week TINYINT NOT NULL,
        hour TINYINT NOT NULL,
        minute TINYINT NOT NULL,
        post_date DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
        delete_date DATETIME,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );

    // to drop - DROP TABLE IF EXISTS harel_recurring_appoinments
}
create_recurring_appoinments_table();
