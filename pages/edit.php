<?php

/**
 * UIKit Banner Design - Editor
 * Step 1: Name & Theme wählen
 * Step 2: Visueller Design-Editor mit Theme-Farben
 */

$bannerId = rex_request('id', 'int');
$func = rex_request('func', 'string');

// Theme Builder Manager
$themeBuilderAvailable = rex_addon::get('uikit_theme_builder')->isAvailable();
$themes = [];
$themeColors = [];

if ($themeBuilderAvailable) {
    $themeManager = new UikitThemeBuilder\UikitThemeBuilderManager();
    $themes = $themeManager->listThemes();
}

// Banner laden
$banner = [];
$isNew = true;
$step = 'create'; // create = Schritt 1, design = Schritt 2

if ($bannerId) {
    $sql = rex_sql::factory();
    $sql->setQuery('SELECT * FROM ' . rex::getTable('uikit_banner_designs') . ' WHERE id = ?', [$bannerId]);
    if ($sql->getRows()) {
        $banner = $sql->getArray()[0];
        $isNew = false;
        $step = 'design'; // Bei existierendem Banner direkt zum Design-Editor
        
        // Theme-Farben laden
        if ($themeBuilderAvailable && !empty($banner['theme_name'])) {
            $themeColors = UikitThemeBuilder\ThemeHelper::getThemeColors($themeManager, $banner['theme_name']);
        }
    }
}

// Speichern - Schritt 1 (Name & Theme)
if ($func === 'save_basic' && $isNew) {
    $sql = rex_sql::factory();
    $sql->setTable(rex::getTable('uikit_banner_designs'));
    
    $sql->setValue('name', rex_post('name', 'string'));
    $sql->setValue('theme_name', rex_post('theme_name', 'string'));
    $sql->setValue('created_at', date('Y-m-d H:i:s'));
    $sql->setValue('updated_at', date('Y-m-d H:i:s'));
    
    // Defaults setzen
    $sql->setValue('height', 'medium');
    $sql->setValue('bg_type', 'color');
    $sql->setValue('bg_color', '#f8f8f8');
    
    $sql->insert();
    $bannerId = $sql->getLastId();
    
    // Weiterleitung zu Schritt 2 (Design-Editor)
    header('Location: ' . rex_url::currentBackendPage(['func' => '', 'id' => $bannerId]));
    exit;
}

// Speichern - Schritt 2 (Design)
if ($func === 'save') {
    $sql = rex_sql::factory();
    $sql->setTable(rex::getTable('uikit_banner_designs'));
    
    $sql->setValue('name', rex_post('name', 'string'));
    $sql->setValue('theme_name', rex_post('theme_name', 'string'));
    
    // Höhe: Custom-Wert oder Preset
    $height = rex_post('height', 'string');
    if ($height === 'custom') {
        $height = rex_post('height_custom', 'string');
    }
    $sql->setValue('height', $height);
    
    $sql->setValue('bg_type', rex_post('bg_type', 'string'));
    $sql->setValue('bg_color', rex_post('bg_color', 'string'));
    $sql->setValue('bg_gradient_start', rex_post('bg_gradient_start', 'string'));
    $sql->setValue('bg_gradient_end', rex_post('bg_gradient_end', 'string'));
    $sql->setValue('bg_gradient_direction', rex_post('bg_gradient_direction', 'string'));
    $sql->setValue('bg_image', rex_post('bg_image', 'string'));
    $sql->setValue('bg_image_repeat', rex_post('bg_image_repeat', 'int', 0));
    $sql->setValue('bg_image_position', rex_post('bg_image_position', 'string'));
    $sql->setValue('bg_video', rex_post('bg_video', 'string'));
    $sql->setValue('bg_svg', rex_post('bg_svg', 'string'));
    
    $sql->setValue('border_top_color', rex_post('border_top_color', 'string'));
    $sql->setValue('border_top_width', rex_post('border_top_width', 'int', 0));
    $sql->setValue('border_bottom_color', rex_post('border_bottom_color', 'string'));
    $sql->setValue('border_bottom_width', rex_post('border_bottom_width', 'int', 0));
    $sql->setValue('border_left_color', rex_post('border_left_color', 'string'));
    $sql->setValue('border_left_width', rex_post('border_left_width', 'int', 0));
    $sql->setValue('border_right_color', rex_post('border_right_color', 'string'));
    $sql->setValue('border_right_width', rex_post('border_right_width', 'int', 0));
    
    $sql->setValue('overlay_type', rex_post('overlay_type', 'string'));
    $sql->setValue('overlay_image', rex_post('overlay_image', 'string'));
    $sql->setValue('overlay_svg', rex_post('overlay_svg', 'string'));
    $sql->setValue('overlay_icon', rex_post('overlay_icon', 'string'));
    $sql->setValue('overlay_position', rex_post('overlay_position', 'string'));
    $sql->setValue('overlay_size', rex_post('overlay_size', 'int', 50));
    $sql->setValue('overlay_min_width', rex_post('overlay_min_width', 'string'));
    $sql->setValue('overlay_min_height', rex_post('overlay_min_height', 'string'));
    $sql->setValue('overlay_padding', rex_post('overlay_padding', 'string'));
    
    $sql->setValue('action_button_text', rex_post('action_button_text', 'string'));
    $sql->setValue('action_button_link', rex_post('action_button_link', 'string'));
    $sql->setValue('action_button_style', rex_post('action_button_style', 'string'));
    $sql->setValue('action_button_position', rex_post('action_button_position', 'string'));
    
    if ($isNew) {
        $sql->setValue('created_at', date('Y-m-d H:i:s'));
    }
    $sql->setValue('updated_at', date('Y-m-d H:i:s'));
    
    if ($bannerId) {
        $sql->setWhere(['id' => $bannerId]);
        $sql->update();
    } else {
        $sql->insert();
        $bannerId = $sql->getLastId();
    }
    
    echo rex_view::success(rex_i18n::msg('uikit_banner_design_saved'));
    
    // Banner neu laden
    $sql = rex_sql::factory();
    $sql->setQuery('SELECT * FROM ' . rex::getTable('uikit_banner_designs') . ' WHERE id = ?', [$bannerId]);
    $banner = $sql->getArray()[0];
    $isNew = false;
    
    // Theme-Farben neu laden
    if ($themeBuilderAvailable && !empty($banner['theme_name'])) {
        $themeColors = UikitThemeBuilder\ThemeHelper::getThemeColors($themeManager, $banner['theme_name']);
    }
}

// Defaults
$banner = array_merge([
    'name' => '',
    'theme_name' => '',
    'height' => 'medium',
    'bg_type' => 'color',
    'bg_color' => '#f8f8f8',
    'bg_gradient_start' => '#667eea',
    'bg_gradient_end' => '#764ba2',
    'bg_gradient_direction' => 'to bottom',
    'bg_image' => '',
    'bg_image_repeat' => 0,
    'bg_image_position' => 'center center',
    'bg_video' => '',
    'bg_svg' => '',
    'border_top_color' => '',
    'border_top_width' => 0,
    'border_bottom_color' => '',
    'border_bottom_width' => 0,
    'border_left_color' => '',
    'border_left_width' => 0,
    'border_right_color' => '',
    'border_right_width' => 0,
    'overlay_type' => 'none',
    'overlay_image' => '',
    'overlay_svg' => '',
    'overlay_icon' => '',
    'overlay_position' => 'center center',
    'overlay_size' => 50,
    'action_button_text' => '',
    'action_button_link' => '',
    'action_button_style' => 'primary',
    'action_button_position' => 'bottom center',
], $banner);

// UIKit Assets einbinden
$content = '';
$content .= '<link rel="stylesheet" href="' . rex_url::assets('uikit/css/uikit.min.css') . '">';
$content .= '<script src="' . rex_url::assets('uikit/js/uikit.min.js') . '"></script>';

// SCHRITT 1: Neues Banner anlegen (nur Name & Theme)
if ($isNew) {
    $content .= '<div class="uk-container uk-container-small uk-margin-top">';
    
    // Card für Schritt 1
    $content .= '<div class="uk-card uk-card-default uk-card-large">';
    $content .= '<div class="uk-card-header">';
    $content .= '<h3 class="uk-card-title"><span uk-icon="icon: plus-circle; ratio: 1.2"></span> Neues Banner erstellen</h3>';
    $content .= '<p class="uk-text-meta uk-margin-remove-top">Schritt 1: Name und Theme auswählen</p>';
    $content .= '</div>';
    
    $content .= '<div class="uk-card-body">';
    $content .= '<form action="' . rex_url::currentBackendPage(['func' => 'save_basic']) . '" method="post" class="uk-form-stacked">';
    
    // Name
    $content .= '<div class="uk-margin">';
    $content .= '<label class="uk-form-label uk-text-bold" for="name">Banner Name</label>';
    $content .= '<div class="uk-form-controls">';
    $content .= '<input type="text" name="name" id="name" class="uk-input" placeholder="z.B. Startseiten-Banner, Hero-Bereich, ..." required>';
    $content .= '<div class="uk-text-small uk-text-muted uk-margin-small-top">';
    $content .= '<span uk-icon="icon: info; ratio: 0.8"></span> Gib deinem Banner einen beschreibenden Namen';
    $content .= '</div>';
    $content .= '</div>';
    $content .= '</div>';
    
    // Theme-Auswahl mit Cards
    if ($themeBuilderAvailable && count($themes) > 0) {
        $content .= '<div class="uk-margin-medium-top">';
        $content .= '<label class="uk-form-label uk-text-bold">Theme wählen</label>';
        $content .= '<div class="uk-margin-small-top">';
        
        $content .= '<div class="uk-grid-small uk-child-width-1-2@s uk-child-width-1-3@m" uk-grid>';
        
        // Kein Theme Option
        $content .= '<div>';
        $content .= '<label class="uk-card uk-card-default uk-card-body uk-card-small uk-card-hover" style="cursor: pointer;">';
        $content .= '<input type="radio" name="theme_name" value="" class="uk-radio" checked style="margin-right: 8px;">';
        $content .= '<div>';
        $content .= '<div class="uk-text-bold">Kein Theme</div>';
        $content .= '<div class="uk-text-small uk-text-muted">Ohne Theme-Farben</div>';
        $content .= '</div>';
        $content .= '</label>';
        $content .= '</div>';
        
        // Theme Cards mit Farbpalette
        foreach ($themes as $theme) {
            $themeColors = UikitThemeBuilder\ThemeHelper::getThemeColors($themeManager, $theme['name']);
            
            $content .= '<div>';
            $content .= '<label class="uk-card uk-card-default uk-card-body uk-card-small uk-card-hover" style="cursor: pointer;">';
            $content .= '<input type="radio" name="theme_name" value="' . htmlspecialchars($theme['name']) . '" class="uk-radio" required style="margin-right: 8px;">';
            $content .= '<div>';
            $content .= '<div class="uk-text-bold">' . htmlspecialchars($theme['name']) . '</div>';
            
            // Farbpalette anzeigen
            if (!empty($themeColors)) {
                $content .= '<div class="uk-margin-small-top" style="display: flex; gap: 4px;">';
                $colorCount = 0;
                foreach ($themeColors as $color) {
                    if ($colorCount >= 5) break; // Max 5 Farben anzeigen
                    $content .= '<div style="width: 20px; height: 20px; background-color: ' . $color . '; border-radius: 3px; border: 1px solid rgba(0,0,0,0.1);"></div>';
                    $colorCount++;
                }
                $content .= '</div>';
            }
            
            $content .= '</div>';
            $content .= '</label>';
            $content .= '</div>';
        }
        
        $content .= '</div>';
        $content .= '</div>';
        $content .= '</div>';
    }
    
    $content .= '</div>'; // card-body
    
    // Footer mit Button
    $content .= '<div class="uk-card-footer uk-background-muted">';
    $content .= '<div class="uk-flex uk-flex-between uk-flex-middle">';
    $content .= '<a href="' . rex_url::currentBackendPage() . '" class="uk-button uk-button-default">Abbrechen</a>';
    $content .= '<button type="submit" class="uk-button uk-button-primary uk-button-large">';
    $content .= '<span uk-icon="icon: arrow-right; ratio: 0.9" class="uk-margin-small-right"></span>Weiter zum Design-Editor';
    $content .= '</button>';
    $content .= '</div>';
    $content .= '</div>';
    
    $content .= '</form>';
    $content .= '</div>'; // card
    
    $content .= '</div>'; // container
}

// SCHRITT 2: Design-Editor (bei bestehendem Banner)
else {
    $content .= '<div class="uk-container uk-container-expand uk-margin-top">';
    
    // Header mit Breadcrumb
    $content .= '<nav class="uk-margin-bottom" uk-navbar>';
    $content .= '<div class="uk-navbar-left">';
    $content .= '<ul class="uk-navbar-nav">';
    $content .= '<li><a href="' . rex_url::currentBackendPage() . '" class="uk-text-muted"><span uk-icon="arrow-left"></span> Zurück zur Übersicht</a></li>';
    $content .= '</ul>';
    $content .= '</div>';
    $content .= '<div class="uk-navbar-right">';
    $content .= '<div class="uk-navbar-item">';
    $content .= '<span class="uk-badge uk-badge-success">UIKit Banner Design</span>';
    $content .= '</div>';
    $content .= '</div>';
    $content .= '</nav>';
    
    // Main Card
    $content .= '<div class="uk-card uk-card-default uk-card-large">';
    $content .= '<div class="uk-card-header">';
    $content .= '<div class="uk-grid-small uk-flex-middle" uk-grid>';
    $content .= '<div class="uk-width-expand">';
    $content .= '<h3 class="uk-card-title uk-margin-remove-bottom"><span uk-icon="icon: image; ratio: 1.2"></span> Banner Design</h3>';
    $content .= '<p class="uk-text-meta uk-margin-remove-top">Banner: <strong>' . rex_escape($banner['name']) . '</strong>';
    if (!empty($banner['theme_name'])) {
        $content .= ' | Theme: <strong>' . rex_escape($banner['theme_name']) . '</strong>';
    }
    $content .= '</p>';
    $content .= '</div>';
    $content .= '</div>';
    $content .= '</div>';
    
    $content .= '<div class="uk-card-body">';
    
    // Form Start
    $content .= '<form action="' . rex_url::currentBackendPage(['func' => 'save', 'id' => $bannerId]) . '" method="post" class="uk-form-horizontal">';
    
    // Accordion mit Sections
    $content .= '<ul uk-accordion="multiple: true">';
    
    // === NAME & THEME SECTION ===
    $content .= '<li class="uk-open">';
    $content .= '<a class="uk-accordion-title" href="#">';
    $content .= '<div class="uk-grid-small uk-flex-middle" uk-grid>';
    $content .= '<div class="uk-width-auto"><span uk-icon="icon: tag; ratio: 1.2" class="uk-text-primary"></span></div>';
    $content .= '<div class="uk-width-expand"><h4 class="uk-margin-remove">Name & Theme</h4><p class="uk-text-meta uk-margin-remove-top">Banner-Name und Theme-Auswahl</p></div>';
    $content .= '</div>';
    $content .= '</a>';
    $content .= '<div class="uk-accordion-content">';
    $content .= '<div class="uk-card uk-card-default uk-card-body">';
    
    // Name
    $content .= '<div class="uk-margin">';
    $content .= '<label class="uk-form-label">Banner Name</label>';
    $content .= '<div class="uk-form-controls">';
    $content .= '<input type="text" name="name" class="uk-input" value="' . rex_escape($banner['name']) . '" placeholder="z.B. Startseiten-Banner">';
    $content .= '</div>';
    $content .= '</div>';
    
    // Theme-Auswahl
    if ($themeBuilderAvailable && count($themes) > 0) {
        $content .= '<div class="uk-margin">';
        $content .= '<label class="uk-form-label">Theme</label>';
        $content .= '<div class="uk-form-controls">';
        $content .= '<select name="theme_name" class="uk-select">';
        $content .= '<option value="">Kein Theme</option>';
        foreach ($themes as $theme) {
            $selected = ($banner['theme_name'] === $theme['name']) ? ' selected' : '';
            $content .= '<option value="' . htmlspecialchars($theme['name']) . '"' . $selected . '>' . htmlspecialchars($theme['name']) . '</option>';
        }
        $content .= '</select>';
        $content .= '<div class="uk-text-small uk-text-muted uk-margin-small-top">';
        $content .= '<span uk-icon="icon: info; ratio: 0.8"></span> Theme-Farben werden im Design-Editor verfügbar';
        $content .= '</div>';
        $content .= '</div>';
        $content .= '</div>';
    }
    
    $content .= '</div>'; // card-body
    $content .= '</div>'; // accordion-content
    $content .= '</li>';
    
    // === BASICS SECTION ===
    $content .= '<li class="uk-open">';
    $content .= '<a class="uk-accordion-title" href="#">';
    $content .= '<div class="uk-grid-small uk-flex-middle" uk-grid>';
    $content .= '<div class="uk-width-auto"><span uk-icon="icon: cog; ratio: 1.2" class="uk-text-primary"></span></div>';
    $content .= '<div class="uk-width-expand"><h4 class="uk-margin-remove">Grundeinstellungen</h4><p class="uk-text-meta uk-margin-remove-top">Höhe und allgemeine Einstellungen</p></div>';
    $content .= '</div>';
    $content .= '</a>';
    $content .= '<div class="uk-accordion-content">';
    $content .= '<div class="uk-card uk-card-default uk-card-body">';
    
    // Höhe
    $content .= '<div class="uk-margin">';
    $content .= '<label class="uk-form-label">Höhe</label>';
    $content .= '<div class="uk-form-controls">';
    
    // Prüfen ob der aktuelle Wert in den Presets ist
    $heights = [
        '30vh' => 'Klein (30vh)',
        '50vh' => 'Mittel (50vh)', 
        '70vh' => 'Groß (70vh)',
        '100vh' => 'Vollbild (100vh)',
        '300px' => 'Klein (300px)',
        '500px' => 'Mittel (500px)',
        '700px' => 'Groß (700px)',
        'custom' => 'Individuelle Höhe'
    ];
    
    $isCustom = !empty($banner['height']) && !array_key_exists($banner['height'], $heights);
    $currentHeight = $isCustom ? 'custom' : $banner['height'];
    $customValue = $isCustom ? $banner['height'] : '';
    
    $content .= '<select name="height" id="height-select" class="uk-select">';
    foreach ($heights as $value => $label) {
        $selected = ($currentHeight === $value) ? ' selected' : '';
        $content .= '<option value="' . $value . '"' . $selected . '>' . $label . '</option>';
    }
    $content .= '</select>';
    
    // Custom Height Input (nur anzeigen wenn custom ausgewählt)
    $customDisplay = $isCustom ? '' : ' style="display:none;"';
    $content .= '<div id="custom-height-wrapper" class="uk-margin-small-top"' . $customDisplay . '>';
    $content .= '<input type="text" name="height_custom" id="height-custom" class="uk-input" ';
    $content .= 'value="' . rex_escape($customValue) . '" placeholder="z.B. 400px, 60vh, 25rem, calc(100vh - 100px)">';
    $content .= '<div class="uk-text-small uk-text-muted uk-margin-small-top">';
    $content .= '<span uk-icon="icon: info; ratio: 0.8"></span> Beliebige CSS-Höhe (px, vh, %, rem, calc, etc.)';
    $content .= '</div>';
    $content .= '</div>';
    
    $content .= '</div>';
    $content .= '</div>';
    
    $content .= '</div></div></li>';
    
    // === BACKGROUND SECTION ===
    $content .= '<li class="uk-open">';
    $content .= '<a class="uk-accordion-title" href="#">';
    $content .= '<div class="uk-grid-small uk-flex-middle" uk-grid>';
    $content .= '<div class="uk-width-auto"><span uk-icon="icon: paint-bucket; ratio: 1.2" class="uk-text-primary"></span></div>';
    $content .= '<div class="uk-width-expand"><h4 class="uk-margin-remove">Hintergrund</h4><p class="uk-text-meta uk-margin-remove-top">Farbe, Verlauf, Bild, Video oder SVG</p></div>';
    $content .= '</div>';
    $content .= '</a>';
    $content .= '<div class="uk-accordion-content">';
    $content .= '<div class="uk-card uk-card-default uk-card-body">';
    
    // BG Type
    $content .= '<div class="uk-margin">';
    $content .= '<label class="uk-form-label">Typ</label>';
    $content .= '<div class="uk-form-controls">';
    $types = ['color' => 'Farbe', 'gradient' => 'Verlauf', 'image' => 'Bild', 'video' => 'Video', 'svg' => 'SVG'];
    $content .= '<select name="bg_type" id="bg_type" class="uk-select">';
    foreach ($types as $value => $label) {
        $selected = ($banner['bg_type'] === $value) ? ' selected' : '';
        $content .= '<option value="' . $value . '"' . $selected . '>' . $label . '</option>';
    }
    $content .= '</select>';
    $content .= '</div>';
    $content .= '</div>';
    
    // BG Color mit Theme-Farben
    $content .= '<div class="uk-margin bg-field bg-color">';
    $content .= '<label class="uk-form-label">Farbe</label>';
    $content .= '<div class="uk-form-controls">';
    $content .= '<input type="color" name="bg_color" value="' . htmlspecialchars($banner['bg_color']) . '" class="uk-input uk-form-width-medium">';
    
    // Theme-Farben als Buttons
    if (!empty($themeColors)) {
        $content .= '<div class="uk-margin-small-top">';
        $content .= '<div class="uk-text-small uk-text-muted uk-margin-small-bottom">Theme-Farben:</div>';
        $content .= '<div class="uk-button-group">';
        foreach ($themeColors as $key => $color) {
            $content .= '<button type="button" class="uk-button uk-button-small" style="background-color: ' . $color . '; width: 40px; height: 40px; padding: 0;" onclick="document.querySelector(\'[name=bg_color]\').value=\'' . $color . '\';" title="' . $key . ' (' . $color . ')"></button>';
        }
        $content .= '</div>';
        $content .= '</div>';
    }
    $content .= '</div>';
    $content .= '</div>';
    
    // Gradient Felder
    $content .= '<div class="uk-margin bg-field bg-gradient" style="display:none;">';
    $content .= '<label class="uk-form-label">Verlauf Start</label>';
    $content .= '<div class="uk-form-controls">';
    $content .= '<input type="color" name="bg_gradient_start" value="' . htmlspecialchars($banner['bg_gradient_start']) . '" class="uk-input uk-form-width-medium">';
    if (!empty($themeColors)) {
        $content .= '<div class="uk-margin-small-top uk-button-group">';
        foreach ($themeColors as $key => $color) {
            $content .= '<button type="button" class="uk-button uk-button-small" style="background-color: ' . $color . '; width: 30px; height: 30px; padding: 0;" onclick="document.querySelector(\'[name=bg_gradient_start]\').value=\'' . $color . '\';"></button>';
        }
        $content .= '</div>';
    }
    $content .= '</div>';
    $content .= '</div>';
    
    $content .= '<div class="uk-margin bg-field bg-gradient" style="display:none;">';
    $content .= '<label class="uk-form-label">Verlauf Ende</label>';
    $content .= '<div class="uk-form-controls">';
    $content .= '<input type="color" name="bg_gradient_end" value="' . htmlspecialchars($banner['bg_gradient_end']) . '" class="uk-input uk-form-width-medium">';
    if (!empty($themeColors)) {
        $content .= '<div class="uk-margin-small-top uk-button-group">';
        foreach ($themeColors as $key => $color) {
            $content .= '<button type="button" class="uk-button uk-button-small" style="background-color: ' . $color . '; width: 30px; height: 30px; padding: 0;" onclick="document.querySelector(\'[name=bg_gradient_end]\').value=\'' . $color . '\';"></button>';
        }
        $content .= '</div>';
    }
    $content .= '</div>';
    $content .= '</div>';
    
    $content .= '<div class="uk-margin bg-field bg-gradient" style="display:none;">';
    $content .= '<label class="uk-form-label">Verlauf Richtung</label>';
    $content .= '<div class="uk-form-controls">';
    $directions = ['to bottom' => 'Nach unten', 'to top' => 'Nach oben', 'to right' => 'Nach rechts', 'to left' => 'Nach links', 'to bottom right' => 'Diagonal'];
    $content .= '<select name="bg_gradient_direction" class="uk-select">';
    foreach ($directions as $value => $label) {
        $selected = ($banner['bg_gradient_direction'] === $value) ? ' selected' : '';
        $content .= '<option value="' . $value . '"' . $selected . '>' . $label . '</option>';
    }
    $content .= '</select>';
    $content .= '</div>';
    $content .= '</div>';
    
    // BG Image
    $content .= '<div class="uk-margin bg-field bg-image" style="display:none;">';
    $content .= '<label class="uk-form-label">Hintergrundbild</label>';
    $content .= '<div class="uk-form-controls">';
    $content .= rex_var_media::getWidget(1, 'bg_image', $banner['bg_image']);
    $content .= '</div>';
    $content .= '</div>';
    
    $content .= '<div class="uk-margin bg-field bg-image" style="display:none;">';
    $content .= '<label class="uk-form-label">Bild Position</label>';
    $content .= '<div class="uk-form-controls">';
    $positions = ['center center' => 'Zentriert', 'top left' => 'Oben links', 'top center' => 'Oben mittig', 'top right' => 'Oben rechts', 'center left' => 'Mittig links', 'center right' => 'Mittig rechts', 'bottom left' => 'Unten links', 'bottom center' => 'Unten mittig', 'bottom right' => 'Unten rechts'];
    $content .= '<select name="bg_image_position" class="uk-select">';
    foreach ($positions as $value => $label) {
        $selected = ($banner['bg_image_position'] === $value) ? ' selected' : '';
        $content .= '<option value="' . $value . '"' . $selected . '>' . $label . '</option>';
    }
    $content .= '</select>';
    $content .= '</div>';
    $content .= '</div>';
    
    $content .= '<div class="uk-margin bg-field bg-image" style="display:none;">';
    $content .= '<label class="uk-form-label">Bild wiederholen</label>';
    $content .= '<div class="uk-form-controls">';
    $content .= '<label><input type="checkbox" name="bg_image_repeat" value="1" class="uk-checkbox"' . ($banner['bg_image_repeat'] ? ' checked' : '') . '> Als Muster wiederholen</label>';
    $content .= '</div>';
    $content .= '</div>';
    
    // BG Video
    $content .= '<div class="uk-margin bg-field bg-video" style="display:none;">';
    $content .= '<label class="uk-form-label">Hintergrundvideo</label>';
    $content .= '<div class="uk-form-controls">';
    $content .= rex_var_media::getWidget(2, 'bg_video', $banner['bg_video']);
    $content .= '</div>';
    $content .= '</div>';
    
    // BG SVG
    $content .= '<div class="uk-margin bg-field bg-svg" style="display:none;">';
    $content .= '<label class="uk-form-label">SVG Code</label>';
    $content .= '<div class="uk-form-controls">';
    $content .= '<textarea name="bg_svg" class="uk-textarea" rows="4" placeholder="<svg>...</svg>">' . htmlspecialchars($banner['bg_svg']) . '</textarea>';
    $content .= '</div>';
    $content .= '</div>';
    
    $content .= '</div></div></li>';
    
    // === BORDERS SECTION ===
    $content .= '<li>';
    $content .= '<a class="uk-accordion-title" href="#">';
    $content .= '<div class="uk-grid-small uk-flex-middle" uk-grid>';
    $content .= '<div class="uk-width-auto"><span uk-icon="icon: minus-circle; ratio: 1.2" class="uk-text-primary"></span></div>';
    $content .= '<div class="uk-width-expand"><h4 class="uk-margin-remove">Rahmen</h4><p class="uk-text-meta uk-margin-remove-top">Rahmenlinien an den Seiten</p></div>';
    $content .= '</div>';
    $content .= '</a>';
    $content .= '<div class="uk-accordion-content">';
    $content .= '<div class="uk-card uk-card-default uk-card-body">';
    
    foreach (['top' => 'Oben', 'bottom' => 'Unten', 'left' => 'Links', 'right' => 'Rechts'] as $side => $label) {
        $content .= '<div class="uk-margin">';
        $content .= '<label class="uk-form-label uk-text-bold">' . $label . '</label>';
        $content .= '<div class="uk-form-controls">';
        $content .= '<div class="uk-grid-small" uk-grid>';
        
        // Color
        $content .= '<div class="uk-width-1-2@s">';
        $content .= '<label class="uk-text-small">Farbe</label>';
        $content .= '<input type="color" name="border_' . $side . '_color" value="' . htmlspecialchars($banner['border_' . $side . '_color']) . '" class="uk-input">';
        if (!empty($themeColors)) {
            $content .= '<div class="uk-margin-small-top uk-button-group">';
            foreach ($themeColors as $key => $color) {
                $content .= '<button type="button" class="uk-button uk-button-small" style="background-color: ' . $color . '; width: 25px; height: 25px; padding: 0;" onclick="document.querySelector(\'[name=border_' . $side . '_color]\').value=\'' . $color . '\';"></button>';
            }
            $content .= '</div>';
        }
        $content .= '</div>';
        
        // Width
        $content .= '<div class="uk-width-1-2@s">';
        $content .= '<label class="uk-text-small">Breite (px)</label>';
        $content .= '<input type="number" name="border_' . $side . '_width" value="' . htmlspecialchars($banner['border_' . $side . '_width']) . '" min="0" max="50" class="uk-input">';
        $content .= '</div>';
        
        $content .= '</div>';
        $content .= '</div>';
        $content .= '</div>';
    }
    
    $content .= '</div></div></li>';
    
    // === OVERLAY SECTION ===
    $content .= '<li>';
    $content .= '<a class="uk-accordion-title" href="#">';
    $content .= '<div class="uk-grid-small uk-flex-middle" uk-grid>';
    $content .= '<div class="uk-width-auto"><span uk-icon="icon: image; ratio: 1.2" class="uk-text-primary"></span></div>';
    $content .= '<div class="uk-width-expand"><h4 class="uk-margin-remove">Overlay</h4><p class="uk-text-meta uk-margin-remove-top">Logo, Icon oder Grafik über dem Banner</p></div>';
    $content .= '</div>';
    $content .= '</a>';
    $content .= '<div class="uk-accordion-content">';
    $content .= '<div class="uk-card uk-card-default uk-card-body">';
    
    // Overlay Type
    $content .= '<div class="uk-margin">';
    $content .= '<label class="uk-form-label">Typ</label>';
    $content .= '<div class="uk-form-controls">';
    $types = ['none' => 'Keins', 'image' => 'Bild', 'svg' => 'SVG', 'icon' => 'UIKit Icon'];
    $content .= '<select name="overlay_type" id="overlay_type" class="uk-select">';
    foreach ($types as $value => $label) {
        $selected = ($banner['overlay_type'] === $value) ? ' selected' : '';
        $content .= '<option value="' . $value . '"' . $selected . '>' . $label . '</option>';
    }
    $content .= '</select>';
    $content .= '</div>';
    $content .= '</div>';
    
    // Overlay Image
    $content .= '<div class="uk-margin overlay-field overlay-image" style="display:none;">';
    $content .= '<label class="uk-form-label">Overlay Bild</label>';
    $content .= '<div class="uk-form-controls">';
    $content .= rex_var_media::getWidget(3, 'overlay_image', $banner['overlay_image']);
    $content .= '</div>';
    $content .= '</div>';
    
    // Overlay SVG
    $content .= '<div class="uk-margin overlay-field overlay-svg" style="display:none;">';
    $content .= '<label class="uk-form-label">SVG Code</label>';
    $content .= '<div class="uk-form-controls">';
    $content .= '<textarea name="overlay_svg" class="uk-textarea" rows="4" placeholder="<svg>...</svg>">' . htmlspecialchars($banner['overlay_svg']) . '</textarea>';
    $content .= '</div>';
    $content .= '</div>';
    
    // Overlay Icon
    $content .= '<div class="uk-margin overlay-field overlay-icon" style="display:none;">';
    $content .= '<label class="uk-form-label">Icon Name</label>';
    $content .= '<div class="uk-form-controls">';
    $content .= '<input type="text" name="overlay_icon" value="' . htmlspecialchars($banner['overlay_icon']) . '" class="uk-input" placeholder="z.B. star, heart, check">';
    $content .= '<div class="uk-text-small uk-text-muted uk-margin-small-top">';
    $content .= '<a href="https://getuikit.com/docs/icon" target="_blank">UIKit Icons ansehen</a>';
    $content .= '</div>';
    $content .= '</div>';
    $content .= '</div>';
    
    // Position & Größe (für alle Overlay-Typen außer none)
    $content .= '<div class="overlay-field overlay-image overlay-svg overlay-icon" style="display:none;">';
    
    $content .= '<div class="uk-margin">';
    $content .= '<label class="uk-form-label">Position</label>';
    $content .= '<div class="uk-form-controls">';
    $positions = ['center center' => 'Zentriert', 'top left' => 'Oben links', 'top center' => 'Oben mittig', 'top right' => 'Oben rechts', 'center left' => 'Mittig links', 'center right' => 'Mittig rechts', 'bottom left' => 'Unten links', 'bottom center' => 'Unten mittig', 'bottom right' => 'Unten rechts'];
    $content .= '<select name="overlay_position" class="uk-select">';
    foreach ($positions as $value => $label) {
        $selected = ($banner['overlay_position'] === $value) ? ' selected' : '';
        $content .= '<option value="' . $value . '"' . $selected . '>' . $label . '</option>';
    }
    $content .= '</select>';
    $content .= '</div>';
    $content .= '</div>';
    
    $content .= '<div class="uk-margin">';
    $content .= '<label class="uk-form-label">Größe (%)</label>';
    $content .= '<div class="uk-form-controls">';
    $content .= '<input type="number" name="overlay_size" value="' . htmlspecialchars($banner['overlay_size']) . '" min="10" max="100" class="uk-input uk-form-width-small">';
    $content .= '</div>';
    $content .= '</div>';
    
    $content .= '<div class="uk-margin">';
    $content .= '<label class="uk-form-label">Min. Breite <span class="uk-text-muted">(optional)</span></label>';
    $content .= '<div class="uk-form-controls">';
    $content .= '<input type="text" name="overlay_min_width" value="' . htmlspecialchars($banner['overlay_min_width'] ?? '') . '" class="uk-input uk-form-width-medium" placeholder="z.B. 200px, 50%, 10rem">';
    $content .= '<div class="uk-text-small uk-text-muted uk-margin-small-top">';
    $content .= '<span uk-icon="icon: info; ratio: 0.8"></span> CSS-Wert für minimale Breite';
    $content .= '</div>';
    $content .= '</div>';
    $content .= '</div>';
    
    $content .= '<div class="uk-margin">';
    $content .= '<label class="uk-form-label">Min. Höhe <span class="uk-text-muted">(optional)</span></label>';
    $content .= '<div class="uk-form-controls">';
    $content .= '<input type="text" name="overlay_min_height" value="' . htmlspecialchars($banner['overlay_min_height'] ?? '') . '" class="uk-input uk-form-width-medium" placeholder="z.B. 100px, 20%, 5rem">';
    $content .= '<div class="uk-text-small uk-text-muted uk-margin-small-top">';
    $content .= '<span uk-icon="icon: info; ratio: 0.8"></span> CSS-Wert für minimale Höhe';
    $content .= '</div>';
    $content .= '</div>';
    $content .= '</div>';
    
    $content .= '<div class="uk-margin">';
    $content .= '<label class="uk-form-label">Abstand zum Rand <span class="uk-text-muted">(optional)</span></label>';
    $content .= '<div class="uk-form-controls">';
    $content .= '<input type="text" name="overlay_padding" value="' . htmlspecialchars($banner['overlay_padding'] ?? '') . '" class="uk-input uk-form-width-medium" placeholder="z.B. 20px, 2rem, 5%">';
    $content .= '<div class="uk-text-small uk-text-muted uk-margin-small-top">';
    $content .= '<span uk-icon="icon: info; ratio: 0.8"></span> Abstand des Overlays zum Banner-Rand';
    $content .= '</div>';
    $content .= '</div>';
    $content .= '</div>';
    
    $content .= '</div>';
    
    $content .= '</div></div></li>';
    
    // === ACTION BUTTON SECTION ===
    $content .= '<li>';
    $content .= '<a class="uk-accordion-title" href="#">';
    $content .= '<div class="uk-grid-small uk-flex-middle" uk-grid>';
    $content .= '<div class="uk-width-auto"><span uk-icon="icon: link; ratio: 1.2" class="uk-text-primary"></span></div>';
    $content .= '<div class="uk-width-expand"><h4 class="uk-margin-remove">Action Button</h4><p class="uk-text-meta uk-margin-remove-top">Optional: Call-to-Action Button</p></div>';
    $content .= '</div>';
    $content .= '</a>';
    $content .= '<div class="uk-accordion-content">';
    $content .= '<div class="uk-card uk-card-default uk-card-body">';
    
    $content .= '<div class="uk-margin">';
    $content .= '<label class="uk-form-label">Button Text</label>';
    $content .= '<div class="uk-form-controls">';
    $content .= '<input type="text" name="action_button_text" value="' . htmlspecialchars($banner['action_button_text']) . '" class="uk-input" placeholder="z.B. Mehr erfahren, Jetzt buchen...">';
    $content .= '</div>';
    $content .= '</div>';
    
    $content .= '<div class="uk-margin">';
    $content .= '<label class="uk-form-label">Button Link</label>';
    $content .= '<div class="uk-form-controls">';
    $content .= '<input type="text" name="action_button_link" value="' . htmlspecialchars($banner['action_button_link']) . '" class="uk-input" placeholder="https://...">';
    $content .= '</div>';
    $content .= '</div>';
    
    $content .= '<div class="uk-margin">';
    $content .= '<label class="uk-form-label">Button Style</label>';
    $content .= '<div class="uk-form-controls">';
    $styles = ['primary' => 'Primary', 'secondary' => 'Secondary', 'default' => 'Default', 'danger' => 'Danger', 'text' => 'Text'];
    $content .= '<select name="action_button_style" class="uk-select">';
    foreach ($styles as $value => $label) {
        $selected = ($banner['action_button_style'] === $value) ? ' selected' : '';
        $content .= '<option value="' . $value . '"' . $selected . '>' . $label . '</option>';
    }
    $content .= '</select>';
    $content .= '</div>';
    $content .= '</div>';
    
    $content .= '<div class="uk-margin">';
    $content .= '<label class="uk-form-label">Button Position</label>';
    $content .= '<div class="uk-form-controls">';
    $positions = [
        'center center' => 'Zentriert',
        'top left' => 'Oben links',
        'top center' => 'Oben mittig',
        'top right' => 'Oben rechts',
        'center left' => 'Mittig links',
        'center right' => 'Mittig rechts',
        'bottom left' => 'Unten links',
        'bottom center' => 'Unten mittig',
        'bottom right' => 'Unten rechts'
    ];
    $content .= '<select name="action_button_position" class="uk-select">';
    foreach ($positions as $value => $label) {
        $selected = ($banner['action_button_position'] === $value) ? ' selected' : '';
        $content .= '<option value="' . $value . '"' . $selected . '>' . $label . '</option>';
    }
    $content .= '</select>';
    $content .= '</div>';
    $content .= '</div>';
    
    $content .= '</div></div></li>';
    
    $content .= '</ul>'; // End Accordion
    
    $content .= '</div>'; // card-body
    
    // Footer mit Buttons
    $content .= '<div class="uk-card-footer uk-background-muted">';
    $content .= '<div class="uk-grid-small uk-flex-middle uk-flex-between" uk-grid>';
    $content .= '<div class="uk-width-auto">';
    $content .= '<a href="' . rex_url::currentBackendPage() . '" class="uk-button uk-button-default">Abbrechen</a>';
    $content .= '</div>';
    $content .= '<div class="uk-width-auto">';
    $content .= '<div class="uk-button-group">';
    $content .= '<button type="submit" class="uk-button uk-button-primary uk-button-large">';
    $content .= '<span uk-icon="icon: check; ratio: 0.9" class="uk-margin-small-right"></span>Speichern';
    $content .= '</button>';
    $content .= '<a href="' . rex_url::currentBackendPage(['page' => 'uikit_banner_design/preview', 'id' => $bannerId]) . '" class="uk-button uk-button-default uk-button-large" target="_blank">';
    $content .= '<span uk-icon="icon: eye; ratio: 0.9" class="uk-margin-small-right"></span>Vorschau';
    $content .= '</a>';
    $content .= '</div>';
    $content .= '</div>';
    $content .= '</div>';
    $content .= '</div>';
    
    $content .= '</form>';
    $content .= '</div>'; // card
    $content .= '</div>'; // container
    
    // JavaScript für bedingte Felder
    $content .= '<script>
document.addEventListener("DOMContentLoaded", function() {
    // Height Select Toggle
    const heightSelect = document.getElementById("height-select");
    const customWrapper = document.getElementById("custom-height-wrapper");
    if (heightSelect && customWrapper) {
        heightSelect.addEventListener("change", function() {
            customWrapper.style.display = this.value === "custom" ? "block" : "none";
        });
    }
    
    // Background Type Toggle
    const bgTypeSelect = document.getElementById("bg_type");
    if (bgTypeSelect) {
        function toggleBgFields() {
            const type = bgTypeSelect.value;
            
            // Alle ausblenden
            document.querySelectorAll(".bg-field").forEach(el => el.style.display = "none");
            
            // Relevante einblenden
            if (type === "color") {
                document.querySelectorAll(".bg-color").forEach(el => el.style.display = "block");
            } else if (type === "gradient") {
                document.querySelectorAll(".bg-gradient").forEach(el => el.style.display = "block");
            } else if (type === "image") {
                document.querySelectorAll(".bg-image").forEach(el => el.style.display = "block");
            } else if (type === "video") {
                document.querySelectorAll(".bg-video").forEach(el => el.style.display = "block");
            } else if (type === "svg") {
                document.querySelectorAll(".bg-svg").forEach(el => el.style.display = "block");
            }
        }
        
        bgTypeSelect.addEventListener("change", toggleBgFields);
        toggleBgFields(); // Initial
    }
    
    // Overlay Type Toggle
    const overlayTypeSelect = document.getElementById("overlay_type");
    if (overlayTypeSelect) {
        function toggleOverlayFields() {
            const type = overlayTypeSelect.value;
            
            // Alle ausblenden
            document.querySelectorAll(".overlay-field").forEach(el => el.style.display = "none");
            
            // Relevante einblenden
            if (type === "image") {
                document.querySelectorAll(".overlay-image").forEach(el => el.style.display = "block");
            } else if (type === "svg") {
                document.querySelectorAll(".overlay-svg").forEach(el => el.style.display = "block");
            } else if (type === "icon") {
                document.querySelectorAll(".overlay-icon").forEach(el => el.style.display = "block");
            }
        }
        
        overlayTypeSelect.addEventListener("change", toggleOverlayFields);
        toggleOverlayFields(); // Initial
    }
});
</script>';
}

echo $content;
