<?php
namespace Drupal\cwd_events_localist_pull\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\cwd_events_localist_pull\LocalistInterface;

/**
 * Defines the LocalistPull entity.
 *
 * @ConfigEntityType(
 *   id = "localist_pull",
 *   label = @Translation("LocalistConfig"),
 *   handlers = {
 *     "list_builder" = "Drupal\cwd_events_localist_pull\Controller\LocalistListBuilder",
 *     "form" = {
 *       "add" = "Drupal\cwd_events_localist_pull\Form\LocalistEntityForm",
 *       "edit" = "Drupal\cwd_events_localist_pull\Form\LocalistEntityForm",
 *       "delete" = "Drupal\cwd_events_localist_pull\Form\LocalistDeleteForm",
 *     }
 *   },
 *   config_prefix = "localist_pull",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "url",
 *     "localist_departments",
 *     "localist_keywords",
 *     "localist_count",
 *     "localist_date",
 *     "localist_relative_date",
 *     "event_machine_name",
 *     "localist_id_field_name",
 *     "localist_url_field_name",
 *     "localist_location_field_name",
 *     "localist_date_field_name",
 *     "localist_end_date_field_name",
 *     "localist_description_field_name",
 *     "localist_image_field_name",
 *     "localist_tag_field_name",
 *     "localist_department_taxonomy",
 *     "localist_department_lookup_field",
 *     "localist_event_type_taxonomy",
 *     "localist_event_type_field_name",
 *     "update_events_bool",
 *     "publish_events_bool",
 *     "pull_specified_departments",
 *     "extra_parameters",
 *   },
 *   links = {
 *     "edit-form" = "/admin/config/system/localist_pull/{localist_pull}",
 *     "delete-form" = "/admin/config/system/localist_pull/{localist_pull}/delete",
 *   }
 * )
 */
class LocalistConfig extends ConfigEntityBase implements LocalistInterface {

  /**
   * The localist_pull entity ID.
   *
   * @var string
   */
  public $id;

  /**
   * The localist_pull entity label.
   *
   * @var string
   */
  public $label;

  /**
   * The localist_pull entity url.
   *
   * @var string
   */
  public $url;

  /**
  * The localist_pull entity url.
  *
  * @var string
  */
  public $localist_departments;
  /**
  * The localist_pull entity url.
  *
  * @var string
  */
  public $localist_keywords;
  /**
  * The localist_pull entity url.
  *
  * @var string
  */
  public $localist_count;
  /**
  * The localist_pull entity url.
  *
  * @var string
  */
  public $localist_date;
  /**
  * The localist_pull entity url.
  *
  * @var string
  */
  public $event_machine_name;
  /**
  * The localist_pull entity url.
  *
  * @var string
  */
  public $localist_id_field_name;
  /**
  * The localist_pull entity url.
  *
  * @var string
  */
  public $localist_url_field_name;
  /**
  * The localist_pull entity url.
  *
  * @var string
  */
  public $localist_location_field_name;
  /**
  * The localist_pull entity url.
  *
  * @var string
  */
  public $localist_date_field_name;
  /**
  * The localist_pull entity url.
  *
  * @var string
  */
  public $localist_end_date_field_name;
  /**
  * The localist_pull entity url.
  *
  * @var string
  */
  public $localist_description_field_name;
  /**
  * The localist_pull entity url.
  *
  * @var string
  */
  public $localist_image_field_name;

  /**
  * The localist_pull entity url.
  *
  * @var boolean
  */
  public $update_events_bool;
  /**
  * The localist_pull entity url.
  *
  * @var boolean
  */
  public $publish_events_bool;

  /**
  * The localist_pull entity url.
  *
  * @var boolean
  */
  public $localist_tag_field_name;
  /**
  * The localist_pull entity url.
  *
  * @var boolean
  */
  public $localist_department_taxonomy;
  /**
  * The localist_pull entity url.
  *
  * @var boolean
  */
  public $localist_department_lookup_field;
  /**
  * The localist_pull entity url.
  *
  * @var boolean
  */
  public $pull_specified_departments;
  /**
  * The localist_pull entity url.
  *
  * @var boolean
  */
  public $localist_relative_date;
  /**
  * The localist_pull entity url.
  *
  * @var boolean
  */
  public $extra_parameters;
  /**
  * The localist_pull entity url.
  *
  * @var boolean
  */
  public $localist_event_type_taxonomy;
  /**
  * The localist_pull entity url.
  *
  * @var boolean
  */
  public $localist_event_type_field_name;

}
