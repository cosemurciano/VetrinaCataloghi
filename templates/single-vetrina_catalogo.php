<?php
/**
 * Template for single catalogo posts with PDF.js viewer.
 *
 * @package VetrinaCataloghi
 */

get_header();

$options   = get_option( 'vc_pdfjs_options', array() );
$logo_id   = isset( $options['logo_id'] ) ? intval( $options['logo_id'] ) : 0;
$logo_url  = $logo_id ? wp_get_attachment_image_url( $logo_id, 'full' ) : '';
$viewer    = isset( $options['viewer_url'] ) ? $options['viewer_url'] : 'https://mozilla.github.io/pdf.js/web/viewer.html';
$params    = isset( $options['viewer_params'] ) ? $options['viewer_params'] : '';
$pdf_id    = get_post_meta( get_the_ID(), '_vc_pdf_id', true );
$pdf_url   = $pdf_id ? wp_get_attachment_url( $pdf_id ) : '';
?>
<div class="vc-pdfjs-wrapper">
    <div class="vc-pdfjs-sidebar">
        <?php if ( $logo_url ) : ?>
            <img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" class="vc-logo" />
        <?php endif; ?>
        <h1 class="vc-title"><?php the_title(); ?></h1>
        <div class="vc-content"><?php the_content(); ?></div>
    </div>
    <div class="vc-pdfjs-viewer">
        <?php if ( $pdf_url ) : ?>
            <iframe src="<?php echo esc_url( $viewer . '?file=' . rawurlencode( $pdf_url ) . $params ); ?>"></iframe>
        <?php else : ?>
            <p><?php esc_html_e( 'PDF non disponibile.', 'vetrina-cataloghi' ); ?></p>
        <?php endif; ?>
    </div>
</div>
<?php
get_footer();
