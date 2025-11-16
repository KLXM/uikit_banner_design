<?php

namespace UikitBannerDesign;

use FriendsOfRedaxo\TemplateManager\AbstractFieldRenderer;

/**
 * Template Manager Field Renderer für Banner Select
 * 
 * Registriert den Feldtyp "banner_select" für den Template Manager
 */
class TemplateManagerFieldRenderer extends AbstractFieldRenderer
{
    public function supports(string $type): bool
    {
        return $type === 'banner_select';
    }
    
    public function render(array $setting, string $value, string $name, int $clangId): string
    {
        $html = $this->renderFormGroupStart($setting);
        
        // Banner aus Datenbank laden
        $sql = \rex_sql::factory();
        
        try {
            $banners = $sql->getArray('SELECT id, name FROM ' . \rex::getTable('uikit_banner_designs') . ' ORDER BY name ASC');
        } catch (\Exception $e) {
            $html .= '<p class="text-danger"><i class="rex-icon fa-exclamation-triangle"></i> Fehler beim Laden der Banner: ' . \rex_escape($e->getMessage()) . '</p>';
            $html .= '<input type="hidden" name="' . $name . '" value="">';
            $html .= $this->renderFormGroupEnd($setting, false);
            return $html;
        }
        
        // Eindeutige ID ohne Sonderzeichen für JavaScript
        $fieldId = 'banner_select_' . $clangId . '_' . preg_replace('/[^a-z0-9_]/i', '_', $setting['key']);
        
        $html .= '<select class="form-control" name="' . $name . '" id="' . $fieldId . '">';
        $html .= '<option value="">-- Kein Banner --</option>';
        
        foreach ($banners as $banner) {
            $selected = $value == $banner['id'] ? 'selected' : '';
            $html .= '<option value="' . (int)$banner['id'] . '" ' . $selected . '>';
            $html .= \rex_escape($banner['name']);
            $html .= '</option>';
        }
        
        $html .= '</select>';
        
        // Vorschau-Link wenn Banner ausgewählt
        if (!empty($value) && is_numeric($value)) {
            $previewUrl = \rex_url::backendPage('uikit_banner_design/preview', ['id' => (int)$value]);
            $html .= '<p class="help-block" data-preview-container>';
            $html .= '<a href="' . htmlspecialchars_decode($previewUrl) . '" target="_blank" class="btn btn-xs btn-default" style="margin-top: 5px;">';
            $html .= '<i class="rex-icon fa-eye"></i> Banner Vorschau';
            $html .= '</a>';
            $html .= '</p>';
        }
        
        // Live-Update für Vorschau-Link mit Security-Fix (json_encode)
        $html .= $this->renderScript('
            jQuery(function($) {
                var $select = $("#" + ' . json_encode($fieldId) . ');
                
                // Live-Update des Vorschau-Links
                $select.on("change", function() {
                    var bannerId = $(this).val();
                    var $container = $select.parent().find("[data-preview-container]");
                    
                    if (bannerId && bannerId !== "") {
                        var baseUrl = "' . htmlspecialchars_decode(\rex_url::backendPage('uikit_banner_design/preview')) . '";
                        var previewUrl = baseUrl + "&id=" + bannerId;
                        
                        if ($container.length === 0) {
                            $container = $("<p class=\\"help-block\\" data-preview-container></p>").insertAfter($select);
                        }
                        $container.html("<a href=\\"" + previewUrl + "\\" target=\\"_blank\\" class=\\"btn btn-xs btn-default\\" style=\\"margin-top: 5px;\\"><i class=\\"rex-icon fa-eye\\"></i> Banner Vorschau</a>");
                    } else {
                        $container.remove();
                    }
                });
            });
        ');
        
        $html .= $this->renderFormGroupEnd($setting);
        
        return $html;
    }
}
