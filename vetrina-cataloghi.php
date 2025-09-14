<?php
/**
 * Plugin Name: Vetrina Cataloghi
 * Description: Gestisce un custom post type "Vetrina Cataloghi" con categorie e upload di file PDF.
 * Version: 1.0.0
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
    if ( in_array( $hook, array( 'post-new.php', 'post.php' ), true ) && 'vetrina_catalogo' === $post_type ) {
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

