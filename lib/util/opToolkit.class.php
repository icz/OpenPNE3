<?php

/**
 * This file is part of the OpenPNE package.
 * (c) OpenPNE Project (http://www.openpne.jp/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file and the NOTICE file that were distributed with this source code.
 */

/**
 * opToolkit provides basic utility methods for OpenPNE.
 *
 * @package    OpenPNE
 * @subpackage util
 * @author     Kousuke Ebihara <ebihara@tejimaya.com>
 */
class opToolkit
{
 /**
  * Returns the list of mobile e-mail address domains.
  *
  * @return array
  */
  public static function getMobileMailAddressDomains()
  {
    return (array)include(sfContext::getInstance()->getConfigCache()->checkConfig('config/mobile_mail_domain.yml'));
  }

  /**
   * Checks if a string is a mobile e-mail address.
   *
   * @param string
   *
   * @return bool true if $string is valid mobile e-mail address and false otherwise.
   */
  public static function isMobileEmailAddress($string)
  {
    $pieces = explode('@', $string, 2);
    $domain = array_pop($pieces);
    
    return in_array($domain, self::getMobileMailAddressDomains());
  }

 /**
  * Takes a text that matched pattern and replaces it to a marker.
  *
  * Replaces text that matched $pattern to a marker.
  * This method returns replaced text and a correspondence table of marker and pre-convert text
  *
  * @param string $subject
  * @param array  $patterns
  *
  * @return array
  */
  public static function replacePatternsToMarker($subject, $patterns = array())
  {
    $i = 0;
    $list = array();

    if (empty($patterns))
    {
      $patterns = array(
        '/<input[^>]+>/is',
        '/<textarea.*?<\/textarea>/is',
        '/<option.*?<\/option>/is',
        '/<img[^>]+>/is',
        '/<head.*?<\/head>/is',
      );
    }

    foreach ($patterns as $pattern)
    {
      if (preg_match_all($pattern, $subject, $matches))
      {
        foreach ($matches[0] as $match)
        {
          $replacement = '<<<MARKER:'.$i.'>>>';
          $list[$replacement] = $match;
          $i++;
        }
      }
    }

    $subject = str_replace(array_values($list), array_keys($list), $subject);
    return array($list, $subject);
  }

  public static function isEnabledRegistration($mode = '')
  {
    $registration = opConfig::get('enable_registration');
    if ($registration == 3)
    {
      return true;
    }

    if (!$mode && $registration)
    {
      return true;
    }

    if ($mode == 'mobile' && $registration == 1)
    {
      return true;
    }

    if ($mode == 'pc' && $registration == 2)
    {
      return true;
    }

    return false;
  }

 /**
  * Unifys EOL characters in the string.
  *
  * @param string $string
  * @param string $eol
  *
  * @return string
  */
  public static function unifyEOLCharacter($string, $eol = "\n")
  {
    $eols = array("\r\n", "\r", "\n");
    if (!in_array($eol, $eols))
    {
      return $string;
    }

    // first, unifys to LF
    $string = str_replace("\r\n", "\n", $string);
    $string = str_replace("\r", "\n", $string);

    // second, unifys to specify EOL character
    if ($eol !== "\n")
    {
      $string = str_replace("\n", $eol, $string);
    }

    return $string;
  }

  function extractEnclosedStrings($string, $enclosure = '"')
  {
    $result = array('base' => $string, 'enclosed' => array());
    $enclosureCount = substr_count($string, $enclosure);

    for ($i = 0; $i < $enclosureCount; $i++)
    {
      $begin = strpos($string, $enclosure);
      $finish = strpos($string, $enclosure, $begin + 1);
      if ($begin !== false && $finish !== false)
      {
        $head = substr($string, 0, $begin - 1);
        $body = substr($string, $begin + 1, $finish - $begin - 1);
        $foot = substr($string, $finish + 1);

        $string = $head.$foot;
        $result['enclosed'][] = $body;
        $i++;
      }
    }

    $result['base'] = $string;
    return $result;
  }
}