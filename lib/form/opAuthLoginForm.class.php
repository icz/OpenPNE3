<?php

/**
 * This file is part of the OpenPNE package.
 * (c) OpenPNE Project (http://www.openpne.jp/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file and the NOTICE file that were distributed with this source code.
 */

/**
 * opAuthLoginForm represents a form to login.
 *
 * @package    OpenPNE
 * @subpackage form
 * @author     Kousuke Ebihara <ebihara@tejimaya.com>
 */
abstract class opAuthLoginForm extends sfForm
{
  protected
    $adapter = null;

  const AUTH_MODE_FIELD_NAME = 'authMode';

  /**
   * Constructor.
   *
   * @param opAuthAdapter $adapter    An opAuthAdapter object
   * @param array         $defaults   An array of field default values
   * @param array         $options    An array of options
   *
   * @see sfForm
   */
  public function __construct(opAuthAdapter $adapter, $defaults = array(), $options = array())
  {
    $this->adapter = $adapter;

    parent::__construct($defaults, $options, false);

    $this->setWidget('next_uri', new opWidgetFormInputHiddenNextUri());
    $this->setValidator('next_uri', new opValidatorNextUri());

    $this->widgetSchema->setNameFormat('auth'.$this->adapter->getAuthModeName().'[%s]');
  }

  /**
   * Returns the name of current authMode.
   *
   * @return string
   */
  public function getAuthMode()
  {
    return $this->adapter->getAuthModeName();
  }

  /**
   * Returns the current authentication adapter
   *
   * @return string
   */
  public function getAuthAdapter()
  {
    return $this->adapter;
  }

  /**
   * Returns the logined member.
   *
   * @return Member
   */
  public function getMember()
  {
    $member = $this->getValue('member');
    if ($member instanceof Member)
    {
      return $member;
    }

    return false;
  }

 /**
  * @todo removes this method.
  */
  public function isUtn()
  {
    return false;
  }
}
