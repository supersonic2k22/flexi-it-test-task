<?php
/**
 * The template for displaying all pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 * @package Understrap
 */

// Exit if accessed directly.
defined('ABSPATH') || exit;

get_header();

$container = get_theme_mod('understrap_container_type');

?>

<div class="wrapper" id="page-wrapper">

    <div class="<?php echo esc_attr($container); ?>" id="content" tabindex="-1">

        <div class="row">

            <?php
            // Do the left sidebar check and open div#primary.
            get_template_part('global-templates/left-sidebar-check');
            ?>

            <main class="site-main" id="main">

                <div class="card">
                    <div class="card-header">
                        <h2><?php the_field('house_title'); ?></h2>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h5>Координати</h5>
                                <p><?php the_field('location_coordinates'); ?></p>
                            </div>
                            <div class="col-md-6">
                                <h5>Кількість поверхів</h5>
                                <p><?php the_field('number_of_floors'); ?></p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h5>Тип будинку</h5>
                                <p><?php the_field('building_type'); ?></p>
                            </div>
                            <div class="col-md-6">
                                <h5>Рівень екологічності</h5>
                                <p><?php the_field('ecology'); ?></p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <h5>Зображення</h5>
                                <?php $image = get_field('image'); ?>
                                <?php if ($image): ?>
                                    <img src="<?php echo esc_url($image['url']); ?>"
                                        alt="<?php echo esc_attr($image['alt']); ?>" class="img-fluid">
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <h5>Деталі</h5>
                                <?php if (have_rows('building')): ?>
                                    <div class="accordion" id="buildingAccordion">
                                        <?php while (have_rows('building')):
                                            the_row(); ?>
                                            <div class="card">
                                                <div class="card-header" id="heading-<?php echo get_row_index(); ?>">
                                                    <h2 class="mb-0">
                                                        <button class="btn btn-link" type="button" data-toggle="collapse"
                                                            data-target="#collapse-<?php echo get_row_index(); ?>"
                                                            aria-expanded="true"
                                                            aria-controls="collapse-<?php echo get_row_index(); ?>">
                                                            Будівля <?php echo get_row_index(); ?>
                                                        </button>
                                                    </h2>
                                                </div>
                                                <div id="collapse-<?php echo get_row_index(); ?>" class="collapse"
                                                    aria-labelledby="heading-<?php echo get_row_index(); ?>"
                                                    data-parent="#buildingAccordion">
                                                    <div class="card-body">
                                                        <p><strong>Площа:</strong> <?php the_sub_field('square'); ?></p>
                                                        <p><strong>Кількість кімнат:</strong>
                                                            <?php the_sub_field('number_of_rooms'); ?></p>
                                                        <p><strong>Балкон:</strong> <?php the_sub_field('balcony'); ?></p>
                                                        <p><strong>Санвузол:</strong> <?php the_sub_field('bathroom'); ?></p>
                                                        <?php $building_image = get_sub_field('image'); ?>
                                                        <?php if ($building_image): ?>
                                                            <img src="<?php echo esc_url($building_image['url']); ?>"
                                                                alt="<?php echo esc_attr($building_image['alt']); ?>"
                                                                class="img-fluid">
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                                <?php else: ?>
                                    <p>No building details available.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php

                // If comments are open or we have at least one comment, load up the comment template.
                if (comments_open() || get_comments_number()) {
                    comments_template();
                }
                ?>

            </main>

            <?php
            // Do the right sidebar check and close div#primary.
            get_template_part('global-templates/right-sidebar-check');
            ?>

        </div><!-- .row -->

    </div><!-- #content -->

</div><!-- #page-wrapper -->
<!-- Bootstrap JS and dependencies (optional) -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<?php
get_footer();
