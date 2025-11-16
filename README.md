# UIKit Banner Design

Ein REDAXO-Addon zum Erstellen und Verwalten von individuellen Banner-Designs mit UIKit 3.

## Features

- 🎨 **Theme-Integration** - Nutzt Farben und Icons aus dem UIKit Theme Builder
- 🖼️ **Flexible Hintergründe** - Farbe, Gradient, Bild, Video oder SVG
- 🎭 **Overlay-Optionen** - PNG, SVG oder Icon-Overlays
- 🎯 **Action Buttons** - Call-to-Action Buttons mit verschiedenen Stilen
- 📐 **Rahmen-Design** - Individuelle Rahmen (oben, unten, links, rechts)
- 📱 **Responsiv** - UIKit 3 basiert, mobile-optimiert
- 👁️ **Live-Preview** - Echtzeitvorschau im Backend-Editor

## Installation

1. Addon über den Installer oder GitHub installieren
2. Addon installieren und aktivieren
3. **Voraussetzung:** UIKit Theme Builder muss installiert sein

## Banner erstellen

### Backend

1. **UIKit Banner Design** → **Übersicht** öffnen
2. **Neues Banner erstellen** klicken
3. Einstellungen in den Tabs konfigurieren:
   - **Hintergrund**: Typ, Farben, Bilder, Videos
   - **Rahmen**: Farben und Breiten für alle Seiten
   - **Overlay**: Bild-, SVG- oder Icon-Overlay
   - **Action Button**: Text, Link und Stil
4. **Speichern**

### Frontend-Nutzung

#### Basis-Verwendung

```php
<?php
// Banner rendern
echo UikitBannerRenderer::render(1);
?>
```

#### Mit eigenem Content

```php
<?php
$content = '
<h1 class="uk-heading-hero">Willkommen</h1>
<p class="uk-text-large">Ihre Beschreibung hier</p>
';

echo UikitBannerRenderer::render(1, $content);
?>
```

#### Als Template-Variable

```php
<?php
// In einem Template oder Modul
$banner = UikitBannerRenderer::render(
    1,  // Banner-ID
    '<h1>Custom Content</h1>'
);
?>

<div class="my-page">
    <?= $banner ?>
    
    <div class="uk-container">
        <?= $this->getArticle() ?>
    </div>
</div>
```

### Template Manager Integration

**Seit Template Manager 1.x** kann das Banner-Select-Feld über einen Extension Point verwendet werden.

#### Schritt 1: Banner-Feld in Template Manager konfigurieren

Im Template Manager unter **Einstellungen** folgende Zeile hinzufügen:

```
DOMAIN_SETTINGS

tm_header_banner: banner_select|Header Banner|5|Banner für den Kopfbereich
tm_footer_banner: banner_select|Footer Banner||Banner für den Fußbereich (optional)
```

**Syntax:**
- `tm_header_banner` = Eindeutiger Key
- `banner_select` = Feldtyp (registriert durch uikit_banner_design)
- `Header Banner` = Label im Backend
- `5` = Default Banner-ID (optional)
- `Banner für...` = Hilfetext

#### Schritt 2: Banner im Template ausgeben

```php
<?php
// Banner-ID aus Template Manager holen
$bannerId = TemplateManager::get('tm_header_banner');

// Banner rendern wenn konfiguriert
if (!empty($bannerId)) {
    echo UikitBannerRenderer::render((int)$bannerId);
}
?>
```

**Mit eigenem Content:**

```php
<?php
$bannerId = TemplateManager::get('tm_header_banner');

if (!empty($bannerId)) {
    $content = '
    <div class="uk-container uk-container-small">
        <h1 class="uk-heading-hero uk-text-center">
            ' . rex_escape(rex_article::getCurrent()->getName()) . '
        </h1>
    </div>
    ';
    
    echo UikitBannerRenderer::render((int)$bannerId, $content);
}
?>
```

**Conditional Banner:**

```php
<?php
// Verschiedene Banner für verschiedene Kategorien
$category = rex_category::getCurrent();
$bannerId = null;

if ($category && $category->getId() == 5) {
    // News-Kategorie
    $bannerId = TemplateManager::get('tm_news_banner');
} else {
    // Standard Header
    $bannerId = TemplateManager::get('tm_header_banner');
}

if (!empty($bannerId)) {
    echo UikitBannerRenderer::render((int)$bannerId);
}
?>
```

#### Backend-Vorschau

Im Template Manager Backend erscheint das Banner-Select-Feld mit:
- ✅ Dropdown-Liste aller verfügbaren Banner
- ✅ "Banner Vorschau" Button (öffnet Vorschau in neuem Tab)
- ✅ Live-Update der Vorschau bei Änderung

#### Multi-Domain Support

Mit YRewrite können unterschiedliche Banner pro Domain konfiguriert werden:

```php
<?php
// Automatisch pro Domain/Sprache
$bannerId = TemplateManager::get('tm_header_banner');

// Oder manuell Domain-spezifisch
$domain = rex_yrewrite::getCurrentDomain();
if ($domain && $domain->getMount() == 1) {
    $bannerId = TemplateManager::get('tm_header_banner'); // DE-Seite
} else {
    $bannerId = TemplateManager::get('tm_header_banner'); // EN-Seite
}

if (!empty($bannerId)) {
    echo UikitBannerRenderer::render((int)$bannerId);
}
?>
```

### Template Manager Field Renderer Details

Das `banner_select` Feld wird automatisch vom `uikit_banner_design` Addon registriert:

**Datei:** `boot.php`
```php
// Extension Point Registration
if (rex_addon::get('template_manager')->isAvailable()) {
    rex_extension::register('TEMPLATE_MANAGER_FIELD_RENDERERS', function($ep) {
        $renderers = $ep->getSubject();
        $renderers[] = new \UikitBannerDesign\TemplateManagerFieldRenderer();
        return $renderers;
    });
}
```

**Vorteile:**
- ✅ Saubere Trennung (kein Code im Template Manager Core)
- ✅ Automatische Integration wenn beide Addons installiert
- ✅ Keine manuelle Konfiguration nötig
- ✅ Update-sicher

**Wichtig:** Ab Template Manager 2.0 wird das `banner_select` Feld **nur noch** über diesen Extension Point verfügbar sein. Die temporäre Integration im Template Manager Core wird entfernt.

## Hintergrund-Typen

### Farbe
Einfache Volltonfarbe als Hintergrund.

### Gradient
Verlauf zwischen zwei Farben mit konfigurierbarer Richtung:
- to bottom, to top
- to left, to right
- to bottom right, to bottom left

### Bild
- Medienpool-Integration
- Gekachelt oder nicht gekachelt
- Position einstellbar (9 Optionen)

### Video
- Autoplay, Loop, Muted
- Perfekt für Hero-Banner
- Object-fit: cover

### SVG
Inline-SVG Code direkt einbinden für:
- Animationen
- Muster
- Formen

## Overlay-System

### Bild-Overlay
- PNG oder SVG aus Medienpool
- Transparenz-Unterstützung
- Größe in % einstellbar

### SVG-Overlay
- Inline-SVG Code
- Perfekt für Icons, Logos
- Volle Kontrolle

### Icon-Overlay
- UIKit Icons verwenden
- Icon-Name eingeben (z.B. "star", "heart")
- Automatische Größenanpassung

### Positionierung
9 Positionen verfügbar:
```
top-left      top-center      top-right
center-left   center          center-right
bottom-left   bottom-center   bottom-right
```

## Rahmen (Borders)

Individuelle Einstellungen für jede Seite:
- **Farbe**: Aus Theme oder custom
- **Breite**: 0-50px

Perfekt für:
- Akzent-Linien
- Marken-Rahmen
- Visuelle Trennung

## Action Buttons

Call-to-Action Buttons mit:
- **Text**: Frei wählbar
- **Link**: Interne oder externe URLs
- **Stil**: 
  - `default` - Standard grau
  - `primary` - Theme Primary-Farbe
  - `secondary` - Theme Secondary-Farbe
  - `danger` - Rot
  - `text` - Nur Text ohne Hintergrund

## Höhen

4 vordefinierte Höhen:
- **Klein**: 300px
- **Mittel**: 500px (Standard)
- **Groß**: 700px
- **Vollbild**: 100vh

## Theme-Integration

Wenn ein UIKit Theme ausgewählt ist:
- Farben aus Theme nutzen
- Icons aus Theme verwenden
- Konsistentes Design

```php
<?php
// Theme-Farben in Border verwenden
$banner = UikitBannerRenderer::render(1);
// Border-Farbe wird automatisch aus Theme geladen
?>
```

## Best Practices

### Performance

**Video-Hintergründe:**
- Max. 10-15 Sekunden
- Komprimiert (H.264)
- Max. 5MB Dateigröße

**Bilder:**
- WebP oder JPEG
- Max. 2000px Breite
- Komprimiert für Web

### Accessibility

**Immer beachten:**
- Kontrast zwischen Hintergrund und Text
- Alternative Texte für Overlay-Bilder
- Button-Texte aussagekräftig

### Responsive Design

UIKit-Klassen nutzen:
```php
$content = '
<h1 class="uk-heading-hero uk-text-center">
    <span class="uk-visible@m">Desktop Heading</span>
    <span class="uk-hidden@m">Mobile Heading</span>
</h1>
';
```

## Beispiele

### Hero-Banner mit Gradient

```php
<?php
// Banner #1: Gradient von Blau zu Lila
// Mit Overlay-Icon "star"
// Action Button "Mehr erfahren"

$content = '
<div class="uk-text-center">
    <h1 class="uk-heading-hero uk-margin-remove">
        Willkommen bei REDAXO
    </h1>
    <p class="uk-text-lead">
        Das flexible Content Management System
    </p>
</div>
';

echo UikitBannerRenderer::render(1, $content);
?>
```

### Video-Hintergrund Banner

```php
<?php
// Banner #2: Video-Hintergrund
// Mit halbtransparentem Overlay
// Centered Content

$content = '
<div class="uk-light uk-text-center" style="max-width: 600px;">
    <h2 class="uk-h1">Innovation trifft Design</h2>
    <p class="uk-text-large">
        Entdecken Sie neue Möglichkeiten
    </p>
</div>
';

echo UikitBannerRenderer::render(2, $content);
?>
```

### Einfacher Call-to-Action

```php
<?php
// Banner #3: Einfarbig mit Action Button
// Border oben & unten in Theme-Farbe

echo UikitBannerRenderer::render(3);
// Button-Text und Link direkt im Banner konfiguriert
?>
```

## API-Referenz

### UikitBannerRenderer::render()

```php
/**
 * @param int $bannerId Banner-ID aus Datenbank
 * @param string $content HTML-Content (optional)
 * @return string Gerenderte Banner-HTML
 */
UikitBannerRenderer::render(int $bannerId, string $content = ''): string
```

### UikitBannerRenderer::renderBanner()

```php
/**
 * @param array $banner Banner-Daten Array
 * @param string $content HTML-Content (optional)
 * @return string Gerenderte Banner-HTML
 */
UikitBannerRenderer::renderBanner(array $banner, string $content = ''): string
```

## Datenbank-Struktur

Tabelle: `rex_uikit_banner_designs`

| Feld | Typ | Beschreibung |
|------|-----|--------------|
| id | int | Primary Key |
| name | varchar(191) | Banner-Name |
| theme_id | int | UIKit Theme ID |
| height | varchar(50) | Höhe (small/medium/large/fullscreen) |
| bg_type | varchar(50) | Hintergrund-Typ |
| bg_color | varchar(7) | Hintergrundfarbe (#hex) |
| bg_gradient_* | varchar | Gradient-Einstellungen |
| bg_image | varchar(191) | Hintergrundbild |
| bg_video | varchar(191) | Hintergrundvideo |
| bg_svg | text | Hintergrund-SVG |
| border_*_color | varchar(7) | Rahmenfarben |
| border_*_width | int | Rahmenbreiten |
| overlay_type | varchar(50) | Overlay-Typ |
| overlay_* | text | Overlay-Einstellungen |
| action_button_* | varchar | Button-Einstellungen |

## Anforderungen

- **REDAXO**: >= 5.17
- **PHP**: >= 8.0
- **UIKit Theme Builder**: >= 1.0

## Lizenz

MIT License

## Credits

Entwickelt von KLXM
