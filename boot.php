<?php

/**
 * UIKit Banner Design
 * 
 * @author KLXM
 */

// Template Manager Field Renderer registrieren
if (rex_addon::get('template_manager')->isAvailable()) {
    rex_extension::register('TEMPLATE_MANAGER_FIELD_RENDERERS', function(rex_extension_point $ep) {
        $renderers = $ep->getSubject();
        $renderers[] = new \UikitBannerDesign\TemplateManagerFieldRenderer();
        return $renderers;
    });
}
