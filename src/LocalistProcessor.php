<?php
namespace Drupal\cwd_events_localist_pull;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Component\Serialization\Json;
use \Drupal\node\Entity\Node;
use Drupal\file\Entity\File;
/**
 * Provides an interface defining an localist_pull entity entity.
 */
class LocalistProcessor {
  private $config;

  public function __construct($provided_config) {
    $this->config = $provided_config;
  }

  private function get_node_by_localist_id($field_search_name, $id) {
    $conf_id = $this->config->id;
    $query = \Drupal::entityQuery('node')->condition($field_search_name, $conf_id.$id);
    $nids = $query->execute();
    return $nids;
  }

  private function convert_localist_date($date_string) {
    $cut_date_string = substr($date_string,0,-6);
    $dateTime = \DateTime::createFromFormat('Y-m-d\TH:i:s',date("Y-m-d\TH:i:s",strtotime($cut_date_string)));
    $sign = substr($date_string,-6,1);
    $offset_sign = "+";
    if($sign == "+") {
      $offset_sign = "-";
    }
    $offset_hours = ltrim(substr($date_string,-5,2), '0');
    $offset = $offset_sign.$offset_hours." hours";
    $dateTime = $dateTime->modify($offset);
    $newdate = $dateTime->format('Y-m-d\TH:i:s');
    return $newdate;
  }

  private function create_node_create_array() {
    $node_create_array = [];
    if(!empty($this->config->get('localist_id_field_name')) && $this->config->get('localist_id_field_name') != '') {
      $node_create_array[$this->config->get('localist_id_field_name')] = '';
    }
    if(!empty($this->config->get('localist_url_field_name')) && $this->config->get('localist_url_field_name') != '') {
      $node_create_array[$this->config->get('localist_url_field_name')] = '';
    }
    if(!empty($this->config->get('localist_location_field_name')) && $this->config->get('localist_location_field_name') != '') {
      $node_create_array[$this->config->get('localist_location_field_name')] = '';
    }
    if(!empty($this->config->get('localist_date_field_name')) && $this->config->get('localist_date_field_name') != '') {
      $node_create_array[$this->config->get('localist_date_field_name')] = '';
    }
    if(!empty($this->config->get('localist_description_field_name')) && $this->config->get('localist_description_field_name') != '') {
      $node_create_array[$this->config->get('localist_description_field_name')] = '';
    }
    if(!empty($this->config->get('localist_image_field_name')) && $this->config->get('localist_image_field_name') != '') {
      $node_create_array[$this->config->get('localist_image_field_name')] = '';
    }
    if(!empty($this->config->get('localist_tag_field_name')) && $this->config->get('localist_tag_field_name') != '') {
      $node_create_array[$this->config->get('localist_tag_field_name')] = '';
    }
    return $node_create_array;
  }

  private function get_localist_event_data($event, $node_array) {
    $new_array = [];
    foreach ($node_array as $fieldname => $value) {
      switch ($fieldname) {
        case $this->config->get('localist_id_field_name'):
          $conf_id = $this->config->id;
          $new_array[$this->config->get('localist_id_field_name')]=$conf_id.$event['event']['id'];
          break;
        case $this->config->get('localist_date_field_name'):
          if(!is_null($event['event']['event_instances']['0']['event_instance']['start'])) {
            $new_array[$this->config->get('localist_date_field_name')]=$this->convert_localist_date($event['event']['event_instances']['0']['event_instance']['start']);
            // \Drupal::logger('localist_pull')->notice($event['event']['title']." START: ".$event['event']['event_instances']['0']['event_instance']['start']." Returned: ".$new_array[$config->get('localist_date_field_name')]);
          }
          break;
        case $this->config->get('localist_end_date_field_name'):
          if(!is_null($event['event']['event_instances']['0']['event_instance']['end'])) {
            $new_array[$this->config->get('localist_end_date_field_name')]=$this->convert_localist_date($event['event']['event_instances']['0']['event_instance']['end']);
            // \Drupal::logger('localist_pull')->notice($event['event']['title']." END: ".$event['event']['event_instances']['0']['event_instance']['end']." Returned: ".$new_array[$config->get('localist_end_date_field_name')]);
          }
          break;
        case $this->config->get('localist_description_field_name'):
          $new_array[$this->config->get('localist_description_field_name')]=$event['event']['description_text'];
          break;
        case $this->config->get('localist_location_field_name'):
          if(!empty($event['event']['location_name']) && !is_null($event['event']['location_name']) && !empty($event['event']['room_number']) && !is_null($event['event']['room_number']))
            $new_array[$this->config->get('localist_location_field_name')]=$event['event']['location_name'].", ".$event['event']['room_number'];
          else
            $new_array[$this->config->get('localist_location_field_name')]=$event['event']['location_name'];
          break;
        case $this->config->get('localist_url_field_name'):
          $new_array[$this->config->get('localist_url_field_name')]=$event['event']['localist_url'];
          break;
        case $this->config->get('localist_image_field_name'):
          if(!empty($event['event']['photo_url']) && $event['event']['photo_url'] != '') {
            $new_array[$this->config->get('localist_image_field_name')]=$this->create_file_and_array($event['event']['photo_url']);
          }
          break;
        case $this->config->get('localist_tag_field_name'):
          if(!empty($event['event']['filters']['departments']) && $event['event']['filters']['departments'] != '') {
            $pull_specified_departments = $this->config->get('pull_specified_departments');
            $valid_department_array = explode(",",$this->config->get('localist_departments'));
            $department_term_array = array();
            foreach ($event['event']['filters']['departments'] as $department_info) {
              if($pull_specified_departments && !in_array($department_info['id'],$valid_department_array)) {
                continue;
              }
              $department_term_array[] = ['target_id' => $this->find_or_create_department($department_info['name'])];
            }
            $new_array[$this->config->get('localist_tag_field_name')] = $department_term_array;
          }
          break;
      }
    }
    $new_array['title']=$event['event']['title'];
    $new_array['type']=$this->config->get('event_machine_name');
    unset($new_array['']);
    return $new_array;
  }


  private function create_file_and_array($url) {
    $temp = explode('/',$url);
    $photo_name = array_pop($temp);
    $data = file_get_contents($url);
    $path = 'public://localist';
    file_prepare_directory($path, FILE_CREATE_DIRECTORY);
    $file = file_save_data($data, 'public://localist/'.$photo_name, FILE_EXISTS_REPLACE);
    $photo_array = [
      'target_id' => $file->id(),
      'alt' => '',
    ];
    return $photo_array;
  }


  private function find_or_create_department($department_name) {
    $tax_vid = $this->config->get('localist_department_taxonomy');
    $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties(['name' => $department_name,'vid' => $tax_vid]);
    if(empty($term)) {
      $new_term = \Drupal\taxonomy\Entity\Term::create([
        'vid' => $tax_vid,
        'name' => $department_name,
      ]);
      $new_term->enforceIsNew();
      $new_term->save();
      $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties(['name' => $department_name,'vid' => $tax_vid]);
    }
    return array_shift($term)->id();
  }


  public function create_localist_url(){
    $uri = $this->config->get('url');
    $keys = str_replace(' ','+',implode('&keyword[]=',explode(',',$this->config->get('localist_keywords'))));
    if(!is_null($keys) && $keys != '') {
        $keys = "&keyword[]=".$keys;
    }
    $depts = str_replace(' ','+',implode('&type[]=',explode(',',$this->config->get('localist_departments'))));
    if(!is_null($depts) && $depts != '') {
        $depts = "&type[]=".$depts;
    }
    $date = $this->config->get('localist_date');
    if(is_null($date) || $date == '') {
        $date = date('Y-m-d');
    }
    $count = $this->config->get('localist_count');
    if(is_null($count) || $count == '') {
        $count = '5';
    }
    $url = $uri.'&days=370&sort=date'.$keys.$depts.'&pp='.$count.'&start='.$date;
    return $url;
  }


  public function process_url_pull($search_field_name,$url) {
    try {
      $response = \Drupal::httpClient()->get($url, array('headers' => array('Accept' => 'text/plain')));
      $json = (string) $response->getBody();
      if (empty($json)) {
        return FALSE;
      } else {
        $events = Json::decode($json)['events'];
        if(!empty($events)) {
          $count = 0;
          foreach ($events as $event) {
            $localist_data_array = $this->get_localist_event_data($event,$this->create_node_create_array());
            $existing_event = $this->get_node_by_localist_id($search_field_name,$event['event']['id']);
            if(empty($existing_event)) {
              $node = Node::create(
                $localist_data_array
              );
              if(!$this->config->publish_events_bool) {
                $node->setUnpublished();
              }
              $node->save();
            } else {
              if($this->config->update_events_bool) {
                $temp = array_reverse($existing_event);
                $existing_node_id = array_pop($temp);
                $node = \Drupal\node\Entity\Node::load($existing_node_id);
                foreach ($localist_data_array as $key => $value) {
                  if($key != 'type') {
                    $node->set($key,$value);
                  }
                }
                $node->save();
              }
            }
          }
        }
      }
    }
    catch (RequestException $e) {
      \Drupal::logger('localist_pull')->notice("exception");
      return FALSE;
    }
  }
}
