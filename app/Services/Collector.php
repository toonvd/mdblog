<?php

namespace App\Services;

use Storage;

class Collector {

    /**
     * @param array $options
     * @return array
     */
    public function getDisks(array $options): array
    {
        return [
            'source' => Storage::build([
                'driver' => 'local',
                'root' => $options['source'],
            ]),
            'target' => Storage::build([
                'driver' => 'local',
                'root' => $options['target'],
            ]),
            'images' => Storage::build([
                'driver' => 'local',
                'root' => $options['image_path'],
            ]),
            'templates' => Storage::build([
                'driver' => 'local',
                'root' => $options['template_path'],
            ])
        ];
    }
}
