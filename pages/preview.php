<?php

/**
 * UIKit Banner Design - Preview
 */

// Output Buffer löschen
while (ob_get_level()) {
    ob_end_clean();
}

$bannerId = rex_request('id', 'int');

if (!$bannerId) {
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Fehler</title></head><body><h1>Keine Banner-ID angegeben.</h1></body></html>';
    exit;
}

// Banner laden
$sql = rex_sql::factory();
$sql->setQuery('SELECT * FROM ' . rex::getTable('uikit_banner_designs') . ' WHERE id = ?', [$bannerId]);

if (!$sql->getRows()) {
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Fehler</title></head><body><h1>Banner nicht gefunden.</h1></body></html>';
    exit;
}

$banner = $sql->getArray()[0];

// Theme laden falls vorhanden
$themeBuilderAvailable = rex_addon::get('uikit_theme_builder')->isAvailable();
$themeCss = '';

if ($themeBuilderAvailable && !empty($banner['theme_name'])) {
    // Theme CSS über PathManager laden
    $themeCss = \UikitThemeBuilder\PathManager::getThemesCompiledPublicUrl($banner['theme_name'] . '.css');
    $themeDebug = "Theme geladen: {$banner['theme_name']}.css | URL: {$themeCss}";
} else {
    // Fallback auf Standard UIKit
    $themeCss = rex_url::assets('addons/uikit_theme_builder/compiled_uikit/css/uikit.min.css');
    $themeDebug = "Fallback UIKit verwendet | URL: {$themeCss}";
}

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Banner Preview: <?= htmlspecialchars($banner['name']) ?></title>
    
    <!-- <?= $themeDebug ?> -->
    
    <!-- UIKit Theme CSS -->
    <link rel="stylesheet" href="<?= $themeCss ?>">
    
    <!-- UIKit JS -->
    <script src="<?= rex_url::assets('addons/uikit_theme_builder/compiled_uikit/js/uikit.min.js') ?>"></script>
    <script src="<?= rex_url::assets('addons/uikit_theme_builder/compiled_uikit/js/uikit-icons.min.js') ?>"></script>    <style>
        body {
            margin: 0;
            padding: 0;
        }
        
        /* Info Bar */
        .preview-info {
            background: linear-gradient(135deg, #324050 0%, #1e2832 100%);
            color: white;
            padding: 15px 20px;
            position: sticky;
            top: 0;
            z-index: 9999;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        
        .preview-info h1 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
        }
        
        .preview-info p {
            margin: 5px 0 0 0;
            font-size: 14px;
            opacity: 0.9;
        }
    </style>
</head>
<body>
<!-- Info Bar -->
<div class="preview-info">
    <div class="uk-container">
        <div class="uk-grid-small uk-flex-middle" uk-grid>
            <div class="uk-width-auto">
                <span uk-icon="icon: image; ratio: 1.5"></span>
            </div>
            <div class="uk-width-expand">
                <h1><?= rex_escape($banner['name']) ?> - Banner Preview</h1>
                <p>
                    Höhe: <?= rex_escape($banner['height']) ?> | 
                    Hintergrund: <?= rex_escape($banner['bg_type']) ?>
                    <?php if (!empty($banner['theme_name'])): ?>
                        | Theme: <?= rex_escape($banner['theme_name']) ?>
                    <?php endif; ?>
                </p>
            </div>
            <div class="uk-width-auto">
                <a href="<?= rex_url::currentBackendPage(['page' => 'uikit_banner_design/edit', 'id' => $bannerId]) ?>" 
                   class="uk-button uk-button-primary uk-button-small">
                    <span uk-icon="icon: pencil"></span> Bearbeiten
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Banner Render -->
<?= UikitBannerRenderer::renderBanner($banner, '') ?>

<!-- Beispiel-Content darunter -->
<div class="uk-section">
    <div class="uk-container">
        <div class="uk-grid-match uk-child-width-1-3@m" uk-grid>
            <div>
                <div class="uk-card uk-card-default uk-card-body">
                    <h3 class="uk-card-title">Feature 1</h3>
                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
                </div>
            </div>
            <div>
                <div class="uk-card uk-card-default uk-card-body">
                    <h3 class="uk-card-title">Feature 2</h3>
                    <p>Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
                </div>
            </div>
            <div>
                <div class="uk-card uk-card-default uk-card-body">
                    <h3 class="uk-card-title">Feature 3</h3>
                    <p>Ut enim ad minim veniam, quis nostrud exercitation ullamco.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="uk-section uk-section-muted">
    <div class="uk-container uk-container-small uk-text-center">
        <h2>Weitere Inhalte</h2>
        <p class="uk-text-large">Dies zeigt wie das Banner im Kontext einer vollständigen Seite aussieht.</p>
        <button class="uk-button uk-button-primary">Beispiel Button</button>
    </div>
</div>

</body>
</html>
<?php
exit;
