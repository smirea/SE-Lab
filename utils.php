<?php

  require_once 'config.php';

  /**
   * @brief check if an array is associative or not
   * @param {array} $array
   * @return {bool} the result as a boolean
   */
  function is_assoc (array $arr) {
    return array_keys($arr) !== range(0, count($arr) - 1);
  }

  /**
   * @brief Outputs a JSON with the proper headers from the given array
   * @warning This function terminates the execution (runs exit())
   * @param {array} $arr the given array
   */
  function json_output ( array $arr ) {
    if( !headers_sent() ){
      header('Cache-Control: no-cache, must-revalidate');
      header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
      header('Content-type:application/json');
      header('Content-attributes: application/json; charset=ISO-8859-15');
    }
    exit(DEBUG ? json_encode_indent($arr) : json_encode($arr));
  }

  /**
   * @brief Outputs an error JSON with the format {"error" => $message}
   * @warning This function terminates the execution (runs exit())
   * @param {string} $message the message to output
   */
  function error_output ( $message ) {
    json_output( array( 'error' => $message ) );
  }

  /**
   * @brief Check if condition is false, in which case run error_output( of_the_message )
   * @warning This function the execution (runs exit())
   * @param {bool} $bool
   * @param {string} $message what to output if the first param is false
   */
  function e_assert ( $bool, $message = "Assertion failed" ) {
    if( !$bool ){
      error_output( $message );
    }
  }

  /**
   * @brief For each key in keys, apply e_assert( isset($arr[$key]) );
   * @param {array} $arr the array to check into
   * @param {string|array} $keys either a associative array with 'keys_to_check'=>'message_to_output'
   *                              or a comma-separated string of keys (in which case a default message will be used )
   */
  function e_assert_isset ( array $arr, $keys ) {
    if( is_string( $keys ) ){
      $keys = array_map( 'trim', explode(',',$keys) );
    }
    if( !is_assoc( $keys ) ){
      $keys = array_flip( $keys );
      array_walk( $keys, function(&$item, $key){ $item = "$key not set!"; });
    }
    foreach( $keys as $k => $v ){
      e_assert( isset( $arr[$k] ), $v );
    }
  }

  /**
   * @brief Takes a mysql resource and returns a list of associative arrays
   *          with the results (one for each row)
   * @param {MySQL} $sql the resource to use
   * @return {JSON String}
   */
  function sql_to_json_output ($sql) {
    if ($sql) {
      json_output(sql_to_array($sql));
    } else {
      error_output(mysql_error());
    }
  }

  /**
   * @brief Takes a mysql resource and returns a list of associative arrays
   *          with the results (one for each row)
   * @param {MySQL} $sql the resource to use
   * @param {} $key
   * @return a list of associative arrays with the result
   */
  function sql_to_array ( $sql, $key = null ) {
    if( $sql ){
      $a = array();
      while( $r = mysql_fetch_assoc( $sql ) ){
        if( $key ){
          $a[ $r[ $key ] ] = $r;
        } else {
          $a[] = $r;
        }
      }
      return $a;
    } else {
      return array();
    }
  }

  /**
   * @brief Applies input sanitizing functions for every value in the array, recursively
   * @param {array} &$arr
   * @returns {array}
   */
  function recursive_escape (array &$arr) {
    foreach ($arr as $k => $v) {
      if (is_array($v)) {
        recursive_escape($arr[$k]);
      } else {
        $arr[$k] = mysql_real_escape_string($v);
      }
    }
    return $arr;
  }

  /**
   * Convert an object to a JSON string with indentation
   * @param  {Array} $arr
   * @param  boolean $from_array
   * @return String
   */
  function json_encode_indent ($arr) {
    $json = json_encode($arr);
    $result = '';
    $pos = 0;
    $strLen = strlen($json);
    $indentStr = '  ';
    $newLine = "\n";
    $prevChar = '';
    $outOfQuotes = true;

    for ($i=0; $i<=$strLen; $i++) {

        // Grab the next character in the string.
        $char = substr($json, $i, 1);

        // Are we inside a quoted string?
        if ($char == '"' && $prevChar != '\\') {
            $outOfQuotes = !$outOfQuotes;

        // If this character is the end of an element,
        // output a new line and indent the next line.
        } else if(($char == '}' || $char == ']') && $outOfQuotes) {
            $result .= $newLine;
            $pos --;
            for ($j=0; $j<$pos; $j++) {
                $result .= $indentStr;
            }
        }

        // Add the character to the result string.
        $result .= $char;

        // If the last character was the beginning of an element,
        // output a new line and indent the next line.
        if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
            $result .= $newLine;
            if ($char == '{' || $char == '[') {
                $pos ++;
            }

            for ($j = 0; $j < $pos; $j++) {
                $result .= $indentStr;
            }
        }

        $prevChar = $char;
    }

    return $result;
  }

  /**
   * @brief Wraps var_export into a <pre></pre> tag for nice formatting
   * @param {mixed} [$arg_n]
   */
  function v_export(){
    $args = func_get_args();
    echo '<pre>';
    foreach( $args as $arg ){
      var_export( $arg );
      echo "\n";
    }
    echo '</pre>';
  }
?>