<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/**
 * WooCommerce_Page_Builder_Addon Class
 *
 * Base class for the WooCommerce Page Builder Addon
 *
 * @package WordPress
 * @subpackage WooCommerce_Page_Builder_Addon
 * @category Core
 * @author Pootlepress
 * @since 1.0.0
 *
 * TABLE OF CONTENTS
 *
 * public $token
 * public $version
 * 
 * - __construct()
 * - add_theme_options()
 * - get_menu_styles()
 * - load_stylesheet()
 * - load_script()
 * - load_localisation()
 * - check_plugin()
 * - load_plugin_textdomain()
 * - activation()
 * - register_plugin_version()
 * - get_header()
 * - woo_nav_custom()
 */
class WooCommerce_Page_Builder_addon {
	public $token = 'woocommerce-page-builder-addon';
	public $version;
	private $file;

	/**
	 * Constructor.
	 * @param string $file The base file of the plugin.
	 * @access public
	 * @since  1.0.0
	 * @return  void
	 */
	public function __construct ( $file ) {
		$this->file = $file;
		$this->load_plugin_textdomain();
		add_action( 'init', array( &$this, 'load_localisation' ), 0 );

		// Run this on activation.
		register_activation_hook( $file, array( &$this, 'activation' ) );

        add_action('widgets_init', array($this, 'register_widgets'));

        add_action('add_meta_boxes_product', array($this, 'add_product_meta_box'));

        add_action('save_post', array($this, 'save_post'));

//        add_action('template_redirect', array($this, 'template_redirect'));
        add_filter('template_include', array($this, 'filter_template_include'), 20); // need to filter later than WooCommerce Template Loader

        // Add the custom theme options.
        add_action('product_cat_edit_form_fields', array($this, 'add_product_category_option'), 5, 2);

        $taxonomy = 'product_cat';
        add_action( "edit_$taxonomy", array($this, 'save_product_cat'), 10, 2);
	} // End __construct()


    public function register_widgets(){

        register_widget('WX_PB_Product_Image');
        register_widget('WX_PB_Product_Title');
        register_widget('WX_PB_Review_Scores');
        register_widget('WX_PB_Product_Excerpt');
        register_widget('WX_PB_Product_Price');
        register_widget('WX_PB_Quantity_Cart');
        register_widget('WX_PB_Product_Description');
        register_widget('WX_PB_Product_Reviews');
        register_widget('WX_PB_Related_Products');
//        register_widget('WX_PB_Product_Grid');
    }

    public function add_product_meta_box() {

        add_meta_box('wx-pb-product-page-meta-box', 'Shop Builder Page', array($this, 'product_page_meta_box'), null, 'side');
    }

    public function filter_template_include($template) {
        if (is_product()) {
            global $product;
            $product = get_page_by_path($product, OBJECT, 'product');
            $linkedPageID = get_post_meta($product->ID, 'wx-pb-product-page', true);
            if (!empty($linkedPageID)) {
                $template = get_page_template();

                global $wp_query;
                $wp_query->current_post = -1;
                $wp_query->posts = array($wp_query->posts[0]);
                $wp_query->post_count = 1;
                global $wp_the_query;
                $wp_the_query = $wp_query;

                add_filter( 'the_content', array($this, 'filter_content'), 7 ); // 10 is used by PaidMembership Pro

                // remove pagination added by integration with WooCommerce
                remove_action( 'woocommerce_after_main_content', 'canvas_commerce_pagination', 01);
            }
        } else if (is_product_category()) {
            global $wp_query;
            $productCatSlug = $wp_query->query_vars['product_cat'];
            $term = get_term_by('slug', $productCatSlug, 'product_cat');
            $linkedPageID = get_woocommerce_term_meta($term->term_id, 'wx-pb-product-page', true);
            if (!empty($linkedPageID)) {

                $wp_query = new WP_Query(array('page_id' => $linkedPageID));
//                $template = wc_locate_template('taxonomy-product_cat.php');

                $template = get_page_template();
                $wp_query->current_post = -1;
                $wp_query->posts = array($wp_query->posts[0]);
                $wp_query->post_count = 1;
                global $wp_the_query;
                $wp_the_query = $wp_query;

                add_filter( 'the_content', array($this, 'filter_content'), 7 ); // 10 is used by PaidMembership Pro

                // remove pagination added by integration with WooCommerce
                remove_action( 'woocommerce_after_main_content', 'canvas_commerce_pagination', 01);
            }
        }
        return $template;
    }

    public function filter_content($content) {
        $productID = get_the_ID();

        $linkedPageID = get_post_meta($productID, 'wx-pb-product-page', true);
        if (!empty($linkedPageID)) {

            remove_filter( 'the_content', array($this, 'filter_content'), 7 ); // 10 is used by PaidMembership Pro

            $panel_content = siteorigin_panels_render( $linkedPageID );

            if ( !empty( $panel_content ) ) $content = $panel_content;
        }

        return $content;
    }

    public function product_page_meta_box() {

        global $post;
        $productPageID = get_post_meta($post->ID, 'wx-pb-product-page', true);
        if (empty($productPageID)) {
            $productPageID = 0;
        }

        echo '<select name="wx-pb-product-page">';
        echo '<option value="">Select page</option>'; // empty value or else the value saved will be "Select page"

        $pages = get_posts(array('post_type' => 'page', 'posts_per_page' => -1, 'orderby' => 'post_title', 'order' => 'ASC'));
        foreach ($pages as $page) {
            echo '<option value="' . $page->ID . '" ' . selected($productPageID, $page->ID) . '>' . $page->post_title . "</option>";
        }

        echo '</select>';

        wp_nonce_field( 'wx_pb_product_meta_box', 'wx_pb_product_meta_box_nonce' );
    }

    public function save_post($postID) {

        /*
         * We need to verify this came from our screen and with proper authorization,
         * because the save_post action can be triggered at other times.
         */

        // Check if our nonce is set.
        if ( ! isset( $_POST['wx_pb_product_meta_box_nonce'] ) ) {
            return;
        }

        // Verify that the nonce is valid.
        if ( ! wp_verify_nonce( $_POST['wx_pb_product_meta_box_nonce'], 'wx_pb_product_meta_box' ) ) {
            return;
        }

        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // Check the user's permissions.
        if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {

            if ( ! current_user_can( 'edit_page', $postID ) ) {
                return;
            }

        } else {

            if ( ! current_user_can( 'edit_post', $postID ) ) {
                return;
            }
        }

        /* OK, it's safe for us to save the data now. */

        // Make sure that it is set.
        if ( ! isset( $_POST['wx-pb-product-page'] ) ) {
            $selectedPageID = '';
        } else {
            $selectedPageID = $_POST['wx-pb-product-page'];
        }


        update_post_meta($postID, 'wx-pb-product-page', $selectedPageID);
    }


    /**
     * Add plugin options to WooCommerce
     * @access public
     * @since 1.0.0
     * @return array
     */
    public function add_product_category_option($term, $taxonomy)
    {
        $productPageID = get_woocommerce_term_meta($term->term_id, 'wx-pb-product-page', true);
        if (empty($productPageID)) {
            $productPageID = 0;
        }

?>
<tr class="form-field">
			<th scope="row"><label for="wx-pb-product-page"><?php _e('Product Page', 'wx-pb'); ?></label></th>
<td>
    <?php
        echo '<select id="wx-pb-product-page" name="wx-pb-product-page">';
        echo '<option value="">Select page</option>'; // empty value or else the value saved will be "Select page"

        $pages = get_posts(array('post_type' => 'page', 'posts_per_page' => -1, 'orderby' => 'post_title', 'order' => 'ASC'));
        foreach ($pages as $page) {
            echo '<option value="' . $page->ID . '" ' . selected($productPageID, $page->ID) . '>' . $page->post_title . "</option>";
        }

        echo '</select>';
        ?>
</td>
</tr>
<?php

    }

    public function save_product_cat($term_id, $tt_id) {
        $productPageID = isset($_REQUEST['wx-pb-product-page']) ? (int)$_REQUEST['wx-pb-product-page'] : 0;
        update_woocommerce_term_meta($term_id, 'wx-pb-product-page', $productPageID);
    }

	/**
	 * Load the plugin's localisation file.
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function load_localisation () {
		load_plugin_textdomain( $this->token, false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_localisation()

	/**
	 * Load the plugin textdomain from the main WordPress "languages" folder.
	 * @access public
	 * @since  1.0.0
	 * @return  void
	 */
	public function load_plugin_textdomain () {
	    $domain = $this->token;
	    // The "plugin_locale" filter is also used in load_plugin_textdomain()
	    $locale = apply_filters( 'plugin_locale', get_locale(), $domain );
	 
	    load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
	    load_plugin_textdomain( $domain, FALSE, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_plugin_textdomain()

	/**
	 * Run on activation.
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function activation () {
		$this->register_plugin_version();
	} // End activation()

	/**
	 * Register the plugin's version.
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	private function register_plugin_version () {
		if ( $this->version != '' ) {
			update_option( $this->token . '-version', $this->version );
		}
	} // End register_plugin_version()


} // End Class


