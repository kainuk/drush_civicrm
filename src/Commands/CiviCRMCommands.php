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

    if(!in_array($options['out'],['json','pretty'])){
      throw new \RuntimeException("Unknown option --out={$options['out']}, must be json or pretty");
    }
    if(!in_array($options['in'],['json','args'])){
      throw new \RuntimeException("Unknown option --in={$options['in']}, must be args or pretty");
    }

    $DEFAULTS = ['version' => 3];
    $args = $commands;
    list($entity, $action) = explode('.', $args[0]);
    array_shift($args);
    \Drupal::service('civicrm')->initialize();
    $user = User::load($options['uid']);
    CRM_Core_BAO_UFMatch::synchronize($user, FALSE, 'Drupal', 'Individual');
    $params = $DEFAULTS;

    if($options['in']=='json'){
      $json = stream_get_contents(STDIN);
      if (empty($json)) {
        $params = $DEFAULTS;
      }
      else {
        $params = array_merge($DEFAULTS, json_decode($json, TRUE));
      }
    } else
      {

      foreach ($args as $arg) {
        $matches = explode('=', $arg);
        $params[$matches[0]] = $matches[1];
      }
    }
    $result = civicrm_api($entity, $action, $params);

    return $options['out']=='pretty'?print_r($result,true):json_encode($result, JSON_PRETTY_PRINT);
  }
}