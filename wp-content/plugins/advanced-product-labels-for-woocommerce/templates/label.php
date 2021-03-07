<?php 
global $pagenow, $post;
$BeRocket_advanced_labels_custom_post = BeRocket_advanced_labels_custom_post::getInstance();
$label = array(
    'label_from_post'       => '',
);
if( ! in_array( $pagenow, array( 'post-new.php' ) ) ) {
    $label = $BeRocket_advanced_labels_custom_post->get_option($post->ID);
}
echo '<div class="panel wc-metaboxes-wrapper" id="br_alabel" style="display: none;">';
wp_nonce_field('br_labels_check', 'br_labels_nonce');
echo '<table style="width: 99%;"><tr><th style="width: 250px;">'.__('Label to display on this product', 'BeRocket_products_label_domain').'</th>
<td><div style="max-height:200px;margin:10px 0;overflow: auto;">';
$args = array(
    'posts_per_page'   => -1,
    'offset'           => 0,
    'category'         => '',
    'category_name'    => '',
    'orderby'          => 'date',
    'order'            => 'DESC',
    'include'          => '',
    'exclude'          => '',
    'meta_key'         => '',
    'meta_value'       => '',
    'post_type'        => 'br_labels',
    'post_mime_type'   => '',
    'post_parent'      => '',
    'author'	   => '',
    'post_status'      => 'publish',
    'suppress_filters' => false 
);
$posts_array = get_posts( $args );
foreach($posts_array as $post_id) {
    $post_title = get_the_title($post_id->ID);
    echo '<p style="margin: 0 0 3px;"><label><input name="br_labels[label_from_post][]" type="checkbox" value="'.$post_id->ID.'"'.(is_array($label['label_from_post']) && in_array($post_id->ID, $label['label_from_post']) ? ' checked' : '').'>('.$post_id->ID.') '.$post_title.'</label></p>';
}
echo '</div></td></tr></table>';
?>
</div>
