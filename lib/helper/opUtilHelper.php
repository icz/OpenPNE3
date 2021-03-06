<?php

/**
 * This file is part of the OpenPNE package.
 * (c) OpenPNE Project (http://www.openpne.jp/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file and the NOTICE file that were distributed with this source code.
 */

/**
 * opUtilHelper provides basic utility helper functions.
 *
 * @package    OpenPNE
 * @subpackage helper
 * @author     Kousuke Ebihara <ebihara@tejimaya.com>
 * @author     Rimpei Ogawa <ogawa@tejimaya.com>
 */

/**
 * Creates a <a> link tag for the pager link
 *
 * @param string  $name
 * @param string  $internal_uri
 * @param integer $page_no
 * @param options $options
 * @return string
 */
function op_link_to_for_pager($name, $internal_uri, $page_no, $options)
{
  $html_options = _parse_attributes($options);

  $html_options = _convert_options_to_javascript($html_options);

  $absolute = false;
  if (isset($html_options['absolute_url']))
  {
    $absolute = (boolean) $html_options['absolute'];
    unset($html_options['absolute_url']);
  }
  
  $internal_uri = sprintf($internal_uri, $page_no);
  $html_options['href'] = url_for($internal_uri, $absolute);

  if (isset($html_options['query_string']))
  {
    if (strpos($html_options['href'], '?') !== false)
    {
      $html_options['href'] .= '&'.$html_options['query_string'];
    }
    else
    {
      $html_options['href'] .= '?'.$html_options['query_string'];
    }
    unset($html_options['query_string']);
  }

  if (!strlen($name))
  {
    $name = $html_options['href'];
  }
 
  return content_tag('a', $name, $html_options);
}

/**
 * Includes a navigation for paginated list
 *
 * @param sfPager $pager
 * @param string  $internal_uri
 * @param array   $options
 */
function op_include_pager_navigation($pager, $internal_uri, $options = array())
{
  $uri = url_for($internal_uri);

  if (isset($options['use_current_query_string']) && $options['use_current_query_string'])
  {
    $options['query_string'] = sfContext::getInstance()->getRequest()->getCurrentQueryString();
    $pageFieldName = isset($options['page_field_name']) ? $options['page_field_name'] : 'page';
    $options['query_string'] = preg_replace('/'.$pageFieldName.'=\d\&*/', '', $options['query_string']);
    unset($options['page_field_name']);
    unset($options['use_current_query_string']);
  }

  if (isset($options['query_string']))
  {
    $options['link_options']['query_string'] = $options['query_string'];
    unset($options['query_string']);
  }

  $params = array(
    'pager' => $pager,
    'internalUri' => $internal_uri,
    'options' => new opPartsOptionHolder($options)
  );
  $pager = sfOutputEscaper::unescape($pager);
  if ($pager instanceof sfReversibleDoctrinePager)
  {
    include_partial('global/pagerReversibleNavigation', $params);
  }
  else
  {
    include_partial('global/pagerNavigation', $params);
  }
}

/**
 * Includes pager total
 *
 * @param sfPager $pager
 */
function op_include_pager_total($pager)
{
  include_partial('global/pagerTotal', array('pager' => $pager));
}

/**
 * Returns a navigation for paginated list.
 *
 * @deprecated since 3.0.3
 * @param  sfPager $pager
 * @param  string  $link_to  A path to go to next/previous page.
                             "%d" will be converted to number of page.
 * @return string  A navigation for paginated list.
 */
function pager_navigation($pager, $link_to, $is_total = true, $query_string = '')
{
  $params = array(
    'pager' => $pager,
    'link_to' => $link_to,
    'options' => new opPartsOptionHolder(array(
      'is_total' => $is_total,
      'query_string' => $query_string
    )
  ));
  return get_partial('global/pagerNavigation', $params);
}

/**
 * Returns a pager total
 *
 * @deprecated since 3.0.3
 * @param  sfPager $pager
 * @return string 
 */
function pager_total($pager)
{
  return get_partial('global/pagerTotal', array('pager' => $pager));
}

function cycle_vars($name, $items, $delimiter = ',')
{
  static $cycles = array();
  if (!isset($cycles[$name]))
  {
    $cycles[$name] = array(
      'count' => 0,
      'items' => explode($delimiter, $items),
    );
  }

  $items = $cycles[$name]['items'];

  $result = $items[$cycles[$name]['count']];
  $cycles[$name]['count']++;

  if ($cycles[$name]['count'] >= count($items))
  {
    $cycles[$name]['count'] = 0;
  }

  return $result;
}

/**
 * Returns a project URL is over an application.
 *
 * @see url_for()
 *
 * @param string $application
 *
 * @return string
 */
function app_url_for()
{
  $arguments = func_get_args();
  if (is_array($arguments[1]) || '@' == substr($arguments[1], 0, 1) || false !== strpos($arguments[1], '/'))
  {
    return call_user_func_array('_app_url_for_internal_uri', $arguments);
  }
  else
  {
    return call_user_func_array('_app_url_for_route', $arguments);
  }
}

function _app_url_for_route($application, $routeName, $params = array(), $absolute = false)
{
  $params = array_merge(array('sf_route' => $routeName), is_object($params) ? array('sf_subject' => $params) : $params);

  return _app_url_for_internal_uri($application, $params, $absolute);
}

function _app_url_for_internal_uri($application, $internal_uri, $absolute = false)
{
  // stores current states
  $current_application = sfContext::getInstance()->getConfiguration()->getApplication();
  $current_environment = sfContext::getInstance()->getConfiguration()->getEnvironment();
  $current_is_debug = sfContext::getInstance()->getConfiguration()->isDebug();
  $current_config = sfConfig::getAll();

  // computes a url
  if (sfContext::hasInstance($application))
  {
    $context = sfContext::getInstance($application);
    sfContext::switchTo($application);
  }
  else
  {
    $config = ProjectConfiguration::getApplicationConfiguration($application, $current_environment, $current_is_debug);
    $context = sfContext::createInstance($config, $application);
  }
  $is_strip_script_name = (bool)sfConfig::get('sf_no_script_name');
  $result_url = $context->getController()->genUrl($internal_uri, $absolute);

  // restores the previous states
  sfContext::switchTo($current_application);
  sfConfig::add($current_config);

  // replaces a script name
  $before_script_name = basename(sfContext::getInstance()->getRequest()->getScriptName());
  $after_script_name = _create_script_name($application, $current_environment);
  if ($is_strip_script_name)
  {
    $before_script_name = '/'.$before_script_name;
    $after_script_name = '';
  }
  return str_replace($before_script_name, $after_script_name, $result_url);
}

function _create_script_name($application, $environment)
{
  $script_name = $application;
  if ($environment !== 'prod')
  {
    $script_name .= '_'.$environment;
  }
  $script_name .= '.php';

  return $script_name;
}

function op_format_date($date, $format = 'd', $culture = null, $charset = null)
{
  use_helper('Date');

  if (!$culture)
  {
    $culture = sfContext::getInstance()->getUser()->getCulture();
  }

  switch ($format)
  {
    case 'XShortDate':
      switch ($culture)
      {
        case 'ja_JP':
          $format = 'MM/dd';
          break;
        default:
          $format = 'd';
          break;
      }
      break;
    case 'XShortDateJa':
      switch ($culture)
      {
        case 'ja_JP':
          $format = 'MM月dd日';
          break;
        default:
          $format = 'd';
          break;
      }
      break;
    case 'XDateTime':
      switch ($culture)
      {
        case 'ja_JP':
          $format = 'yyyy/MM/dd HH:mm';
          break;
        default:
          $format = 'f';
          break;
      }
      break;
    case 'XDateTimeJa':
      switch ($culture)
      {
        case 'ja_JP':
          $format = 'yyyy年MM月dd日 HH:mm';
          break;
        default:
          $format = 'f';
          break;
      }
      break;
    case 'XDateTimeJaBr':
      switch ($culture)
      {
        case 'ja_JP':
          $format = "yyyy年\nMM月dd日\nHH:mm";
          break;
        default:
          $format = 'f';
          break;
      }
      break;
    case 'XCalendarMonth':
      switch ($culture)
      {
        case 'ja_JP':
          $format = 'yyyy年M月';
          break;
        default:
          $format = 'MMMM yyyy';
          break;
      }
      break;
  }

  return format_date($date, $format, $culture, $charset);
}

function op_url_cmd($text)
{
  $url_pattern = '/https?:\/\/([a-zA-Z0-9\-.]+)\/?(?:[a-zA-Z0-9_\-\/.,:;~?@=+$%#!()]|&amp;)*/';

  return preg_replace_callback($url_pattern, '_op_url_cmd', $text);
}

if (!defined('SF_AUTO_LINK_RE'))
{
  define('SF_AUTO_LINK_RE', '~
    (                       # leading text
      <\w+.*?>|             #   leading HTML tag, or
      [^=!:\'"/]|           #   leading punctuation, or
      ^                     #   beginning of line
    )
    (
      (?:https?://)|        # protocol spec, or
      (?:www\.)             # www.*
    )
    (
      [-\w]+                   # subdomain or domain
      (?:\.[-\w]+)*            # remaining subdomains or domain
      (?::\d+)?                # port
      \/?
      [a-zA-Z0-9_\-\/.,:;\~\?@&=+$%#!()]*
    )
    ([[:punct:]]|\s|<|$)    # trailing text
   ~x');
}

function _op_url_cmd($matches)
{
  $url = $matches[0];
  $cmd = $matches[1];

  $file = $cmd . '.js';
  $path = './cmd/' . $file;

  if (!is_readable($path)) {
    return str_replace('&', '&amp;', op_auto_link_text(str_replace('&amp;', '&', $url)));
  }

  sfContext::getInstance()->getResponse()->addJavascript('util');

  $public_path = _compute_public_path($file, 'cmd', 'js');
  $result = <<<EOD
<script type="text/javascript" src="{$public_path}"></script>
<script type="text/javascript">
<!--
url2cmd('{$url}');
//-->
</script>
EOD;
  return $result;
}

/**
 * @see auto_link_text
 */
function op_auto_link_text($text, $link = 'urls', $href_options = array('target' => '_blank'), $truncate = true, $truncate_len = 57, $pad = '...')
{
  use_helper('Text');
  return auto_link_text($text, $link, $href_options, $truncate, $truncate_len, $pad);
}

/**
 * truncates a string
 *
 * @param string $string
 * @param int    $width
 * @param string $etc
 * @param int    $rows
 * @param bool   $is_html
 *
 * @return string
 */
function op_truncate($string, $width = 80, $etc = '', $rows = 1, $is_html = true)
{
  $rows = (int)$rows;
  if (!($rows > 0))
  {
    $rows = 1;
  }

  // converts special chars
  $trans = array(
    "\r\n" => ' ',
    "\r"   => ' ',
    "\n"   => ' ',
  );

  // converts special chars (for HTML)
  if ($is_html)
  {
    $trans += array(
      // for htmlspecialchars
      '&amp;'  => '&',
      '&lt;'   => '<',
      '&gt;'   => '>',
      '&quot;' => '"',
      '&#039;' => "'",
      // for IE's bug
      '　'     => ' ',
    );
  }
  $string = strtr($string, $trans);

  $result = array();
  $p_string = $string;
  for ($i = 1; $i <= $rows; $i++)
  {
    if ($i === $rows)
    {
      $p_etc = $etc;
    }
    else
    {
      $p_etc = '';
    }

    if ($i > 0)
    {
      // strips the string of pre-line
      if (isset($result[$i - 1]))
      {
        $p_string = substr($p_string, strlen($result[$i - 1]));
      }
      if (!$p_string && ($p_string !== '0'))
      {
        break;
      }
    }

    $result[$i] = op_truncate_callback($p_string, $width, $p_etc);
  }
  $string = implode("\n", $result);

  if ($is_html)
  {
    $string = htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
  }

  return nl2br($string);
}

function op_truncate_callback($string, $width, $etc = '')
{
  $width = $width - mb_strwidth($etc);

  if (mb_strwidth($string) > $width)
  {
    // for Emoji
    $offset = 0;
    $tmp_string = $string;
    while (preg_match('/\[[ies]:[0-9]{1,3}\]/', $tmp_string, $matches, PREG_OFFSET_CAPTURE))
    {
      $emoji_str = $matches[0][0];
      $emoji_pos = $matches[0][1] + $offset;
      $emoji_len = strlen($emoji_str);
      $emoji_width = $emoji_len;

      // a width by Emoji
      $substr_width = mb_strwidth(substr($string, 0, $emoji_pos));

      if ($substr_width >= $width)  // Emoji position is after a width
      {
        break;
      }

      if ($substr_width + 2 == $width)  // substr_width + Emoji width is equal to a width
      {
        $width = $substr_width + $emoji_width;
        break;
      }

      if ($substr_width + 2 > $width)  // substr_width + Emoji width is rather than a width
      {
        $width = $substr_width;
        break;
      }

      // less than a width
      $offset = $emoji_pos + $emoji_len;
      $width = $width + $emoji_width - 2;

      $tmp_string = substr($string, $offset);
    }

    $string = mb_strimwidth($string, 0, $width, '', 'UTF-8').$etc;
  }

  return $string;
}

function op_within_page_link($marker = '▼')
{
  static $n = 0;

  $options = array();
  if ($n)
  {
    $options['name'] = sprintf('a%d', $n);
  }
  if ($marker)
  {
    $options['href'] = sprintf('#a%d', $n+1);
  }

  $n++;

  return content_tag('a', $marker, $options);
}

function op_mail_to($route, $params = array(), $name = '', $options = array(), $default_value = array())
{
  $configuration = sfContext::getInstance()->getConfiguration();
  $configPath = '/mobile_mail_frontend/config/routing.yml';
  $files = array_merge(array(sfConfig::get('sf_apps_dir').$configPath), $configuration->globEnablePlugin('/apps'.$configPath));

  $user = sfContext::getInstance()->getUser();

  if (sfConfig::get('op_is_mail_address_contain_hash') && $user->hasCredential('SNSMember'))
  {
    $params['hash'] = $user->getMember()->getMailAddressHash();
  }

  $routing = new opMailRouting(new sfEventDispatcher());
  $config = new sfRoutingConfigHandler();
  $routes = $config->evaluate($files);

  $routing->setRoutes(array_merge(sfContext::getInstance()->getRouting()->getRoutes(), $routes));

  return mail_to($routing->generate($route, $params), $name, $options, $default_value);
}

function op_banner($name)
{
  $banner = Doctrine::getTable('Banner')->findByName($name);
  if (!$banner)
  {
    return false;
  }

  if ($banner->getIsUseHtml())
  {
    return $banner->getHtml();
  }

  $bannerImage = $banner->getRandomImage();
  if (!$bannerImage)
  {
    return false;
  }
  $imgHtml = image_tag_sf_image($bannerImage->getFile(), array('alt' => $bannerImage->getName()));
  if ($bannerImage->getUrl() != '')
  {
    return link_to($imgHtml, $bannerImage->getUrl(), array('target' => '_blank'));
  }

  return $imgHtml;
}

function op_have_privilege($privilege, $member_id = null, $route = null)
{
  if (!$member_id)
  {
    $member_id = sfContext::getInstance()->getUser()->getMemberId();
  }

  if (!$route)
  {
    $route = sfContext::getInstance()->getRequest()->getAttribute('sf_route');
  }

  return $route->getAcl()->isAllowed($member_id, null, $privilege);
}

function op_have_privilege_by_uri($uri, $params = array(), $member_id = null)
{
  $routing = sfContext::getInstance()->getRouting();
  $routes = $routing->getRoutes();

  if (empty($routes[$uri]))
  {
    return true;
  }

  $route = clone $routes[$uri];
  if ($route instanceof opDynamicAclRoute)
  {
    $route->bind(sfContext::getInstance(), $params);
    try
    {
      $route->getObject();
    }
    catch (sfError404Exception $e)
    {
      // do nothing
    }
    $options = $route->getOptions();
    return op_have_privilege($options['privilege'], $member_id, $route);
  }

  return true;
}

function op_decoration($string, $is_strip = false, $is_use_stylesheet = null)
{
  if (is_null($is_use_stylesheet))
  {
    $is_use_stylesheet = true;
    if (sfConfig::get('sf_app') == 'mobile_frontend')
    {
      $is_use_stylesheet = false;
    }
  }

  return opWidgetFormRichTextareaOpenPNE::toHtml($string, $is_strip, $is_use_stylesheet);
}
