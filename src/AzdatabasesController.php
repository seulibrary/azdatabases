<?php

namespace Seumunday\Azdatabases;
 
use App\Http\Controllers\Controller;
use Log;

class AzdatabasesController extends Controller
{

    /**
    * Run everytime this class is called
    *
    * Becuase this page is based on a physical file, check and make sure the file exists, if not, run the import script.
    *
    * @return Null
    */
    public function __construct() {
        
        $file = file_exists(storage_path('app/azDatabase.json'));

        if (!$file) {
            \Artisan::call('importAZDB');
        }
    }

    /**
    * Return Page View
    *
    * @return View
    */
    public function index()
    {
        $navigation = $this->navigation();

        return view('azdatabases::index', ['navigation' => $navigation]);
    }

    /**
     * Return sorted list of all items in xml file.
     * 
     * @return JSON
     */
    public function list()
    {
        $dbList = json_decode($this->getFile());
        
        usort($dbList, array($this, "cmp"));

        return $dbList;
    }

    /**
     * Return list of items in database that start with certain letter.
     * 
     * @param String $letter
     * @return JSON
     */
    public function letter($letter)
    {
        $dbList = json_decode($this->getFile());
        
        $array = array_where($dbList, function ($value, $key) use ($letter) {
            return (strpos($value->name, $letter) === 0);
        });

        $output = [];

        foreach ($array as $k => $v) {
            $output[] = $v;
        }
        usort($output, array($this, "cmp"));
        
        return response()->json($output);
    }

    /**
     * Return list of databsae items that have matching areas
     * 
     * @param [String] $area
     * @param [Bool] $subject
     * @return JSON
     */
    public function area($area, $subject = null)
    {
        $area = strtolower($area);
        
        if ($area === htmlspecialchars_decode($area)) {
            $area = htmlspecialchars($area, ENT_QUOTES);
        }

        $dbList = json_decode($this->getFile());

        $array = array_where($dbList, function ($value, $key) use ($area) {
                return in_array($area, $value->categories);
        });
        
        if (isset($subject)) {
            $subject = strtolower($subject);
           
            if ($subject === htmlspecialchars_decode($subject)) {
                $subject = htmlspecialchars($subject, ENT_QUOTES);
            }
           
            $array = array_where($array, function ($value, $key) use ($subject, $area) {
                if (array_key_exists((string) $area, (array) $value->subjects)) {
                    return in_array($subject, $value->subjects->$area);
                }
            });
        }
    
        usort($array, array($this, "cmp"));
    
        return response()->json($array);
    }

    /**
     * Returns list of areas and subjects for navigation
     * 
     * @return JSON
     */
    public function navigation()
    {
        $dbList = $this->getFile();
        $dbList = json_decode($this->getFile());
        $areas = [];
        $subjects = [];
        $subjectOutput = [];
        $array = [];

        foreach ($dbList as $db) {
            foreach ($db->categories as $area) {
                $areas[] = $area;
            }

            foreach ($db->subjects as $k => $v) {
                $subjects[$k][] = $v;
            }
        }
        
        foreach ($subjects as $k => $v) {
            $subjectOutput[$k] = $v;
            $subjectOutput[$k] = array_unique(array_flatten($subjectOutput[$k]));
        }

        $areas = array_unique($areas);
        
        foreach ($areas as $k => $v) {
            $array[$v] = $v;
        }

        $array = array_merge($array, $subjectOutput);
        
        $array = array_unique($array, SORT_REGULAR);
        
        ksort($array);
        
        return $array;
    }

    /**
     * Returns list of database items based on search
     *
     * The function runs through multiple fields for each item and does a simple regex search for matches.
     *  
     * @param [string] $query
     * @return JSON
     */
    public function search($query)
    {
        $dbList = $this->getFile();
        $dbList = json_decode($this->getFile());
        $matches = [];

        foreach ($dbList as $k => $v) {
            $hit = false;
            if (preg_match("/$query/i", $v->name)) {
                $hit = true;
                $matches[$k][] = $v;
            } elseif (preg_match("/$query/i", $v->description)) {
                $hit = true;
                $matches[$k][] = $v;
            } elseif (preg_match("/$query/i", $v->metadata)) {
                $hit = true;
                $matches[$k][] = $v;
            }
            if ($hit) {
                $matches[$k] = $this->flattenArray($matches[$k]);
            }
        }

        usort($matches, array($this, "cmp"));

        return response()->json($matches);
    }

    /**
     * Grab file that is acting as database
     * 
     * @return JSON
     */
    private function getFile()
    {
        return file_get_contents(storage_path('app/azDatabase.json'));
    }

    /**
     * Returns array sorted alphabetically 
     * 
     * @param [array] $a
     * @param [array] $b
     * @return array
     */
    public function cmp($a, $b)
    {
        return strcasecmp($a->name, $b->name);
    }

    /**
     * Compresses array by one level.
     * 
     * @param [array] $array
     * @return array
     */
    private function flattenArray(array $array)
    {
        $return = array();

        array_walk_recursive($array, function ($a) use (&$return) {
            $return = $a;
        });

        return $return;
    }
}
