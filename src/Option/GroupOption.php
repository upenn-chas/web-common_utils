<?php

namespace Drupal\common_utils\Option;

use Drupal\Core\Access\AccessResultForbidden;
use Drupal\Core\Session\AccountInterface;
use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupMembership;

/**
 * Get list of groups for current user.
 */
class GroupOption
{
    /**
     * Logged in user.
     * 
     * @var \Drupal\Core\Session\AccountInterface
     */
    protected $user;

    public function __construct(AccountInterface $user)
    {
        $this->user = $user;
    }
    /**
     * Get the list of groups.
     * 
     * @param string  $groupType
     *  Type of group to return,
     * 
     * @param bool $allGroupsForNonMember
     *  Return all groups for non member user.
     * 
     * @return array []
     *  Key value pair of group id and group label
     */
    public function options(string $groupType = 'house1', bool $allGroupsForNonMember = true)
    {
        $options = [];

        if (!$this->user->isAuthenticated()) {
            return [];
        }

        $groupMemberships = GroupMembership::loadByUser($this->user);
        if ($groupMemberships) {
            foreach ($groupMemberships as $groupMembership) {
                $gid = $groupMembership->get('gid')->getString();
                $group = Group::load($gid);
                $options[$group->id()] = $group->label();
            }
        } else if ($allGroupsForNonMember) {
            $groupsId =  \Drupal::entityQuery('group')
                ->condition('type', $groupType)
                ->condition('status', 1)->accessCheck(true)->execute();
            $groups = Group::loadMultiple($groupsId);

            foreach ($groups as $group) {
                $options[$group->id()] = $group->label();
            }
        }
        return $options;
    }

    public function getUserGroupsWithPermission(string $permission, string $groupType = 'house1')
    {
        $options = [];

        if (!$this->user->isAuthenticated()) {
            return [];
        }
        $groupMemberships = GroupMembership::loadByUser($this->user);
        if ($groupMemberships) {
            foreach ($groupMemberships as $groupMembership) {
                $gid = $groupMembership->get('gid')->getString();
                $group = Group::load($gid);
                if ($group->hasPermission($permission, $this->user)) {
                    $options[$group->id()] = $group->label();
                }
            }
        } else if ($this->user->hasPermission($permission)) {
            $groupsId =  \Drupal::entityQuery('group')
                ->condition('type', $groupType)
                ->condition('status', 1)->accessCheck(true)->execute();
            $groups = Group::loadMultiple($groupsId);

            foreach ($groups as $group) {
                $options[$group->id()] = $group->label();
            }
        }

        return $options;
    }
}
