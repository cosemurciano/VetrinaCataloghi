<?php
/**
 * Plugin Name: Vetrina Cataloghi
 * Description: Gestisce un custom post type "Vetrina Cataloghi" con categorie e upload di file PDF.
 * Version: 1.5
 * Author: Cosè Murciano
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

    add_meta_box(
        'vc_info_metabox',
        __( 'Testo informativo', 'vetrina-cataloghi' ),
        'vc_info_metabox_callback',
        'vetrina_catalogo',
        'normal',
        'default'
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
 * Callback for info text metabox.
 *
 * @param WP_Post $post The post object.
 */
function vc_info_metabox_callback( $post ) {
    wp_nonce_field( 'vc_save_info', 'vc_info_nonce' );
    $info_text = get_post_meta( $post->ID, '_vc_info_text', true );
    ?>
    <p>
        <textarea name="vc_info_text" id="vc-info-text" rows="5" style="width:100%;"><?php echo esc_textarea( $info_text ); ?></textarea>
    </p>
    <p class="description"><?php esc_html_e( 'Se compilato, sovrascrive il testo informativo delle impostazioni PDF.js.', 'vetrina-cataloghi' ); ?></p>
    <?php
}

/**
 * Saves the PDF meta.
 *
 * @param int $post_id The post ID.
 */
function vc_save_pdf_meta( $post_id ) {
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! isset( $_POST['post_type'] ) || 'vetrina_catalogo' !== $_POST['post_type'] ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }
    $pdf_nonce_valid  = isset( $_POST['vc_pdf_nonce'] ) && wp_verify_nonce( $_POST['vc_pdf_nonce'], 'vc_save_pdf' );
    $info_nonce_valid = isset( $_POST['vc_info_nonce'] ) && wp_verify_nonce( $_POST['vc_info_nonce'], 'vc_save_info' );

    if ( $pdf_nonce_valid ) {
        $pdf_id = isset( $_POST['vc_pdf_id'] ) ? intval( $_POST['vc_pdf_id'] ) : 0;
        if ( $pdf_id ) {
            update_post_meta( $post_id, '_vc_pdf_id', $pdf_id );
        } else {
            delete_post_meta( $post_id, '_vc_pdf_id' );
        }
    }

    if ( $info_nonce_valid ) {
        $info_text = isset( $_POST['vc_info_text'] ) ? wp_kses_post( wp_unslash( $_POST['vc_info_text'] ) ) : '';
        if ( '' !== trim( $info_text ) ) {
            update_post_meta( $post_id, '_vc_info_text', $info_text );
        } else {
            delete_post_meta( $post_id, '_vc_info_text' );
        }
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
    if ( 'vetrina_catalogo_page_vc-css-template' === $hook ) {
        $cm_settings = wp_enqueue_code_editor( array( 'type' => 'text/css' ) );
        if ( $cm_settings ) {
            wp_add_inline_script(
                'code-editor',
                sprintf( 'jQuery(function(){ wp.codeEditor.initialize( "vc_template_css", %s ); });', wp_json_encode( $cm_settings ) )
            );
        }
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
        $custom_css = get_option( 'vc_template_css', '' );
        if ( ! empty( $custom_css ) ) {
            wp_add_inline_style( 'vc-pdf-viewer', $custom_css );
        }
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
 * Load custom template for catalog categories taxonomy.
 *
 * @param string $template Current template.
 * @return string Template path.
 */
function vc_taxonomy_template( $template ) {
    if ( is_tax( 'categoria_cataloghi' ) ) {
        $custom = plugin_dir_path( __FILE__ ) . 'templates/taxonomy-categoria_cataloghi.php';
        if ( file_exists( $custom ) ) {
            return $custom;
        }
    }
    return $template;
}
add_filter( 'taxonomy_template', 'vc_taxonomy_template' );

/**
 * Set posts per page for catalog archives and taxonomy listings.
 *
 * @param WP_Query $query The WP_Query instance.
 */
function vc_cataloghi_posts_per_page( $query ) {
    if ( ! is_admin() && $query->is_main_query() ) {
        if ( $query->is_post_type_archive( 'vetrina_catalogo' ) || $query->is_tax( 'categoria_cataloghi' ) ) {
            $query->set( 'posts_per_page', 20 );
        }
    }
}
add_action( 'pre_get_posts', 'vc_cataloghi_posts_per_page' );

/**
 * Register settings for PDF.js viewer.
 */
function vc_register_settings() {
    register_setting( 'vc_pdfjs_settings', 'vc_pdfjs_options', 'vc_sanitize_options' );
    register_setting( 'vc_css_settings', 'vc_template_css', 'vc_sanitize_css' );
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
    $available_features = array( 'toolbar', 'navpanes', 'download', 'print', 'openfile', 'viewBookmark', 'secondaryToolbar' );
    $output['logo_id']       = isset( $input['logo_id'] ) ? intval( $input['logo_id'] ) : 0;
    $output['features']      = array();
    foreach ( $available_features as $feature ) {
        $output['features'][ $feature ] = ! empty( $input['features'][ $feature ] ) ? 1 : 0;
    }
    $output['info_text'] = isset( $input['info_text'] ) ? wp_kses_post( wp_unslash( $input['info_text'] ) ) : '';
    return $output;
}

/**
 * Sanitize custom CSS.
 *
 * @param string $css Raw CSS.
 * @return string Sanitized CSS.
 */
function vc_sanitize_css( $css ) {
    return wp_strip_all_tags( $css );
}

/**
 * Add settings page to admin menu.
 */
function vc_add_settings_page() {
    add_submenu_page(
        'edit.php?post_type=vetrina_catalogo',
        __( 'Shortcode Cataloghi', 'vetrina-cataloghi' ),
        __( 'Shortcode Cataloghi', 'vetrina-cataloghi' ),
        'manage_options',
        'vc-shortcode-generator',
        'vc_render_shortcode_page',
        20
    );
    add_submenu_page(
        'edit.php?post_type=vetrina_catalogo',
        __( 'Impostazioni PDF.js', 'vetrina-cataloghi' ),
        __( 'PDF.js Viewer', 'vetrina-cataloghi' ),
        'manage_options',
        'vc-pdfjs-settings',
        'vc_render_settings_page',
        30
    );
    add_submenu_page(
        'edit.php?post_type=vetrina_catalogo',
        __( 'CSS Template', 'vetrina-cataloghi' ),
        __( 'CSS Template', 'vetrina-cataloghi' ),
        'manage_options',
        'vc-css-template',
        'vc_render_css_page',
        40
    );
}
add_action( 'admin_menu', 'vc_add_settings_page', 20 );

/**
 * Render settings page.
 */
function vc_render_settings_page() {
    $options   = get_option( 'vc_pdfjs_options', array() );
    $logo_id   = isset( $options['logo_id'] ) ? intval( $options['logo_id'] ) : 0;
    $logo_url  = $logo_id ? wp_get_attachment_url( $logo_id ) : '';
    $features  = isset( $options['features'] ) ? (array) $options['features'] : array();
    $info_text = isset( $options['info_text'] ) ? $options['info_text'] : '';
    $available_features = array(
        'toolbar'          => __( 'Toolbar', 'vetrina-cataloghi' ),
        'navpanes'         => __( 'Pannello di navigazione', 'vetrina-cataloghi' ),
        'download'         => __( 'Download', 'vetrina-cataloghi' ),
        'print'            => __( 'Stampa', 'vetrina-cataloghi' ),
        'openfile'         => __( 'Apri file', 'vetrina-cataloghi' ),
        'viewBookmark'     => __( 'Segnalibro', 'vetrina-cataloghi' ),
        'secondaryToolbar' => __( 'Toolbar secondaria', 'vetrina-cataloghi' ),
    );
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Impostazioni PDF.js Viewer', 'vetrina-cataloghi' ); ?></h1>
        <form method="post" action="options.php">
            <?php settings_fields( 'vc_pdfjs_settings' ); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php esc_html_e( 'Funzionalità PDF.js', 'vetrina-cataloghi' ); ?></th>
                    <td>
                        <div class="vc-pdfjs-features">
                        <?php foreach ( $available_features as $key => $label ) : ?>
                            <label>
                                <input type="checkbox" name="vc_pdfjs_options[features][<?php echo esc_attr( $key ); ?>]" value="1" <?php checked( ! isset( $features[ $key ] ) || $features[ $key ] ); ?> />
                                <?php echo esc_html( $label ); ?>
                            </label>
                        <?php endforeach; ?>
                        </div>
                        <p class="description"><?php esc_html_e( 'Seleziona le funzionalità da abilitare.', 'vetrina-cataloghi' ); ?></p>
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
                <tr>
                    <th scope="row"><?php esc_html_e( 'Testo informativo', 'vetrina-cataloghi' ); ?></th>
                    <td>
                        <textarea name="vc_pdfjs_options[info_text]" rows="5" class="large-text"><?php echo esc_textarea( $info_text ); ?></textarea>
                        <p class="description"><?php esc_html_e( 'Testo mostrato nel catalogo subito dopo il titolo. Può essere sovrascritto dal singolo catalogo.', 'vetrina-cataloghi' ); ?></p>
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
    <style>
    .vc-pdfjs-features{display:flex;flex-wrap:wrap;}
    .vc-pdfjs-features label{width:33%;margin-bottom:8px;}
    </style>
    <?php
}

/**
 * Render custom CSS settings page.
 */
function vc_render_css_page() {
    $css = get_option( 'vc_template_css', '' );
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'CSS Template', 'vetrina-cataloghi' ); ?></h1>
        <form method="post" action="options.php">
            <?php settings_fields( 'vc_css_settings' ); ?>
            <textarea id="vc_template_css" name="vc_template_css" rows="20" class="large-text code"><?php echo esc_textarea( $css ); ?></textarea>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

/**
 * Render shortcode generator page.
 */
function vc_render_shortcode_page() {
    wp_enqueue_style( 'dashicons' );
    $categories = get_terms(
        array(
            'taxonomy'   => 'categoria_cataloghi',
            'hide_empty' => false,
        )
    );
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Generatore Shortcode Cataloghi', 'vetrina-cataloghi' ); ?></h1>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="vc_sc_categoria"><?php esc_html_e( 'Categoria', 'vetrina-cataloghi' ); ?></label></th>
                <td>
                    <select id="vc_sc_categoria">
                        <option value=""><?php esc_html_e( 'Tutte', 'vetrina-cataloghi' ); ?></option>
                        <?php foreach ( $categories as $cat ) : ?>
                            <option value="<?php echo esc_attr( $cat->slug ); ?>"><?php echo esc_html( $cat->name ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="vc_sc_numero"><?php esc_html_e( 'Numero', 'vetrina-cataloghi' ); ?></label></th>
                <td>
                    <input type="number" id="vc_sc_numero" value="" min="1" />
                    <p class="description"><?php esc_html_e( 'Lascia vuoto o usa "tutti" per mostrare tutti i cataloghi.', 'vetrina-cataloghi' ); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="vc_sc_per_riga"><?php esc_html_e( 'Cataloghi per riga', 'vetrina-cataloghi' ); ?></label></th>
                <td><input type="number" id="vc_sc_per_riga" value="3" min="1" /></td>
            </tr>
        </table>
        <h2><?php esc_html_e( 'Shortcode generato', 'vetrina-cataloghi' ); ?></h2>
        <div style="display:flex;align-items:center;">
            <input type="text" id="vc_sc_output" class="large-text code" readonly value="[vc_cataloghi]" />
            <button type="button" id="vc_sc_copy" class="button" style="margin-left:5px;" title="<?php esc_attr_e( 'Copia shortcode', 'vetrina-cataloghi' ); ?>">
                <span class="dashicons dashicons-clipboard"></span>
            </button>
        </div>
    </div>
    <script>
    (function($){
        function updateShortcode(){
            var categoria = $('#vc_sc_categoria').val();
            var numero = $('#vc_sc_numero').val();
            var perRiga = $('#vc_sc_per_riga').val();
            var parts = [];
            if(categoria){
                parts.push('categoria="'+categoria+'"');
            }
            if(numero){
                parts.push('numero="'+numero+'"');
            }
            if(perRiga){
                parts.push('per_riga="'+perRiga+'"');
            }
            var shortcode = '[vc_cataloghi';
            if(parts.length){
                shortcode += ' '+parts.join(' ');
            }
            shortcode += ']';
            $('#vc_sc_output').val(shortcode);
        }
        $('#vc_sc_categoria, #vc_sc_numero, #vc_sc_per_riga').on('change keyup', updateShortcode);
        updateShortcode();

        $('#vc_sc_copy').on('click', function(){
            var shortcode = $('#vc_sc_output').val();
            if (navigator.clipboard) {
                navigator.clipboard.writeText(shortcode);
            } else {
                var temp = $('<input>');
                $('body').append(temp);
                temp.val(shortcode).select();
                document.execCommand('copy');
                temp.remove();
            }
        });
    })(jQuery);
    </script>
    <?php
}

/**
 * Shortcode to display a list of cataloghi.
 *
 * Usage: [vc_cataloghi categoria="slug" numero="5" per_riga="3"]
 *
 * @param array $atts Shortcode attributes.
 * @return string HTML output for the catalog list.
 */
function vc_cataloghi_shortcode( $atts = array() ) {
    $atts = shortcode_atts(
        array(
            'categoria' => '',
            'numero'    => 'tutti',
            'per_riga'  => 3,
        ),
        $atts,
        'vc_cataloghi'
    );

    $posts_per_page = -1;
    if ( ! in_array( strtolower( $atts['numero'] ), array( 'tutti', 'all', '' ), true ) ) {
        $posts_per_page = intval( $atts['numero'] );
        if ( $posts_per_page <= 0 ) {
            $posts_per_page = -1;
        }
    }

    $args = array(
        'post_type'      => 'vetrina_catalogo',
        'posts_per_page' => $posts_per_page,
    );

    if ( ! empty( $atts['categoria'] ) ) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'categoria_cataloghi',
                'field'    => 'slug',
                'terms'    => array_map( 'sanitize_title', array_map( 'trim', explode( ',', $atts['categoria'] ) ) ),
            ),
        );
    }

    $query = new WP_Query( $args );
    if ( ! $query->have_posts() ) {
        wp_reset_postdata();
        return '';
    }

    wp_enqueue_style(
        'vc-cataloghi-shortcode',
        plugin_dir_url( __FILE__ ) . 'assets/css/cataloghi-shortcode.css',
        array(),
        '1.0.0'
    );

    $columns = max( 1, intval( $atts['per_riga'] ) );
    $output  = '<div class="vc-cataloghi-grid" style="--vc-columns:' . esc_attr( $columns ) . ';">';

    while ( $query->have_posts() ) {
        $query->the_post();
        $link   = get_permalink();
        $output .= '<div class="vc-cataloghi-item">';
        if ( has_post_thumbnail() ) {
            $output .= '<a href="' . esc_url( $link ) . '">' .
                get_the_post_thumbnail( get_the_ID(), 'medium' ) . '</a>';
        }
        $output .= '<h3><a href="' . esc_url( $link ) . '">' .
            esc_html( get_the_title() ) . '</a></h3>';
        $output .= '</div>';
    }

    $output .= '</div>';
    wp_reset_postdata();

    return $output;
}
add_shortcode( 'vc_cataloghi', 'vc_cataloghi_shortcode' );


