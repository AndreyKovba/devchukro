<ul class="products clearfix products-4">
    <?php
        while ( have_posts() ): the_post();
            include ABSPATH . 'wp-content/plugins/woocommerce/templates/content-product.php';
        endwhile; // end have_posts()
    ?>
</ul>

<?php
global $wp_query;
$total = $wp_query->max_num_pages - 1;
$current = (get_query_var('paged')) ? get_query_var('paged') : 1;
include ABSPATH . 'wp-content/plugins/woocommerce/templates/loop/pagination.php';
