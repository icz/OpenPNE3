<?php

/**
 * This file is part of the OpenPNE package.
 * (c) OpenPNE Project (http://www.openpne.jp/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file and the NOTICE file that were distributed with this source code.
 */

class MemberTable extends opAccessControlDoctrineTable
{
  public function createPre()
  {
    $member = new Member();
    $member->setIsActive(false);
    $member->save();

    return $member;
  }

  public function searchMemberIds($nickname, $ids = null)
  {
    if (!empty($nickname))
    {
      $_result = $this->createQuery()
        ->select('id')
        ->where('name LIKE ?', '%'.$nickname.'%')
        ->execute();
      $result = array();

      foreach ($_result as $value)
      {
        $result[] = $value->getId();
      }

      if (is_array($ids))
      {
        $ids = array_values(array_intersect($ids, $result));
      }
      else
      {
        $ids = array_values($result);
      }
    }

    return $ids;
  }

  public function retrivesByInviteMemberId($memberId)
  {
//    opActivateBehavior::disable();

    $members = $this->createQuery()
      ->where('invite_member_id = ?', $memberId)
      ->andWhere('is_active = ?', false)
      ->execute();

//    opActivateBehavior::enable();

    return $members;
  }

  public function appendRoles(Zend_Acl $acl)
  {
    return $acl
      ->addRole(new Zend_Acl_Role('everyone'))
      ->addRole(new Zend_Acl_Role('self'), 'everyone')
      ->addRole(new Zend_Acl_Role('blocked'));
  }

  public function appendRules(Zend_Acl $acl, $resource = null)
  {
    return $acl
      ->allow('everyone', $resource, 'view')
      ->allow('self', $resource, 'edit')
      ->deny('blocked');
  }
}
