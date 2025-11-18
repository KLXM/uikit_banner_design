<?php

/**
 * UIKit Banner Design Renderer
 */

class UikitBannerRenderer
{
    /**
     * Rendert ein Banner-Design
     *
     * @param int $bannerId Banner-ID
     * @param string $content Optionaler Content im Banner
     * @return string HTML-Output
     */
    public static function render(int $bannerId, string $content = ''): string
    {
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT * FROM ' . rex::getTable('uikit_banner_designs') . ' WHERE id = ?', [$bannerId]);
        
        if (!$sql->getRows()) {
            return '<!-- Banner #' . $bannerId . ' nicht gefunden -->';
        }
        
        $bannerData = $sql->getArray();
        if (!isset($bannerData[0]) || !is_array($bannerData[0])) {
            return '<!-- Banner #' . $bannerId . ' - Fehlerhafte Daten -->';
        }
        
        return self::renderBanner($bannerData[0], $content);
    }
    
    /**
     * Rendert Banner aus Array-Daten
     *
     * @param array $banner Banner-Daten
     * @param string $content Optionaler Content
     * @return string HTML-Output
     */
    public static function renderBanner(array $banner, string $content = ''): string
    {
        // Höhe direkt verwenden (unterstützt jetzt vh und px)
        $height = $banner['height'] ?: '50vh';
        
        // Falls die Höhe ein Preset ist, in CSS-Wert umwandeln
        $heightPresets = [
            'small' => '30vh',
            'medium' => '50vh',
            'large' => '70vh',
            'fullscreen' => '100vh'
        ];
        
        if (isset($heightPresets[$height])) {
            $height = $heightPresets[$height];
        }
        
        // Container Styles
        $styles = [
            'position: relative',
            'height: ' . $height,
            'overflow: hidden',
            'display: flex',
            'align-items: center',
            'justify-content: center'
        ];
        
        // Borders
        if ($banner['border_top_width']) {
            $styles[] = 'border-top: ' . $banner['border_top_width'] . 'px solid ' . ($banner['border_top_color'] ?: '#000');
        }
        if ($banner['border_bottom_width']) {
            $styles[] = 'border-bottom: ' . $banner['border_bottom_width'] . 'px solid ' . ($banner['border_bottom_color'] ?: '#000');
        }
        if ($banner['border_left_width']) {
            $styles[] = 'border-left: ' . $banner['border_left_width'] . 'px solid ' . ($banner['border_left_color'] ?: '#000');
        }
        if ($banner['border_right_width']) {
            $styles[] = 'border-right: ' . $banner['border_right_width'] . 'px solid ' . ($banner['border_right_color'] ?: '#000');
        }
        
        $html = '<div class="uikit-banner" style="' . implode('; ', $styles) . '">';
        
        // Background Layer
        $html .= self::renderBackground($banner);
        
        // Overlay Layer
        if ($banner['overlay_type'] && $banner['overlay_type'] !== 'none') {
            $html .= self::renderOverlay($banner);
        }
        
        // Content Layer
        $html .= '<div class="uk-position-center uk-text-center uk-light" style="z-index: 2;">';
        
        if ($content) {
            $html .= $content;
        }
        
        $html .= '</div>';
        
        // Action Button mit Position
        if ($banner['action_button_text'] && $banner['action_button_link']) {
            $position = $banner['action_button_position'] ?: 'bottom center';
            
            // Position zu UIKit-Klasse konvertieren
            $positionMap = [
                'top left' => 'uk-position-top-left',
                'top center' => 'uk-position-top-center',
                'top right' => 'uk-position-top-right',
                'center left' => 'uk-position-center-left',
                'center center' => 'uk-position-center',
                'center right' => 'uk-position-center-right',
                'bottom left' => 'uk-position-bottom-left',
                'bottom center' => 'uk-position-bottom-center',
                'bottom right' => 'uk-position-bottom-right',
            ];
            
            $posClass = $positionMap[$position] ?? 'uk-position-bottom-center';
            
            // Styles mit 1rem Abstand zu den Rändern
            $styles = ['z-index: 3'];
            $parts = explode(' ', $position);
            
            if (in_array('top', $parts)) {
                $styles[] = 'top: 1rem';
            }
            if (in_array('bottom', $parts)) {
                $styles[] = 'bottom: 1rem';
            }
            if (in_array('left', $parts)) {
                $styles[] = 'left: 1rem';
            }
            if (in_array('right', $parts)) {
                $styles[] = 'right: 1rem';
            }
            
            $html .= '<div class="' . $posClass . '" style="' . implode('; ', $styles) . '">';
            $html .= '<a href="' . rex_escape($banner['action_button_link']) . '" class="uk-button uk-button-' . rex_escape($banner['action_button_style']) . ' uk-button-large">';
            $html .= rex_escape($banner['action_button_text']);
            $html .= '</a>';
            $html .= '</div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Rendert den Hintergrund
     */
    private static function renderBackground(array $banner): string
    {
        $bgStyles = [
            'position: absolute',
            'top: 0',
            'left: 0',
            'width: 100%',
            'height: 100%',
            'z-index: 0'
        ];
        
        switch ($banner['bg_type']) {
            case 'color':
                $bgStyles[] = 'background-color: ' . ($banner['bg_color'] ?: '#f8f8f8');
                return '<div style="' . implode('; ', $bgStyles) . '"></div>';
                
            case 'gradient':
                $start = $banner['bg_gradient_start'] ?: '#667eea';
                $end = $banner['bg_gradient_end'] ?: '#764ba2';
                $direction = $banner['bg_gradient_direction'] ?: 'to bottom';
                $bgStyles[] = 'background: linear-gradient(' . $direction . ', ' . $start . ', ' . $end . ')';
                return '<div style="' . implode('; ', $bgStyles) . '"></div>';
                
            case 'image':
                if ($banner['bg_image']) {
                    $bgStyles[] = 'background-image: url(' . rex_url::media($banner['bg_image']) . ')';
                    $bgStyles[] = 'background-size: ' . ($banner['bg_image_repeat'] ? 'auto' : 'cover');
                    $bgStyles[] = 'background-position: ' . ($banner['bg_image_position'] ?: 'center center');
                    $bgStyles[] = 'background-repeat: ' . ($banner['bg_image_repeat'] ? 'repeat' : 'no-repeat');
                }
                return '<div style="' . implode('; ', $bgStyles) . '"></div>';
                
            case 'video':
                if ($banner['bg_video']) {
                    return '<video autoplay loop muted playsinline style="' . implode('; ', $bgStyles) . '; object-fit: cover;">' .
                           '<source src="' . rex_url::media($banner['bg_video']) . '">' .
                           '</video>';
                }
                return '<div style="' . implode('; ', $bgStyles) . '; background-color: #000;"></div>';
                
            case 'svg':
                if ($banner['bg_svg']) {
                    return '<div style="' . implode('; ', $bgStyles) . '">' . $banner['bg_svg'] . '</div>';
                }
                return '<div style="' . implode('; ', $bgStyles) . '"></div>';
        }
        
        return '<div style="' . implode('; ', $bgStyles) . '"></div>';
    }
    
    /**
     * Rendert das Overlay
     */
    private static function renderOverlay(array $banner): string
    {
        $position = $banner['overlay_position'] ?: 'center center';
        $size = $banner['overlay_size'] ?: 50;
        
        // Position zu CSS-Klasse konvertieren
        $positionMap = [
            'top left' => 'uk-position-top-left',
            'top center' => 'uk-position-top-center',
            'top right' => 'uk-position-top-right',
            'center left' => 'uk-position-center-left',
            'center center' => 'uk-position-center',
            'center right' => 'uk-position-center-right',
            'bottom left' => 'uk-position-bottom-left',
            'bottom center' => 'uk-position-bottom-center',
            'bottom right' => 'uk-position-bottom-right',
        ];
        
        $posClass = $positionMap[$position] ?? 'uk-position-center';
        
        // Styles zusammenbauen
        $styles = [
            'z-index: 1',
            'width: ' . $size . '%',
            'max-width: ' . $size . '%'
        ];
        
        // Optional: Padding zum Rand
        if (!empty($banner['overlay_padding'])) {
            // Position-spezifisches Padding
            $parts = explode(' ', $position);
            if (in_array('top', $parts)) {
                $styles[] = 'top: ' . $banner['overlay_padding'];
            }
            if (in_array('bottom', $parts)) {
                $styles[] = 'bottom: ' . $banner['overlay_padding'];
            }
            if (in_array('left', $parts)) {
                $styles[] = 'left: ' . $banner['overlay_padding'];
            }
            if (in_array('right', $parts)) {
                $styles[] = 'right: ' . $banner['overlay_padding'];
            }
        }
        
        // Image/SVG Styles für min-width/min-height
        $contentStyles = ['max-width: 100%', 'height: auto', 'display: block'];
        
        if (!empty($banner['overlay_min_width'])) {
            $contentStyles[] = 'min-width: ' . $banner['overlay_min_width'];
        }
        if (!empty($banner['overlay_min_height'])) {
            $contentStyles[] = 'min-height: ' . $banner['overlay_min_height'];
        }
        
        $contentStyleString = implode('; ', $contentStyles);
        
        $html = '<div class="' . $posClass . '" style="' . implode('; ', $styles) . '">';
        
        switch ($banner['overlay_type']) {
            case 'image':
                if ($banner['overlay_image']) {
                    $html .= '<img src="' . rex_url::media($banner['overlay_image']) . '" alt="" style="' . $contentStyleString . '">';
                }
                break;
                
            case 'svg':
                if ($banner['overlay_svg']) {
                    $html .= '<div style="' . $contentStyleString . '">' . $banner['overlay_svg'] . '</div>';
                }
                break;
                
            case 'icon':
                if ($banner['overlay_icon']) {
                    $html .= '<span uk-icon="icon: ' . rex_escape($banner['overlay_icon']) . '; ratio: 5"></span>';
                }
                break;
                
            case 'text':
                // Text aus dem Textfeld oder von einem Artikel holen
                $text = '';
                if (!empty($banner['overlay_text'])) {
                    $text = $banner['overlay_text'];
                } elseif (!empty($banner['overlay_text_article_id'])) {
                    $article = rex_article::get($banner['overlay_text_article_id']);
                    if ($article) {
                        $text = $article->getName();
                    }
                }
                
                if ($text) {
                    $format = $banner['overlay_text_format'] ?? 'h2';
                    $animation = $banner['overlay_text_animation'] ?? '';
                    
                    // Text-Ausrichtung basierend auf Position bestimmen
                    $textAlign = 'center'; // Default
                    $parts = explode(' ', $position);
                    
                    if (in_array('left', $parts)) {
                        $textAlign = 'left';
                    } elseif (in_array('right', $parts)) {
                        $textAlign = 'right';
                    }
                    
                    // Style mit text-align erweitern
                    $textStyles = $contentStyleString . '; text-align: ' . $textAlign . '; margin: 0;';
                    
                    // Animation-Attribute hinzufügen
                    $animationAttr = '';
                    $animationClass = '';
                    if (!empty($animation)) {
                        // Verwende uk-scrollspy mit offset für sofortige Ausführung
                        $animationAttr = ' uk-scrollspy="cls: uk-animation-' . rex_escape($animation) . '; offset-top: -100; repeat: false"';
                        // Alternativ: direkte Animation ohne Scrollspy für Banner am Seitenanfang
                        // $animationClass = ' uk-animation-' . rex_escape($animation);
                    }
                    
                    // Prüfen ob es ein h1-h4 Tag oder eine UIKit-Klasse ist
                    if (in_array($format, ['h1', 'h2', 'h3', 'h4'])) {
                        // Standard HTML-Überschrift
                        $html .= '<' . $format . ' style="' . $textStyles . '"' . $animationAttr . '>' . rex_escape($text) . '</' . $format . '>';
                    } else {
                        // UIKit-Überschriftenklasse
                        $html .= '<h2 class="' . rex_escape($format) . '" style="' . $textStyles . '"' . $animationAttr . '>' . rex_escape($text) . '</h2>';
                    }
                }
                break;
        }
        
        $html .= '</div>';
        
        return $html;
    }
}
