<?php

require_once 'smartmenu.civix.php';

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function smartmenu_civicrm_config(&$config) {
  _smartmenu_civix_civicrm_config($config);
  // if (isset(Civi::$statics[__FILE__]['configured'])) {
  //   return;
  // }
  // Civi::$statics[__FILE__]['configured'] = TRUE;
  //
  // $path = 'packages/smartmenus-1.1.0/';
  // CRM_Core_Resources::singleton()
  //   ->addScriptFile('uk.squiffle.smartmenu', $path . 'jquery.smartmenus.js', 10, 'html-header')
  //   ->addScriptFile('uk.squiffle.smartmenu', $path . 'addons/keyboard/jquery.smartmenus.keyboard.js', 10, 'html-header')
  //
  //   ->addStyleFile('uk.squiffle.smartmenu', $path . 'css/sm-core-css.css')
  //   ->addStyleFile('uk.squiffle.smartmenu', $path . 'css/sm-blue/sm-blue.css');
  //
  // $native = FALSE;
  // if ($native) {
  //   CRM_Core_Region::instance('page-footer')->add(array(
  //     'jquery' => '$("#civicrm-menu").prop("id", "sm-mainmenu");'
  //   ));
  //   CRM_Core_Region::instance('page-footer')->add(array(
  //     'jquery' => '$("#sm-mainmenu").wrap( "<nav id=\"main-nav\"></nav)" );'
  //   ));
  //   CRM_Core_Region::instance('page-footer')->add(array(
  //     'jquery' => '$("#sm-mainmenu").addClass( "" ).removeAttr("style");'
  //   ));
  //   CRM_Core_Region::instance('page-footer')->add(array(
  //     'jquery' => '$("#sm-mainmenu").smartmenus({
  //       subMenusSubOffsetX: 1,
  //       subMenusSubOffsetY: -8
  //       });',
  //     ));
  //
  // }
  // else {
  //   define('CIVICRM_DISABLE_DEFAULT_MENU', TRUE);
  //   $navigations = CRM_Core_BAO_Navigation::buildNavigationTree();
  //   // run the Navigation  through a hook so users can modify it
  //   CRM_Utils_Hook::navigationMenu($navigations);
  //   CRM_Core_BAO_Navigation::fixNavigationMenu($navigations);
  //
  //   $menuString = _smartmenu_buildMenu($navigations, $first = TRUE);
  //   $menuString = '<nav id="main-nav">' . $menuString . '</nav>';
  //   CRM_Core_Region::instance('page-header')->add(array(
  //     'markup' => $menuString . '<pre>' . var_export($navigations, 1) . '</pre>',
  //   ));
  //
  //   CRM_Core_Region::instance('page-header')->add(array(
  //     'jquery' => '$("#sm-mainmenu").insertBefore("#wrapper");',
  //   ));
  //   CRM_Core_Region::instance('page-header')->add(array(
  //     'jquery' => '$("#sm-mainmenu").smartmenus({
  //       subMenusSubOffsetX: 1,
  //       subMenusSubOffsetY: -8
  //       });',
  //     ));
  // }
  //

}

function _smartmenu_buildMenu($navigations, $first = FALSE) {
  if ($first) {
    $str = '<ul id="sm-mainmenu" class="sm sm-blue">' . "\n";
  }
  else {
    $str = '<ul>' . "\n";
  }
  foreach ($navigations as $navigation) {
    $str .= '<li>';
    if ($navigation['attributes']['url']) {
      $str .= '<a href="' . $navigation['attributes']['url'] . '">' . $navigation['attributes']['name'] . '</a>' . "\n";
    }
    else {
      $str .= '<a href="?' . $navigation['attributes']['label'] . '">' . $navigation['attributes']['name'] . '</a>' . "\n";
      $str .= _smartmenu_buildMenu($navigation['child']);
    }
    $str .= '</li>'. "\n";
  }

  $str .= '</ul>'. "\n";
  return $str;
}

/**
 * Adds js/css for the slicknav menu
 *
 * @param $list
 * @param $region
 */
function smartmenu_civicrm_coreResourceList(&$list, $region) {
  $config = CRM_Core_Config::singleton();
  //check if logged in user has access CiviCRM permission and build menu
  $buildNavigation = !CRM_Core_Config::isUpgradeMode() && CRM_Core_Permission::check('access CiviCRM');

  $cssWeDontWant = array_search('css/civicrmNavigation.css', $list);
  // ddl($cssWeDontWant);
  unset($list[$cssWeDontWant]);
  // ddl($list);
  define('CIVICRM_DISABLE_DEFAULT_MENU', TRUE);

  if ($config->userFrameworkFrontend) {
    return;
  }
  // if (defined('CIVICRM_DISABLE_DEFAULT_MENU') || $config->userFrameworkFrontend) {
  //   $buildNavigation = FALSE;
  // }
  $buildNavigation = TRUE;
  if ($buildNavigation && $region == 'html-header') {
    $contactID = CRM_Core_Session::getLoggedInContactID();
    if ($contactID) {
      $path = 'packages/smartmenus-1.1.0/';
      CRM_Core_Resources::singleton()
        ->addScriptFile('uk.squiffle.smartmenu', $path . 'jquery.smartmenus.js', 0, 'html-header')
        ->addScriptFile('uk.squiffle.smartmenu', $path . 'addons/keyboard/jquery.smartmenus.keyboard.js', 1, 'html-header')
        ->addStyleFile('uk.squiffle.smartmenu', $path . 'css/sm-core-css.css', 10)
        ->addStyleFile('uk.squiffle.smartmenu', $path . 'css/sm-blue/sm-blue.css', 11)
        ->addStyleFile('uk.squiffle.smartmenu', 'css/sm-civicrm.css', 12);

      // These params force the browser to refresh the js file when switching user, domain, or language
      if (is_callable(array('CRM_Core_I18n', 'getLocale'))) {
        $tsLocale = CRM_Core_I18n::getLocale();
      }
      // 4.6 compatibility
      else {
        global $tsLocale;
      }
      $domain = CRM_Core_Config::domainID();
      $key = CRM_Core_BAO_Navigation::getCacheKey($contactID);
      $src = CRM_Utils_System::url("civicrm/ajax/smartmenu/$contactID/$tsLocale/$domain/$key", 1, 'html-header');
      CRM_Core_Resources::singleton()->addScriptUrl($src);
    }
  }
}


/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 * Change all 'url' keys with null values to '#'
 * Submenus need a url so they are recognised by smartmenus
 */
function smartmenu_civicrm_navigationMenu(&$params) {
  array_walk_recursive($params,
    function (&$value, $key) {
      if ($key == 'url' && !$value) {
        $value = '#';
      }
    }
  );
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function smartmenu_civicrm_xmlMenu(&$files) {
  _smartmenu_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function smartmenu_civicrm_install() {
  _smartmenu_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function smartmenu_civicrm_postInstall() {
  _smartmenu_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function smartmenu_civicrm_uninstall() {
  _smartmenu_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function smartmenu_civicrm_enable() {
  _smartmenu_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function smartmenu_civicrm_disable() {
  _smartmenu_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function smartmenu_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _smartmenu_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function smartmenu_civicrm_managed(&$entities) {
  _smartmenu_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function smartmenu_civicrm_caseTypes(&$caseTypes) {
  _smartmenu_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function smartmenu_civicrm_angularModules(&$angularModules) {
  _smartmenu_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function smartmenu_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _smartmenu_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *
function smartmenu_civicrm_preProcess($formName, &$form) {

} // */

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 *
function smartmenu_civicrm_navigationMenu(&$menu) {
  _smartmenu_civix_insert_navigation_menu($menu, NULL, array(
    'label' => ts('The Page', array('domain' => 'uk.squiffle.smartmenu')),
    'name' => 'the_page',
    'url' => 'civicrm/the-page',
    'permission' => 'access CiviReport,access CiviContribute',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _smartmenu_civix_navigationMenu($menu);
} // */
