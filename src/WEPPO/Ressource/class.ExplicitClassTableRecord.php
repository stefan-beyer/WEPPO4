<?php

namespace WEPPO\Ressource;

/**
 * TableRecord, für das man in der Spalte 'class'
 * explizit eine spezialisierte (Unter-)Klasse (von ExplicitClassTableRecord)
 * angeben kann.
 * 
 * Hierzu wird lediglich _dynamicBindResults überschrieben, worin die Objekte
 * aus dem DB-Result erzeugt werden.
 * 
 * @TODO der größte Teil der Funktion _dynamicBindResults wird aus dem Original
 * wiederholt. Das ist Bääh!
 */
class ExplicitClassTableRecord extends TableRecord {
    
    static $allClasses = false;
    
    public function __construct($id = 0, $cols = '*') {
        $this->class = '\\'.get_called_class();
        parent::__construct($id, $cols);
    }
    
    static function ignoreClass() {
        static::$allClasses = true;
    }
    
    static function get($numRows = null, $columns = '*') {
        if (!static::$allClasses) {
            if (!isset(static::$_where['class'])) {
                static::where('class', '\\'.get_called_class());
            }
        }
        static::$allClasses = false;
        return parent::get($numRows, $columns);
    }
    
    
    /**
     * ersetzt den Constructor für bereits existierende datensätze.
     * Erzeugung eines objektes unter berücksichtigung der explicit Class
     * wenn !id return null
     * 
     * @param type $id
     * @param type $cols
     * @return \WEPPO\Ressource\class
     */
    public static function create($id = 0, $cols = '*') {
        if (!$id) { 
            return null;
            
            # ist keine id angegeben wird kein objekt erzeugt.
            # soll ein neues 'leeres' objekt erzeugt werden, kann wohl direkt new verwendet werden
            
            # Das macht also keinen sinn:
            #$class = get_called_class();
            #return new $class();
        }
        static::where(static::getTablename() . '1.id', intval($id));
        $r = static::getOne($cols);
        if ($r) $r->afterCreation();
        return $r;
    }
    
    // create without class
    public static function createAny($id = 0, $cols = '*') {
        static::ignoreClass();
        return static::create($id, $cols);
    }
    
    static function getAny($numRows = null, $columns = '*') {
        static::ignoreClass();
        return static::get($numRows, $columns);
    }
    
    static function getAnyOne($columns = '*') {
        static::ignoreClass();
        return static::get($columns);
    }
    
    
    public function afterCreation() {
        
    }
    
    /**
     * This helper method takes care of prepared statements' "bind_result method
     * , when the number of variables to pass is unknown.
     *
     * @param mysqli_stmt $stmt Equal to the prepared statement object.
     *
     * @return array The results of the SQL fetch.
     */
    static protected function _dynamicBindResults(\mysqli_stmt $stmt, $createObjects = true) {
        $parameters = array();
        $results = array();

        $meta = $stmt->result_metadata();

        // if $meta is false yet sqlstate is true, there's no sql error but the query is
        // most likely an update/insert/delete which doesn't produce any results
        if (!$meta && $stmt->sqlstate) {
            return array();
        }
        // das hier bereitet ein result array vor in das bei statmt->fetch die werte geschrieben werden
        $row = array();
        while ($field = $meta->fetch_field()) {
            $row[$field->name] = null;
            $parameters[] = & $row[$field->name];
        }


        // avoid out of memory bug in php 5.2 and 5.3
        // https://github.com/joshcam/PHP-MySQLi-Database-Class/pull/119
        if (\version_compare(\phpversion(), '5.4', '<'))
            $stmt->store_result();

        \call_user_func_array(array($stmt, 'bind_result'), $parameters);



        if (static::createObjects()) {
            // $classname = static::getStaticClass();
            $std_classname = static::getStaticClass();
            while ($stmt->fetch()) {
                $classname = (isset($row['class']) && $row['class']) ? $row['class'] : $std_classname;
                if (!isset($row['class']) || !class_exists($classname)) {
                    $explicitClassError = 'Class '.$classname.' not found';
                    $classname = $std_classname;
                }
                $x = new $classname();
                $x->assign($row);
                if (isset($explicitClassError)) {
                    $x->explicitClassError = $explicitClassError;
                }
                static::$_count += count($row); // ????
                \array_push($results, $x);
                #} else {
                    //trigger_error('class ' . $classname . ' not found in ExplicitClassTableRecord', E_USER_ERROR);
                #}
            }

            if (static::$doResolve /* && !empty(static::$resolveForeinFields) */) {
                foreach ($results as &$r) {
                    $r->resolveForeinFields();
                }
            }
        } else {
            while ($stmt->fetch()) {
                $x = array();
                foreach ($row as $key => $val) {
                    $x[$key] = $val;
                }
                static::$_count++;
                \array_push($results, $x);
            }
        }

        static::createObjects(true);

        return $results;
    }

}

