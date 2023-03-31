<?php

namespace Drupal\dagplejelager_fixtures\Fixture;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\user\Entity\User;

/**
 * User fixture.
 *
 * @package Drupal\dagplejelager_fixtures\Fixture
 */
class UserFixture extends AbstractFixture implements FixtureGroupInterface {

  /**
   * {@inheritdoc}
   */
  public function load() {
    $user = User::create([
      'name' => 'administrator',
      'mail' => 'administrator@example.com',
      'pass' => 'administrator-password',
      // Active.
      'status' => 1,
      'roles' => [
        'administrator',
      ],
    ]);
    $user->save();
    $this->setReference('user:administrator', $user);

    $user = User::create([
      'name' => 'manager',
      'mail' => 'manager@example.com',
      'pass' => 'manager-password',
      // Active.
      'status' => 1,
      'roles' => [
        'manager',
      ],
    ]);
    $user->save();
    $this->setReference('user:manager', $user);

    $user = User::create([
      'name' => 'manager1',
      'mail' => 'manager1@example.com',
      'pass' => 'manager1-password',
      // Active.
      'status' => 1,
      'roles' => [
        'manager',
      ],
    ]);
    $user->save();
    $this->setReference('user:manager1', $user);

    $user = User::create([
      'name' => 'user',
      'mail' => 'user@example.com',
      'pass' => 'user-password',
      // Active.
      'status' => 1,
      'roles' => [],
    ]);
    $user->save();
    $this->setReference('user:user', $user);
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups() {
    return ['dagplejelager'];
  }

}
