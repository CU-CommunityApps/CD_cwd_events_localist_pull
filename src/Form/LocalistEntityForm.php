<?php
namespace Drupal\cwd_events_localist_pull\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form handler for the localist_pull entity add and edit forms.
 */
class LocalistEntityForm extends EntityForm {

  /**
   * Constructs a localist_pull entityForm object.
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
    $form['localist_label'] = array(
      '#type' => 'label',
      '#title' => $this->t('<br/><hr/><h2>Localist URL configurations:</h2><hr/>'),
    );
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
      '#title' => $this->t('Department IDs'),
      '#default_value' => $localist_pull->localist_departments,
      '#size' => 20,
      '#maxlength' => 255,
      '#description' => $this->t('Enter the department IDs you wish to use to pull back events from the Cornell Calendar. Separate department IDs with commas.'),
      '#required' => FALSE,
    ];
    $form['localist_keywords'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Keywords'),
      '#default_value' => $localist_pull->localist_keywords,
      '#size' => 20,
      '#maxlength' => 255,
      '#description' => $this->t('Enter the keyword you wish to use to pull back events from the Cornell Calendar. Separate keywords with commas.'),
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

    $form['localist_page_count'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Page Count'),
      '#default_value' => $localist_pull->localist_page_count,
      '#size' => 20,
      '#maxlength' => 255,
      '#description' => $this->t('Enter the number events pages you want to process. Max is 3'),
      '#required' => FALSE,
    ];

    $form['extra_parameters_type'] = array(
      '#type' => 'value',
      '#value' => array('none' => t('None'),
      'distinct' => t('Distinct'),
      'all' => t('All Instances'))
    );
    $form['extra_parameters'] = array(
      '#title' => t('Extra Localist URL Parameter'),
      '#type' => 'select',
      '#description' => '<ul><li>None: adds no parameters to localist query</li><li>Distinct: will return only next instance of an event</li><li>All Instances: returns all instances of an event</li>',
      '#options' => $form['extra_parameters_type']['#value'],
      '#default_value' => $localist_pull->extra_parameters,
      '#required' => TRUE,
    );
    $form['date_label'] = array(
      '#type' => 'label',
      '#title' => $this->t('<br/>Date instructions:
        <ul>
          <li>Relative date  will be used if filled in</li>
          <li>If relative date is blank this connection will use the static date</li>
          <li>If there is no static date the default is today</li>
        </ul>'),
    );
    $form['localist_relative_date'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Relative Date'),
      '#default_value' => $localist_pull->localist_relative_date,
      '#size' => 20,
      '#maxlength' => 255,
      '#description' => $this->t('Enter a relative date such as "+30 days". This will be used like: date(\'Y-m-d\', strtotime("+30 days")). CAUTION: The relative date must be a valid PHP relative date/time format.'),
      '#required' => FALSE,
    ];
    $form['localist_date'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Static Date'),
      '#default_value' => $localist_pull->localist_date,
      '#size' => 20,
      '#maxlength' => 255,
      '#description' => $this->t('Enter the start date that you want begin pulling from the Cornell Calendar. Format: YYYY-MM-DD'),
      '#required' => FALSE,
    ];

    $form['extras_label'] = array(
      '#type' => 'label',
      '#title' => $this->t('<br/><hr/><h2>How events are imported:</h2><hr/>'),
    );
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

    $form['event_ct_label'] = array(
      '#type' => 'label',
      '#title' => $this->t('<br/><hr/><h2>Event content type configuration:</h2><hr/>'),
    );
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

    $form['localist_image_field_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Localist event image field'),
      '#default_value' => $localist_pull->localist_image_field_name,
      '#size' => 20,
      '#maxlength' => 255,
      '#description' => $this->t('Mapping: field machine name for the localist image'),
      '#required' => FALSE,
    ];
    $form['department_label'] = array(
      '#type' => 'label',
      '#title' => $this->t('<br/><hr/><h2>Taxonomy</h2>
       Instructions for departments:
        <ul>
          <li>If you want to feed in localist departments fill in the machine name of the taxonomy that should hold localist departments.</li>
          <li>If you do not plan to edit these localist department names leave the Department Term lookup blank. We will lookup by term name.</li>
          <li>If you plan to edit these localist department names you must add a field to your Department taxonomy terms and put that machine name in the Department Term lookup field.</li>
        </ul>'),
    );
    $form['localist_department_taxonomy'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Taxonomy for Department Terms machine name'),
      '#default_value' => $localist_pull->localist_department_taxonomy,
      '#size' => 20,
      '#maxlength' => 255,
      '#description' => $this->t('Machine name of the taxonomy to feed localist departments'),
      '#required' => FALSE,
    ];
    $form['localist_tag_field_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Event CT field for Localist (department) tag'),
      '#default_value' => $localist_pull->localist_tag_field_name,
      '#size' => 20,
      '#maxlength' => 255,
      '#description' => $this->t('Mapping: field machine name to add departments as term refereneces.'),
      '#required' => FALSE,
    ];
    $form['localist_department_lookup_field'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Taxonomy field for Department Term lookup'),
      '#default_value' => $localist_pull->localist_department_lookup_field,
      '#size' => 20,
      '#maxlength' => 255,
      '#description' => $this->t('Machine name of the taxonomy field to search for tax term in Drupal taxonomy that maps to localist department'),
      '#required' => FALSE,
    ];
    $form['pull_specified_departments'] =[
      '#type' => 'checkbox',
      '#title' => $this->t('Should we pull only the specified departments as tags? By not checking this box we will pull all departments on an event.'),
      '#default_value' => $localist_pull->pull_specified_departments,
    ];
    $form['etypes_label'] = array(
      '#type' => 'label',
      '#title' => $this->t('<h3>Event types</h3>
       Less complex than departments: Simply enter the taxonomy machine name and event CT field for event type terms.'),
    );
    $form['localist_event_type_taxonomy'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Taxonomy for Event type terms'),
      '#default_value' => $localist_pull->localist_event_type_taxonomy,
      '#size' => 20,
      '#maxlength' => 255,
      '#description' => $this->t('Machine name of the taxonomy to feed localist event types.'),
      '#required' => FALSE,
    ];
    $form['localist_event_type_field_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Event CT field for Localist event_types'),
      '#default_value' => $localist_pull->localist_event_type_field_name,
      '#size' => 20,
      '#maxlength' => 255,
      '#description' => $this->t('Mapping: event type term reference field machine name.'),
      '#required' => FALSE,
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
      \Drupal::messenger()->addMessage($this->t('Saved the %label localist_pull entity.', [
        '%label' => $localist_pull->label(),
      ]));
    }
    else {
      \Drupal::messenger()->addMessage($this->t('The %label localist_pull entity was not saved.', [
        '%label' => $localist_pull->label(),
      ]));
    }

    $form_state->setRedirect('localist_pull.localist_pull.collection');
  }

  /**
   * Helper function to check whether a localist_pull entity configuration entity exists.
   */
  public function exist($id) {
    $entity = $this->entityTypeManager->getStorage('localist_pull')->getQuery()
      ->condition('id', $id)
      ->accessCheck(TRUE)
      ->execute();
    return (bool) $entity;
  }

}
