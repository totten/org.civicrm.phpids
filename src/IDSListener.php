<?php
namespace Civi\IDS;

class IDSListener {

  /**
   * @param \Civi\Core\Event\GenericHookEvent $e
   * @see CRM_Utils_Hook::ids
   */
  public static function onIDS(\Civi\Core\Event\GenericHookEvent $e) {
    $e->stopPropagation();

    $config = \CRM_Core_IDS::createStandardConfig();
    foreach (array('json', 'html', 'exception') as $section) {
      if (isset($e->route['ids_arguments'][$section])) {
        foreach ($e->route['ids_arguments'][$section] as $v) {
          $config['General'][$section][] = $v;
        }
        $config['General'][$section] = array_unique($config['General'][$section]);
      }
    }

    self::run($e->route['path'], $config);
  }


  /**
   * @param string $path
   * @param array $config
   *   PHPIDS configuration array (per INI format).
   */
  protected static function run($path, $config) {
    $init = self::create($config);

    $crmIDS = new \CRM_Core_IDS($path);

    // Add request url and user agent.
    // Unlike upstream, we don't modify the global $_REQUEST.
    $theRequest = $_REQUEST;
    $theRequest['IDS_request_uri'] = $_SERVER['REQUEST_URI'];
    if (isset($_SERVER['HTTP_USER_AGENT'])) {
      $theRequest['IDS_user_agent'] = $_SERVER['HTTP_USER_AGENT'];
    }

    require_once 'IDS/Monitor.php';
    $ids = new \IDS_Monitor($theRequest, $init);

    $result = $ids->run();
    if (!$result->isEmpty()) {
      $crmIDS->react($result);
    }
  }

  /**
   * Create a new PHPIDS configuration object.
   *
   * @param array $config
   *   PHPIDS configuration array (per INI format).
   * @return \IDS_Init
   */
  protected static function create($config) {
    require_once 'IDS/Init.php';
    $init = \IDS_Init::init(NULL);
    $init->setConfig($config, TRUE);

    // Cleanup
    $reflection = new \ReflectionProperty('IDS_Init', 'instances');
    $reflection->setAccessible(TRUE);
    $value = $reflection->getValue(NULL);
    unset($value[NULL]);
    $reflection->setValue(NULL, $value);

    return $init;
  }

}
