<?php
namespace Drupal\localist_pull\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form handler for the localist_pull entity add and edit forms.
 */
class LocalistEntityForm extends EntityForm {

  /**
   * Constructs an localist_pull entityForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entityTypeManager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $localist_pull = $this->entity;
    // kint($localist_pull);
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $localist_pull->label(),
      '#description' => $this->t("Label for the localist_pull entity."),
      '#required' => TRUE,
    ];
    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $localist_pull->id(),
      '#machine_name' => [
        'exists' => [$this, 'exist'],
      ],
      '#disabled' => !$localist_pull->isNew(),
    ];
    $form['url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('URL'),
      '#maxlength' => 255,
      '#default_value' => $localist_pull->url,
      '#description' => $this->t("URL for localist."),
      '#required' => TRUE,
    ];

    $form['localist_departments'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Deparment ID\'s'),
      '#default_value' => $localist_pull->localist_departments,
      '#size' => 20,
      '#maxlength' => 255,
      '#description' => $this->t('Enter the deparment ids you wish to use to pull back events from the Cornell Calendar. Seperate department id\'s with commas.'),
      '#required' => FALSE,
    ];
    $form['localist_keywords'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Keywords'),
      '#default_value' => $localist_pull->localist_keywords,
      '#size' => 20,
      '#maxlength' => 255,
      '#description' => $this->t('Enter the keyword you wish to use to pull back events from the Cornell Calendar. Seperate keywords with commas.'),
      '#required' => FALSE,
    ];

    $form['localist_count'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Count'),
      '#default_value' => $localist_pull->localist_count,
      '#size' => 20,
      '#maxlength' => 255,
      '#description' => $this->t('Enter the number events from the Cornell Calendar.'),
      '#required' => FALSE,
    ];

    $form['localist_date'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Date'),
      '#default_value' => $localist_pull->localist_date,
      '#size' => 20,
      '#maxlength' => 255,
      '#description' => $this->t('Enter the start date that you want begin pulling from the Cornell Calendar. Format: YYYY-MM-DD'),
      '#required' => FALSE,
    ];
    $form['event_machine_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Event Machine Name'),
      '#default_value' => $localist_pull->event_machine_name,
      '#size' => 20,
      '#maxlength' => 255,
      '#description' => $this->t('Machine name of the content type that localist will feed into'),
      '#required' => FALSE,
    ];
    $form['localist_id_field_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Localist ID field'),
      '#default_value' => $localist_pull->localist_id_field_name,
      '#size' => 20,
      '#maxlength' => 255,
      '#description' => $this->t('Mapping: field machine name for the localist event id'),
      '#required' => FALSE,
    ];
    $form['localist_url_field_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Localist URL field'),
      '#default_value' => $localist_pull->localist_url_field_name,
      '#size' => 20,
      '#maxlength' => 255,
      '#description' => $this->t('Mapping: field machine name for the localist event URL'),
      '#required' => FALSE,
    ];
    $form['localist_location_field_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Localist event location field'),
      '#default_value' => $localist_pull->localist_location_field_name,
      '#size' => 20,
      '#maxlength' => 255,
      '#description' => $this->t('Mapping: field machine name for the localist event location'),
      '#required' => FALSE,
    ];
    $form['localist_date_field_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Localist event date/time field'),
      '#default_value' => $localist_pull->localist_date_field_name,
      '#size' => 20,
      '#maxlength' => 255,
      '#description' => $this->t('Mapping: field machine name for the localist event date and time'),
      '#required' => FALSE,
    ];
    $form['localist_end_date_field_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Localist event end date/time field'),
      '#default_value' => $localist_pull->localist_end_date_field_name,
      '#size' => 20,
      '#maxlength' => 255,
      '#description' => $this->t('Mapping: field machine name for the localist event end date and time'),
      '#required' => FALSE,
    ];
    $form['localist_description_field_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Localist event description field'),
      '#default_value' => $localist_pull->localist_description_field_name,
      '#size' => 20,
      '#maxlength' => 255,
      '#description' => $this->t('Mapping: field machine name for the localist event discription'),
      '#required' => FALSE,
    ];
    $form['localist_image_field_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Localist event image field'),
      '#default_value' => $localist_pull->localist_image_field_name,
      '#size' => 20,
      '#maxlength' => 255,
      '#description' => $this->t('Mapping: field machine name for the localist image'),
      '#required' => FALSE,
    ];
    $form['update_events_bool'] =[
      '#type' => 'checkbox',
      '#title' => $this->t('Update all existing events'),
      '#default_value' => $localist_pull->update_events_bool,
    ];
    $form['publish_events_bool'] =[
      '#type' => 'checkbox',
      '#title' => $this->t('Publish all new events'),
      '#default_value' => $localist_pull->publish_events_bool,
    ];
    // You will need additional form elements for your custom properties.
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $localist_pull = $this->entity;
    // kint($localist_pull);
    $status = $localist_pull->save();
    // kint($status);
    if ($status) {
      drupal_set_message($this->t('Saved the %label localist_pull entity.', [
        '%label' => $localist_pull->label(),
      ]));
    }
    else {
      drupal_set_message($this->t('The %label localist_pull entity was not saved.', [
        '%label' => $localist_pull->label(),
      ]));
    }

    $form_state->setRedirect('localist_pull.localist_pull.collection');
  }

  /**
   * Helper function to check whether an localist_pull entity configuration entity exists.
   */
  public function exist($id) {
    $entity = $this->entityTypeManager->getStorage('localist_pull')->getQuery()
      ->condition('id', $id)
      ->execute();
    return (bool) $entity;
  }

}
