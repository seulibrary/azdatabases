<?php

namespace Seumunday\Azdatabases;
 
use App\Http\Controllers\Controller;
use Log;

class AzdatabasesController extends Controller
{
    public function index()
    {
        $navigation = $this->navigation();

        return view('azdatabases::index', ['navigation' => $navigation]);
    }

    public function list()
    {
        $dbList = json_decode($this->getFile());
        
        usort($dbList, array($this, "cmp"));
        
        return $dbList;
    }

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
                
                if (property_exists($value->subjects, (string) $area)) {
                    return in_array($subject, $value->subjects->$area);
                }
            });
        }

        return response()->json($array);
    }

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

        return response()->json($matches);
    }

    private function getFile()
    {
        return file_get_contents(storage_path('app/azDatabase.json'));
    }

    public function cmp($a, $b)
    {
        return strcasecmp($a->name, $b->name);
    }

    private function flattenArray(array $array)
    {
        $return = array();

        array_walk_recursive($array, function ($a) use (&$return) {
            $return = $a;
        });

        return $return;
    }
}
