<?php

class DbHelper {
  
  /**
   * Renvoie une nouvelle clé
   * @access public
   * @param string nom de la table
   * @param string nom de la clé première
   * @return int la nouvelle clé si l'opération à réussie, null sinon
   */
  public static function getNewKey($table, $pk) {
    $newKey = null;
    $db = Db::getInstance();
    $sql = 'select IFNULL(max('.$pk.'),0)+1 newcod from '.$table;
    $statement = $db->prepare($sql);
    if ($statement != false) {
      $statement->execute();
      foreach($statement as $row) {
        $newKey = $row['newcod'];
      }
    }
    return $newKey;
  }
  
  public static function getNextVal($table) {
    $val = null;
    $sql = "SELECT nextval($table.'_seq')";
    $db = Db::getInstance();
    $result = $db->query($sql);
    $result->setFetchMode(PDO::FETCH_NUM);
    if ($data = $result->fetch()) {
      $val = $data[0];
    }
    return $val;
  }
  
  /**
   * Retourne la liste des colonnes formatté pour une requête sql
   * @param array $cols
   * @param string $prefix
   * @return string
   */
  
  public static function getSqlListCols(array $cols = null, $prefix = null) {
    if (!$cols) {
      return "";
    }
    if ($prefix) {
      $GLOBALS['DbHelper/getSqlListCols/prefix'] = $prefix;
      return implode(',', array_map(function($col){return $GLOBALS['DbHelper/getSqlListCols/prefix'] . '.' . $col; }, $cols));
    } else {
      return implode(',', $cols);
    }
  }
  
  /**
   * Savoir si une clé est enrgistré dans une table ou non
   * @access public
   * @param string $valeur valeur à tester
   * @param string $table nom de la table
   * @param string $keyName nom de la clé
   * @return int la nouvelle clé si l'opération à réussie, null sinon
   */
  public static function isExistingKey($value, $table, $keyName) {
    return self::selectCount($table, array($keyName => $value)) > 0;
  }
  
  
  /**
   * Insère une ligne dans la table
   * @access public
   * @param array $data : 
   *   clé = nom de la colonne, valeur = la valeur à mettre en db ($isMultilpe = false)
   *   ou array (array) chaque élément correspondant à une ligne à insérer ($isMultilpe = true)
   * @param string $table : le nom de la table
   * @param $isMultilpe : true si on souhaite insérer plusieurs ligne à la fois false sinon
   * @param int 
   * @return boolean true si la ligne a bien été insérée, false sinon
   */
  public static function insert($data, $table, $isMultilpe = false) {
    $insert = false;
    $sql = 'insert into '.$table;
    if (!$isMultilpe) {
      $sql .= ' ( '.implode(', ' ,array_keys($data)).') ';
      $sql .= ' values ( :'.implode(', :' ,array_keys($data)).');';
      $params = $data;
    } else {
      $params = array();
      $sql .= ' ( '.implode(', ' ,array_keys($data[0])).') values ';
      $sqlLines = array();
      for ($i = 0; $i < count($data); $i++) {
        $ligne = $i + 1;
        $rowData = $data[$i];
        $rowSql = array();
        foreach ($rowData as $col => $value) {
          $params[$col . $ligne] = $value;
          $rowSql[] = ":" . $col . $ligne;
        }
        $sqlLines[] = "(" . implode(",", $rowSql) . ")";
      }
      $sql .= " ". implode(",", $sqlLines) . " ;";
    }
    $db = Db::getInstance();
    $statement = $db->prepare($sql);
    if ($statement != false) {
      $insert = $statement->execute($params);
    }
    return $insert;
  }
  
  
  /**
   * Supprime une ou plusieurs ligne en db,
   * Par mesure de sécurité si plus d'une ligne est trouvée la suppression n'est pas effectuée 
   * si le paramètre $single n'est pas explicitement déclaré false
   * @access public
   * @param string $table : le nom de la table
   * @param: array $where : clé = nom de la colonne, valeur = valeur de la colonne
   * @param: boolean $single : true : une seule ligne ne sera supprimée false, autorise la suppression de plusieurs lignes.
   * @return boolean true si la ligne a bien été supprimée, false sinon
   */
  public static function delete($table, array $where, $single = true) {
    $isDeleted = false;
    if(!$single || self::selectCount($table, $where) == 1) {
      $db = Db::getInstance();
      $sql = 'delete from '.$table;
      $sql .= ' where '.implode(' and ', array_map(function($key){return $key.'=:'.$key;}, array_keys($where))).';';
      $statement = $db->prepare($sql);
      if ($statement != false) {
        $isDeleted = $statement->execute($where);
      }
      return $isDeleted;
    }
  }
  
  /**
   * Permet d'effectuer un select (sans ordre, sur une seule table)
   * @access public
   * @param: string | array $table : 
   *   string : nom de la table
   *   array : clé nom d'une table / valeur (array) liste des clés premières communes entre les tables
   * @param: array[optional] $cols : nom des colonnes à renvoyer dans un tableau simple (si plusieurs tables : spécifier la table d'appartenance de la colonne)
   * @param: array[optional] $where : clé = nom de la colonne, valeur = valeur de la colonne
   * @param: array[optional] $order : clé = nom de la colonne, valeur = key word ASC or DESC or ''
   * @return: array données renvoyées par la requête, chaque élément est un array qui corespond à une ligne de la réponse sql,
   *  retour null si la requête s'est mal passée
   */
  public static function select($table, $cols, $where = null, $order = null) {
    $sql = 'select '. self::getSqlCols($cols) . self::getSqlFrom($table) . self::getSqlWhere($table, $where) . self::getSqlOrder($order) . ';';
    $db = Db::getInstance();
    $statement = $db->prepare($sql);
    if ($statement != false && $statement->execute($where) != false) {
      $out = $statement->fetchAll(PDO::FETCH_ASSOC);
      return $out && count($out) > 0 ? $out : null;
    } else {
      return null;
    }
  }
  
  /**
   * Renvoie la liste des colonnes pour une requête sql
   * @param array $cols
   */
  
  private static function getSqlCols(array $cols = null) {
    return empty($cols) ? ' * ' : ' '. implode(', ', $cols) .' ';
  }
  
  /**
   * Renvoie la condition from à partir du paramètre array
   * @param string | array $table
   */
  
  private static function getSqlFrom($table) {
    if(is_string($table)) {
      return ' from '. $table . ' ';
    } else if(is_array($table)) {
      return ' from '.implode(', ', array_keys($table)) . ' ';
    }
  }
  
  /**
   * Renvoie la where clause (liste des condtions) d'une requête sql automatique
   * @param string | array $table
   * @param array $where
   */
  
  private static function getSqlWhere($table, $where = null) {
    if(empty($where)) {
      return (is_string($table)) ? ' ' : ' where ' . self::getPrimaryKeysConditions($table);
    } else {
      $sql = ' where '. implode(' and ', array_map(function($key){return $key.'=:'.$key;}, array_keys($where))) . ' ';
      return (is_string($table)) ? $sql : $sql. ' and '. self::getPrimaryKeysConditions($table);
    }
  }
  
  /**
   * Renvoie la liste des égalités de clé primaire
   * @param array $table
   * @return string
   */
  
  private static function getPrimaryKeysConditions(array $table) {
    $equals = array();
    foreach ($table as $tableName => $primaryKeys) {
      foreach ($primaryKeys as $pk) {
        foreach ($table as $tn => $pks) {
          if($tn != $tableName && in_array($pk, $pks) && !in_array(array($pk, $tableName, $tn), $equals) && !in_array(array($pk, $tn, $tableName), $equals)) {
            $equals[] = array($pk, $tableName, $tn);
          }
        }
      }
    }
    return implode(" and ", array_map(function($equal){return $equal[1].'.'.$equal[0] . " = ". $equal[2].'.'.$equal[0];}, $equals));
  }
  
  /**
   * Renvoie la clause order by d'une requête sql
   * @param unknown $order
   */
  
  private static function getSqlOrder(array $order = null) {
    return !empty($order) ? ' order by '.implode(', ', array_map(function($key, $value){return $key.' '.$value;}, array_keys($order), array_values($order))). ' ' : ' ';
  }
  
  /**
   * Permet d'effectuer un select count sur une table
   * @access public
   * @param: array[optional] $where : clé = nom de la colonne, valeur = valeur de la colonne
   * @param: string $table : nom de la table
   * @return: int le nombre de count si la query se passe bien.
   */
  public static function selectCount($table, $where) {
    $sql = 'select count(*) ' . self::getSqlFrom($table) . self::getSqlWhere($table, $where) . ';';
    $db = Db::getInstance();
    $statement = $db->prepare($sql);
    if ($statement !== false && $statement->execute($where) !== false) {
       return $statement->fetchColumn();
    } else {
      return 0;
    }
  }
  
  /**
   * Permet d'effectuer un update sur une ou plusieurs lignes dans une table
   * @access public static
   * @param: array $values : clé = nom de la colonne, valeur = valeur de la colonne
   * @param: array $where :  clé = nom de la colonne, valeur = valeur de la colonne
   * @param: string $table : nom de la table
   * @return: int nombre de lignes modifiées si la query se passe bien, null sinon
   */
  public static function update($table, $values, $where) {
    $sql = 'update '.$table;
    $sql .= ' set '.implode(', ', array_map(function($key){return $key.'=?';}, array_keys($values)));
    $sql .= ' where '.implode(' and ', array_map(function($key){return $key.'=?';}, array_keys($where))).';';
    $db = Db::getInstance();
    $statement = $db->prepare($sql);
    if ($statement != false && $statement->execute(array_merge(array_values($values), array_values($where))) === true) {
      return $statement->rowCount();
    } else {
      return null;
    }
  }
  
  /**
   * Execute une requête sql de manière sécurisée
   * On veillera à préparer la requête correctement et a encapsuler les variables dans params
   * @param string $sql
   * @param array $params : 
   *  $key : nom de la variable dans la requête sql
   *  $value : valeur de la variable
   * @return array | NULL
   */
  
  public static function exec($sql, array $params = null) {
    $db = Db::getInstance();
    $statement = $db->prepare($sql);
    if ($statement != false && $statement->execute($params) != false) {
      return $statement->fetchAll(PDO::FETCH_ASSOC);
    } else {
      return null;
    }
  }
  
}