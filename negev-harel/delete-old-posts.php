<?
function get_delete_old_post() {
    $query = new WP_Query( [
        'fields'         => 'ids',
        'post_type'      =>  ['pro-appointments'],
        'posts_per_page' => '-1',
        'date_query'     => [
                                    'column'  => 'post_date',
                                    'before'   => '-1 months'
        ],
        ] );
    if ( !$query->have_posts() ) {
        return;
    }
    while ( $query->have_posts() ) {
        $query->the_post();
        wp_delete_post(get_the_ID(),true);
    }
    wp_reset_postdata();

}

// add_action('init','get_delete_old_post');
