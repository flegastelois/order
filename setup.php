<?php
/*
 * @version $Id: bill.tabs.php 530 2011-06-30 11:30:17Z walid $
 LICENSE

 This file is part of the order plugin.

 Order plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Order plugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; along with Order. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   order
 @author    the order plugin team
 @copyright Copyright (c) 2010-2015 Order plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://forge.indepnet.net/projects/order
 @link      http://www.glpi-project.org/
 @since     2009
 ---------------------------------------------------------------------- */

if (!defined('PLUGIN_ORDER_TEMPLATE_DIR')) {
   define ("PLUGIN_ORDER_TEMPLATE_DIR",  GLPI_ROOT."/plugins/order/templates/");
}
if (!defined('PLUGIN_ORDER_SIGNATURE_DIR')) {
   define ("PLUGIN_ORDER_SIGNATURE_DIR", GLPI_ROOT."/plugins/order/signatures/");
}
if (!defined('PLUGIN_ORDER_TEMPLATE_CUSTOM_DIR')) {
   define ("PLUGIN_ORDER_TEMPLATE_CUSTOM_DIR",  GLPI_ROOT."/plugins/order/generate/");
}
if (!defined('PLUGIN_ORDER_TEMPLATE_LOGO_DIR')) {
   define ("PLUGIN_ORDER_TEMPLATE_LOGO_DIR",  GLPI_ROOT."/plugins/order/logo/");
}

if (!defined('PLUGIN_ORDER_TEMPLATE_EXTENSION')) {
   define ("PLUGIN_ORDER_TEMPLATE_EXTENSION", "odt");
}
if (!defined('PLUGIN_ORDER_SIGNATURE_EXTENSION')) {
   define ("PLUGIN_ORDER_SIGNATURE_EXTENSION", "png");
}

/* init the hooks of the plugins -needed- */
function plugin_init_order() {
   global $PLUGIN_HOOKS, $CFG_GLPI, $ORDER_TYPES;

   Plugin::registerClass('PluginOrderProfile');
   $PLUGIN_HOOKS['csrf_compliant']['order'] = true;

   /* load changeprofile function */
   $PLUGIN_HOOKS['change_profile']['order'] = array('PluginOrderProfile', 'changeProfile');

   $plugin = new Plugin();
   if ($plugin->isInstalled('order')) {
      $PLUGIN_HOOKS['migratetypes']['order'] = 'plugin_order_migratetypes';
   }

   if ($plugin->isInstalled('order') && $plugin->isActivated('order')) {
      $PLUGIN_HOOKS['assign_to_ticket']['order'] = true;

      //Itemtypes in use for an order
      $ORDER_TYPES = array(
         'Computer',
         'Monitor',
         'NetworkEquipment',
         'Peripheral',
         'Printer',
         'Phone',
         'ConsumableItem',
         'CartridgeItem',
         'Contract',
         'PluginOrderOther',
         'SoftwareLicense',
      );

      $PLUGIN_HOOKS['pre_item_purge']['order'] = array(
         'Profile'  => array('PluginOrderProfile', 'purgeProfiles'),
      );
      $PLUGIN_HOOKS['pre_item_update']['order'] = array(
         'Infocom'  => array('PluginOrderOrder_Item', 'updateItem'),
         'Contract' => array('PluginOrderOrder_Item', 'updateItem'),
      );
      $PLUGIN_HOOKS['item_add']['order'] = array(
         'Document' => array('PluginOrderOrder', 'addDocumentCategory')
      );

      include_once(GLPI_ROOT . "/plugins/order/inc/order_item.class.php");
      foreach (PluginOrderOrder_Item::getClasses(true) as $type) {
         $PLUGIN_HOOKS['item_purge']['order'][$type] = 'plugin_item_purge_order';
      }

      Plugin::registerClass('PluginOrderOrder', array(
         'document_types'              => true,
         'unicity_types'               => true,
         'notificationtemplates_types' => true,
         'helpdesk_visible_types'      => true,
         'ticket_types'                => true,
         'contract_types'              => true,
         'linkuser_types'              => true,
         'addtabon'                    => array('Budget'))
      );

      Plugin::registerClass('PluginOrderReference', array('document_types' => true));
      Plugin::registerClass('PluginOrderProfile', array('addtabon' => array('Profile')));
      Plugin::registerClass('PluginOrderOrder_Item', array(
         'notificationtemplates_types' => true,
         'addtabon'                    => PluginOrderOrder_Item::getClasses(true))
      );

      if (PluginOrderOrder::canView()) {
         Plugin::registerClass('PluginOrderOrder_Supplier', array('addtabon' => array('Supplier')));
         Plugin::registerClass('PluginOrderPreference', array('addtabon' => array('Preference')));
      }

      /*if glpi is loaded */
      if (Session::getLoginUserID()) {

         /* link to the config page in plugins menu */
         if (Session::haveRight("config", UPDATE)) {
            $PLUGIN_HOOKS['config_page']['order'] = 'front/config.form.php';
         }

         if (PluginOrderOrder::canView()
               || PluginOrderReference::canView()
               || PluginOrderBill::canView()) {

            $PLUGIN_HOOKS['menu_toadd']['order']['management'] = 'PluginOrderMenu';

         }

         if (PluginOrderOrder::canCreate()) {
            //order
            $PLUGIN_HOOKS['submenu_entry']['order']['options']['order']['links']['template']
               = '/front/setup.templates.php?itemtype=PluginOrderOrder&add=0';
            $PLUGIN_HOOKS['submenu_entry']['order']['options']['order']['links']['add']
               = '/front/setup.templates.php?itemtype=PluginOrderOrder&add=1';
            if (Session::haveRight('config', UPDATE)) {
               $PLUGIN_HOOKS['submenu_entry']['order']['options']['order']['links']['config']
                  = '/plugins/order/front/config.form.php';
            }

         }

         if (PluginOrderBill::canCreate()) {
            //order
            $PLUGIN_HOOKS['submenu_entry']['order']['options']['PluginOrderBill']['links']['add']
               = '/plugins/order/front/bill.form.php';

         }

         if (PluginOrderReference::canCreate()) {
            //references
            $PLUGIN_HOOKS['submenu_entry']['order']['options']['reference']['links']['add']
               = '/plugins/order/front/reference.form.php';
           if (Session::haveRight('config', UPDATE)) {
               $PLUGIN_HOOKS['submenu_entry']['order']['options']['reference']['links']['config']
                  = '/plugins/order/front/config.form.php';
           }
         }
         if (Session::haveRight("config", UPDATE)) {
            $PLUGIN_HOOKS['submenu_entry']['order']['options']['config']['title'] = __("Setup");
            $PLUGIN_HOOKS['submenu_entry']['order']['options']['config']['page']
               = '/plugins/order/front/config.form.php';
            if (Session::haveRight('config', UPDATE)) {
               $PLUGIN_HOOKS['submenu_entry']['order']['config'] = 'front/config.form.php';
            }
         }
         $PLUGIN_HOOKS['use_massive_action']['order'] = 1;
         $PLUGIN_HOOKS['plugin_datainjection_populate']['order'] = "plugin_datainjection_populate_order";
      }
   }
}

/* get the name and the version of the plugin - needed- */
function plugin_version_order() {
   return array ('name'           => __("Orders management", "order"),
                 'version'        => '2.0',
                 'author'         => 'The plugin order team',
                 'homepage'       => 'https://forge.indepnet.net/projects/show/order',
                 'minGlpiVersion' => '0.85',
                 'license'        => 'GPLv2+');
}

/* check prerequisites before install : may print errors or add to message after redirect -optional- */
function plugin_order_check_prerequisites(){
   if (version_compare(GLPI_VERSION,'0.85','lt') || version_compare(GLPI_VERSION,'0.86','ge')) {
      echo "This plugin requires GLPI 0.85";
   } else {
      return true;
   }
}

function plugin_order_check_config() {
   return true;
}

