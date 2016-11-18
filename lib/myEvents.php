<?php

    /**
     * Addon MyEvents
     * @author  kgde@wendenburg.de
     * @package redaxo 5
     * @version $Id: myEvents.php, v 2.1.0
     */
    class myEvents {

        /**
         * create insert sql query
         * @author  kgde@wendenburg.de
         * @param   {array} fieldname => array( int or string (0/1), value);
         * @param   {string} tablename
         * @return  {string} query
         */
        public static function createInsertQuery($in, $table) {
            $str_fields =  "";
            $str_vals   =  "";
            foreach ($in as $field => $val) {
                # wrap strings in '"'
                $is_str =  ($val[0] === 0) ? "\"" : "";
                $str_fields .= (strlen($str_fields)? "," : "") . "`" . $field . "`";
                $str_vals   .= (strlen($str_vals)? "," : "") . $is_str . $val[1] . $is_str;
            }
            return "insert into `" . $table . "`(" . $str_fields . ") values(" . $str_vals . ")";
        }

        /**
         * create update sql query
         * @author  kgde@wendenburg.de
         * @param   {array} fieldname => array( int or string (0/1), value);
         * @param   {string} tablename
         * @return  {string} query
         */
        public static function createUpdateQuery($in, $table, $where) {
            $str_upd =  "";
            foreach ($in as $field => $val) {
                if($val[0] === 9 || $val[0] === 2) {
                    continue;
                }
                # wrap strings in '"'
                $is_str =  ($val[0] === 0) ? "\"" : "";
                $str_upd .= (strlen($str_upd)? "," : "") . "`" . $field . "`=" . $is_str . $val[1] . $is_str;
            }
            return "update `" . $table . "` set " . $str_upd . " where " . $where;
        }
    }