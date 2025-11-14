<?php

/**
 * UIKit Banner Design
 * 
 * @author KLXM
 */

$addon = rex_addon::get('uikit_banner_design');

echo rex_view::title($addon->i18n('uikit_banner_design_title'));

rex_be_controller::includeCurrentPageSubPath();
