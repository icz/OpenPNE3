<dl>
<dt>ニックネーム</dt>
<dd><?php echo $member->getName() ?></dd>
<?php foreach ($member->getProfiles() as $profile) : ?>
<dt><?php echo $profile->getCaption() ?></dt>
<dd><?php echo $profile ?></dd>
<?php endforeach; ?>
</dl>

<ul>
<li><?php echo link_to(sprintf('フレンド一覧(%d)', $member->countFriendsRelatedByMemberIdTo()), 'friend/list?member_id=' . $member->getId()) ?></li>
<li><?php echo link_to(sprintf('参加コミュニティ一覧(%d)', $member->countCommunityMembers()), 'community/joinlist?member_id=' . $member->getId()) ?></li>
</ul>

<ul>
<?php if ($sf_user->getMemberId() != $member->getId()) : ?>
<?php if (FriendPeer::isFriend($sf_user->getMemberId(), $member->getId())): ?>
<li><?php echo link_to('フレンドをやめる', 'friend/unlink?id=' . $member->getId()) ?></li>
<?php else: ?>
<li><?php echo link_to('フレンドになる', 'friend/link?id=' . $member->getId()) ?></li>
<?php endif; ?>
<?php else : ?>
<li><?php echo link_to('プロフィール編集', 'member/editProfile') ?></li>
<li><?php echo link_to('ホームに戻る', 'member/home') ?></li>
<?php endif; ?>
</ul>