<?php

/**
 * UIKit Banner Design - Overview
 */

$func = rex_request('func', 'string');
$bannerId = rex_request('id', 'int');

// Banner löschen
if ($func === 'delete' && $bannerId) {
    $sql = rex_sql::factory();
    $sql->setQuery('DELETE FROM ' . rex::getTable('uikit_banner_designs') . ' WHERE id = ?', [$bannerId]);
    echo rex_view::success(rex_i18n::msg('uikit_banner_design_deleted'));
}

// Banner laden
$sql = rex_sql::factory();
$banners = $sql->getArray('SELECT * FROM ' . rex::getTable('uikit_banner_designs') . ' ORDER BY name ASC');

$content = '';

// UIKit JS und Icons einbinden
$content .= '<script src="' . rex_url::assets('uikit/js/uikit.min.js') . '"></script>';
$content .= '<script src="' . rex_url::assets('uikit/js/uikit-icons.min.js') . '"></script>';

$content .= '<div class="uk-container uk-container-expand uk-margin-top">';

// Header mit Hero-Bereich
$content .= '<div class="uk-card uk-card-default uk-card-body uk-margin-bottom" style="background: linear-gradient(135deg, #324050 0%, #1e2832 100%); color: white;">';
$content .= '<div class="uk-grid-small uk-flex-middle" uk-grid>';
$content .= '<div class="uk-width-auto">';
$content .= '<span uk-icon="icon: image; ratio: 2.5" style="color: white;"></span>';
$content .= '</div>';
$content .= '<div class="uk-width-expand">';
$content .= '<h1 class="uk-card-title uk-margin-remove" style="color: white; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">UIKit Banner Design</h1>';
$content .= '<p class="uk-text-large uk-margin-remove-top" style="color: rgba(255,255,255,0.95); text-shadow: 0 1px 3px rgba(0,0,0,0.3);">Erstelle und verwalte deine individuellen Banner-Designs</p>';
$content .= '</div>';
$content .= '<div class="uk-width-auto">';
$content .= '<a href="' . rex_url::currentBackendPage(['page' => 'uikit_banner_design/edit']) . '" class="uk-button uk-button-primary uk-button-large" style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); color: white; backdrop-filter: blur(10px);">';
$content .= '<span uk-icon="icon: plus; ratio: 0.9" class="uk-margin-small-right"></span>' . rex_i18n::msg('uikit_banner_design_create');
$content .= '</a>';
$content .= '</div>';
$content .= '</div>';
$content .= '</div>';

if (empty($banners)) {
    // Empty State
    $content .= '<div class="uk-card uk-card-default uk-card-large uk-text-center">';
    $content .= '<div class="uk-card-body">';
    $content .= '<span uk-icon="icon: image; ratio: 4" class="uk-text-muted uk-margin-bottom"></span>';
    $content .= '<h3 class="uk-text-muted">' . rex_i18n::msg('uikit_banner_design_no_banners') . '</h3>';
    $content .= '<p class="uk-text-large uk-text-muted">Erstelle dein erstes Banner-Design um loszulegen.</p>';
    $content .= '<a href="' . rex_url::currentBackendPage(['page' => 'uikit_banner_design/edit']) . '" class="uk-button uk-button-primary uk-button-large uk-margin-top">';
    $content .= '<span uk-icon="icon: plus; ratio: 0.9" class="uk-margin-small-right"></span>' . rex_i18n::msg('uikit_banner_design_create');
    $content .= '</a>';
    $content .= '</div>';
    $content .= '</div>';
} else {
    // Banners Grid
    $content .= '<div class="uk-grid-match uk-child-width-1-1@s uk-child-width-1-2@m uk-child-width-1-3@l" uk-grid>';
    
    foreach ($banners as $banner) {
        $content .= '<div>';
        $content .= '<div class="uk-card uk-card-default uk-card-hover uk-height-1-1">';
        
        // Card Header
        $content .= '<div class="uk-card-header uk-background-muted">';
        $content .= '<div class="uk-grid-small uk-flex-middle" uk-grid>';
        $content .= '<div class="uk-width-auto">';
        $content .= '<span uk-icon="icon: image; ratio: 1.5" class="uk-text-primary"></span>';
        $content .= '</div>';
        $content .= '<div class="uk-width-expand">';
        $content .= '<h3 class="uk-card-title uk-margin-remove">' . rex_escape($banner['name']) . '</h3>';
        $content .= '</div>';
        $content .= '</div>';
        $content .= '</div>';
        
        // Banner Vorschau (Mini-Version)
        $content .= '<div class="uk-card-media-top">';
        $content .= '<div class="banner-preview" style="height: 150px; position: relative; overflow: hidden;">';
        
        // Hintergrund
        $bgStyle = 'width: 100%; height: 100%;';
        $bgContent = '';
        
        switch ($banner['bg_type']) {
            case 'color':
                $bgStyle .= ' background-color: ' . ($banner['bg_color'] ?: '#f8f8f8') . ';';
                break;
            case 'gradient':
                $bgStyle .= ' background: linear-gradient(' . ($banner['bg_gradient_direction'] ?: 'to bottom') . ', ' . ($banner['bg_gradient_start'] ?: '#667eea') . ', ' . ($banner['bg_gradient_end'] ?: '#764ba2') . ');';
                break;
            case 'image':
                if ($banner['bg_image']) {
                    $bgStyle .= ' background-image: url(' . rex_url::media($banner['bg_image']) . ');';
                    $bgStyle .= ' background-size: ' . ($banner['bg_image_repeat'] ? 'auto' : 'cover') . ';';
                    $bgStyle .= ' background-position: ' . ($banner['bg_image_position'] ?: 'center center') . ';';
                    $bgStyle .= ' background-repeat: ' . ($banner['bg_image_repeat'] ? 'repeat' : 'no-repeat') . ';';
                }
                break;
            case 'video':
                if ($banner['bg_video']) {
                    $bgContent = '<video autoplay loop muted playsinline style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover;">' .
                                '<source src="' . rex_url::media($banner['bg_video']) . '">' .
                                '</video>';
                } else {
                    $bgStyle .= ' background-color: #000;';
                }
                break;
            case 'svg':
                if ($banner['bg_svg']) {
                    $bgContent = $banner['bg_svg'];
                }
                break;
        }
        
        if ($bgContent) {
            $content .= '<div style="' . $bgStyle . '">' . $bgContent . '</div>';
        } else {
            $content .= '<div style="' . $bgStyle . '"></div>';
        }
        
        // Borders
        $borderStyles = '';
        if ($banner['border_top_width']) {
            $borderStyles .= 'border-top: ' . $banner['border_top_width'] . 'px solid ' . ($banner['border_top_color'] ?: '#000') . ';';
        }
        if ($banner['border_bottom_width']) {
            $borderStyles .= 'border-bottom: ' . $banner['border_bottom_width'] . 'px solid ' . ($banner['border_bottom_color'] ?: '#000') . ';';
        }
        if ($banner['border_left_width']) {
            $borderStyles .= 'border-left: ' . $banner['border_left_width'] . 'px solid ' . ($banner['border_left_color'] ?: '#000') . ';';
        }
        if ($banner['border_right_width']) {
            $borderStyles .= 'border-right: ' . $banner['border_right_width'] . 'px solid ' . ($banner['border_right_color'] ?: '#000') . ';';
        }
        if ($borderStyles) {
            $content .= '<div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; ' . $borderStyles . ' pointer-events: none;"></div>';
        }
        
        // Overlay
        if ($banner['overlay_type'] && $banner['overlay_type'] !== 'none') {
            $position = $banner['overlay_position'] ?: 'center center';
            $size = $banner['overlay_size'] ?: 50;
            
            // Position zu CSS konvertieren
            $posMap = [
                'top left' => 'top: 0; left: 0;',
                'top center' => 'top: 0; left: 50%; transform: translateX(-50%);',
                'top right' => 'top: 0; right: 0;',
                'center left' => 'top: 50%; left: 0; transform: translateY(-50%);',
                'center center' => 'top: 50%; left: 50%; transform: translate(-50%, -50%);',
                'center right' => 'top: 50%; right: 0; transform: translateY(-50%);',
                'bottom left' => 'bottom: 0; left: 0;',
                'bottom center' => 'bottom: 0; left: 50%; transform: translateX(-50%);',
                'bottom right' => 'bottom: 0; right: 0;',
            ];
            
            $posStyle = $posMap[$position] ?? 'top: 50%; left: 50%; transform: translate(-50%, -50%);';
            
            $content .= '<div style="position: absolute; ' . $posStyle . ' width: ' . $size . '%; max-width: ' . $size . '%; z-index: 1;">';
            
            switch ($banner['overlay_type']) {
                case 'image':
                    if ($banner['overlay_image']) {
                        $content .= '<img src="' . rex_url::media($banner['overlay_image']) . '" alt="" style="width: 100%; height: auto; display: block;">';
                    }
                    break;
                case 'svg':
                    if ($banner['overlay_svg']) {
                        $content .= $banner['overlay_svg'];
                    }
                    break;
                case 'icon':
                    if ($banner['overlay_icon']) {
                        $content .= '<span uk-icon="icon: ' . rex_escape($banner['overlay_icon']) . '; ratio: 2"></span>';
                    }
                    break;
            }
            
            $content .= '</div>';
        }
        
        // Action Button
        if ($banner['action_button_text']) {
            $btnStyle = $banner['action_button_style'] ?: 'primary';
            $btnPos = $banner['action_button_position'] ?: 'bottom center';
            
            // Position zu CSS
            $posMap = [
                'top left' => 'top: 10px; left: 10px;',
                'top center' => 'top: 10px; left: 50%; transform: translateX(-50%);',
                'top right' => 'top: 10px; right: 10px;',
                'center left' => 'top: 50%; left: 10px; transform: translateY(-50%);',
                'center center' => 'top: 50%; left: 50%; transform: translate(-50%, -50%);',
                'center right' => 'top: 50%; right: 10px; transform: translateY(-50%);',
                'bottom left' => 'bottom: 10px; left: 10px;',
                'bottom center' => 'bottom: 10px; left: 50%; transform: translateX(-50%);',
                'bottom right' => 'bottom: 10px; right: 10px;',
            ];
            
            $posStyle = $posMap[$btnPos] ?? 'bottom: 10px; left: 50%; transform: translateX(-50%);';
            
            $content .= '<div style="position: absolute; ' . $posStyle . ' z-index: 2;">';
            $content .= '<a href="#" class="uk-button uk-button-' . rex_escape($btnStyle) . ' uk-button-small" onclick="return false;">';
            $content .= rex_escape($banner['action_button_text']);
            $content .= '</a>';
            $content .= '</div>';
        }
        
        $content .= '</div>';
        $content .= '</div>';
        
        // Card Body mit Infos
        $content .= '<div class="uk-card-body">';
        $content .= '<dl class="uk-description-list uk-description-list-divider uk-text-small">';
        $content .= '<dt>Höhe</dt>';
        $content .= '<dd class="uk-text-meta">' . rex_escape($banner['height']) . '</dd>';
        $content .= '<dt>Hintergrund</dt>';
        $content .= '<dd class="uk-text-meta">' . rex_escape($banner['bg_type']) . '</dd>';
        if ($banner['overlay_type'] && $banner['overlay_type'] !== 'none') {
            $content .= '<dt>Overlay</dt>';
            $content .= '<dd class="uk-text-meta">' . rex_escape($banner['overlay_type']) . '</dd>';
        }
        $content .= '</dl>';
        $content .= '</div>';
        
        // Card Footer mit Actions
        $content .= '<div class="uk-card-footer">';
        $content .= '<div class="uk-grid-small uk-child-width-auto uk-flex-wrap" uk-grid>';
        
        // Bearbeiten
        $content .= '<div>';
        $content .= '<a href="' . rex_url::currentBackendPage(['page' => 'uikit_banner_design/edit', 'id' => $banner['id']]) . '" class="uk-button uk-button-primary" uk-tooltip="title: ' . rex_i18n::msg('uikit_banner_design_edit') . '">';
        $content .= '<span uk-icon="icon: pencil"></span> <span class="btn-text">Bearbeiten</span>';
        $content .= '</a>';
        $content .= '</div>';
        
        // Vorschau
        $content .= '<div>';
        $content .= '<a href="' . rex_url::currentBackendPage(['page' => 'uikit_banner_design/preview', 'id' => $banner['id']]) . '" class="uk-button uk-button-default" uk-tooltip="title: Vorschau" target="_blank">';
        $content .= '<span uk-icon="icon: eye"></span>';
        $content .= '</a>';
        $content .= '</div>';
        
        // Löschen
        $content .= '<div>';
        $content .= '<a href="' . rex_url::currentBackendPage(['func' => 'delete', 'id' => $banner['id']]) . '" class="uk-button uk-button-danger" uk-tooltip="title: ' . rex_i18n::msg('uikit_banner_design_delete') . '" onclick="return confirm(\'' . rex_i18n::msg('uikit_banner_design_delete_confirm') . '\')">';
        $content .= '<span uk-icon="icon: trash"></span>';
        $content .= '</a>';
        $content .= '</div>';
        
        $content .= '</div>';
        $content .= '</div>';
        
        $content .= '</div>';
        $content .= '</div>';
    }
    
    $content .= '</div>';
}

$content .= '</div>';

// CSS für responsives Design
$content .= '
<style>
@media (max-width: 640px) {
    .btn-text {
        display: none;
    }
}
</style>
';

$fragment = new rex_fragment();
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');
