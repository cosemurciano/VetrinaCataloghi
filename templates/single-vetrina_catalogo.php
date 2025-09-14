<?php
/**
 * Template for single catalogo posts with PDF.js viewer without theme header and footer.
 *
 * @package VetrinaCataloghi
 */

$options   = get_option( 'vc_pdfjs_options', array() );
$logo_id   = isset( $options['logo_id'] ) ? intval( $options['logo_id'] ) : 0;
$logo_url  = $logo_id ? wp_get_attachment_image_url( $logo_id, 'full' ) : '';
$params    = isset( $options['viewer_params'] ) ? $options['viewer_params'] : '';
// Use the bundled PDF.js viewer directly from the plugin.
$viewer    = plugins_url( 'pdfjs-5-4-149/web/viewer.html', dirname( __FILE__, 2 ) );
$pdf_id    = get_post_meta( get_the_ID(), '_vc_pdf_id', true );
$pdf_url   = $pdf_id ? wp_get_attachment_url( $pdf_id ) : '';

// Force the PDF URL to use the current site's domain.
if ( $pdf_url ) {
    $pdf_path = wp_parse_url( $pdf_url, PHP_URL_PATH );
    $pdf_url  = home_url( $pdf_path );
}

$iframe_src = '';
if ( $pdf_url ) {
    $iframe_src = $viewer . '?file=' . rawurlencode( $pdf_url ) . $params;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
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
                <iframe src="<?php echo esc_url( $iframe_src ); ?>"></iframe>
            <?php else : ?>
                <p><?php esc_html_e( 'PDF non disponibile.', 'vetrina-cataloghi' ); ?></p>
            <?php endif; ?>
        </div>
    </div>
    <?php wp_footer(); ?>
</body>
</html>
