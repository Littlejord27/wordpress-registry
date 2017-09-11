<?php

function registry_search($atts = [], $content = null, $tag = '')
{

 
    // start output
    $o = '';
 
    // start box
    $o .= '<div class="registry-box">';
 
    // title
    $o .= '<h2>Registry</h2>';

    $o .= '<div class="registry-search-box">';

    $o .= '<input type="text" placeholder="Registry Name" class="registry-search-name"><input type="submit" class="registry-search-submit">';

    $o .= '<div class="registry-search-results">';

    // end registry search results
    $o .= '</div>';

    $o .= '';

    $o .= '';

    $o .= '';

    $o .= '';

    //end search-box
    $o .= '</div>';
 
    // end box
    $o .= '</div>';
 
    // return output
    return $o;
}
 
function registry_shortcodes_init()
{
    add_shortcode('registry_landingpage', 'registry_search');
}

add_action( 'init', 'registry_shortcodes_init');