<?php

namespace model;

use app\App;

class Pages extends Model
{  
  /**
   * Get count page
   *
   * @param  array $params
   * @return int
   */
  public function getCount($params = array())
  {
    $where = array();

    if (isset($params['filters'])) {
      foreach ($params['filters'] as $key => $value) {
        if ($value !== false) {
          $where[] = array($key, '=', $value);
        }
      }
    }

    $pagesCount = DB::table($this->prefix . 'pages')
      ->where($where)
      ->count();

    return $pagesCount->success ? $pagesCount->result : 0;
  }
 
  /**
   * Get all pages
   *
   * @param  array $params
   * @return array
   */
  public function getAll($params = array())
  {
    $limit = $params['limit'];
    $offset = isset($params['listId']) ? $limit * $params['listId'] : 0;

    $where = array();

    if (isset($params['filters'])) {
      foreach ($params['filters'] as $key => $value) {
        if ($value !== false) {
          $where[] = array($key, '=', $value);
        }
      }
    }

    $pages = DB::table($this->prefix . 'pages')
      ->select('id', 'url', 'bind_id', 'title', 'count_main', 'count_answer')
      ->where($where)
      ->limit($limit)
      ->offset($offset)
      ->get();

    return $pages->success ? $pages->result : array();
  }
 
  /**
   * Get page by ID
   *
   * @param  int $id
   * @return array
   */
  public function getById($id)
  {
    $page = DB::table($this->prefix . 'pages')
      ->select('id', 'url', 'bind_id', 'title', 'count_main', 'count_answer')
      ->where('id', $id)
      ->first();

    return $page->success ? $page->result : array();
  }
 
  /**
   * Update page by id
   *
   * @param  int $id
   * @param  array $set
   * @return bool
   */
  public function set($id, $set)
  {
    $update = DB::table($this->prefix . 'pages')
      ->set($set)
      ->where('id', (int) $id)
      ->update();

    return $update->success && $update->result->affected_rows !== -1 ? true : false;
  }

  /**
   * Delete page by IDs
   *
   * @param  array $idsList
   * @return bool
   */
  public function delete($idsList)
  {
    $delete = DB::table($this->prefix . 'pages')
      ->whereIn('id', $idsList)
      ->whereAnd(array(
        array('count_main', '<', 1),
        array('count_answer', '<', 1),
      ))
      ->delete();

    return $delete->success && $delete->result->affected_rows > 0 ? true : false;
  }
}
