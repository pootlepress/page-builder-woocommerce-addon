<?php
/**
 * Created by Alan on 24/11/2014.
 */

class WX_PB_Product_Image extends WP_Widget
{
    function __construct() {
        parent::__construct(
            'wx-pb-product-image',
            __( 'Product Image', 'wx-pp-pb' ),
            array(
                'description' => __( 'Displays product image.', 'wx-pp-pb' ),
            )
        );
    }

    function widget( $args, $instance ) {
        echo $args['before_widget'];

        woocommerce_show_product_images();

        echo $args['after_widget'];
    }

    function update( $new, $old ) {
        return $new;
    }

    function form( $instance ) {
    }
}

class WX_PB_Product_Title extends WP_Widget
{
    function __construct() {
        parent::__construct(
            'wx-pb-product-title',
            __( 'Product Title', 'wx-pp-pb' ),
            array(
                'description' => __( 'Displays product title.', 'wx-pp-pb' ),
            )
        );
    }

    function widget( $args, $instance ) {
        echo $args['before_widget'];

        woocommerce_template_single_title();

        echo $args['after_widget'];
    }

    function update( $new, $old ) {
        return $new;
    }

    function form( $instance ) {
    }
}

class WX_PB_Review_Scores extends WP_Widget
{
    function __construct() {
        parent::__construct(
            'wx-pb-review-scores',
            __( 'Review Scores', 'wx-pp-pb' ),
            array(
                'description' => __( 'Displays review scores.', 'wx-pp-pb' ),
            )
        );
    }

    function widget( $args, $instance ) {
        echo $args['before_widget'];

        woocommerce_template_single_rating();

        echo $args['after_widget'];
    }

    function update( $new, $old ) {
        return $new;
    }

    function form( $instance ) {
    }
}

class WX_PB_Product_Excerpt extends WP_Widget
{
//    function __construct() {
//        parent::__construct();
//
//        $this->name = 'Product Excerpt';
//        $this->id_base = 'wx-pb-product-excerpt';
//        $this->widget_options['description'] = 'Display product excerpt.';
//    }
    function __construct() {
        parent::__construct(
            'wx-pb-product-excerpt',
            __( 'Product Excerpt', 'wx-pp-pb' ),
            array(
                'description' => __( 'Displays product excerpt.', 'wx-pp-pb' ),
            )
        );
    }

    function widget( $args, $instance ) {
        echo $args['before_widget'];

        woocommerce_template_single_excerpt();

        echo $args['after_widget'];
    }

    function update( $new, $old ) {
        return $new;
    }

    function form( $instance ) {
    }
}

class WX_PB_Product_Price extends WP_Widget
{
    function __construct() {
        parent::__construct(
            'wx-pb-product-price',
            __( 'Product Price', 'wx-pp-pb' ),
            array(
                'description' => __( 'Displays product price.', 'wx-pp-pb' ),
            )
        );
    }

    function widget( $args, $instance ) {
        echo $args['before_widget'];

        woocommerce_template_single_price();

        echo $args['after_widget'];
    }

    function update( $new, $old ) {
        return $new;
    }

    function form( $instance ) {
    }
}

class WX_PB_Quantity_Cart extends WP_Widget
{
    function __construct() {
        parent::__construct(
            'wx-pb-quantity-cart',
            __( 'Quantity / Add to Cart', 'wx-pp-pb' ),
            array(
                'description' => __( 'Displays quantity/add to cart.', 'wx-pp-pb' ),
            )
        );
    }

    function widget( $args, $instance ) {
        echo $args['before_widget'];

        woocommerce_template_single_add_to_cart();

        echo $args['after_widget'];
    }

    function update( $new, $old ) {
        return $new;
    }

    function form( $instance ) {
    }
}

class WX_PB_Product_Description extends WP_Widget
{
    function __construct() {
        parent::__construct(
            'wx-pb-product-description',
            __( 'Product Description', 'wx-pp-pb' ),
            array(
                'description' => __( 'Displays product description.', 'wx-pp-pb' ),
            )
        );
    }

    function widget( $args, $instance ) {
        echo $args['before_widget'];

        woocommerce_product_description_tab();

        echo $args['after_widget'];
    }

    function update( $new, $old ) {
        return $new;
    }

    function form( $instance ) {
    }
}

class WX_PB_Product_Reviews extends WP_Widget
{
    private $instance;

    function __construct() {
        parent::__construct(
            'wx-pb-product-reviews',
            __( 'Product Reviews', 'wx-pp-pb' ),
            array(
                'description' => __( 'Displays product reviews.', 'wx-pp-pb' ),
            )
        );
    }

    function widget( $args, $instance ) {
        echo $args['before_widget'];


        add_filter('woocommerce_product_review_list_args', array($this, 'filter_comments_args'));

        // set this to let the filter have access to the instance data
        $this->instance = $instance;

        comments_template(); // this is the callback function for reviews tab

        $this->instance = null;


        remove_filter('woocommerce_product_review_list_args', array($this, 'filter_comments_args'));

        echo $args['after_widget'];
    }

    public function filter_comments_args($args) {
        $args['per_page'] = $this->instance['per_page'];
        return $args;
    }

    function update( $new, $old ) {
        $new = wp_parse_args($new, array(
            'per_page' => '5',
        ));
        return $new;
    }

    function form( $instance ) {
        $instance = wp_parse_args($instance, array(
            'per_page' => '5'
        ));

        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'per_page' ) ?>"><?php _e( 'Reviews per page', 'wx-pp-pb' ) ?></label>
            <input type="number" min='1' max='100' step='1' class="small-text" id="<?php echo $this->get_field_id( 'per_page' ) ?>" name="<?php echo $this->get_field_name( 'per_page' ) ?>" value="<?php echo esc_attr($instance['per_page']) ?>" />
        </p>
    <?php
    }
}

class WX_PB_Related_Products extends WP_Widget
{
    private $instance;

    function __construct() {
        parent::__construct(
            'wx-pb-related-products',
            __( 'Related Products', 'wx-pp-pb' ),
            array(
                'description' => __( 'Displays related products.', 'wx-pp-pb' ),
            )
        );
    }

    function widget( $args, $instance ) {
        echo $args['before_widget'];

        $this->instance = $instance;

        add_filter('woocommerce_output_related_products_args', array($this, 'filter_related_products_args'));
        woocommerce_output_related_products();
        remove_filter('woocommerce_output_related_products_args', array($this, 'filter_related_products_args'));

        $this->instance = null;

        echo $args['after_widget'];
    }

    public function filter_related_products_args($args) {
        $args['posts_per_page'] = $this->instance['posts_per_page'];
        return $args;
    }

    function update( $new, $old ) {
        return $new;
    }

    function form( $instance ) {
        $instance = wp_parse_args($instance, array(
            'posts_per_page' => '5'
        ));

        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'posts_per_page' ) ?>"><?php _e( 'Related products per page', 'wx-pp-pb' ) ?></label>
            <input type="number" min='1' max='100' step='1' class="small-text" id="<?php echo $this->get_field_id( 'posts_per_page' ) ?>" name="<?php echo $this->get_field_name( 'posts_per_page' ) ?>" value="<?php echo esc_attr($instance['posts_per_page']) ?>" />
        </p>
    <?php
    }
}

//class WX_PB_Product_Grid extends WP_Widget
//{
//    function __construct() {
//        parent::__construct(
//            'wx-pb-product-grid',
//            __( 'Product Grid', 'wx-pp-pb' ),
//            array(
//                'description' => __( 'Displays product grid.', 'wx-pp-pb' ),
//            )
//        );
//    }
//
//    function widget( $args, $instance ) {
//        echo $args['before_widget'];
//
//        global $wp_query;
//        $old_query = $wp_query;
//        $wp_query = new WP_Query(array('post_type' => 'product'));
//
//        $this->output_product_archive();
//
//        $wp_query = $old_query;
//
//        echo $args['after_widget'];
//    }
//
//    public function output_product_archive() {
//        // code from WooCommerce archive-product.php
//        ?>
<!---->
<!--        --><?php //if ( have_posts() ) : ?>
<!---->
<!--            --><?php
//            /**
//             * woocommerce_before_shop_loop hook
//             *
//             * @hooked woocommerce_result_count - 20
//             * @hooked woocommerce_catalog_ordering - 30
//             */
//            do_action( 'woocommerce_before_shop_loop' );
//            ?>
<!---->
<!--            --><?php //woocommerce_product_loop_start(); ?>
<!---->
<!--            --><?php //woocommerce_product_subcategories(); ?>
<!---->
<!--            --><?php //while ( have_posts() ) : the_post(); ?>
<!---->
<!--                --><?php //wc_get_template_part( 'content', 'product' ); ?>
<!---->
<!--            --><?php //endwhile; // end of the loop. ?>
<!---->
<!--            --><?php //woocommerce_product_loop_end(); ?>
<!---->
<!--            --><?php
//            /**
//             * woocommerce_after_shop_loop hook
//             *
//             * @hooked woocommerce_pagination - 10
//             */
//            do_action( 'woocommerce_after_shop_loop' );
//            ?>
<!---->
<!--        --><?php //elseif ( ! woocommerce_product_subcategories( array( 'before' => woocommerce_product_loop_start( false ), 'after' => woocommerce_product_loop_end( false ) ) ) ) : ?>
<!---->
<!--            --><?php //wc_get_template( 'loop/no-products-found.php' ); ?>
<!---->
<!--        --><?php //endif; ?>
<?php
//    }
//
//    function update( $new, $old ) {
//        return $new;
//    }
//
//    function form( $instance ) {
//    }
//}
