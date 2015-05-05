<?php

class PluginOrderMenu extends CommonGLPI {

   public static function getTypeName($nb = 0) {
      return __("Orders management", "order");
   }

   static function getMenuContent() {
      global $CFG_GLPI;

      $menu          = array();
      $menu['title'] = self::getTypeName(2);
      $menu['page']  = self::getSearchURL(false);

      $menu['options']['order']['title']                = PluginOrderOrder::getTypeName(2);
      $menu['options']['order']['page']                 = PluginOrderOrder::getSearchURL(false);
      $menu['options']['order']['links']['add']         = "/front/setup.templates.php?itemtype=PluginOrderOrder&add=1";
      $menu['options']['order']['links']['search']      = PluginOrderOrder::getSearchURL(false);
      $menu['options']['order']['links']['template']    = "/front/setup.templates.php?itemtype=PluginOrderOrder&add=0";
      $menu['options']['order']['links']['config']      = PluginOrderConfig::getFormURL(false);

      $menu['options']['bill']['title']                 = PluginOrderBill::getTypeName(2);
      $menu['options']['bill']['page']                  = PluginOrderBill::getSearchURL(false);
      $menu['options']['bill']['links']['search']       = PluginOrderBill::getSearchURL(false);
      $menu['options']['bill']['links']['add']          = PluginOrderBill::getFormURL(false);
      $menu['options']['bill']['links']['config']       = PluginOrderConfig::getFormURL(false);

      $menu['options']['references']['title']           = PluginOrderReference::getTypeName(2);
      $menu['options']['references']['page']            = PluginOrderReference::getSearchURL(false);
      $menu['options']['references']['links']['search'] = PluginOrderReference::getSearchURL(false);
      $menu['options']['references']['links']['add']    = PluginOrderReference::getFormURL(false);
      $menu['options']['references']['links']['config'] = PluginOrderConfig::getFormURL(false);

      return $menu;
   }

}
