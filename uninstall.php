<?php

/**
 * Deinstallation
 */

$sql = rex_sql::factory();
$sql->setQuery('DROP TABLE IF EXISTS `' . rex::getTable('uikit_banner_designs') . '`');
