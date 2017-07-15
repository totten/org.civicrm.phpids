<?php

/**
 * Implements hook_civicrm_config().
 */
function phpids_civicrm_config(&$config) {
  if (isset(Civi::$statics[__FUNCTION__])) {
    return;
  }
  Civi::$statics[__FUNCTION__] = 1;
  Civi::dispatcher()->addListener('hook_civicrm_ids',
    array('\Civi\IDS\IDSListener', 'onIDS'));
}
