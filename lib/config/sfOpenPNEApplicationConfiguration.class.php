<?php

/**
 * This file is part of the OpenPNE package.
 * (c) OpenPNE Project (http://www.openpne.jp/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file and the NOTICE file that were distributed with this source code.
 */

/**
 * sfOpenPNEApplicationConfiguration represents a configuration for OpenPNE application.
 *
 * @package    OpenPNE
 * @subpackage config
 * @author     Kousuke Ebihara <ebihara@tejimaya.com>
 */
abstract class sfOpenPNEApplicationConfiguration extends sfApplicationConfiguration
{
  static protected $zendLoaded = false;

  public function initialize()
  {
    mb_internal_encoding('UTF-8');

    require sfConfig::get('sf_data_dir').'/version.php';

    $this->dispatcher->connect('task.cache.clear', array($this, 'clearPluginCache'));
    $this->dispatcher->connect('template.filter_parameters', array($this, 'filterTemplateParameters'));

    $this->setConfigHandlers();
  }

  public function setup()
  {
    $DS = DIRECTORY_SEPARATOR;
    $OpenPNE2Path = sfConfig::get('sf_lib_dir').$DS.'vendor'.$DS;  // ##PROJECT_LIB_DIR##/vendor/
    set_include_path($OpenPNE2Path.PATH_SEPARATOR.get_include_path());

    $result = parent::setup();
    $configCache = $this->getConfigCache();
    $file = $configCache->checkConfig('data/config/plugin.yml', true);
    if ($file)
    {
      include($file);
    }

    if (sfConfig::get('op_plugin_activation'))
    {
      $pluginActivations = array_merge(array_fill_keys($this->getPlugins(), true), sfConfig::get('op_plugin_activation'));
      foreach ($pluginActivations as $key => $value)
      {
        if (!in_array($key, $this->getPlugins()))
        {
          unset($pluginActivations[$key]);
        }
      }
      $this->enablePlugins(array_keys($pluginActivations, true));
      $this->disablePlugins(array_keys($pluginActivations, false));
    }

    // gadget
    include($this->getConfigCache()->checkConfig('config/gadget_layout_config.yml'));
    include($this->getConfigCache()->checkConfig('config/gadget_config.yml'));

    require_once sfConfig::get('sf_lib_dir').'/config/opGadgetConfigHandler.class.php';
    $gadgetConfigs = sfConfig::get('op_gadget_config', array());
    foreach ($gadgetConfigs as $key => $config)
    {
      $filename = 'config/'.sfInflector::underscore($key);
      $params = array();
      if ($key != 'gadget')
      {
        $filename .= '_gadget';
        $params['prefix'] = sfInflector::underscore($key).'_';
      }
      $filename .= '.yml';
      $this->getConfigCache()->registerConfigHandler($filename, 'opGadgetConfigHandler', $params);
      include($this->getConfigCache()->checkConfig($filename));
    }

    return $result;
  }

  public function getAllPlugins()
  {
    return array_keys($this->getAllPluginPaths());
  }

  public function getAllOpenPNEPlugins()
  {
    $list = $this->getAllPlugins();
    $result = array();

    foreach ($list as $value)
    {
      if (!strncmp($value, 'op', 2))
      {
        $result[] = $value;
      }
    }

    return $result;
  }

  public function getEnabledAuthPlugin()
  {
    $list = $this->getPlugins();
    $result = array();

    foreach ($list as $value)
    {
      if (!strncmp($value, 'opAuth', 6))
      {
        $result[] = $value;
      }
    }

    return $result;
  }

  public function isPluginExists($pluginName)
  {
    return in_array($pluginName, $this->getAllPlugins());
  }

  public function isEnabledPlugin($pluginName)
  {
    return in_array($pluginName, $this->getPlugins());
  }

  public function isDisabledPlugin($pluginName)
  {
    return (bool)(!$this->isEnabledPlugin($pluginName) && in_array($pluginName, $this->getAllPlugins()));
  }

  public function clearPluginCache($params = array())
  {
    $appConfiguration = $params['app'];
    $environment = $params['env'];
    $subDir = sfConfig::get('sf_cache_dir').'/'.$appConfiguration->getApplication().'/'.$environment.'/plugins';

    if (is_dir($subDir))
    {
      $filesystem = new sfFilesystem();
      $filesystem->remove(sfFinder::type('any')->discard('.sf')->in($subDir));
    }
  }

  /**
   * @see sfApplicationConfiguration
   */
  public function getConfigCache()
  {
    if (is_null($this->configCache))
    {
      require_once sfConfig::get('sf_lib_dir').'/config/opConfigCache.class.php';
      $this->configCache = new opConfigCache($this);
    }

    return $this->configCache;
  }

  /**
   * Listens to the template.filter_parameters event.
   *
   * @param  sfEvent $event       An sfEvent instance
   * @param  array   $parameters  An array of template parameters to filter
   *
   * @return array   The filtered parameters array
   */
  public function filterTemplateParameters(sfEvent $event, $parameters)
  {
    $parameters['op_config']  = new opConfig();
    sfOutputEscaper::markClassAsSafe('opConfig');

    $parameters['op_color']  = new opColorConfig();

    return $parameters;
  }

  /**
   * Gets directories where controller classes are stored for a given module.
   *
   * @param string $moduleName The module name
   *
   * @return array An array of directories
   */
  public function getControllerDirs($moduleName)
  {
    $dirs = array();

    $dirs = array_merge($dirs, $this->globEnablePlugin('/apps/'.sfConfig::get('sf_app').'/modules/'.$moduleName.'/actions', true));
    $dirs = array_merge($dirs, parent::getControllerDirs($moduleName));

    return $dirs;
  }

  /**
   * Gets directories where template files are stored for a given module.
   *
   * @param string $moduleName The module name
   *
   * @return array An array of directories
   */
  public function getTemplateDirs($moduleName)
  {
    $dirs = array();

    $dirs = array_merge($dirs, $this->globEnablePlugin('/apps/'.sfConfig::get('sf_app').'/templates'));
    $dirs = array_merge($dirs, $this->globEnablePlugin('/apps/'.sfConfig::get('sf_app').'/modules/'.$moduleName.'/templates'));
    $dirs = array_merge($dirs, parent::getTemplateDirs($moduleName));

    return $dirs;
  }

  /**
   * Gets the decorator directories.
   *
   * @return array  An array of the decorator directories
   */
  public function getDecoratorDirs()
  {
    $dirs = array();

    $dirs = array_merge($dirs, $this->globEnablePlugin('/apps/'.sfConfig::get('sf_app').'/templates'));
    $dirs = array_merge($dirs, parent::getDecoratorDirs());

    return $dirs;
  }

  /**
   * Gets the i18n directories to use for a given module.
   *
   * @param string $moduleName The module name
   *
   * @return array An array of i18n directories
   */
  public function getI18NDirs($moduleName)
  {
    $dirs = array();

    $dirs = array_merge($dirs, $this->globEnablePlugin('/apps/'.sfConfig::get('sf_app').'/i18n'));
    $dirs = array_merge($dirs, $this->globEnablePlugin('/apps/'.sfConfig::get('sf_app').'/modules/'.$moduleName.'/i18n'));
    $dirs = array_merge($dirs, parent::getI18NDirs($moduleName));

    return $dirs;
  }

  /**
   * Gets the configuration file paths for a given relative configuration path.
   *
   * @param string $configPath The configuration path
   *
   * @return array An array of paths
   */
  public function getConfigPaths($configPath)
  {
    $files = array();

    if ($libDirs = glob(sfConfig::get('sf_lib_dir').'/config/'.$configPath)) {
      $files = array_merge($files, $libDirs); // library configurations
    }

    $files = array_merge($files, $this->globEnablePlugin($configPath));
    $files = array_merge($files, $this->globEnablePlugin('/apps/'.sfConfig::get('sf_app').'/'.$configPath));

    $configs = array();
    foreach (array_unique($files) as $file)
    {
      if (is_readable($file))
      {
        $configs[] = $file;
      }
    }

    $configs = array_merge(parent::getConfigPaths($configPath), $configs);
    return $configs;
  }

  public function globEnablePlugin($pattern, $isControllerPath = false)
  {
    $dirs = array();
    $pluginPaths = $this->getPluginPaths();

    foreach ($pluginPaths as $pluginPath)
    {
      if ($pluginDirs = glob($pluginPath.$pattern))
      {
        if ($isControllerPath)
        {
          $dirs = array_merge($dirs, array_combine($pluginDirs, array_fill(0, count($pluginDirs), false)));
        }
        else
        {
          $dirs = array_merge($dirs, $pluginDirs);
        }
      }
    }

    return $dirs;
  }

  public function getGlobalTemplateDir($templateFile)
  {
    foreach ($this->getGlobalTemplateDirs() as $dir)
    {
      if (is_readable($dir.'/'.$templateFile))
      {
        return $dir;
      }
    }

    return null;
  }

  public function getGlobalTemplateDirs()
  {
    $dirs = array();
    $dirs[] = sfConfig::get('sf_root_dir').'/templates';
    $dirs   = array_merge($dirs, $this->getPluginSubPaths('/templates'));
    return $dirs;
  }

  protected function setConfigHandlers()
  {
    $this->getConfigCache()->registerConfigHandler('config/sns_config.yml', 'opConfigConfigHandler', array('prefix' => 'openpne_sns_'));
    include($this->getConfigCache()->checkConfig('config/sns_config.yml'));

    $this->getConfigCache()->registerConfigHandler('config/member_config.yml', 'opConfigConfigHandler', array('prefix' => 'openpne_member_'));
    include($this->getConfigCache()->checkConfig('config/member_config.yml'));

    $this->getConfigCache()->registerConfigHandler('config/community_config.yml', 'opConfigConfigHandler', array('prefix' => 'openpne_community_'));
    include($this->getConfigCache()->checkConfig('config/community_config.yml'));

    $this->getConfigCache()->registerConfigHandler('config/community_notification.yml', 'opConfigConfigHandler', array('prefix' => 'openpne_community_notification_'));
    include($this->getConfigCache()->checkConfig('config/community_notification.yml'));
  }

  static public function registerZend()
  {
    if (self::$zendLoaded)
    {
      return true;
    }

    $DS = DIRECTORY_SEPARATOR;  // Alias
    $zendPath = sfConfig::get('sf_lib_dir').$DS.'vendor'.$DS;  // ##PROJECT_LIB_DIR##/vendor/

    set_include_path($zendPath.PATH_SEPARATOR.get_include_path());
    require_once 'Zend/Loader.php';
    Zend_Loader::registerAutoLoad();
    self::$zendLoaded = true;
  }

  static public function unregisterZend()
  {
    if (!self::$zendLoaded)
    {
      return true;
    }

    require_once 'Zend/Loader.php';
    Zend_Loader::registerAutoLoad('Zend_Loader', false);
    self::$zendLoaded = false;
  }

  static public function registerJanRainOpenID()
  {
    $DS = DIRECTORY_SEPARATOR;
    $openidPath = sfConfig::get('sf_lib_dir').$DS.'vendor'.$DS.'php-openid'.$DS;  // ##PROJECT_LIB_DIR##/vendor/php-openid/
    set_include_path($openidPath.PATH_SEPARATOR.get_include_path());

    require_once 'Auth/OpenID/Consumer.php';
    require_once 'Auth/OpenID/FileStore.php';
    require_once 'Auth/OpenID/SReg.php';
  }
}
