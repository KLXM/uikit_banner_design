# UIKit Banner Design - Template Integration

## Verwendung in Templates

### 1. Template Manager Integration

Banner können über den Template Manager als Domain/Sprach-spezifisches Setting konfiguriert werden:

```php
/**
 * DOMAIN_SETTINGS:
 * tm_banner_id: banner_select|Header Banner||Optional: Banner nach der Navbar anzeigen
 */
```

Im Template kann der Banner dann ausgegeben werden:

```php
use FriendsOfRedaxo\TemplateManager\TemplateManager;

// Banner ID aus Template Manager laden
$bannerId = TemplateManager::get('tm_banner_id', '');

// Banner rendern wenn gesetzt
if (!empty($bannerId) && is_numeric($bannerId)) {
    echo UikitBannerRenderer::render((int)$bannerId);
}
```

### 2. Direkte Verwendung

Banner können auch direkt ohne Template Manager ausgegeben werden:

```php
// Banner mit ID 1 ausgeben
echo UikitBannerRenderer::render(1);

// Banner mit eigenem Content
echo UikitBannerRenderer::render(1, '<h1 class="uk-heading-large">Willkommen</h1>');
```

## Beispiel: Standard-Template mit Banner

```php
<?php
use FriendsOfRedaxo\TemplateManager\TemplateManager;

// Banner-Einstellung aus Template Manager
$bannerId = TemplateManager::get('tm_banner_id', '');
?>

<!-- Header mit Navigation -->
<header role="banner">
    <nav class="uk-navbar-container" uk-navbar>
        <!-- Navigation hier -->
    </nav>
</header>

<!-- Optional: Banner nach Navbar -->
<?php 
if (!empty($bannerId) && is_numeric($bannerId)) {
    echo UikitBannerRenderer::render((int)$bannerId);
}
?>

<!-- Main Content -->
<main>
    <?php echo 'REX_ARTICLE[]' ?>
</main>
```

## Banner-Features

- **Responsive Höhen**: vh oder px Werte (z.B. "50vh", "400px")
- **Hintergründe**: Farbe, Gradient, Bild, Video, SVG
- **Overlays**: Logo, Icon oder SVG mit Positionierung
- **Action Buttons**: Call-to-Action mit UIKit Button-Styles
- **Borders**: Individuelle Rahmen pro Seite

## API

### UikitBannerRenderer::render()

```php
/**
 * @param int $bannerId Banner-ID aus der Datenbank
 * @param string $content Optionaler HTML-Content im Banner
 * @return string Gerendeter HTML-Code
 */
public static function render(int $bannerId, string $content = ''): string
```

### Rückgabewert

Bei Erfolg: Vollständiger HTML-Code mit allen Styles
Bei Fehler: HTML-Kommentar mit Fehlermeldung

## Beispiel-Ausgabe

```html
<div class="uikit-banner" style="position: relative; height: 50vh; ...">
    <!-- Hintergrund Layer -->
    <div style="...background-image: url(...)"></div>
    
    <!-- Overlay Layer -->
    <div class="uk-position-center" style="...">
        <img src="logo.svg" alt="">
    </div>
    
    <!-- Content Layer -->
    <div class="uk-position-center uk-text-center uk-light">
        <!-- Eigener Content oder leer -->
    </div>
    
    <!-- Action Button -->
    <div class="uk-position-bottom-center">
        <a href="#" class="uk-button uk-button-primary">Mehr erfahren</a>
    </div>
</div>
```

## Template Manager Field-Type

Der neue `banner_select` Field-Type bietet:

- ✅ Dropdown mit allen verfügbaren Bannern
- ✅ Live-Suche (via Bootstrap Selectpicker)
- ✅ Vorschau-Link zum Banner
- ✅ "Kein Banner" Option
- ✅ Prüfung ob Banner Designer installiert ist

## Barrierefreiheit

Banner werden mit semantischen HTML-Elementen und ARIA-Labels ausgegeben:
- Responsive Höhen für alle Geräte
- Touch-optimierte Action Buttons
- Keyboard-Navigation unterstützt

## Performance

- Banner-HTML wird serverseitig generiert (kein JavaScript nötig)
- Inline-Styles für schnelles Rendering
- Videos optional mit autoplay/loop/muted
