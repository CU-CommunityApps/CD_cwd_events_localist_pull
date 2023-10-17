<?php

namespace Drupal\cwd_events_localist_pull;

use Drupal\Core\File\FileSystemInterface;
use Drupal\Component\Serialization\Json;
use \Drupal\node\Entity\Node;
use \Drupal\media\Entity\Media;
use Drupal\taxonomy\Entity\Term;
use GuzzleHttp\Exception\RequestException;

/**
 * Provides an interface defining a localist_pull entity.
 */
class LocalistProcessor {
  private $config;

  /**
   * @var \Drupal\Core\File\FileSystemInterface
   */
  private $file_system;

  public function __construct($provided_config, FileSystemInterface $file_system) {
    $this->config = $provided_config;
    $this->file_system = $file_system;
  }

  private function get_node_by_localist_id($field_search_name, $id) {
    $event_id = $this->config->id . $id;
    $database = \Drupal::database();
    $query = $database->select('node', 'n');
    $query->innerJoin("node__{$field_search_name}", 'lid', 'lid.entity_id=n.nid');
    $query->addField('n', 'nid');
    $query->condition("lid.{$field_search_name}_value", $event_id);
    return $query->execute()->fetchCol();
    // $conf_id = $this->config->id;
    // $query = \Drupal::entityQuery('node')->condition($field_search_name, $conf_id . $id);
    // $nids = $query->execute();
    // return $nids;
  }

  private function convert_localist_date($date_string) {
    $cut_date_string = substr($date_string, 0, -6);
    $dateTime = \DateTime::createFromFormat('Y-m-d\TH:i:s', date("Y-m-d\TH:i:s", strtotime($cut_date_string)));
    $sign = substr($date_string, -6, 1);
    $offset_sign = "+";
    if ($sign == "+") {
      $offset_sign = "-";
    }
    $offset_hours = ltrim(substr($date_string, -5, 2), '0');
    $offset = $offset_sign . $offset_hours . " hours";
    $dateTime = $dateTime->modify($offset);
    $newdate = $dateTime->format('Y-m-d\TH:i:s');
    return $newdate;
  }

  private function create_node_create_array() {
    $node_create_array = [];
    if (!empty($this->config->get('localist_id_field_name')) && $this->config->get('localist_id_field_name') != '') {
      $node_create_array[$this->config->get('localist_id_field_name')] = '';
    }
    if (!empty($this->config->get('localist_url_field_name')) && $this->config->get('localist_url_field_name') != '') {
      $node_create_array[$this->config->get('localist_url_field_name')] = '';
    }
    if (!empty($this->config->get('localist_location_field_name')) && $this->config->get('localist_location_field_name') != '') {
      $node_create_array[$this->config->get('localist_location_field_name')] = '';
    }
    if (!empty($this->config->get('localist_date_field_name')) && $this->config->get('localist_date_field_name') != '') {
      $node_create_array[$this->config->get('localist_date_field_name')] = '';
    }
    if (!empty($this->config->get('localist_end_date_field_name')) && $this->config->get('localist_end_date_field_name') != '') {
      $node_create_array[$this->config->get('localist_end_date_field_name')] = '';
    }
    if (!empty($this->config->get('localist_description_field_name')) && $this->config->get('localist_description_field_name') != '') {
      $node_create_array[$this->config->get('localist_description_field_name')] = '';
    }
    if (!empty($this->config->get('localist_image_field_name')) && $this->config->get('localist_image_field_name') != '') {
      $node_create_array[$this->config->get('localist_image_field_name')] = '';
    }
    if (!empty($this->config->get('localist_media_field_name')) && $this->config->get('localist_media_field_name') != '') {
      $node_create_array[$this->config->get('localist_media_field_name')] = '';
    }
    if (!empty($this->config->get('localist_tag_field_name')) && $this->config->get('localist_tag_field_name') != '') {
      $node_create_array[$this->config->get('localist_tag_field_name')] = '';
    }
    if (!empty($this->config->get('localist_event_type_field_name')) && $this->config->get('localist_event_type_field_name') != '') {
      $node_create_array[$this->config->get('localist_event_type_field_name')] = '';
    }
    return $node_create_array;
  }

  private function get_localist_event_data($event, $node_array) {
    $new_array = [];
    foreach ($node_array as $fieldname => $value) {
      switch ($fieldname) {
        case $this->config->get('localist_id_field_name'):
          $conf_id = $this->config->id;
          $new_array[$this->config->get('localist_id_field_name')] = $conf_id . $event['event']['id'];
          break;
        case $this->config->get('localist_date_field_name'):
          if (!is_null($event['event']['event_instances']['0']['event_instance']['start'])) {
            $new_array[$this->config->get('localist_date_field_name')] = $this->convert_localist_date($event['event']['event_instances']['0']['event_instance']['start']);
            // \Drupal::logger('localist_pull')->notice($event['event']['title']." START: ".$event['event']['event_instances']['0']['event_instance']['start']." Returned: ".$new_array[$config->get('localist_date_field_name')]);
          }
          break;
        case $this->config->get('localist_end_date_field_name'):
          if (!is_null($event['event']['event_instances']['0']['event_instance']['end'])) {
            $new_array[$this->config->get('localist_end_date_field_name')] = $this->convert_localist_date($event['event']['event_instances']['0']['event_instance']['end']);
            // \Drupal::logger('localist_pull')->notice($event['event']['title']." END: ".$event['event']['event_instances']['0']['event_instance']['end']." Returned: ".$new_array[$config->get('localist_end_date_field_name')]);
          }
          break;
        case $this->config->get('localist_description_field_name'):
          $new_array[$this->config->get('localist_description_field_name')] = $event['event']['description_text'];
          break;
        case $this->config->get('localist_location_field_name'):
          if (!empty($event['event']['location_name']) && !is_null($event['event']['location_name']) && !empty($event['event']['room_number']) && !is_null($event['event']['room_number']))
            $new_array[$this->config->get('localist_location_field_name')] = $event['event']['location_name'] . ", " . $event['event']['room_number'];
          else
            $new_array[$this->config->get('localist_location_field_name')] = $event['event']['location_name'];
          break;
        case $this->config->get('localist_url_field_name'):
          $new_array[$this->config->get('localist_url_field_name')] = $event['event']['localist_url'];
          break;
        case $this->config->get('localist_image_field_name'):
          if (!empty($event['event']['photo_url']) && $event['event']['photo_url'] != '') {
            $new_array[$this->config->get('localist_image_field_name')] = $this->create_file_and_array($event['event']['photo_url']);
          }
          break;
        case $this->config->get('localist_media_field_name'):
          if (!empty($event['event']['photo_url']) && $event['event']['photo_url'] != '') {
            $image_array = $this->create_file_and_array($event['event']['photo_url']);
            $media_name = "Event Image: " . $event['event']['title'];
            $new_array[$this->config->get('localist_media_field_name')] = $this->create_media_from_file($image_array, $media_name);
          }
          break;
        case $this->config->get('localist_tag_field_name'):
          if (!empty($event['event']['filters']['departments']) && $event['event']['filters']['departments'] != '') {
            $pull_specified_departments = $this->config->get('pull_specified_departments');
            $valid_department_array = explode(",", $this->config->get('localist_departments'));
            $department_term_array = array();
            foreach ($event['event']['filters']['departments'] as $department_info) {
              if ($pull_specified_departments && !in_array($department_info['id'], $valid_department_array)) {
                continue;
              }
              $department_term_array[] = ['target_id' => $this->find_or_create_department($department_info['name'])];
            }
            $new_array[$this->config->get('localist_tag_field_name')] = $department_term_array;
          }
          break;
        case $this->config->get('localist_event_type_field_name'):
          if (!empty($event['event']['filters']['event_types']) && $event['event']['filters']['event_types'] != '') {
            $event_type_term_array = array();
            foreach ($event['event']['filters']['event_types'] as $event_type_info) {
              $event_type_term_array[] = ['target_id' => $this->find_or_create_event_type($event_type_info['name'])];
            }
            $new_array[$this->config->get('localist_event_type_field_name')] = $event_type_term_array;
          }
          break;
      }
    }
    $new_array['title'] = $event['event']['title'];
    $new_array['type'] = $this->config->get('event_machine_name');
    unset($new_array['']);
    return $new_array;
  }


  private function create_media_from_file($image_array, $media_name) {
    $media_id = $this->find_media_for_file($image_array);
    if ($media_id) {
      return $media_id;
    }

    $media = Media::create([
      'bundle' => 'image',
      'uid' => 1,
      'field_media_image' => $image_array,
    ]);
    $media->setName($media_name)->setPublished()->save();
    return $media->id();
  }

  private function find_media_for_file($image_array) {
    $file = \Drupal::entityTypeManager()->getStorage('file')->load($image_array['target_id']);
    $result = \Drupal::service('file.usage')->listUsage($file);
    if ($result && array_key_exists('media', $result['file'])) {
      return array_key_first($result['file']['media']);
    }
    else {
      return null;
    }
  }
  private function create_file_and_array($url) {
    $temp = explode('/', $url);
    $url = str_replace("https:", "http:", $url);
    $photo_name = array_pop($temp);
    $data = file_get_contents($url);
    $path = 'public://localist';
    $this->file_system->prepareDirectory($path, FileSystemInterface::CREATE_DIRECTORY);
    $file = \Drupal::service('file.repository')->writeData($data, $path .'/'. $photo_name, FileSystemInterface::EXISTS_REPLACE);
    $photo_array = [
      'target_id' => $file->id(),
      'alt' => '',
    ];
    return $photo_array;
  }


  private function find_or_create_department($term_name) {
    $tax_vid = $this->config->get('localist_department_taxonomy');
    $tax_search_field = $this->config->get('localist_department_lookup_field');

    $term = null;
    if ($tax_search_field != '') {
      $term_id = $this->getTermByField($tax_search_field, $term_name);
      if ($term_id != false) {
        return $term_id;
      }
      else {
        $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties(['name' => $term_name, 'vid' => $tax_vid]);
      }
    }
    else {
      $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties(['name' => $term_name, 'vid' => $tax_vid]);
    }

    if (empty($term) || is_null($term)) {
      $new_term = Term::create([
        'vid' => $tax_vid,
        'name' => $term_name,
      ]);
      if ($tax_search_field != '') {
        $new_term->set($tax_search_field, $term_name);
      }
      $new_term->enforceIsNew();
      $new_term->save();
      $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties(['name' => $term_name, 'vid' => $tax_vid]);
    }
    return array_shift($term)->id();
  }

  private function find_or_create_event_type($term_name) {
    $tax_vid = $this->config->get('localist_event_type_taxonomy');

    $term = null;
    $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties(['name' => $term_name, 'vid' => $tax_vid]);

    if (empty($term) || is_null($term)) {
      $new_term = Term::create([
        'vid' => $tax_vid,
        'name' => $term_name,
      ]);
      $new_term->enforceIsNew();
      $new_term->save();
      $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties(['name' => $term_name, 'vid' => $tax_vid]);
    }
    return array_shift($term)->id();
  }

  protected function getTermByField($tax_search_field, $term_name) {
    $query_string = "select taxonomy_term_field_data.tid";
    $query_string .= " from taxonomy_term_field_data, taxonomy_term__" . $tax_search_field;
    $query_string .= " where taxonomy_term_field_data.tid = taxonomy_term__" . $tax_search_field . ".entity_id";
    $query_string .= " and taxonomy_term__" . $tax_search_field . "." . $tax_search_field . "_value = '" . $term_name . "'";
    $query_string .= " limit 1;";
    $database = \Drupal::database();
    $query = $database->query($query_string);
    $results = $query->fetchAll();
    if (count($results) == 0) {
      //if no results then send back false
      return false;
    }
    else {
      return array_shift($results)->tid;
    }
  }

  public function create_localist_url($page = 1) {
    $uri = $this->config->get('url');
    $keys = str_replace(' ', '+', implode('&keyword[]=', explode(',', $this->config->get('localist_keywords'))));
    if (!is_null($keys) && $keys != '') {
      $keys = "&keyword[]=" . $keys;
    }
    $depts = str_replace(' ', '+', implode('&type[]=', explode(',', $this->config->get('localist_departments'))));
    if (!is_null($depts) && $depts != '') {
      $depts = "&type[]=" . $depts;
    }

    //Date for localist url (bad dates)
    $date = $this->config->get('localist_relative_date');
    //if relative date is empty use fixed date.
    if (is_null($date) || $date == '') {
      $date = $this->config->get('localist_date');
      //if fixed date is empty use today.
      if (is_null($date) || $date == '') {
        $date = date('Y-m-d');
      }
    }
    else {
      //construct relative date
      $date = date('Y-m-d', strtotime($date));
    }

    $count = $this->config->get('localist_count');
    if (is_null($count) || $count == '') {
      $count = '5';
    }
    $extra_param = $this->config->get('extra_parameters');
    if ($extra_param == "distinct") {
      $extra_param = "&distinct=true";
    }
    elseif ($extra_param == "all") {
      $extra_param = "&all_instances=true";
    }
    else {
      $extra_param = "";
    }
    $url = $uri . '&days=370&sort=date' . $keys . $depts . '&pp=' . $count . '&start=' . $date . $extra_param . "&page=$page";
    return $url;
  }


  public function process_url_pull($search_field_name, $url) {
    try {
      \Drupal::logger('localist_pull')->notice($url);
      $response = \Drupal::httpClient()->get($url, array('headers' => array('Accept' => 'text/plain')));
      $json = (string) $response->getBody();
      if (empty($json)) {
        return FALSE;
      }
      else {
        $events = Json::decode($json)['events'];
        $current_page = Json::decode($json)['page']['current'];
        $total_pages = Json::decode($json)['page']['total'];
        if (empty($events) || !$this->should_process_current_page($json, $current_page, $total_pages)) {
          \Drupal::logger('localist_pull')->notice("should not process page $current_page of $total_pages");
          return;
        }
        \Drupal::logger('localist_pull')->notice("process page $current_page of $total_pages");
        if (!empty($events)) {
          foreach ($events as $event) {
            $localist_data_array = $this->get_localist_event_data($event, $this->create_node_create_array());
            $existing_event = $this->get_node_by_localist_id($search_field_name, $event['event']['id']);
            if (empty($existing_event)) {
              $node = Node::create(
                $localist_data_array
              );
              if (!$this->config->publish_events_bool) {
                $node->setUnpublished();
              }
              $node->save();
            }
            else {
              if ($this->config->update_events_bool) {
                // $nid = reset($existing_event);
                // $node = Node::load($nid);
                $temp = array_reverse($existing_event);
                $existing_node_id = array_pop($temp);
                $node = Node::load($existing_node_id);
                foreach ($localist_data_array as $key => $value) {
                  if ($key != 'type') {
                    $node->set($key, $value);
                  }
                }
                $node->save();
              }
            }
          }
        }

        //recursive call until we hit localist_page_count limit if configured, max of 3 pages
        $next_page_url = $this->create_localist_url($current_page + 1);
        $this->process_url_pull($search_field_name, $next_page_url);
      }
    }
    catch (RequestException $e) {
      \Drupal::logger('localist_pull')->notice($e->getMessage());
      return FALSE;
    }
  }

  private function should_process_current_page($json, $current_page, $total_pages) {
    $pages_to_process = 1;
    $have_page_count = is_numeric($this->config->get('localist_page_count'));
    if ($have_page_count) {
      $pages_to_process = min(($this->config->get('localist_page_count')), 3);
    }

    if ($current_page > $pages_to_process) {
      return false;
    }

    if ($current_page > $total_pages) {
      return false;
    }
    return true;
  }
}
