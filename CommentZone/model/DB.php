<?php

namespace model;

use Exception;
use mysqli;

class DB
{
  protected $mysqli;
  protected $sql = array();
  protected $bindParams = array();

  public static function create($table, $collback)
  {
    $self = new self();
    return $self->createTable($table, $collback);
  }

  public function createTable($table, $collback)
  {
    $this->sql['action'] = 'create';
    $this->sql['create_table'] = $table;
    $collback($this);

    $create = $this->execute();

    return $create;
  }

  public function addColumn($name, $type)
  {
    $this->sql['createName'] = $name;
    $this->sql['create'][$name] = array(
      'type' => $type,
      'null' => 'NOT NULL',
    );

    return $this;
  }

  public function id($name = 'id', $count = null)
  {
    $this->addColumn($name, $count ? "INT($count)" : "INT");

    $this->sql['create'][$name]['index'] = 'PRIMARY KEY';
    $this->sql['create'][$this->sql['createName']]['auto_increment'] = 'AUTO_INCREMENT';

    return $this;
  }

  public function int($name, $count = null)
  {
    $this->addColumn($name, $count ? "INT($count)" : "INT");

    return $this;
  }

  public function binary($name, $count = 16)
  {
    $this->addColumn($name, "BINARY ($count)");

    return $this;
  }

  public function string($name, $count = 255)
  {
    $this->addColumn($name, "VARCHAR($count)");

    return $this;
  }

  public function text($name)
  {
    $this->addColumn($name, "text");

    return $this;
  }

  public function json($name)
  {
    $this->addColumn($name, "json");

    return $this;
  }

  public function timestamp($name)
  {
    $this->addColumn($name, "timestamp");

    return $this;
  }

  public function datetime($name)
  {
    $this->addColumn($name, "datetime");

    return $this;
  }

  public function comment($prop)
  {
    $this->sql['create'][$this->sql['createName']]['comment'] = "COMMENT '$prop'";

    return $this;
  }

  public function defaultValue($prop)
  {
    if ($prop === null || strtoupper($prop) === 'NULL') {
      $this->sql['create'][$this->sql['createName']]['null'] = "NULL";
    } else {
      $this->sql['create'][$this->sql['createName']]['default'] = "DEFAULT " . (is_integer($prop) || strtoupper($prop) === 'CURRENT_TIMESTAMP' ? $prop : "'$prop'");
    }

    return $this;
  }

  public function primary()
  {
    $this->sql['create'][$this->sql['createName']]['index'] = 'PRIMARY KEY';

    return $this;
  }

  public function auto_increment()
  {
    $this->sql['create'][$this->sql['createName']]['auto_increment'] = 'AUTO_INCREMENT';

    return $this;
  }

  public function nullable()
  {
    $this->sql['create'][$this->sql['createName']]['null'] = "NULL";
    return $this;
  }

  public function index()
  {
    $this->sql['create_index']['index'][] = "`" . $this->sql['createName'] . "`";

    return $this;
  }

  public function unique()
  {
    $this->sql['create_index']['unique'][] = "`" . $this->sql['createName'] . "`";

    return $this;
  }

  public function fulltext()
  {
    $this->sql['create_index']['fulltext'][] = "`" . $this->sql['createName'] . "`";

    return $this;
  }

  public static function table($table, $as = '')
  {
    $self = new self();
    return $self->from($table, $as);
  }

  public function from($table, $as = '')
  {
    $this->sql['table'][] = $table;
    $this->sql['table'][] = $as;

    return $this;
  }

  public function select($col = array('*'))
  {
    $this->sql['action'] = 'select';

    $col = is_null($col) || empty($col) ? array('*') : (is_array($col) ? $col : func_get_args());

    $this->sql['select'] = $col;

    return $this;
  }

  public function set($set)
  {
    $this->sql['set'] = $set;

    return $this;
  }

  public function values($col, $values)
  {
    $this->sql['values'] = array(
      'c' => $col,
      'v' => $values
    );

    return $this;
  }

  public function where($column, $operator = null, $value = null)
  {
    if (!empty($column)) {
      $this->sql['where'][] = array(
        'column' => $column,
        'operator' => $operator,
        'value' => $value
      );
    }

    return $this;
  }

  public function whereAnd($column, $operator = null, $value = null)
  {
    if (!empty($column)) {
      $this->sql['whereAnd'][] = array(
        'column' => $column,
        'operator' => $operator,
        'value' => $value
      );
    }

    return $this;
  }

  public function whereOr($column, $operator = null, $value = null)
  {
    if (!empty($column)) {
      $this->sql['whereOr'][] = array(
        'column' => $column,
        'operator' => $operator,
        'value' => $value
      );
    }

    return $this;
  }

  public function whereIn($col, $list)
  {
    // if (is_array($list)) {
    //   $list = array_map(function ($a) {
    //     return is_string($a) ? "$a" : $a;
    //   }, $list);
    // }

    $this->sql['whereIn'][] = array(
      $col, 'IN', $list
    );

    return $this;
  }

  public function whereNotIn($col, $list)
  {
    if (is_array($list)) {
      $list = array_map(function ($a) {
        return is_string($a) ? "'$a'" : $a;
      }, $list);
    }

    $this->sql['whereNotIn'][] = array(
      $col, 'NOT IN', $list
    );

    return $this;
  }

  public function leftJoin($table, $param1, $exp = null, $param2 = null)
  {
    $this->sql['leftJoin'][] = array(
      'table' => $table,
      'exp' => $exp !== null && $param2 !== null ? array($param1, $exp, $param2) : $param1,
    );

    return $this;
  }

  public function orderBy($col, $sort = '')
  {
    if (is_array($col)) {
      $orderStr = array();
      foreach ($col as $value) {
        $orderStr[] = $value[0] . ' ' . $value[1];
      }
      $this->sql['orderBy'][] = implode(' ', $orderStr);
    } else {
      $this->sql['orderBy'][] = $col . ' ' . $sort;
    }

    return $this;
  }

  public function limit($num)
  {
    $this->sql['limit'] = $num;

    return $this;
  }

  public function offset($num)
  {
    $this->sql['offset'] = $num;

    return $this;
  }

  public function get()
  {
    if (!isset($this->sql['select'])) {
      $this->select();
    }

    $select = $this->execute();

    if (!$select->success) {
      return $select;
    }

    $result = $select->result->get_result();

    $data = array();

    if ($result->num_rows > 0) {
      while ($a = $result->fetch_assoc()) {
        $data[] = $a;
      }
    }

    return (object) array(
      'success' => true,
      'result' => $data,
      'error' => null,
    );
  }

  public function first()
  {
    if (!isset($this->sql['select'])) {
      $this->select();
    }

    $this->sql['limit'] = 1;

    $select = $this->execute();

    if (!$select->success) {
      return $select;
    }

    $result = $select->result->get_result();

    $data = array();

    if ($result->num_rows > 0) {
      $data = $result->fetch_assoc();
    }

    return (object) array(
      'success' => true,
      'result' => $data,
      'error' => null,
    );
  }

  public function countId() {
    return $this->count('id');
  }

  public function count($arg = '*')
  {
    if (!isset($this->sql['select'])) {
      $this->select(array('count' => "COUNT($arg)"));
    }

    $select = $this->execute();

    if (!$select->success) {
      return $select;
    }

    $result = $select->result->get_result();
    $data = $result->fetch_assoc();

    return (object) array(
      'success' => true,
      'result' => $data['count'],
      'error' => null,
    );
  }

  /**
   * @return object
   */
  public function update()
  {
    $this->sql['action'] = 'update';

    $update = $this->execute();

    return $update;
  }

  /**
   * @return object
   */
  public function insert()
  {
    $this->sql['action'] = 'insert';

    $insert = $this->execute();

    return $insert;
  }

  /**
   * @return object    
   */
  public function delete()
  {
    $this->sql['action'] = 'delete';

    $delete = $this->execute();

    return $delete;
  }

  /**
   * @return object    
   */
  public function insertOrUpdate()
  {
    $this->sql['action'] = 'insertOrUpdate';

    $insert = $this->execute();

    return $insert;
  }

  /**
   * @return object    
   */
  public function insertGetId()
  {
    $this->sql['action'] = 'insert';

    $insert = $this->execute();

    if (!$insert->success) {
      return $insert;
    }

    return (object) array(
      'success' => true,
      'result' => $insert->result->insert_id,
      'error' => null,
    );
  }

  public function truncate()
  {
    $this->sql['action'] = 'truncate';

    $truncate = $this->execute();

    return $truncate;
  }


  public function dump()
  {
    $select = $this->processing();

    return $select;
  }

  protected function bindParams($param)
  {
    $bind_params = array('');

    if ($param !== null) {
      $bind_params[0] .= is_int($param) ? 'i' : 's';
      $bind_params[] = &$param;
    }

    return $bind_params;
  }

  protected function bindParamsMerge($arr1, $arr2)
  {
    $data = array('');

    if (empty($arr1) && empty($arr2)) {
      return $data;
    }

    $data[0] .= array_shift($arr1) . array_shift($arr2);
    $data = array_merge($data, array_merge($arr1, $arr2));

    return $data;
  }

  protected function whereObjectToStr($a)
  {
    $subSql = $a->processing();
    $this->bindParams = $this->bindParamsMerge($this->bindParams, $subSql['bindParams']);

    return $subSql['sqlStr'];
  }

  protected function whereArrayToStr($a)
  {
    $data = '';

    if (is_array($a[0])) {
      $a[0] = $this->whereArrayToStr($a[0]);
    }

    if (isset($a[2])) {
      if (strtoupper($a[1]) === 'IN' || strtoupper($a[1]) === 'NOT IN' || strtoupper($a[1]) === 'IS') {
        if (is_array($a[2])) {
          foreach ($a[2] as &$value) {
            $this->bindParams = $this->bindParamsMerge($this->bindParams, $this->bindParams($value));
            $value = ($value === null ? "NULL" : "?");
          }
          $a[2] = '(' . implode(',', $a[2]) . ')';
        } elseif ($a[2] instanceof DB) {
          $a[2] = '(' . $this->whereObjectToStr($a[2]) . ')';
        }
      } elseif (strtoupper($a[1]) === 'BETWEEN') {
        $this->bindParams = $this->bindParamsMerge($this->bindParams, $this->bindParams($a[2][0]));
        $a[2][0] = ($a[2][0] === null ? "NULL" : "?");
        $this->bindParams = $this->bindParamsMerge($this->bindParams, $this->bindParams($a[2][1]));
        $a[2][1] = ($a[2][1] === null ? "NULL" : "?");

        $a[2] = implode(' AND ', $a[2]);
      } else {
        if (is_array($a[2])) {
          $a[2] = $this->whereArrayToStr($a[2]);
        } elseif ($a[2] instanceof DB) {
          $a[2] = '(' . $this->whereObjectToStr($a[2]) . ')';
        } else {
          $this->bindParams = $this->bindParamsMerge($this->bindParams, $this->bindParams($a[2]));
          $a[2] = ($a[2] === null ? "NULL" : "?");
        }
      }

      $data = implode(' ', $a);
    } else {
      if (is_array($a[1])) {
        $a[1] = $this->whereArrayToStr($a[1]);
      } elseif ($a[1] instanceof DB) {
        $a[1] = '(' . $this->whereObjectToStr($a[1]) . ')';
      } else {
        $this->bindParams = $this->bindParamsMerge($this->bindParams, $this->bindParams($a[1]));
        $a[1] = ($a[1] === null ? "NULL" : "?");
      }

      $data = implode(' = ', $a);
    }

    return $data;
  }

  protected function whereExp($args)
  {
    $str = '';

    foreach ($args as $key => $value) {
      if (is_array($value)) {
        $a[] = '(' . $this->whereArrayToStr($value) . ')';
      } elseif ($value instanceof DB) {
        $a[] = $this->whereObjectToStr($value);
      } else {
        if (is_string($key)) {
          $this->bindParams = $this->bindParamsMerge($this->bindParams, $this->bindParams($value));
          $a[] = '(' . $key . ' = ' . ($value === null ? "NULL" : "?") . ')';
        } else {
          $a[] = $value;
        }
      }
    }

    if (isset($args[1]) && is_string($args[1])) {
      if (in_array(strtoupper($args[1]), array('OR', 'IN', 'NOT IN', 'LIKE', 'NOT LIKE', '>', '='))) {
        $str = '(' . implode(' ', $a) . ')';
      } else {
        $str = implode(' AND ', $a);
      }
    } else {
      $str = implode(' AND ', $a);
    }

    return $str;
  }

  public function processing()
  {
    $sqlStr = '';

    if ($this->sql['action'] === 'create') {
      if (isset($this->sql['create_table'])) {
        $sqlStr .= "CREATE TABLE IF NOT EXISTS " . $this->sql['create_table'];
      }

      if (isset($this->sql['create'])) {
        if (is_array($this->sql['create'])) {
          $sqlStr .= "(" . PHP_EOL;

          $cols = array();
          foreach ($this->sql['create'] as $key => $elem) {
            $cols[] = "`$key` " . implode(' ', $elem);
          }

          $sqlStr .= implode(',' . PHP_EOL, $cols);

          if (isset($this->sql['create_index']) && is_array($this->sql['create_index'])) {
            $indexes = array();

            if (isset($this->sql['create_index']['index'])) {
              $indexes[] = "INDEX `INDEX KEY` (" . implode(', ', $this->sql['create_index']['index']) . ")";
            }
            if (isset($this->sql['create_index']['unique'])) {
              $indexes[] = "UNIQUE `UNIQUE KEY` (" . implode(', ', $this->sql['create_index']['unique']) . ")";
            }
            if (isset($this->sql['create_index']['fulltext'])) {
              $indexes[] = "FULLTEXT `FULLTEXT KEY` (" . implode(', ', $this->sql['create_index']['fulltext']) . ")";
            }

            $sqlStr .= "," . PHP_EOL . implode(',' . PHP_EOL, $indexes) . PHP_EOL;
          }
          $sqlStr .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        }
      }
    }

    if ($this->sql['action'] === 'select') {
      if (isset($this->sql['select'])) {
        $select = '';
        foreach ($this->sql['select'] as $key => $value) {
          if (is_string($value)) {
            $select .= $value . (is_string($key) ? " AS $key" : "") . ', ';
          }
          if ($value instanceof DB) {
            $subSql = $value->processing();
            $this->bindParams = $this->bindParamsMerge($this->bindParams, $subSql['bindParams']);
            $select .= "(" . $subSql['sqlStr'] . ")" . (is_string($key) ? " AS $key" : "") . ', ';
          }
        }

        $sqlStr .= "SELECT " . trim($select, ', ') . " FROM ";
      }
    }

    if ($this->sql['action'] === 'insert' || $this->sql['action'] === 'insertOrUpdate') {
      $sqlStr .= "INSERT INTO ";
    }

    if ($this->sql['action'] === 'update') {
      $sqlStr .= "UPDATE ";
    }

    if ($this->sql['action'] === 'delete') {
      $sqlStr .= "DELETE FROM ";
    }

    if ($this->sql['action'] === 'truncate') {
      $sqlStr .= "TRUNCATE TABLE ";
    }

    if (isset($this->sql['table'])) {
      $data = '';
      if ($this->sql['table'][0] instanceof DB) {
        $subSql = $this->sql['table'][0]->processing();
        $this->bindParams = $this->bindParamsMerge($this->bindParams, $subSql['bindParams']);
        $data .= "(" . $subSql['sqlStr'] . ")" . (!empty($this->sql['table'][1]) ? " AS " . $this->sql['table'][1] : "");
      } else {
        $preffix = '';
        $data .= $preffix . $this->sql['table'][0] . (!empty($this->sql['table'][1]) ? " AS " . $this->sql['table'][1] : "");;
      }

      $sqlStr .= $data;
    }

    if (isset($this->sql['set'])) {
      $data = array();

      foreach ($this->sql['set'] as $key => $value) {
        if ($value instanceof DB) {
          $subSql = $value->processing();
          $this->bindParams = $this->bindParamsMerge($this->bindParams, $subSql['bindParams']);
          $data[] = $key . ' = (' . $subSql['sqlStr'] . ')';
        } elseif (is_array($value)) {
          $data[] = "$key = " . $value[0] . " " . $value[1] . " " . ($value[2] === null ? "NULL" : "?");
          $this->bindParams = $this->bindParamsMerge($this->bindParams, $this->bindParams($value[2]));
        } else {
          if (is_string($key)) {
            $data[] = $key . " = " . ($value === null ? "NULL" : "?");
            $this->bindParams = $this->bindParamsMerge($this->bindParams, $this->bindParams($value));
          } else {
            $data[] = $value;
          }
        }
      }

      $sqlStr .= " SET " . implode(', ', $data);

      if ($this->sql['action'] === 'insertOrUpdate') {
        $this->bindParams = $this->bindParamsMerge($this->bindParams, $this->bindParams);
        $sqlStr .= " ON DUPLICATE KEY UPDATE " . implode(', ', $data);
      }
    }

    if (isset($this->sql['values'])) {
      $data = array();

      if (is_array($this->sql['values']['c'])) {
        $data['col'] = implode(',', $this->sql['values']['c']);
      } else {
        $data['col'] = $this->sql['values']['c'];
      }

      if (is_array($this->sql['values']['v'])) {
        $a = array();
        foreach ($this->sql['values']['v'] as $val) {
          if (is_array($val)) {
            $b = array();
            foreach ($val as $v) {
              $b[] = ($v === null ? "NULL" : "?");
              $this->bindParams = $this->bindParamsMerge($this->bindParams, $this->bindParams($v));
            }
            $a[] = '(' . implode(',', $b) . ')';
          } else {
            $a[] = ($val === null ? "NULL" : "?");
            $this->bindParams = $this->bindParamsMerge($this->bindParams, $this->bindParams($val));
          }
        }

        if (is_array($val)) {
          $data['value'] = implode(',', $a);
        } else {
          $data['value'] = '(' . implode(',', $a) . ')';
        }
      } else {
        $data['value'] = '(' . ($this->sql['values']['v'] === null ? "NULL" : "?") . ')';
        $this->bindParams = $this->bindParamsMerge($this->bindParams, $this->bindParams($this->sql['values']['v']));
      }

      $sqlStr .= " (" . $data['col'] . ") VALUES " . $data['value'];

      if ($this->sql['action'] === 'insertOrUpdate') {
        $updateStr = '';

        if (is_array($this->sql['values']['c'])) {
          $v = array();
          foreach ($this->sql['values']['c'] as $key => $value) {
            $v[] = $value . ' = VALUES(' . $value . ')';
          }
          $updateStr = implode(',', $v);
        } else {
          $updateStr .= $this->sql['values']['c'] . ' = VALUES(' . $this->sql['values']['c'] . ')';
        }

        $sqlStr .= " ON DUPLICATE KEY UPDATE $updateStr";
      }
    }

    if (isset($this->sql['leftJoin'])) {
      foreach ($this->sql['leftJoin'] as $key => $value) {
        $sqlStr .= ' LEFT JOIN ' . $value['table'] . ' ON ';

        if (is_array($value['exp'])) {
          if (count($value['exp']) === 3 && is_string($value['exp'][0]) && is_string($value['exp'][1]) && is_string($value['exp'][2]) ) {
            $sqlStr .= $value['exp'][0] . ' ' . $value['exp'][1] . ' ' . $value['exp'][2];
          } else {
            $exp = array();
            foreach ($value['exp'] as $val) {
              if (count($val) === 3) {
                $exp[] = $val[0] . ' ' . $val[1] . ' ' . $val[2];
              } elseif (count($val) === 2) {
                $exp[] = $val[0] . ' = ' . $val[2];
              } else {
                $exp[] = $val[0];
              }
            }
            $sqlStr .= '(' . implode(' AND ', $exp) . ')';
          }
        } else {
          $sqlStr .= $value['exp'];
        }
      }
    }

    if (isset($this->sql['where'])) {
      $exp = array();
      foreach ($this->sql['where'] as $where) {
        if (is_array($where['column'])) {
          $args = $this->whereExp($where['column']);
        } else {
          if (isset($where['value']) || isset($where['operator'])) {
            $args = $this->whereExp(
              array(
                array(
                  $where['column'],
                  ($where['value'] ? $where['operator'] : '='),
                  ($where['value'] ? $where['value'] : $where['operator'])
                )
              )
            );
          } else {
            $args = $where['column'];
          }
        }

        $exp[] = $args;
      }

      $sqlStr .= ' WHERE ' . implode(' AND ', $exp);
    }

    if (isset($this->sql['whereIn'])) {
      $sqlStr .= (isset($this->sql['where']) ? ' AND ' : ' WHERE ');

      $exp = array();
      foreach ($this->sql['whereIn'] as $whereIn) {
        $in = '';
        if (is_array($whereIn[2])) {
          $a = array();
          foreach ($whereIn[2] as $key => $value) {
            $a[] = '?';
            $this->bindParams = $this->bindParamsMerge($this->bindParams, $this->bindParams($value));
          }
          $in = implode(',', $a);
        } else {
          $in = $whereIn[2];
        }
        $exp[] = $whereIn[0] . ' ' . $whereIn[1] . ' (' . $in . ')';
      }

      $sqlStr .= implode(' AND ', $exp);
    }

    if (isset($this->sql['whereNotIn'])) {
      $sqlStr .= (isset($this->sql['where']) ? ' AND ' : ' WHERE ');

      $exp = array();
      foreach ($this->sql['whereNotIn'] as $whereNotIn) {
        $in = '';
        if (is_array($whereNotIn[2])) {
          $a = array();
          foreach ($whereNotIn[2] as $key => $value) {
            $a[] = '?';
            $this->bindParams = $this->bindParamsMerge($this->bindParams, $this->bindParams($value));
          }
          $in = implode(',', $a);
        } else {
          $in = $whereNotIn[2];
        }
        $exp[] = $whereNotIn[0] . ' ' . $whereNotIn[1] . ' (' . $in . ')';
      }

      $sqlStr .= implode(' AND ', $exp);
    }

    if (isset($this->sql['whereAnd'])) {
      $exp = array();
      foreach ($this->sql['whereAnd'] as $whereAnd) {
        if (is_array($whereAnd['column'])) {
          $args = $this->whereExp($whereAnd['column']);
        } else {
          if (isset($whereAnd['value']) || isset($whereAnd['operator'])) {
            $args = $this->whereExp(
              array(
                array(
                  $whereAnd['column'],
                  ($whereAnd['value'] ? $whereAnd['operator'] : '='),
                  ($whereAnd['value'] ? $whereAnd['value'] : $whereAnd['operator'])
                )
              )
            );
          } else {
            $args = $whereAnd['column'];
          }
        }

        $exp[] = $args;
      }

      $sqlStr .= ' AND ' . implode(' AND ', $exp);
    }

    if (isset($this->sql['whereOr'])) {
      $exp = array();
      foreach ($this->sql['whereOr'] as $whereOr) {
        if (is_array($whereOr['column'])) {
          $args = $this->whereExp($whereOr['column']);
        } else {
          if (isset($whereOr['value']) || isset($whereOr['operator'])) {
            $args = $this->whereExp(
              array(
                array(
                  $whereOr['column'],
                  ($whereOr['value'] ? $whereOr['operator'] : '='),
                  ($whereOr['value'] ? $whereOr['value'] : $whereOr['operator'])
                )
              )
            );
          } else {
            $args = $whereOr['column'];
          }
        }

        $exp[] = $args;
      }

      $sqlStr .= ' OR ' . implode(' OR ', $exp);
    }

    if (isset($this->sql['orderBy'])) {
      $sqlStr .= ' ORDER BY ' . implode(',', $this->sql['orderBy']);
    }

    if (isset($this->sql['limit'])) {
      $sqlStr .= ' LIMIT ' . $this->sql['limit'];
    }

    if (isset($this->sql['offset'])) {
      $sqlStr .= ' OFFSET ' . $this->sql['offset'];
    }

    return array(
      'sqlStr' => $sqlStr,
      'bindParams' => $this->bindParams,
    );
  }

  protected function execute()
  {
    $params = require dirname(__DIR__) . '/config/db.php';
    $mysqli = @new mysqli($params['host'], $params['user'], $params['password'], $params['base_name']);

    if ($mysqli->connect_errno > 0) {
      return (object) array(
        'success' => false,
        'error' => 'MySql: ' . $mysqli->connect_error
      );
    }

    $mysqli->set_charset($params['charset']);

    $this->mysqli = $mysqli;

    $sql = $this->processing();

    $query = $this->mysqli->prepare($sql['sqlStr']);

    if ($this->mysqli->errno > 0) {
      return (object) array(
        'success' => false,
        'error' => 'MySql: ' . $this->mysqli->error
      );
    }

    if (!empty($sql['bindParams'])) {
      $type = array_shift($sql['bindParams']);
      $params = $sql['bindParams'];
      $query->bind_param($type, ...$params);
    }

    $query->execute();

    if ($this->mysqli->errno > 0) {
      return (object) array(
        'success' => false,
        'error' => $this->mysqli->error
      );
    }

    return (object) array(
      'success' => true,
      'result' => $query,
      'error' => null,
    );
  }
}
