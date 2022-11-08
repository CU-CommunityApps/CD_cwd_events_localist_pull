<?php

$localist_configs = \Drupal::entityTypeManager()->getStorage('localist_pull')->loadMultiple();

$event_type = null;
$image_field_name = null;
$field_name_to_create = "field_event_media";
$image_media_bundle_name = "image";


foreach($localist_configs as $config) {
    $event_machine_name = $config->event_machine_name;
    $node_type = \Drupal\node\Entity\NodeType::load($event_machine_name);
    if($node_type) {
        echo "We have node type: " . $node_type->id() . "\n";
    }

    $image_field_name = $config->localist_image_field_name;
    if($event_machine_name && $image_field_name) {

        $image_media_type = \Drupal\media\Entity\MediaType::load($image_media_bundle_name);
        if(is_null($image_media_type)) {
            echo "No image media type found do not continue this upgrade.\n";
            break;
        }

        $field_media_storage = _get_or_create_media_field_storage($field_name_to_create);
        $field_media_image = _get_or_create_media_field_on_node_type($event_machine_name, $field_name_to_create, $field_media_storage);

        $config->localist_media_field_name = $field_name_to_create;
        $config->save();

        $form_display = \Drupal::service('entity_display.repository')
            ->getFormDisplay('node', $event_machine_name , 'default')
            ->setComponent($field_name_to_create, array('weight' => 2));
        $form_display->save();


        $event_nodes = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties(["type"=>$event_machine_name]);
        foreach($event_nodes as $event_node) {
            $node_title = $event_node->getTitle();
            $node_id = $event_node->id();
            $no_image = $event_node->get($image_field_name)->isEmpty();
            $already_has_media = !$event_node->get($field_name_to_create)->isEmpty();
            if($no_image) {
                echo $node_title ." (".$node_id.") does not have an image to move to media\n";
            }
            if($already_has_media) {
                echo $event_node->getTitle() . " (" . $event_node->id() . ") already has media attached to it\n";
            }

            $needs_media_added = !$no_image && !$already_has_media;
            if($needs_media_added) {
                $media = \Drupal\media\Entity\Media::create([
                    'bundle'           => 'image',
                    'uid'              => 1,
                    'field_media_image' => [
                        'target_id' => $event_node->$image_field_name->target_id,
                        'alt' => $event_node->$image_field_name->alt,
                    ],
                ]);
                $media->save();
                $event_node->$field_name_to_create = $media->id();
                $event_node->save();
                echo $event_node->getTitle() . " (" . $event_node->id() . ") updated with new media object\n";
            }
        }
    }
}

function _get_or_create_media_field_storage($field_name_to_create) {
    $media_field_storage = \Drupal\field\Entity\FieldStorageConfig::loadByName("node",$field_name_to_create);
    if(is_null($media_field_storage)) {
        $media_field_storage = \Drupal\field\Entity\FieldStorageConfig::create([
            'field_name' => $field_name_to_create,
            'entity_type' => 'node',
            'type' => 'entity_reference',
            'settings' => [
                'target_type' => 'media',
            ],
        ]);
        $media_field_storage->save();
    } else {
        echo "Already have field storage: ". $media_field_storage->id() ."\n";
    }
    return $media_field_storage;
}

function _get_or_create_media_field_on_node_type($event_type, $field_name_to_create, $media_field_storage) {
    $media_image_field = \Drupal\field\Entity\FieldConfig::loadByName("node",$event_type, $field_name_to_create);
    if(is_null($media_image_field)) {
        $field = \Drupal\field\Entity\FieldConfig::create([
            'field_storage' => $media_field_storage,
            'bundle' => $event_type,
            'label' => 'Event Image Media',
            'required' => false,
            'settings' => [
                'handler' => 'default:media',
                'handler_settings'=> [
                    'target_bundles' => [
                        'image'=> 'image'
                    ],
                    'sort'=> [
                        'field'=> '_none',
                        'direction'=> 'ASC'
                    ],
                    'auto_create'=> 'false',
                    'auto_create_bundle'=> ''
                ]
            ]
        ]);
        $field->save();
    } else {
        echo "Already have field ". $media_image_field->id() ." on ". $event_type . " node\n" ;
    }
    return $media_image_field;
}




// $media_bundle = "blarge";
// $image_media_type = \Drupal\media\Entity\MediaType::load($media_bundle);
// if($image_media_type) {
//     echo "We have media type: " . $image_media_type->id() . "\n";
// } else {
//     echo "need to create bundle\n";
//     $type = \Drupal\media\Entity\MediaType::create([
//         'id' => $media_bundle,
//         'label'=> 'Gary',
//         'description' => 'Use local images for reusable media.',
//         'source' => 'image',
//         'queue_thumbnail_downloads' => 'false',
//         'new_revision' => 'true'
//     ]);
//     $type->save();
// }