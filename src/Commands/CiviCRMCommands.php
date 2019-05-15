<?php

namespace Drupal\drush_civicrm\Commands;

use CRM_Core_BAO_UFMatch;
use Drush\Commands\DrushCommands;
use Drupal\user\Entity\User;


class CiviCRMCommands extends DrushCommands {

  /**
   * CLI access to CiviCRM APIs. It can return pretty-printor json formatted
   * data.
   *
   * @param array $commands
   *
   * @command civicrm:api
   * @aliases cvapi
   * @option  uid Drupal uid that is used to check the privileges (type is number, default=1)
   * @option  in  Use arguments (standard, value args) or as json from the standard input JSON (not implemented yet)
   * @option  out
   * @usage drush civicrm:api contact.create first_name=John last_name=Doe
   *   contact_type=Individual
   *
   */

  public function api(array $commands, $options = [
    'uid' => 1,
    'in' => 'args',
    'out' => 'json',
  ]) {
    $DEFAULTS = ['version' => 3];
    $args = $commands;
    list($entity, $action) = explode('.', $args[0]);
    array_shift($args);
    \Drupal::service('civicrm')->initialize();
    $user = User::load($options['uid']);
    CRM_Core_BAO_UFMatch::synchronize($user, FALSE, 'Drupal', 'Individual');
    $params = $DEFAULTS;
    foreach ($args as $arg) {
      $matches = explode('=', $arg);
      $params[$matches[0]] = $matches[1];
    }
    $result = civicrm_api($entity, $action, $params);
    return json_encode($result, JSON_PRETTY_PRINT);
  }
}