<?php
/*
 * @version $Id: HEADER 2011-03-23 15:41:26 tsmr $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2010 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 --------------------------------------------------------------------------
// ----------------------------------------------------------------------
// Original Authors of file: 
// NOUH Walid & FONTAN Benjamin & CAILLAUD Xavier & François Legastelois
// Purpose of file: plugin order v1.4.0 - GLPI 0.80
// ----------------------------------------------------------------------
// ---------------------------------------------------------------------- */

if (!defined('GLPI_ROOT')){
   die("Sorry. You can't access directly to this file");
}

class PluginOrderProfile extends CommonDBTM {
   
   static function getTypeName() {
      global $LANG;

      return $LANG['plugin_order']['profile'][0];
   }
   
   function canCreate() {
      return haveRight('profile', 'w');
   }

   function canView() {
      return haveRight('profile', 'r');
   }
   
   //if profile deleted
   static function purgeProfiles(Profile $prof) {
      $plugprof = new self();
      $plugprof->deleteByCriteria(array('profiles_id' => $prof->getField("id")));
   }
   
   function getFromDBByProfile($profiles_id) {
      global $DB;
      
      $query = "SELECT * FROM `".$this->getTable()."`
               WHERE `profiles_id` = '" . $profiles_id . "' ";
      if ($result = $DB->query($query)) {
         if ($DB->numrows($result) != 1) {
            return false;
         }
         $this->fields = $DB->fetch_assoc($result);
         if (is_array($this->fields) && count($this->fields)) {
            return true;
         } else {
            return false;
         }
      }
      return false;
   }
  
   static function createFirstAccess($ID) {
      
      $myProf = new self();
      if (!$myProf->getFromDBByProfile($ID)) {

         $myProf->add(array('profiles_id' => $ID, 'order' => 'w', 'reference'=>'w',
                            'validation'=>'w', 'cancel'=>'w', 'undo_validation'=>'w', 'bill' => 'w'));
            
      }
   }

   static function addRightToProfile($profiles_id, $right , $value = '') {
      $myProf = new self();
      if ($myProf->getFromDBByProfile($profiles_id)) {
         $tmp = $myProf->fields;
         $tmp[$right] = $value;
         $myProf->update($tmp);
      }   
   }
   
   function createAccess($ID) {

      $this->add(array('profiles_id' => $ID));
   }
   
   static function changeProfile() {
      $prof = new self();
      if ($prof->getFromDBByProfile($_SESSION['glpiactiveprofile']['id'])) {
         $_SESSION["glpi_plugin_order_profile"] = $prof->fields;
      } else {
         unset($_SESSION["glpi_plugin_order_profile"]);
      }
   }

   /* profiles modification */
   function showForm ($ID, $options=array()) {
      global $LANG;

      if (!haveRight("profile","r")) {
         return false;
      }
      
      $prof = new Profile();
      if ($ID) {
         $this->getFromDBByProfile($ID);
         $prof->getFromDB($ID);
      }

      $this->showFormHeader($options);

      echo "<tr class='tab_bg_2'>";
      
      echo "<th colspan='4' align='center'><strong>" . 
         $LANG['plugin_order']['profile'][0] . " " . $prof->fields["name"] . "</strong></th>";
      
      echo "</tr>";  
      echo "<tr class='tab_bg_2'>";
      
      echo "<td>" . $LANG['plugin_order']['menu'][1] . ":</td><td>";
      if ($prof->fields['interface']!='helpdesk') {
         Profile::dropdownNoneReadWrite("order",$this->fields["order"], 1, 1, 1);
      } else {
         echo $LANG['profiles'][12]; // No access;
      }
      echo "</td>";

      echo "<td>" . $LANG['plugin_order']['menu'][2] . ":</td><td>";
      if ($prof->fields['interface']!='helpdesk') {
         Profile::dropdownNoneReadWrite("reference",$this->fields["reference"], 1, 1, 1);
      } else {
         echo $LANG['profiles'][12]; // No access;
      }
      echo "</td>";
      
      echo "</tr>";
      echo "<tr class='tab_bg_2'>";

      echo "<td>" . $LANG['plugin_order']['menu'][6] . ":</td><td>";
      if ($prof->fields['interface']!='helpdesk') {
         Profile::dropdownNoneReadWrite("bill",$this->fields["bill"], 1, 1, 1);
      } else {
         echo $LANG['profiles'][12]; // No access;
      }
      echo "</td>";
      echo "<td colspan='2'></td>";
      
      echo "</tr>";
      
      echo "<tr align='center'><th colspan='4' >".$LANG['plugin_order'][5]."</th></tr>";
      
      echo "<tr class='tab_bg_2'>";
      
      echo "<td>" . $LANG['plugin_order']['profile'][1] . ":</td><td>";
      if ($prof->fields['interface']!='helpdesk') {
         Profile::dropdownNoneReadWrite("validation",$this->fields["validation"],1,0,1);
      } else {
         echo $LANG['profiles'][12]; // No access;
      }
      echo "</td>";
      
      echo "<td>" . $LANG['plugin_order']['profile'][2] . ":</td><td>";
      if ($prof->fields['interface']!='helpdesk') {
         Profile::dropdownNoneReadWrite("cancel",$this->fields["cancel"],1,0,1);
      } else {
         echo $LANG['profiles'][12]; // No access;
      }
      echo "</td>";
      
      echo "</tr>";
      echo "<tr class='tab_bg_2'>";
      
      echo "<td>" . $LANG['plugin_order']['profile'][3] . ":</td><td>";
      if ($prof->fields['interface']!='helpdesk') {
         Profile::dropdownNoneReadWrite("undo_validation",$this->fields["undo_validation"],1,0,1);
      } else {
         echo $LANG['profiles'][12]; // No access;
      }
      echo "</td>";
      
      echo "<td colspan='2'></td>";
      
      echo "</tr>";

      echo "<input type='hidden' name='id' value=".$this->fields["id"].">";
      
      $options['candel'] = false;
      $this->showFormButtons($options);
   }
   
  static function install(Migration $migration) {
      global $DB;
      
      $table = getTableForItemType(__CLASS__);
      if (!TableExists($table)) {
         $query = "CREATE TABLE `glpi_plugin_order_profiles` (
               `id` int(11) NOT NULL auto_increment,
               `profiles_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_profiles (id)',
               `order` char(1) collate utf8_unicode_ci default NULL,
               `reference` char(1) collate utf8_unicode_ci default NULL,
               `validation` char(1) collate utf8_unicode_ci default NULL,
               `cancel` char(1) collate utf8_unicode_ci default NULL,
               `undo_validation` char(1) collate utf8_unicode_ci default NULL,
               `bill` char(1) collate utf8_unicode_ci default NULL,
               PRIMARY KEY  (`id`),
               KEY `profiles_id` (`profiles_id`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
         $DB->query($query) or die($DB->error());
         PluginOrderProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);

      } else {

         //1.2.0
         $migration->dropField("glpi_plugin_order_profiles", "name");

         $migration->changeField($table, "ID", "id", "int(11) NOT NULL auto_increment");
         foreach (array('order', 'reference', 'budget', 'validation', 'cancel', 'undo_validation') as $right) {
            $migration->changeField($table, $right, $right, 
                                    "char(1) collate utf8_unicode_ci default NULL");
         }
         $migration->addField($table, "profiles_id", "int(11) NOT NULL default '0'");
         $migration->addKey($table, "profiles_id");
         
         //1.4.0
         $migration->dropField($table, "budget");
         
         //1.5.0
         $migration->addField("glpi_plugin_order_profiles", "bill", 
                              "CHAR( 1 ) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->migrationOneTable($table);
         PluginOrderProfile::addRightToProfile($_SESSION['glpiactiveprofile']['id'], "bill" , "w");
      }
   }
   
   static function uninstall() {
      global $DB;
      
      //Current table name
      $DB->query("DROP TABLE IF EXISTS  `".getTableForItemType(__CLASS__)."`") or die ($DB->error());
   }
}

?>