<?php
/*
Plugin Name: Real Estate Object
Description: Registers a custom post type for real estate objects and a taxonomy for districts.
Version: 1.0
Author: Roman Karbivskyi
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Register custom post type "Об'єкт нерухомості"
function reo_register_custom_post_type()
{
    $labels = array(
        'name' => 'Об\'єкти нерухомості',
        'singular_name' => 'Об\'єкт нерухомості',
        'menu_name' => 'Нерухомість',
        'name_admin_bar' => 'Об\'єкт нерухомості',
        'add_new' => 'Додати новий',
        'add_new_item' => 'Додати новий об\'єкт нерухомості',
        'new_item' => 'Новий об\'єкт нерухомості',
        'edit_item' => 'Редагувати об\'єкт нерухомості',
        'view_item' => 'Переглянути об\'єкт нерухомості',
        'all_items' => 'Всі об\'єкти нерухомості',
        'search_items' => 'Шукати об\'єкти нерухомості',
        'not_found' => 'Не знайдено',
        'not_found_in_trash' => 'Не знайдено у кошику'
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'real-estate-object'),
        'capability_type' => 'post',
        'has_archive' => true,
        'hierarchical' => false,
        'menu_position' => 20,
        'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'comments'),
    );

    register_post_type('real_estate_object', $args);
}

// Register taxonomy "Район"
function reo_register_custom_taxonomy()
{
    $labels = array(
        'name' => 'Райони',
        'singular_name' => 'Район',
        'search_items' => 'Шукати райони',
        'all_items' => 'Всі райони',
        'edit_item' => 'Редагувати район',
        'update_item' => 'Оновити район',
        'add_new_item' => 'Додати новий район',
        'new_item_name' => 'Нова назва району',
        'menu_name' => 'Район',
    );

    $args = array(
        'labels' => $labels,
        'hierarchical' => false, // Set to false if you don't want parent-child relationships
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'district'),
    );

    register_taxonomy('district', array('real_estate_object'), $args);
}
// Function to hide default fields
function reo_hide_default_fields()
{
    remove_post_type_support('real_estate_object', 'editor');    // Hide editor
    remove_post_type_support('real_estate_object', 'comments');  // Hide comments
    remove_post_type_support('real_estate_object', 'thumbnail'); // Hide featured image
    remove_post_type_support('real_estate_object', 'excerpt');   // Hide excerpt

}

// Remove the default taxonomy metabox and replace it with a select dropdown
function reo_add_custom_taxonomy_dropdown()
{
    // Remove default metabox
    remove_meta_box('tagsdiv-district', 'real_estate_object', 'side');

    // Add custom dropdown metabox
    add_meta_box('district_dropdown', 'Район', 'reo_district_dropdown_metabox', 'real_estate_object', 'side', 'default');
}


// Custom callback to display taxonomy as a select dropdown
function reo_district_dropdown_metabox($post)
{
    // Get all terms in the "district" taxonomy
    $terms = get_terms(array(
        'taxonomy' => 'district',
        'hide_empty' => false,
    ));

    // Get currently selected terms for this post
    $current_terms = wp_get_object_terms($post->ID, 'district', array('fields' => 'ids'));
    $current_term_id = !empty($current_terms) ? $current_terms[0] : ''; // Assuming only one term

    // Start the select dropdown
    echo '<select name="district_select" id="district-select" class="postbox">';
    echo '<option value="">Виберіть район</option>';

    foreach ($terms as $term) {
        $selected = ($term->term_id == $current_term_id) ? ' selected="selected"' : '';
        echo '<option value="' . $term->term_id . '"' . $selected . '>' . $term->name . '</option>';
    }

    echo '</select>';
}

// Save the selected district term
function reo_save_district_term($post_id)
{
    // Check if the 'district_select' is set and valid
    if (isset($_POST['district_select'])) {
        $district_term = intval($_POST['district_select']); // Get the selected term ID

        // Set the post's taxonomy term for "district"
        wp_set_object_terms($post_id, $district_term, 'district', false);
    }
}
// Функція, яка відображає фільтр
// Функція, яка відображає фільтр
function reo_filter_shortcode()
{
    // Get the real range of square meters
    $square_range = get_real_square_range();
    $min_square = $square_range['min'];
    $max_square = $square_range['max'];
    ob_start(); ?>
    <div id="real-estate-filter">
        <form id="real-estate-filter-form">
            <!-- Поля фільтрації -->
            <label for="district">Район:</label>
            <?php
            // Dropdown з районами
            $terms = get_terms(array('taxonomy' => 'district', 'hide_empty' => false));
            echo '<select name="district" id="district">';
            echo '<option value="">Виберіть район</option>';
            foreach ($terms as $term) {
                echo '<option value="' . $term->term_id . '">' . $term->name . '</option>';
            }
            echo '</select>';
            ?>

            <!-- Діапазон площі -->
            <label for="square">Площа (кв.м):</label>
            <input type="range" class="form-range" id="square" name="square" min="<?php echo esc_attr($min_square); ?>"
                max="<?php echo esc_attr($max_square); ?>" step="10" oninput="this.nextElementSibling.value = this.value">
            <output><?php echo ($max_square + $min_square) / 2; ?></output> кв.м

            <button type="submit">Шукати</button>
        </form>
    </div>
    <div id="real-estate-results"></div>
    <?php
    return ob_get_clean();
}


class Real_Estate_Filter_Widget extends WP_Widget
{
    public function __construct()
    {
        parent::__construct(
            'real_estate_filter_widget',
            'Фільтр об\'єктів нерухомості',
            array('description' => 'Віджет фільтрації об\'єктів нерухомості')
        );
    }

    public function widget($args, $instance)
    {
        echo do_shortcode('[real_estate_filter]');
    }
}

// Реєструємо віджет
function register_real_estate_filter_widget()
{
    register_widget('Real_Estate_Filter_Widget');
}
// AJAX action для пошуку
function reo_ajax_search()
{
    $district = isset($_POST['district']) ? intval($_POST['district']) : '';
    $square = isset($_POST['square']) ? intval($_POST['square']) : '';
    $paged = isset($_POST['paged']) ? intval($_POST['paged']) : 1;

    $args = array(
        'post_type' => 'real_estate_object',
        'posts_per_page' => 5,
        'paged' => $paged,
        'meta_query' => array(
            'relation' => 'AND'
        )
    );

    // Фільтрація по району (taxonomy)
    if (!empty($district)) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'district',
                'field' => 'id',
                'terms' => $district,
            ),
        );
    }

    // Add square filter if selected
    if (!empty($square)) {
        $args['meta_query'][] = array(
            'key' => 'building_0_square', // ACF field inside repeater
            'value' => $square,
            'compare' => '<=',
            'type' => 'NUMERIC'
        );
    }

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            ?>
            <div class="real-estate-item">
                <a href="<?php the_permalink(); ?>">
                    <?php the_post_thumbnail('thumbnail'); ?>
                    <h3><?php the_title(); ?></h3>
                    <p><?php echo wp_trim_words(get_the_excerpt(), 20); ?></p>
                </a>
            </div>
            <?php
        }
        $total_pages = $query->max_num_pages;
        if ($total_pages > 1) {
            ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
                    <button class="pagination-btn" data-page="<?php echo $i; ?>"><?php echo $i; ?></button>
                <?php } ?>
            </div>
            <?php
        }
    } else {
        echo '<p>Нічого не знайдено</p>';
    }

    wp_die();
}
function get_real_square_range()
{
    global $wpdb;

    // Query to get the minimum value of the ACF "square" subfield inside the "building" repeater
    $min_square = $wpdb->get_var("
        SELECT MIN(CAST(meta_value AS UNSIGNED)) 
        FROM $wpdb->postmeta 
        WHERE meta_key LIKE 'building_%_square'
    ");

    // Query to get the maximum value of the ACF "square" subfield inside the "building" repeater
    $max_square = $wpdb->get_var("
        SELECT MAX(CAST(meta_value AS UNSIGNED)) 
        FROM $wpdb->postmeta 
        WHERE meta_key LIKE 'building_%_square'
    ");

    // Return the range as an associative array
    return array(
        'min' => $min_square ? intval($min_square) : 0,
        'max' => $max_square ? intval($max_square) : 500, // Set a fallback if no values are found
    );
}


function reo_enqueue_assets()
{
    // Підключення CSS файлу
    wp_enqueue_style('reo-style', plugin_dir_url(__FILE__) . 'assets/css/style.css', array(), '1.0.0');

    // Підключення JS файлу
    wp_enqueue_script('reo-script', plugin_dir_url(__FILE__) . 'assets/js/filter.js', array('jquery'), '1.0.0', true);

    // Передаємо ajaxurl для використання в JS
    wp_localize_script('reo-script', 'ajaxurl', admin_url('admin-ajax.php'));
}



// Hook into WordPress
add_action('init', 'reo_register_custom_post_type');
add_action('init', 'reo_register_custom_taxonomy');
add_action('init', 'reo_hide_default_fields');
add_action('add_meta_boxes', 'reo_add_custom_taxonomy_dropdown');
add_action('save_post', 'reo_save_district_term');
add_shortcode('real_estate_filter', 'reo_filter_shortcode');
add_action('widgets_init', 'register_real_estate_filter_widget');
add_action('wp_ajax_reo_search', 'reo_ajax_search');
add_action('wp_ajax_nopriv_reo_search', 'reo_ajax_search');
add_action('wp_enqueue_scripts', 'reo_enqueue_assets');







