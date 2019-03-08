<?php
namespace Drupal\localist_pull\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\localist_pull\LocalistInterface;

/**
 * Defines the LocalistPull entity.
 *
 * @ConfigEntityType(
 *   id = "localist_pull",
 *   label = @Translation("LocalistConfig"),
 *   handlers = {
 *     "list_builder" = "Drupal\localist_pull\Controller\LocalistListBuilder",
 *     "form" = {
 *       "add" = "Drupal\localist_pull\Form\LocalistEntityForm",
 *       "edit" = "Drupal\localist_pull\Form\LocalistEntityForm",
 *       "delete" = "Drupal\localist_pull\Form\LocalistDeleteForm",
 *     }
 *   },
 *   config_prefix = "localist_pull",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
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
}
