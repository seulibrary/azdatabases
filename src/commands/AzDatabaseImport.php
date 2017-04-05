<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Storage;

class AzDatabaseImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'importAZDB';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import and parse your Databases from Alma into an XML and JSON file.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $items = file_get_contents(config('azdatabases.almaApi.url'));

        Storage::disk('local')->put('azDatabase.xml', $items);

        $xml = simplexml_load_string($items);
        $output = [];

        foreach ($xml->ListRecords->record as $data) {
            if (isset($data->metadata->record->leader)) {
                $titles = [];
                $title = '';
                $alt_title = '';
                $alt_title_type = '';
                $url = '';
                $description = '';
                $userLimit = '';
                $metadata = '';
                $area = '';
                $area_map = [];
                $subject = '';
                $mmsID = '';
                $subject_map = [];
                $database_area_subject_map = [];

                foreach ($data->metadata->record->controlfield as $mm) {
                    if (substr($mm, 0, 2) == '99') {
                        $mmsID = $mm;
                        break;
                    }
                }

                foreach ($data->metadata->record->datafield as $field) {
                    $alt_title_type = '';

                    switch ((string) $field['tag']) {
                        case '245':
                            foreach ($field->subfield as $sub) {
                                switch ((string) $sub['code']) {
                                    case 'a':
                                        $title = $sub;
                                        break;
                                    case 'b':
                                        $title = $title .' : '. $sub;
                                        break;
                                }
                            }
                            array_push($titles, (string)$title);
                            break;
                        case '856':
                            //url
                            foreach ($field->subfield as $sub) {
                                switch ((string) $sub['code']) {
                                    case 'u':
                                        $url = (array) $sub;
                                        $url = $url[0];

                                        break;
                                }
                            }
                            break;
                        case '520':
                            //descriptionitle
                            foreach ($field->subfield as $sub) {
                                switch ((string) $sub['code']) {
                                    case 'a':
                                        $description = (array) $sub;
                                        $description = $description[0];
                                        break;
                                }
                            }
                            break;
                        case '500':
                            //uer limit
                            foreach ($field->subfield as $sub) {
                                switch ((string) $sub['code']) {
                                    case 'a':
                                        $userLimit = (array) $sub;
                                        $userLimit = $userLimit[0];
                                        break;
                                }
                            }
                            break;
                        case '246':
                            // "alt-titles, but, using this for metadata (tags for searching)
                            foreach ($field->subfield as $sub) {
                                switch ((string) $sub['code']) {
                                    case 'a':
                                        $alt_title = $sub;
                                        break;
                                }
                            }
                            switch ((string) $field['ind1']) {
                                case '1':
                                    $metadata .= $alt_title ." : ";
                                    break;
                                case '3':
                                    $metadata .= $alt_title ." : ";
                                    break;
                                case ' ':
                                    $alt_title_type='ERROR';
                                    $metadata .= $alt_title ." : ";
                                    break;
                                default:
                                    $metadata .= $alt_title ." : ";
                                    break;
                            }
                            break;
                        case '960':
                            $area='';
                            $subject='';
                            foreach ($field->subfield as $sub) {
                                switch ((string) $sub['code']) {
                                    case 'a':
                                        $area = trim($sub);
                                        $area_map[$area][]='';
                                        //if code !in area array add it
                                        break;
                                    case 'b':
                                        $subject =  trim($sub);
                                        $area_map[$area][] = $subject;
                                        //if subejct !in subejct area array add it
                                        break;
                                }
                            }
                            break;
                    }
                }

                $categories = [];
                $subjects = [];
                
                foreach ($titles as $az_database_title) {
                    if ($area_map) {
                        foreach ($area_map as $area_key => $area_value) {
                            if (is_string($area_key) && $area_key !== '' && !preg_match('/^AZ Database/', $area_key) && !preg_match('/^All Databases/', $area_key)) {
                                $areaKeySlug = str_replace(' ', '_', htmlspecialchars(strtolower($area_key), ENT_QUOTES));
                                // Add only unique/new areas to array.
                                
                                if (!in_array($areaKeySlug, $categories)) {
                                    $categories[] = $areaKeySlug;
                                }
                                
                                foreach ($area_map[$area_key] as $subject_key => $subject_value) {
                                    if (is_string($subject_value) && $subject_value !== '') {
                                            $subjects[$areaKeySlug][] = str_replace(' ', '_', htmlspecialchars(strtolower($subject_value), ENT_QUOTES));
                                            $subjects[$areaKeySlug] = array_unique($subjects[$areaKeySlug]);
                                    }
                                }
                            }
                        }
                    }
                }
                
                $entry = [
                    'name' => $az_database_title,
                    'description' => htmlspecialchars($description, ENT_QUOTES),
                    'url' => $url,
                    'metadata' => $metadata,
                    'categories' => $categories,
                    'subjects' => $subjects,
                    'userLimit' => $userLimit
                ];

                array_push($output, $entry);
            }
        } // end ListRecords->record

        $file = json_encode($output);
        Storage::disk('local')->put('azDatabase.json', $file);
    }
}
