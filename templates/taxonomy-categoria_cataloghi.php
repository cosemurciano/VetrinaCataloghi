<?php
/**
 * Template for catalog category listings.
 *
 * @package VetrinaCataloghi
 */

get_header();
?>

<style>
.vc-cataloghi-grid{display:flex;flex-wrap:wrap;margin:0 -10px;}
.vc-cataloghi-item{width:25%;padding:0 10px;box-sizing:border-box;margin-bottom:20px;text-align:center;}
@media(max-width:1024px){.vc-cataloghi-item{width:33.3333%;}}
@media(max-width:768px){.vc-cataloghi-item{width:50%;}}
@media(max-width:480px){.vc-cataloghi-item{width:100%;}}
.vc-cataloghi-item h3{font-size:1.1em;margin-top:10px;}
</style>

<div class="vc-cataloghi-grid">
<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
    <div class="vc-cataloghi-item">
        <a href="<?php the_permalink(); ?>" target="_blank" rel="noopener">
            <?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'medium' ); } ?>
        </a>
        <h3><a href="<?php the_permalink(); ?>" target="_blank" rel="noopener"><?php the_title(); ?></a></h3>
    </div>
<?php endwhile; ?>
</div>

<?php
the_posts_pagination( array(
    'mid_size'  => 2,
    'prev_text' => __( '&laquo; Precedente', 'vetrina-cataloghi' ),
    'next_text' => __( 'Successivo &raquo;', 'vetrina-cataloghi' ),
) );
else :
    echo '<p>' . esc_html__( 'Nessun catalogo trovato.', 'vetrina-cataloghi' ) . '</p>';
endif;
?>

<?php get_footer(); ?>
