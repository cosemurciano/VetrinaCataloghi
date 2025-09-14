<?php
/**
 * Plugin Name: Vetrina Cataloghi
 * Description: Gestisce un custom post type "Vetrina Cataloghi" con categorie e upload di file PDF.
 * Version: 1.1.0
 * Author: OpenAI ChatGPT
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Registers custom post type and taxonomy.
 */
function vc_register_cataloghi_cpt() {
    $labels = array(
        'name'               => __( 'Vetrina Cataloghi', 'vetrina-cataloghi' ),
        'singular_name'      => __( 'Catalogo', 'vetrina-cataloghi' ),
        'menu_name'          => __( 'Vetrina Cataloghi', 'vetrina-cataloghi' ),
        'name_admin_bar'     => __( 'Catalogo', 'vetrina-cataloghi' ),
        'add_new'            => __( 'Nuovo Catalogo', 'vetrina-cataloghi' ),
        'add_new_item'       => __( 'Nuovo Catalogo', 'vetrina-cataloghi' ),
        'new_item'           => __( 'Nuovo Catalogo', 'vetrina-cataloghi' ),
        'edit_item'          => __( 'Modifica Catalogo', 'vetrina-cataloghi' ),
        'view_item'          => __( 'Visualizza Catalogo', 'vetrina-cataloghi' ),
        'all_items'          => __( 'Elenco Cataloghi', 'vetrina-cataloghi' ),
        'search_items'       => __( 'Cerca Cataloghi', 'vetrina-cataloghi' ),
        'parent_item_colon'  => __( 'Catalogo padre:', 'vetrina-cataloghi' ),
        'not_found'          => __( 'Nessun catalogo trovato.', 'vetrina-cataloghi' ),
        'not_found_in_trash' => __( 'Nessun catalogo trovato nel cestino.', 'vetrina-cataloghi' ),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'cataloghi' ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 20,
        'supports'           => array( 'title', 'editor', 'thumbnail' ),
    );

    register_post_type( 'vetrina_catalogo', $args );

    // Register custom taxonomy for categories.
    $taxonomy_labels = array(
        'name'              => __( 'Categorie', 'vetrina-cataloghi' ),
        'singular_name'     => __( 'Categoria Cataloghi', 'vetrina-cataloghi' ),
        'search_items'      => __( 'Cerca Categoria', 'vetrina-cataloghi' ),
        'all_items'         => __( 'Tutte le Categorie', 'vetrina-cataloghi' ),
        'parent_item'       => __( 'Categoria Padre', 'vetrina-cataloghi' ),
        'parent_item_colon' => __( 'Categoria Padre:', 'vetrina-cataloghi' ),
        'edit_item'         => __( 'Modifica Categoria', 'vetrina-cataloghi' ),
        'update_item'       => __( 'Aggiorna Categoria', 'vetrina-cataloghi' ),
        'add_new_item'      => __( 'Aggiungi Nuova Categoria', 'vetrina-cataloghi' ),
        'new_item_name'     => __( 'Nome Nuova Categoria', 'vetrina-cataloghi' ),
        'menu_name'         => __( 'Categorie', 'vetrina-cataloghi' ),
    );

    $taxonomy_args = array(
        'hierarchical'      => true,
        'labels'            => $taxonomy_labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'categoria-cataloghi' ),
        'show_in_menu'      => 'edit.php?post_type=vetrina_catalogo',
    );

    register_taxonomy( 'categoria_cataloghi', array( 'vetrina_catalogo' ), $taxonomy_args );
}
add_action( 'init', 'vc_register_cataloghi_cpt' );

/**
 * Add meta box for PDF upload.
 */
function vc_add_pdf_metabox() {
    add_meta_box(
        'vc_pdf_metabox',
        __( 'File PDF', 'vetrina-cataloghi' ),
        'vc_pdf_metabox_callback',
        'vetrina_catalogo',
        'side'
    );
}
add_action( 'add_meta_boxes', 'vc_add_pdf_metabox' );

/**
 * Callback for PDF metabox.
 *
 * @param WP_Post $post The post object.
 */
function vc_pdf_metabox_callback( $post ) {
    wp_nonce_field( 'vc_save_pdf', 'vc_pdf_nonce' );
    $pdf_id  = get_post_meta( $post->ID, '_vc_pdf_id', true );
    $pdf_url = $pdf_id ? wp_get_attachment_url( $pdf_id ) : '';
    ?>
    <div id="vc-pdf-wrapper">
        <p>
            <input type="hidden" id="vc-pdf-id" name="vc_pdf_id" value="<?php echo esc_attr( $pdf_id ); ?>" />
            <input type="text" id="vc-pdf-url" value="<?php echo esc_attr( $pdf_url ); ?>" readonly style="width:100%;" />
        </p>
        <p>
            <button type="button" class="button" id="vc-pdf-upload">
                <?php esc_html_e( 'Seleziona PDF', 'vetrina-cataloghi' ); ?>
            </button>
            <button type="button" class="button" id="vc-pdf-remove" <?php echo $pdf_id ? '' : 'style="display:none;"'; ?>>
                <?php esc_html_e( 'Rimuovi', 'vetrina-cataloghi' ); ?>
            </button>
        </p>
    </div>
    <script>
        jQuery(document).ready(function($){
            var frame;
            $('#vc-pdf-upload').on('click', function(e){
                e.preventDefault();
                if(frame){
                    frame.open();
                    return;
                }
                frame = wp.media({
                    title: '<?php esc_html_e( 'Seleziona o carica PDF', 'vetrina-cataloghi' ); ?>',
                    button: { text: '<?php esc_html_e( 'Usa questo PDF', 'vetrina-cataloghi' ); ?>' },
                    library: { type: 'application/pdf' },
                    multiple: false
                });
                frame.on('select', function(){
                    var attachment = frame.state().get('selection').first().toJSON();
                    $('#vc-pdf-id').val(attachment.id);
                    $('#vc-pdf-url').val(attachment.url);
                    $('#vc-pdf-remove').show();
                });
                frame.open();
            });
            $('#vc-pdf-remove').on('click', function(){
                $('#vc-pdf-id').val('');
                $('#vc-pdf-url').val('');
                $(this).hide();
            });
        });
    </script>
    <?php
}

/**
 * Saves the PDF meta.
 *
 * @param int $post_id The post ID.
 */
function vc_save_pdf_meta( $post_id ) {
    if ( ! isset( $_POST['vc_pdf_nonce'] ) || ! wp_verify_nonce( $_POST['vc_pdf_nonce'], 'vc_save_pdf' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( isset( $_POST['post_type'] ) && 'vetrina_catalogo' === $_POST['post_type'] ) {
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
    }
    $pdf_id = isset( $_POST['vc_pdf_id'] ) ? intval( $_POST['vc_pdf_id'] ) : 0;
    if ( $pdf_id ) {
        update_post_meta( $post_id, '_vc_pdf_id', $pdf_id );
    } else {
        delete_post_meta( $post_id, '_vc_pdf_id' );
    }
}
add_action( 'save_post', 'vc_save_pdf_meta' );

/**
 * Ensure media scripts are available.
 */
function vc_admin_scripts( $hook ) {
    global $post_type;
    if (
        ( in_array( $hook, array( 'post-new.php', 'post.php' ), true ) && 'vetrina_catalogo' === $post_type ) ||
        'vetrina_catalogo_page_vc-pdfjs-settings' === $hook
    ) {
        wp_enqueue_media();
    }
}
add_action( 'admin_enqueue_scripts', 'vc_admin_scripts' );

/**
 * Adds featured image thumbnail column to catalog list.
 *
 * @param array $columns Existing columns.
 * @return array Modified columns with thumbnail.
 */
function vc_add_thumbnail_column( $columns ) {
    $new = array();
    if ( isset( $columns['cb'] ) ) {
        $new['cb'] = $columns['cb'];
        unset( $columns['cb'] );
    }
    $new['vc_thumbnail'] = __( 'Miniatura', 'vetrina-cataloghi' );
    return array_merge( $new, $columns );
}
add_filter( 'manage_edit-vetrina_catalogo_columns', 'vc_add_thumbnail_column' );

/**
 * Renders the featured image thumbnail column.
 *
 * @param string $column  Column name.
 * @param int    $post_id Post ID.
 */
function vc_render_thumbnail_column( $column, $post_id ) {
    if ( 'vc_thumbnail' === $column ) {
        $thumb = get_the_post_thumbnail( $post_id, array( 60, 60 ) );
        echo $thumb ? $thumb : '&mdash;';
    }
}
add_action( 'manage_vetrina_catalogo_posts_custom_column', 'vc_render_thumbnail_column', 10, 2 );

/**
 * Flush rewrite rules on activation.
 */
function vc_activate_plugin() {
    vc_register_cataloghi_cpt();
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'vc_activate_plugin' );

/**
 * Flush rewrite rules on deactivation.
 */
function vc_deactivate_plugin() {
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'vc_deactivate_plugin' );

/**
 * Enqueue frontend assets for PDF viewer.
 */
function vc_enqueue_frontend_assets() {
    if ( is_singular( 'vetrina_catalogo' ) ) {
        wp_enqueue_style(
            'vc-pdf-viewer',
            plugin_dir_url( __FILE__ ) . 'assets/css/pdf-viewer.css',
            array(),
            '1.0.0'
        );
    }
}
add_action( 'wp_enqueue_scripts', 'vc_enqueue_frontend_assets' );

/**
 * Load custom template for catalog post type.
 *
 * @param string $template Current template.
 * @return string Template path.
 */
function vc_single_template( $template ) {
    if ( is_singular( 'vetrina_catalogo' ) ) {
        $custom = plugin_dir_path( __FILE__ ) . 'templates/single-vetrina_catalogo.php';
        if ( file_exists( $custom ) ) {
            return $custom;
        }
    }
    return $template;
}
add_filter( 'single_template', 'vc_single_template' );

/**
 * Register settings for PDF.js viewer.
 */
function vc_register_settings() {
    register_setting( 'vc_pdfjs_settings', 'vc_pdfjs_options', 'vc_sanitize_options' );
}
add_action( 'admin_init', 'vc_register_settings' );

/**
 * Sanitize settings values.
 *
 * @param array $input Raw input.
 * @return array Sanitized values.
 */
function vc_sanitize_options( $input ) {
    $output = array();
    $output['viewer_params'] = isset( $input['viewer_params'] ) ? sanitize_text_field( $input['viewer_params'] ) : '';
    $output['logo_id']       = isset( $input['logo_id'] ) ? intval( $input['logo_id'] ) : 0;
    return $output;
}

/**
 * Add settings page to admin menu.
 */
function vc_add_settings_page() {
    add_submenu_page(
        'edit.php?post_type=vetrina_catalogo',
        __( 'Impostazioni PDF.js', 'vetrina-cataloghi' ),
        __( 'PDF.js Viewer', 'vetrina-cataloghi' ),
        'manage_options',
        'vc-pdfjs-settings',
        'vc_render_settings_page'
    );
}
add_action( 'admin_menu', 'vc_add_settings_page' );

/**
 * Render settings page.
 */
function vc_render_settings_page() {
    $options   = get_option( 'vc_pdfjs_options', array() );
    $logo_id   = isset( $options['logo_id'] ) ? intval( $options['logo_id'] ) : 0;
    $logo_url  = $logo_id ? wp_get_attachment_url( $logo_id ) : '';
    $params     = isset( $options['viewer_params'] ) ? esc_attr( $options['viewer_params'] ) : '';
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Impostazioni PDF.js Viewer', 'vetrina-cataloghi' ); ?></h1>
        <form method="post" action="options.php">
            <?php settings_fields( 'vc_pdfjs_settings' ); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="vc-viewer-params"><?php esc_html_e( 'Parametri viewer', 'vetrina-cataloghi' ); ?></label></th>
                    <td><input type="text" id="vc-viewer-params" name="vc_pdfjs_options[viewer_params]" value="<?php echo $params; ?>" class="regular-text" />
                        <p class="description"><?php esc_html_e( 'Esempio: #zoom=page-width&toolbar=1', 'vetrina-cataloghi' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Logo', 'vetrina-cataloghi' ); ?></th>
                    <td>
                        <img id="vc-logo-preview" src="<?php echo esc_url( $logo_url ); ?>" style="max-width:150px;<?php echo $logo_url ? '' : 'display:none;'; ?>" alt="" />
                        <input type="hidden" id="vc-logo-id" name="vc_pdfjs_options[logo_id]" value="<?php echo esc_attr( $logo_id ); ?>" />
                        <p>
                            <button type="button" class="button" id="vc-upload-logo"><?php esc_html_e( 'Carica logo', 'vetrina-cataloghi' ); ?></button>
                            <button type="button" class="button" id="vc-remove-logo" <?php echo $logo_url ? '' : 'style="display:none;"'; ?>><?php esc_html_e( 'Rimuovi', 'vetrina-cataloghi' ); ?></button>
                        </p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <script>
    jQuery(document).ready(function($){
        var frame;
        $('#vc-upload-logo').on('click', function(e){
            e.preventDefault();
            if(frame){frame.open();return;}
            frame = wp.media({
                title: '<?php esc_html_e( 'Seleziona o carica logo', 'vetrina-cataloghi' ); ?>',
                button: { text: '<?php esc_html_e( 'Usa questo logo', 'vetrina-cataloghi' ); ?>' },
                multiple: false
            });
            frame.on('select', function(){
                var attachment = frame.state().get('selection').first().toJSON();
                $('#vc-logo-id').val(attachment.id);
                $('#vc-logo-preview').attr('src', attachment.url).show();
                $('#vc-remove-logo').show();
            });
            frame.open();
        });
        $('#vc-remove-logo').on('click', function(){
            $('#vc-logo-id').val('');
            $('#vc-logo-preview').hide();
            $(this).hide();
        });
    });
    </script>
    <?php
}


