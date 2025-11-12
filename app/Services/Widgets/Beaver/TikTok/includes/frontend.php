<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

$wpsr_tiktok_template_id = $settings->template_id; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
if(!$settings->template_id){
    return;
}
echo wp_kses_post(do_shortcode('[wp_social_ninja id="'.esc_html($wpsr_tiktok_template_id).'" platform="tiktok"]'));