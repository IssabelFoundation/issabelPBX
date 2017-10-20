<?php

class issabelpbx_db extends DB {
    function &connect($dsn, $options = array())
    {
        $dsninfo = DB::parseDSN($dsn);
        $type = $dsninfo['phptype'];

        if (!is_array($options)) {
            /*
             * For backwards compatibility.  $options used to be boolean,
             * indicating whether the connection should be persistent.
             */
            $options = array('persistent' => $options);
        }

        if (isset($options['debug']) && $options['debug'] >= 2) {
            // expose php errors with sufficient debug level
            include_once "DB/${type}.php";
        } else {
            @include_once "DB/${type}.php";
        }

        $classname = "DB_${type}";
        if (!class_exists($classname)) {
            $tmp = PEAR::raiseError(null, DB_ERROR_NOT_FOUND, null, null,
                                    "Unable to include the DB/{$type}.php"
                                    . " file for '$dsn'",
                                    'DB_Error', true);
            return $tmp;
        }

		include(dirname(__FILE__).'/issabelpbx_DB_extends.php');

		$classname = class_exists('issabelpbx_'.$classname) ? 'issabelpbx_'.$classname : $classname;
        @$obj = new $classname;

        foreach ($options as $option => $value) {
            $test = $obj->setOption($option, $value);
            if (DB::isError($test)) {
                return $test;
            }
        }

        $err = $obj->connect($dsninfo, $obj->getOption('persistent'));
        if (DB::isError($err)) {
            if (is_array($dsn)) {
                $err->addUserInfo(DB::getDSNString($dsn, true));
            } else {
                $err->addUserInfo($dsn);
            }
            return $err;
        }

        return $obj;
    }
}
