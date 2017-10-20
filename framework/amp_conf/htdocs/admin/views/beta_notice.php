<?php
/* TODO: Something like this needs to be added above the module page to represent this functionality as Beta
 *       May be nice to have a tool tip saying what it means, etc. Commneted out until it is put together.
 *       it is triggered with a beta="yes" attribute in the menuitem tag in module.xml such that specific
 *       pages can be beta vs. the whole module.
 */
$html = '<div id="menuitem_beta">';
$html .= _("-This page is currently BETA-");
$html .= '</div>';

echo $html;
