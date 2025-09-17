<?php
/**
 * Template for single catalogo posts with PDF.js viewer without theme header and footer.
 *
 * @package VetrinaCataloghi
 */

$options   = get_option( 'vc_pdfjs_options', array() );
$logo_id   = isset( $options['logo_id'] ) ? intval( $options['logo_id'] ) : 0;
$logo_url  = $logo_id ? wp_get_attachment_image_url( $logo_id, 'full' ) : '';
$features  = isset( $options['features'] ) ? $options['features'] : array();
$info_text_option = isset( $options['info_text'] ) ? $options['info_text'] : '';
// Build PDF.js parameters from selected features.
$available_features = array( 'toolbar', 'navpanes', 'download', 'print', 'openfile', 'viewBookmark', 'secondaryToolbar' );
$params = '#zoom=page-width';
foreach ( $available_features as $feature ) {
    $enabled = isset( $features[ $feature ] ) ? (bool) $features[ $feature ] : true;
    $params .= '&' . $feature . '=' . ( $enabled ? '1' : '0' );
}
// Use the bundled PDF.js viewer directly from the plugin.
$viewer    = plugins_url( 'pdfjs-5-4-149/web/viewer.html', plugin_dir_path( __DIR__ ) . 'vetrina-cataloghi.php' );
$pdf_id    = get_post_meta( get_the_ID(), '_vc_pdf_id', true );
$pdf_url   = $pdf_id ? wp_get_attachment_url( $pdf_id ) : '';
$info_text_meta = get_post_meta( get_the_ID(), '_vc_info_text', true );
$info_text      = '' !== trim( (string) $info_text_meta ) ? $info_text_meta : $info_text_option;

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
        <div class="vc-pdfjs-viewer">
            <?php if ( $pdf_url ) : ?>
                <iframe src="<?php echo esc_url( $iframe_src ); ?>"></iframe>
            <?php else : ?>
                <p><?php esc_html_e( 'PDF non disponibile.', 'vetrina-cataloghi' ); ?></p>
            <?php endif; ?>
        </div>
        <div class="vc-pdfjs-sidebar">
            <?php if ( $logo_url ) : ?>
                <img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" class="vc-logo" />
            <?php endif; ?>
            <h1 class="vc-title"><?php the_title(); ?></h1>
            <?php if ( ! empty( $info_text ) ) : ?>
                <div class="vc-info-text"><?php echo wp_kses_post( wpautop( $info_text ) ); ?></div>
            <?php endif; ?>
            <div class="vc-content"><?php the_content(); ?></div>
        </div>
    </div>
    <?php wp_footer(); ?>
</body>
</html>
