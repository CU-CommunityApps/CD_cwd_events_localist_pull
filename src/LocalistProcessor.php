<?php
namespace Drupal\localist_pull;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Component\Serialization\Json;
use \Drupal\node\Entity\Node;
use Drupal\file\Entity\File;
/**
 * Provides an interface defining an localist_pull entity entity.
 */
class LocalistProcessor {
  public $config;

  public function __construct($provided_config) {
    $this->config = $provided_config;
  }

  public function _get_node_by_localist_id($field_search_name,$id) {
    $conf_id = $this->config->id;
    $query = \Drupal::entityQuery('node')->condition($field_search_name, $conf_id.$id);
    $nids = $query->execute();
    return $nids;
  }

  public function _convert_localist_date($date_string) {
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

  public function _create_node_create_array() {
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
    return $node_create_array;
  }

  public function _get_localist_event_data($event,$node_array) {
    $new_array = [];
    foreach ($node_array as $fieldname => $value) {
      switch ($fieldname) {
        case $this->config->get('localist_id_field_name'):
            $conf_id = $this->config->id;
            $new_array[$this->config->get('localist_id_field_name')]=$conf_id.$event['event']['id'];
        case $this->config->get('localist_date_field_name'):
          if(!is_null($event['event']['event_instances']['0']['event_instance']['start'])) {
            $new_array[$this->config->get('localist_date_field_name')]=$this->_convert_localist_date($event['event']['event_instances']['0']['event_instance']['start']);
            // \Drupal::logger('localist_pull')->notice($event['event']['title']." START: ".$event['event']['event_instances']['0']['event_instance']['start']." Returned: ".$new_array[$config->get('localist_date_field_name')]);
          }
        case $this->config->get('localist_end_date_field_name'):
            if(!is_null($event['event']['event_instances']['0']['event_instance']['end'])) {
              $new_array[$this->config->get('localist_end_date_field_name')]=$this->_convert_localist_date($event['event']['event_instances']['0']['event_instance']['end']);
              // \Drupal::logger('localist_pull')->notice($event['event']['title']." END: ".$event['event']['event_instances']['0']['event_instance']['end']." Returned: ".$new_array[$config->get('localist_end_date_field_name')]);
            }
        case $this->config->get('localist_description_field_name'):
          $new_array[$this->config->get('localist_description_field_name')]=$event['event']['description_text'];
        case $this->config->get('localist_location_field_name'):
            if(!empty($event['event']['location_name']) && !is_null($event['event']['location_name']) && !empty($event['event']['room_number']) && !is_null($event['event']['room_number']))
              $new_array[$this->config->get('localist_location_field_name')]=$event['event']['location_name'].", ".$event['event']['room_number'];
            else
              $new_array[$this->config->get('localist_location_field_name')]=$event['event']['location_name'];
        case $this->config->get('localist_url_field_name'):
            $new_array[$this->config->get('localist_url_field_name')]=$event['event']['localist_url'];
        case $this->config->get('localist_image_field_name'):
          if(!empty($event['event']['photo_url']) && $event['event']['photo_url'] != '') {
            $new_array[$this->config->get('localist_image_field_name')]=$this->_create_file_and_array($event['event']['photo_url']);
          }
        default:
          break;
      }
    }
    $new_array['title']=$event['event']['title'];
    $new_array['type']=$this->config->get('event_machine_name');
    unset($new_array['']);
    return $new_array;
  }


  public function _create_file_and_array($url) {
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
            $localist_data_array = $this->_get_localist_event_data($event,$this->_create_node_create_array());

            $existing_event = $this->_get_node_by_localist_id($search_field_name,$event['event']['id']);

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
