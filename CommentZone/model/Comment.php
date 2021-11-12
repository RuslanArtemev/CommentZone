<?php

namespace model;

use app\App;
use app\Helper;

class Comment extends Model
{ 
  /**
   * Write page
   *
   * @param  string $url
   * @param  string $bindId
   * @param  string $title
   * @return int insert_id or 0
   */
  public function writePage($url, $bindId, $title)
  {
    $urlArr = Helper::urlParams($url);

    $bindId = empty($bindId) || $bindId === 0 ? null : $bindId;

    $pageId = DB::table($this->prefix . 'pages')
      ->set(array(
        'url' => $urlArr['url'],
        "hash_url = UNHEX(MD5('" . $urlArr['url'] . "'))",
        'bind_id' => $bindId,
        'title' => $title,
      ))
      ->insertGetId();

    return $pageId->success ? $pageId->result : 0;
  }
 
  /**
   * Get page id
   *
   * @param  string $url
   * @param  string $bindId
   * @return int ID or 0
   */
  public function getPageId($url, $bindId)
  {
    $urlArr = Helper::urlParams($url);

    $page = DB::table($this->prefix . 'pages')
      ->select('id');

    if (!empty($bindId)) {
      $page->where('bind_id', $bindId);
    } else {
      $page->where('url', $urlArr['url']);
    }

    $data = $page->first();

    return $data->success && !empty($data->result) ? $data->result['id'] : 0;
  }

  /**
   * Get page ID by comment ID
   *
   * @param  int $cid comment id
   * @return int ID or 0
   */
  public function getPageIdByCid($cid)
  {
    $page = DB::table($this->prefix . 'comments')
      ->select('page_id')
      ->where('id', $cid)
      ->first();

    return $page->success ? $page->result['page_id'] : 0;
  }
 
  /**
   * Write main comment
   *
   * @param  array $data
   * @return int insert_id or 0
   */
  public function writeMain($data)
  {
    $date = date("Y-m-d H:i:s", time());
    $pageId = $this->getPageId($data['url'], $data['bindId']);

    if (!$pageId) {
      $pageId = $this->writePage($data['url'], $data['bindId'], $data['title']);
    }

    $insertId = DB::table($this->prefix . 'comments')->set(array(
      'uid' => $data['uid'],
      'page_id' => $pageId,
      'text' => $data['text'],
      'attach' => $data['attach'],
      'type' => $data['type'],
      'moderation' => $data['moderation'],
      'new' => $data['new'],
      'date_create' => $date,
      'date_update' => $date,
    ))->insertGetId();

    if ($insertId->success && $insertId->result > 0 && !$data['moderation']) {
      DB::table($this->prefix . 'pages')
        ->set(array(
          'count_main' => array('count_main', '+', 1),
        ))
        ->where('id', (int) $pageId)
        ->limit(1)
        ->update();
    }

    return $insertId->success ? $insertId->result : 0;
  }

  /**
   * Write answer comment
   *
   * @param  array $data
   * @return int insert_id or 0
   */
  public function writeAnswer($data)
  {
    $date = date("Y-m-d H:i:s", time());
    $parent = array();
    $pageId = $this->getPageId($data['url'], $data['bindId']);

    $parent = DB::table($this->prefix . 'comments')
      ->select(array('id', 'mid', 'path'))
      ->where('id', (int) $data['parent']['pid'])
      ->first();

    if ($parent->success) {
      if ($data['parent']['type'] === 'answer') {
        $parent->result['path'] = json_decode($parent->result['path']);
      }

      if ($data['parent']['type'] === 'main') {
        $parent->result['mid'] = $parent->result['id'];
        $parent->result['path'] = array();
        // $parent['id'] = 0;
      }
    }

    if (!$parent->success || empty($parent->result)) {
      return 0;
    }

    array_push($parent->result['path'], $data['parent']['pid']);
    $parent->result['path'] = json_encode($parent->result['path']);

    $insertId = DB::table($this->prefix . 'comments')
      ->set(array(
        'uid' => (int) $data['uid'],
        'pid' => (int) $parent->result['id'],
        'mid' => (int) $parent->result['mid'],
        'page_id' => (int) $pageId,
        'text' => $data['text'],
        'path' => $parent->result['path'],
        'attach' => $data['attach'],
        'type' => $data['type'],
        'moderation' => (int) $data['moderation'],
        'new' => (int) $data['new'],
        'date_create' => $date,
        'date_update' => $date,
      ))->insertGetId();

    if ($insertId->success && $insertId->result > 0 && !$data['moderation']) {
      DB::table($this->prefix . 'pages')
        ->set(array(
          'count_answer' => array('count_answer', '+', 1)
        ))
        ->where('id', (int) $pageId)
        ->limit(1)
        ->update();
    }

    return $insertId->success ? $insertId->result : 0;
  }
 
  /**
   * Uodate comment
   *
   * @param  array $data
   * @return bool
   */
  public function update($data)
  {
    $date = date("Y-m-d H:i:s", time());

    $update = DB::table($this->prefix . 'comments')
      ->set(array(
        'text' => $data['text'],
        'attach' => $data['attach'],
        'moderation' => (int) $data['moderation'],
        'new' => (int) $data['new'],
        'date_update' => $date,
      ))
      ->where('id', (int) $data['id'])
      ->limit(1)
      ->update();

    return $update->success && $update->result->affected_rows > 0 ? true : false;
  }

  /**
   * Approve comment
   *
   * @param  int $id
   * @param  string $type
   * @return bool
   */
  public function approve($id, $type)
  {
    $update = DB::table($this->prefix . 'comments')
      ->set(array(
        'moderation' => 0,
        'new' => 0,
      ))
      ->where('id', (int) $id)
      ->limit(1)
      ->update();

    if ($update->success && $update->result->affected_rows > 0) {
      $pageId  = $this->getPageIdByCid($id);

      DB::table($this->prefix . 'pages')
        ->set(array(
          "count_$type" => array("count_$type", '+', 1),
        ))
        ->where('id', (int) $pageId)
        ->limit(1)
        ->update();
    }

    return true;
  }
 
  /**
   * Read comment
   *
   * @param  int $id
   * @return bool
   */
  public function read($id)
  {
    DB::table($this->prefix . 'comments')
      ->set(array(
        "new" => 0,
      ))
      ->where('id', (int) $id)
      ->limit(1)
      ->update();

    return true;
  }
 
  /**
   * Read selected comments by IDs
   *
   * @param  array $ids
   * @return bool
   */
  public function readSelected($ids)
  {
    DB::table($this->prefix . 'comments')
      ->set(array(
        "new" => 0,
      ))
      ->whereIn('id', $ids)
      ->update();

    return true;
  }
  
  /**
   * Get comment by IP or UID
   *
   * @param  string $ip
   * @param  int $uid
   * @param  array $params
   * @return array
   */
  public function getByIpOrUid($ip, $uid, $params = array())
  {
    $limit = isset($params['limit']) ? $params['limit'] : 1;

    $select = DB::table($this->prefix . 'comments')
      ->select()
      ->where('uid', DB::table($this->prefix . 'signin')
        ->select('uid')
        ->where('ip', $ip)
        ->orderBy('id', 'DESC')
        ->limit(1))
      ->whereOr('uid', $uid)
      ->orderBy('id', 'DESC')
      ->limit($limit);
    if ($limit > 1) {
      $comment = $select->get();
    } else {
      $comment = $select->first();
    }

    return $comment->success ? $comment->result : array();
  }
 
  /**
   * Get comment by UID
   *
   * @param  int $uid
   * @param  array $params
   * @return array
   */
  public function getByUid($uid, $params = array())
  {
    $limit = isset($params['limit']) ? $params['limit'] : 1;

    $select = DB::table($this->prefix . 'comments')
      ->select()
      ->where('uid', (int) $uid)
      ->orderBy('id', 'DESC')
      ->limit($limit);
    if ($limit > 1) {
      $comment = $select->get();
    } else {
      $comment = $select->first();
    }

    return $comment->success ? $comment->result : array();
  }
 
  /**
   * Get comment by ID
   *
   * @param  int $id
   * @param  string $type
   * @return mixed array or null
   */
  public function getById($id, $type)
  {
    $comment = DB::table($this->prefix . 'comments')
      ->select()
      ->where('id', (int) $id)
      ->first();

    return $comment->success && !empty($comment->result) ? $comment->result : null;
  }
 
  /**
   * Get view comment by ID
   *
   * @param  int $id
   * @return array
   */
  public function getViewById($id)
  {
    $comments = DB::table($this->prefix . 'comments', 'tc')
      ->select(array(
        'tc.*',
        'authorName' => 'tu.name',
        'authorAvatar' => 'tu.avatar',
        'authorPuid' => 'tu.puid',
        'authorRole' => 'tu.role',
        'authorDeleted' => 'tu.deleted',
        'pageUrl' => 'tp.url',
        'pageBindId' => 'tp.bind_id',
        'pageTitle' => 'tp.title',
        'ratingUidIncrease' => 'tr.uid_increase',
        'ratingUidDecrease' => 'tr.uid_decrease',
        'ratingIncrease' => 'tr.increase',
        'ratingDecrease' => 'tr.decrease',
      ))
      ->leftJoin($this->prefix . 'users AS tu', 'tu.id', '=', 'tc.uid')
      ->leftJoin($this->prefix . 'pages AS tp', 'tp.id', '=', 'tc.page_id')
      ->leftJoin($this->prefix . 'rating AS tr', 'tr.cid', '=', 'tc.id')
      ->where('tc.id', (int) $id)
      ->first();

    if (!$comments->success || empty($comments->result)) {
      return null;
    }

    $comment = $comments->result;

    ### Select parent
    if ($comment['type'] === 'answer') {
      $pid = $comment['pid'];

      $parent = DB::table($this->prefix . 'comments', 'tc')
        ->select(array(
          'tc.text',
          'tu.name',
          'tu.deleted'
        ))
        ->leftJoin($this->prefix . 'users AS tu', 'tu.id', '=', 'tc.uid')
        ->where('tc.id', (int) $pid)
        ->first();

      if ($parent->success && !empty($parent->result)) {
        $comment['parentAuthorName'] = isset($parent->result['deleted']) && (int) $parent->result['deleted'] === 0 ? $parent->result['name'] : 'DELETED';
        $comment['parentText'] = $parent->result['text'];
      }
    }

    return array(
      'id' => (int) $comment['id'],
      'pid' => (int) $comment['pid'],
      'mid' => (int) $comment['mid'],
      'posted' => (int) $comment['posted'],
      'moderation' => (int) $comment['moderation'],
      'prefixId' => ($comment['type'] === 'main' ? 'csm-' : 'csa-') . $comment['id'],
      'pathStr' => $comment['type'] === 'answer' ? implode('.', array_slice(json_decode($comment['path']), 0, $this->config['level'])) : '',
      'type' => $comment['type'],
      'authorId' => (int) $comment['uid'],
      'authorPuid' => isset($comment['authorPuid']) && (int) $comment['authorDeleted'] === 0  ? $comment['authorPuid'] : '',
      'authorName' => isset($comment['authorName']) && (int) $comment['authorDeleted'] === 0 ? $comment['authorName'] : 'DELETED',
      'authorAvatar' => isset($comment['authorAvatar']) && (int) $comment['authorDeleted'] === 0 ? json_decode($comment['authorAvatar']) : null,
      'authorRole' => isset($comment['authorRole']) && (int) $comment['authorDeleted'] === 0 ? $comment['authorRole'] : '',
      'authorDeleted' => isset($comment['authorDeleted']) ? (int) $comment['authorDeleted'] : 1,
      'datePublished' => array(
        'origin' => $comment['date_create'],
        'seconds' => strtotime($comment['date_create']),
        'title' => (isset($_SESSION['time_zone'])) ?
          Helper::changeDate(date('d.m.Y H:i:s', strtotime($comment['date_create']) + ($_SESSION['time_zone'] * 60 * 60))) :
          Helper::changeDate(date('d.m.Y H:i:s T', strtotime($comment['date_create']))),
        'view' => Helper::changeTime($comment['date_create']),
      ),
      'textOrigin' => (int) $comment['posted'] === 1 ? $comment['text'] : '',
      'text' => (int) $comment['posted'] === 1 ? $this->filter($comment['text']) : $this->language['comment_deleted'],
      'attach' => (int) $comment['posted'] === 1 ? json_decode($comment['attach']) : null,
      'countChild' => 0,
      'pageTitle' => $comment['pageTitle'],
      'pageUrl' => $comment['pageUrl'],
      'pageBindId' => (int) $comment['pageBindId'],
      'parentAuthorName' => isset($comment['parentAuthorName']) ? $comment['parentAuthorName'] : '',
      'parentTextOrigin' => isset($comment['parentText']) ? $comment['parentText'] : '',
      'parentText' => isset($comment['parentText']) ? $this->filter($comment['parentText'], array('emojiSize' => 14)) : '',
      'rating' => array(
        'uidIncrease' => json_decode($comment['ratingUidIncrease']),
        'uidDecrease' => json_decode($comment['ratingUidDecrease']),
        'increase' => isset($comment['ratingIncrease']) ? (int) $comment['ratingIncrease'] : 0,
        'decrease' => isset($comment['ratingDecrease']) ? (int) $comment['ratingDecrease'] : 0,
      ),
    );
  }
  
  /**
   * Get view comment by page
   *
   * @param  string $url
   * @param  string $bindId
   * @param  string $sort
   * @param  int $listId
   * @return array
   */
  public function getViewByPage($url, $bindId, $sort, $listId)
  {
    $data = array('main' => array(), 'answer' => array());
    $pageId = $this->getPageId($url, $bindId);
    $limit = $this->config['limit'];
    $offset = $limit * $listId;

    switch ($sort) {
      case 'new':
        $orderBy = "tc1.id DESC";
        break;
      case 'old':
        $orderBy = "tc1.id ASC";
        break;
      case 'pop':
        $orderBy = "tc1.rating DESC, tc1.id DESC";
        break;
      default:
        $orderBy = "tc1.id DESC";
        break;
    }

    if ($pageId) {
      $comments = DB::table(
        DB::table($this->prefix . 'comments', 'tc1')
          ->select()
          ->where(
            array(
              array('tc1.type', 'main'),
              array('tc1.page_id', (int) $pageId),
              array('tc1.moderation', 0),
              array(array('tc1.posted', 1), 'OR', DB::table($this->prefix . 'comments', 'tc2')
                ->select('COUNT(tc2.id)')
                ->where(
                  array(
                    array('tc2.type', 'answer'),
                    array('tc2.moderation', 0),
                    'tc2.mid = tc1.id',
                    array('tc2.posted', '>', 0)
                  )
                ), '>', 0)
            )
          )
          ->orderBy($orderBy)
          ->limit($limit)
          ->offset($offset),
        'tc'
      )
        ->select(array(
          'tc.id',
          'tc.uid',
          'tc.posted',
          'tc.moderation',
          'tc.type',
          'tc.text',
          'tc.attach',
          'tc.rating',
          'tc.date_create',
          'authorName' => 'tu.name',
          'authorAvatar' => 'tu.avatar',
          'authorPuid' => 'tu.puid',
          'authorRole' => 'tu.role',
          'authorDeleted' => 'tu.deleted',
          'ratingUidIncrease' => 'tr.uid_increase',
          'ratingUidDecrease' => 'tr.uid_decrease',
          'ratingIncrease' => 'tr.increase',
          'ratingDecrease' => 'tr.decrease',
          'countChild' => DB::table($this->prefix . 'comments', 'tc3')
            ->select('COUNT(tc3.id)')
            ->where(
              array(
                array('tc3.type', 'answer'),
                array('tc3.moderation', 0),
                'tc3.mid = tc.id',
                array('tc3.posted', '>', 0),
              )
            ),
        ))
        ->leftJoin($this->prefix . 'users AS tu', 'tu.id', '=', 'tc.uid')
        ->leftJoin($this->prefix . 'rating AS tr', 'tr.cid', '=', 'tc.id')
        ->get();

      if ($comments->success && !empty($comments->result)) {
        $listId = array();
        foreach ($comments->result as $key => $value) {
          $data['main'][] = array(
            'id' => (int) $value['id'],
            'posted' => (int) $value['posted'],
            'moderation' => (int) $value['moderation'],
            'prefixId' => 'csm-' . $value['id'],
            'pathStr' => '',
            'type' => 'main',
            'authorId' => (int) $value['uid'],
            'authorPuid' => isset($value['authorPuid']) && (int) $value['authorDeleted'] === 0  ? $value['authorPuid'] : '',
            'authorName' => isset($value['authorName']) && (int) $value['authorDeleted'] === 0 ? $value['authorName'] : 'DELETED',
            'authorAvatar' => isset($value['authorAvatar']) && (int) $value['authorDeleted'] === 0 ? json_decode($value['authorAvatar']) : null,
            'authorRole' => isset($value['authorRole']) && (int) $value['authorDeleted'] === 0 ? $value['authorRole'] : '',
            'authorDeleted' => isset($value['authorDeleted']) ? (int) $value['authorDeleted'] : 1,
            'datePublished' => array(
              'origin' => $value['date_create'],
              'seconds' => strtotime($value['date_create']),
              'title' => (isset($_SESSION['time_zone'])) ?
                Helper::changeDate(date('d.m.Y H:i:s', strtotime($value['date_create']) + ($_SESSION['time_zone'] * 60 * 60))) :
                Helper::changeDate(date('d.m.Y H:i:s T', strtotime($value['date_create']))),
              'view' => Helper::changeTime($value['date_create']),
            ),
            'textOrigin' => (int) $value['posted'] === 1 ? $value['text'] : '',
            'text' => (int) $value['posted'] === 1 ? $this->filter($value['text']) : $this->language['comment_deleted'],
            'attach' => (int) $value['posted'] === 1 ? json_decode($value['attach']) : null,
            'rating' => array(
              'uidIncrease' => json_decode($value['ratingUidIncrease']),
              'uidDecrease' => json_decode($value['ratingUidDecrease']),
              'increase' => isset($value['ratingIncrease']) ? (int)$value['ratingIncrease'] : 0,
              'decrease' => isset($value['ratingDecrease']) ? (int)$value['ratingDecrease'] : 0,
            ),
            'countChild' => $value['countChild'],
          );

          $listId[] = $value['id'];
        }

        $answers = DB::table(
          DB::table($this->prefix . 'comments', 'tc1')
            ->select()
            ->where(
              array(
                array('tc1.mid', 'IN', $listId),
                array('tc1.type', 'answer'),
                array('tc1.moderation', 0)
              )
            ),
          'tc'
        )
          ->select(
            array(
              'tc.id',
              'tc.pid',
              'tc.mid',
              'tc.uid',
              'tc.posted',
              'tc.moderation',
              'tc.type',
              'tc.path',
              'tc.text',
              'tc.attach',
              'tc.rating',
              'tc.date_create',
              'authorName' => 'tu.name',
              'authorAvatar' => 'tu.avatar',
              'authorPuid' => 'tu.puid',
              'authorRole' => 'tu.role',
              'authorDeleted' => 'tu.deleted',
              'ratingUidIncrease' => 'tr.uid_increase',
              'ratingUidDecrease' => 'tr.uid_decrease',
              'ratingIncrease' => 'tr.increase',
              'ratingDecrease' => 'tr.decrease',
            )
          )
          ->leftJoin($this->prefix . 'users AS tu', 'tu.id', '=', 'tc.uid')
          ->leftJoin($this->prefix . 'rating AS tr', 'tr.cid', '=', 'tc.id')
          ->get();

        if ($answers->success && !empty($answers->result)) {
          foreach ($answers->result as $value) {
            $value['pathStr'] = implode('.', array_slice(json_decode($value['path']), 0, $this->config['level']));
            $data['answer'][$value['pathStr']][$value['id']] = array(
              'id' => (int) $value['id'],
              'pid' => (int) $value['pid'],
              'mid' => (int) $value['mid'],
              'posted' => (int) $value['posted'],
              'moderation' => (int) $value['moderation'],
              'prefixId' => 'csa-' . $value['id'],
              'pathStr' => $value['pathStr'],
              'path' => json_decode($value['path']),
              'type' => 'answer',
              'authorId' => (int) $value['uid'],
              'authorPuid' => isset($value['authorPuid']) && (int) $value['authorDeleted'] === 0  ? $value['authorPuid'] : '',
              'authorName' => isset($value['authorName']) && (int) $value['authorDeleted'] === 0 ? $value['authorName'] : 'DELETED',
              'authorAvatar' => isset($value['authorAvatar']) && (int) $value['authorDeleted'] === 0 ? json_decode($value['authorAvatar']) : null,
              'authorRole' => isset($value['authorRole']) && (int) $value['authorDeleted'] === 0 ? $value['authorRole'] : '',
              'authorDeleted' => isset($value['authorDeleted']) ? (int) $value['authorDeleted'] : 1,
              'datePublished' => array(
                'origin' => $value['date_create'],
                'seconds' => strtotime($value['date_create']),
                'title' => (isset($_SESSION['time_zone'])) ?
                  Helper::changeDate(date('d.m.Y H:i:s', strtotime($value['date_create']) + ($_SESSION['time_zone'] * 60 * 60))) :
                  Helper::changeDate(date('d.m.Y H:i:s T', strtotime($value['date_create']))),
                'view' => Helper::changeTime($value['date_create']),
              ),
              'textOrigin' => (int) $value['posted'] === 1 ? $value['text'] : '',
              'text' => (int) $value['posted'] === 1 ? $this->filter($value['text']) : $this->language['comment_deleted'],
              'attach' => (int) $value['posted'] === 1 ? json_decode($value['attach']) : null,
              'rating' => array(
                'uidIncrease' => json_decode($value['ratingUidIncrease']),
                'uidDecrease' => json_decode($value['ratingUidDecrease']),
                'increase' => isset($value['ratingIncrease']) ? (int) $value['ratingIncrease'] : 0,
                'decrease' => isset($value['ratingDecrease']) ? (int) $value['ratingDecrease'] : 0,
              ),
            );
          }

          #Count posted child answer on comments
          foreach ($data['answer'] as &$dataAnswer) {
            foreach ($dataAnswer as &$answer) {
              $answer['countChild'] = $this->countChild($data, $answer['pathStr'] . '.' . $answer['id']);
            }
          }
        }
      }
    }

    return $data;
  }
  
  /**
   * Get all comments for panel
   *
   * @param  array $params
   * @return array
   */
  public function getAll($params = array())
  {
    $limit = $params['limit'];
    $offset = isset($params['listId']) ? $limit * $params['listId'] : 0;

    $where = array();
    $sort = isset($params['sort']) ? $params['sort'] : 'DESC';

    if (isset($params['page']) && $params['page'] !== false) {
      $page = DB::table($this->prefix . 'pages')
        ->select('id')
        ->where('url', $params['page'])
        ->first();

      $pageId = $page->success && !empty($page->result) ? $page->result['id'] : 0;
      $where[] = array('tc1.page_id', $pageId);
    }
    if (isset($params['bindId']) && $params['bindId'] !== false) {
      $page = DB::table($this->prefix . 'pages')
        ->select('id')
        ->where('bind_id', $params['bindId'])
        ->first();

      $pageId = $page->success && !empty($page->result) ? $page->result['id'] : 0;
      $where[] = array('tc1.page_id', $pageId);
    }
    if ((isset($params['posted']) && $params['posted'] === false) || (isset($params['reports']) && $params['reports'] !== false)) {
      $where[] = array('tc1.posted', (int) $params['posted']);
    }
    if (isset($params['moderation']) && $params['moderation'] !== false) {
      $where[] = array('tc1.moderation', (int) $params['moderation']);
    }
    if (isset($params['new']) && $params['new'] !== false) {
      $where[] = array('tc1.new', (int) $params['new']);
    }
    if (isset($params['reports']) && $params['reports'] !== false) {
      $where[] = array('tc1.reports', '>', 0);
      $where[] = array(
        'tc1.id', 'IN', DB::table($this->prefix . 'reports', 'tcmpl')
          ->select('cid')
          ->where(array(
            'tcmpl.cid = tc1.id',
            'tcmpl.new = 1'
          ))
      );
    }
    if (isset($params['uid']) && $params['uid'] !== false) {
      $where[] = array('tc1.uid', (int) $params['uid']);
    }
    if (isset($params['id'])) {
      $where[] = array(array('tc1.id', (int)$params['id']), 'OR', array('tc1.mid', (int) $params['id']));
    }

    $comments = DB::table(
      DB::table($this->prefix . 'comments', 'tc1')
        ->select()
        ->where($where)
        ->orderBy('tc1.id', $sort)
        ->limit($limit)
        ->offset($offset),
      'tc'
    )->select(array(
      'tc.*',
      'pageUrl' => 'tp.url',
      'pageBindId' => 'tp.bind_id',
      'pageTitle' => 'tp.title',
      'authorName' => 'tu.name',
      'authorAvatar' => 'tu.avatar',
      'authorPuid' => 'tu.puid',
      'authorRole' => 'tu.role',
      'ratingUidIncrease' => 'tr.uid_increase',
      'ratingUidDecrease' => 'tr.uid_decrease',
      'ratingIncrease' => 'tr.increase',
      'ratingDecrease' => 'tr.decrease',
      'newReportsCount' => DB::table($this->prefix . 'reports', 'tcmpl')
        ->select('COUNT(tcmpl.id)')
        ->where(array(
          'tcmpl.cid = tc.id',
          'tcmpl.new' => 1
        )),
      'parentAuthorName' => DB::table($this->prefix . 'users')
        ->select('name')
        ->where('id', DB::table($this->prefix . 'comments')
          ->select('uid')
          ->where('id = tc.pid')
          ->limit(1))
        ->limit(1),
      'parentText' => DB::table($this->prefix . 'comments')
        ->select('text')
        ->where(array('id = tc.pid', array('tc.type', 'answer')))
        ->limit(1),
    ))
      ->leftJoin($this->prefix . 'pages AS tp', 'tp.id', '=', 'tc.page_id')
      ->leftJoin($this->prefix . 'users AS tu', 'tu.id', '=', 'tc.uid')
      ->leftJoin($this->prefix . 'rating AS tr', 'tr.cid', '=', 'tc.id')
      ->orderBy('tc.id', $sort)
      ->get();

    $data = array();
    if ($comments->success && !empty($comments->result)) {
      foreach ($comments->result as $value) {
        $data[] = array(
          'type' => $value['type'],
          'new' => (int) $value['new'],
          'posted' => (int) $value['posted'],
          'moderation' => (int) $value['moderation'],
          'id' => (int) $value['id'],
          'prefixId' => ($value['type'] === 'main' ? 'csm-' : 'csa-') . $value['id'],
          'pageUrl' => $value['pageUrl'],
          'pageBindId' => (int) $value['pageBindId'],
          'pageTitle' => $value['pageTitle'],
          'pid' => (int) $value['pid'],
          'mid' => (int) $value['mid'],
          'uid' => (int) $value['uid'],
          'page_id' => (int) $value['page_id'],
          'path' => $value['path'],
          'textOrigin' => $value['text'],
          'text' => $this->filter($value['text']),
          'attach' => json_decode($value['attach']),
          'rating' => array(
            'uidIncrease' => json_decode($value['ratingUidIncrease']),
            'uidDecrease' => json_decode($value['ratingUidDecrease']),
            'increase' => isset($value['ratingIncrease']) ? (int) $value['ratingIncrease'] : 0,
            'decrease' => isset($value['ratingDecrease']) ? (int) $value['ratingDecrease'] : 0,
          ),
          'datePublished' => array(
            'origin' => $value['date_create'],
            'seconds' => strtotime($value['date_create']),
            'title' => (isset($_SESSION['time_zone'])) ?
              Helper::changeDate(date('d.m.Y H:i:s', strtotime($value['date_create']) + ($_SESSION['time_zone'] * 60 * 60))) :
              Helper::changeDate(date('d.m.Y H:i:s T', strtotime($value['date_create']))),
            'view' => Helper::changeTime($value['date_create']),
          ),
          'authorId' => (int) $value['uid'],
          'authorName' => isset($value['authorName']) ? $value['authorName'] : 'DELETED',
          'authorAvatar' => isset($value['authorAvatar']) ? json_decode($value['authorAvatar']) : null,
          'authorPuid' => isset($value['authorPuid']) ? $value['authorPuid'] : '',
          'authorRole' => isset($value['authorRole']) ? $value['authorRole'] : '',
          'parentAuthorName' => isset($value['parentAuthorName']) ? $value['parentAuthorName'] : ($value['type'] === 'answer' ? 'DELETED' : null),
          'parentTextOrigin' => $value['parentText'],
          'parentText' => $this->filter($value['parentText'], array('emojiSize' => 14)),
          'newReportsCount' => $value['newReportsCount'],
          'reportsCount' => $value['reports'],
        );
      }
    }

    return $data;
  }
  
  /**
   * Get count child comments
   *
   * @param  array $data
   * @param  string $path
   * @return int
   */
  public function countChild($data, $path)
  {
    $count = 0;

    if (isset($data['answer'][$path])) {
      foreach ($data['answer'][$path] as $value) {
        $count += (int)$value['posted'] > 0 ? 1 : 0;
        $count += $this->countChild($data, $path . '.' . $value['id']);
      }
    }

    return $count;
  }
 
  /**
   * Get count comments list
   *
   * @param  object $params
   * @return int
   */
  public function getCountList($params)
  {
    $page = DB::table($this->prefix . 'pages')
      ->select('count_main + count_answer as count', 'url', 'bind_id');

    if (!empty($params->bindId)) {
      $page->whereIn('bind_id', $params->bindId);
    }
    if (!empty($params->url)) {
      if (!empty($params->bindId)) {
        $page->whereOr('url', 'IN', $params->url);
      } else {
        $page->whereIn('url', $params->url);
      }
      $page->whereAnd('bind_id', 'IS', 'NULL');
    }

    $data = $page->get();

    $count = array();
    if ($data->success && !empty($data->result)) {
      foreach ($data->result as $value) {
        if ($value['bind_id']) {
          $count['bindId'][$value['bind_id']] = $value['count'];
        } else {
          $count['url'][$value['url']] = $value['count'];
        }
      }
    }

    return $count;
  }
  
  /**
   * Get count comments
   *
   * @param  string $url
   * @param  string $bindId
   * @return array
   */
  public function getCount($url, $bindId)
  {
    $urlArr = Helper::urlParams($url);

    $page = DB::table($this->prefix . 'pages')
      ->select('count_main', 'count_answer');

    if (!empty($bindId)) {
      $page->where('bind_id', $bindId);
    } else {
      $page->where('url', $urlArr['url']);
    }

    $data = $page->first();

    $main = $data->success && !empty($data->result) ? $data->result['count_main'] : 0;
    $answer = $data->success && !empty($data->result) ? $data->result['count_answer'] : 0;

    return array('main' => (int)$main, 'answer' => (int)$answer);
  }

  /**
   * Get count comments for panel
   *
   * @param  array $params
   * @return int
   */
  public function getCountForPanel($params = array())
  {
    $where = array();

    if (isset($params['page']) && $params['page'] !== false) {
      $page = DB::table($this->prefix . 'pages')
        ->select('id')
        ->where('url', $params['page'])
        ->first();

      $pageId = $page->success && !empty($page->result) ? $page->result['id'] : 0;
      $where[] = array('tc.page_id', $pageId);
    }
    if (isset($params['bindId']) && $params['bindId'] !== false) {
      $page = DB::table($this->prefix . 'pages')
        ->select('id')
        ->where('bind_id', $params['bindId'])
        ->first();

      $pageId = $page->success && !empty($page->result) ? $page->result['id'] : 0;
      $where[] = array('tc.page_id', $pageId);
    }
    if ((isset($params['posted']) && $params['posted'] === false) || (isset($params['reports']) && $params['reports'] !== false)) {
      $where[] = array('tc.posted', (int) $params['posted']);
    }
    if (isset($params['moderation']) && $params['moderation'] !== false) {
      $where[] = array('tc.moderation', (int) $params['moderation']);
    }
    if (isset($params['new']) && $params['new'] !== false) {
      $where[] = array('tc.new', (int) $params['new']);
    }
    if (isset($params['reports']) && $params['reports'] !== false) {
      $where[] = array('tc.reports', '>', 0);
      $where[] = array(
        'tc.id', 'IN', DB::table($this->prefix . 'reports', 'tcmpl')
          ->select('cid')
          ->where(array(
            'tcmpl.cid = tc.id',
            'tcmpl.new = 1'
          ))
      );
    }
    if (isset($params['uid']) && $params['uid'] !== false) {
      $where[] = array('tc.uid', (int) $params['uid']);
    }
    if (isset($params['id'])) {
      $where[] = array(array('tc.id', (int) $params['id']), 'OR', array('tc.mid', (int)$params['id']));
    }

    $count = DB::table($this->prefix . 'comments', 'tc');
    if (!empty($where)) {
      $count->where($where);
    }
    $data = $count->count('id');

    return $data->success ? $data->result : 0;
  }
 
  /**
   * Comment posted
   *
   * @param  int $id
   * @param  string $type
   * @return bool
   */
  public function posted($id, $type)
  {
    $pageId  = $this->getPageIdByCid($id);

    $update = DB::table($this->prefix . 'comments')
      ->set(array(
        'posted' => 1,
        'new' => 0
      ))
      ->where('id', (int) $id)
      ->limit(1)
      ->update();

    if ($update->success && $update->result->affected_rows > 0) {
      $update = DB::table($this->prefix . 'pages')
        ->set(array(
          "count_$type" => array("count_$type", '+', 1),
        ))
        ->where('id', (int) $pageId)
        ->limit(1)
        ->update();
    }

    return $update->success && $update->result->affected_rows > 0 ? true : false;
  }
 
  /**
   * Comment selected posted
   *
   * @param  array $ids
   * @return bool
   */
  public function postedSelected($ids)
  {
    $select = DB::table($this->prefix . 'comments')
      ->select('id', 'type', 'page_id')
      ->whereIn('id', $ids)
      ->get();

    $data = array();
    foreach ($select->result as $value) {
      $data[$value['page_id']]['page_id'] = $value['page_id'];
      $data[$value['page_id']][$value['type']]++;
    }

    $update = DB::table($this->prefix . 'comments')
      ->set(array(
        'posted' => 1,
        'new' => 0
      ))
      ->whereIn('id', $ids)
      ->update();

    if ($update->success && $update->result->affected_rows > 0) {
      foreach ($data as $value) {
        DB::table($this->prefix . 'pages')
          ->set(array(
            "count_main" => array("count_main", '+', isset($value['main']) ? $value['main'] : 0),
            "count_answer" => array("count_answer", '+', isset($value['answer']) ? $value['answer'] : 0),
          ))
          ->where('id', (int) $value['page_id'])
          ->limit(1)
          ->update();
      }
    }

    return $update->success && $update->result->affected_rows > 0 ? true : false;
  }

  /**
   * Comment selected unposted
   *
   * @param  array $ids
   * @return bool
   */
  public function unpostedSelected($ids)
  {
    $select = DB::table($this->prefix . 'comments')
      ->select('id', 'type', 'page_id', 'moderation', 'posted')
      ->whereIn('id', $ids)
      ->get();

    $data = array();
    foreach ($select->result as $value) {
      $data[$value['page_id']]['page_id'] = $value['page_id'];
      if ($value['moderation'] === 0 && $value['posted'] === 1) {
        $data[$value['page_id']][$value['type']]++;
      }
    }

    $update = DB::table($this->prefix . 'comments')
      ->set(array(
        'posted' => 0,
        'moderation' => 0,
        'new' => 0,
      ))
      ->whereIn('id', $ids)
      ->update();

    if ($update->success && $update->result->affected_rows > 0) {
      foreach ($data as $value) {
        DB::table($this->prefix . 'pages')
          ->set(array(
            "count_main" => array("count_main", '-', isset($value['main']) ? $value['main'] : 0),
            "count_answer" => array("count_answer", '-', isset($value['answer']) ? $value['answer'] : 0),
          ))
          ->where('id', (int) $value['page_id'])
          ->limit(1)
          ->update();
      }
    }

    return $update->success && $update->result->affected_rows > 0 ? true : false;
  }
 
  /**
   * Comment unposted
   *
   * @param  int $id
   * @param  string $type
   * @return bool
   */
  public function unposted($id, $type)
  {
    $pageId  = $this->getPageIdByCid($id);

    $countDeleteing = DB::table($this->prefix . 'comments')
      ->where('id', (int) $id)
      ->whereAnd('moderation', 0)
      ->whereAnd('posted', 1)
      ->count();

    $update = DB::table($this->prefix . 'comments')
      ->set(array(
        'posted' => 0,
        'moderation' => 0,
        'new' => 0,
      ))
      ->where('id', (int) $id)
      ->limit(1)
      ->update();

    if ($update->success && $update->result->affected_rows > 0) {
      DB::table($this->prefix . 'pages')
        ->set(array(
          "count_$type" => array("count_$type", '-', $countDeleteing->result),
        ))
        ->where('id', (int) $pageId)
        ->limit(1)
        ->update();
    }

    return $update->success && $update->result->affected_rows > 0 ? true : false;
  }
 
  /**
   * Delete comment selected
   *
   * @param  array $ids
   * @return bool
   */
  public function deleteSelected($ids)
  {
    $select = DB::table($this->prefix . 'comments')
      ->select('id', 'type', 'page_id', 'path', 'moderation', 'posted')
      ->whereIn('id', $ids)
      ->get();

    if (!$select->success || empty($select->result)) {
      return false;
    }

    $data = array();
    foreach ($select->result as $value) {
      if ($value['type'] === 'answer') {
        $answerPath = json_decode($value['path']);
        array_push($answerPath, (int) $value['id']);
        $answerPath = implode('+', $answerPath);
        $data[$value['page_id']]['path'][] = $answerPath;
      } else {
        $data[$value['page_id']]['path'][] = (int) $value['id'];
      }
      $data[$value['page_id']]['page_id'] = $value['page_id'];
      if ($value['moderation'] === 0 && $value['posted'] === 1) {
        $data[$value['page_id']][$value['type']]++;
      }
    }

    $idListAnswer = array();
    foreach ($data as &$value) {
      if (isset($value['path'])) {
        foreach ($value['path'] as $path) {
          #Delete child answers to a comment
          $commentIds = DB::table($this->prefix . 'comments')
            ->select('id')
            ->where("MATCH(path) AGAINST('\"+$path\"' IN BOOLEAN MODE)")
            ->whereNotIn("id",  $ids)
            ->get();

          foreach ($commentIds->result as $result) {
            if (!in_array($result['id'], $ids)) {
              $idListAnswer[] = $result['id'];
            }
          }
        }
      }

      $value['answer'] += count(array_unique($idListAnswer));
    }

    $delete = DB::table($this->prefix . 'comments')
      ->whereIn('id', $ids)
      ->whereOr('mid', 'IN', $ids);

    if (!empty($idListAnswer)) {
      $delete->whereOr('id', 'IN', $idListAnswer);
    }

    $deleteResult = $delete->delete();

    if (!$deleteResult->success || $deleteResult->result->affected_rows <= 0) {
      return false;
    }

    foreach ($data as $value) {
      DB::table($this->prefix . 'pages')
        ->set(array(
          "count_main" => array("count_main", '-', isset($value['main']) ? $value['main'] : 0),
          "count_answer" => array("count_answer", '-', isset($value['answer']) ? $value['answer'] : 0),
        ))
        ->where('id', (int) $value['page_id'])
        ->limit(1)
        ->update();
    }

    return true;
  }
 
  /**
   * Delete comment
   *
   * @param  int  $id
   * @param  string $type
   * @return bool
   */
  public function delete($id, $type)
  {
    $pageId  = $this->getPageIdByCid($id);
    $countDeleteMain = 0;
    $countDeleteAnswer = 0;

    $error = false;

    #Delete all childrens to a comment
    if ($type === 'main') {
      $countDeleteing = DB::table($this->prefix . 'comments')
        ->where('mid', (int) $id)
        ->whereAnd('moderation', 0)
        ->whereAnd('posted', 1)
        ->count();

      $delete = DB::table($this->prefix . 'comments')
        ->where('mid', (int) $id)
        ->delete();

      if ($delete->success && $delete->result->affected_rows > 0) {
        $countDeleteAnswer += $countDeleteing->success ? $countDeleteing->result : 0;
      }

      #Delete parrent comment
      $countDeleteing = DB::table($this->prefix . 'comments')
        ->where('id', (int) $id)
        ->whereAnd('moderation', 0)
        ->whereAnd('posted', 1)
        ->count();

      $delete = DB::table($this->prefix . 'comments')
        ->where('id', (int) $id)
        ->limit(1)
        ->delete();

      if ($delete->success && $delete->result->affected_rows > 0) {
        $countDeleteMain += $countDeleteing->success ? $countDeleteing->result : 0;
      } else {
        $error = true;
      }
    }

    #Delete child answers to a comment
    if ($type === 'answer') {
      $answer = DB::table($this->prefix . 'comments')
        ->select('path')
        ->where('id', (int) $id)
        ->first();

      if (!$answer->success || empty($answer->result)) {
        $error = true;
      }

      $answerPath = json_decode($answer->result['path']);
      array_push($answerPath, (int)$id);
      $answerPath = implode('+', $answerPath);

      $comments = DB::table($this->prefix . 'comments')
        ->select('id')
        ->where("MATCH(path) AGAINST('\"+$answerPath\"' IN BOOLEAN MODE)")
        ->get();

      $idListArr = array($id);

      if ($comments->success && !empty($comments->result)) {
        foreach ($comments->result as $value) {
          $idListArr[] = $value['id'];
        }
      }

      $countDeleting = DB::table($this->prefix . 'comments')
        ->whereIn('id', $idListArr)
        ->whereAnd('moderation', 0)
        ->whereAnd('posted', 1)
        ->count();

      $delete = DB::table($this->prefix . 'comments')
        ->whereIn('id', $idListArr)
        ->delete();

      if ($delete->success && $delete->result->affected_rows > 0) {
        $countDeleteAnswer += $countDeleting->success ? $countDeleting->result : 0;
      } else {
        $error = true;
      }
    }

    $update = DB::table($this->prefix . 'pages')
      ->set(array(
        'count_main' => array('count_main', '-', $countDeleteMain),
        'count_answer' => array('count_answer', '-', $countDeleteAnswer)
      ))
      ->where('id', (int) $pageId)
      ->limit(1)
      ->update();

    return !$error ? true : false;
  }
  
  /**
   * Move comments to another page
   *
   * @param  array $fromIds
   * @param  int $toId
   * @return bool
   */
  public function moveToPage($fromIds, $toId)
  {
    $pages = DB::table($this->prefix . 'pages')
      ->whereIn('id', $fromIds)
      ->get();

    $countComments = array(
      'count_main' => 0,
      'count_answer' => 0,
    );

    if ($pages->success && !empty($pages->result)) {
      foreach ($pages->result as $value) {
        $countComments['count_main'] += $value['count_main'];
        $countComments['count_answer'] += $value['count_answer'];
      }
    }

    $update = DB::table($this->prefix . 'comments')
      ->set(array(
        'page_id' => $toId
      ))
      ->whereIn('page_id', $fromIds)
      ->update();

    if ($update->success && $update->result->affected_rows > 0) {

      DB::table($this->prefix . 'pages')
        ->set(array(
          'count_main' => 0,
          'count_answer' => 0,
        ))
        ->whereIn('id', $fromIds)
        ->update();

      DB::table($this->prefix . 'pages')
        ->set(array(
          'count_main' => array('count_main', '+', $countComments['count_main']),
          'count_answer' => array('count_answer', '+', $countComments['count_answer']),
        ))
        ->whereIn('id', $toId)
        ->update();
    }
    return $update->success && $update->result->affected_rows > 0 ? true : false;
  }
 
  /**
   * Recount comments by pages
   *
   * @param  array $idsList
   * @return bool
   */
  public function recountForPages($idsList)
  {
    foreach ($idsList as $value) {
      $countCommentMain = DB::table($this->prefix . 'comments')
        ->where(array(
          'page_id' => $value,
          'posted' => 1,
          'moderation' => 0,
          'type' => 'main',
        ))
        ->count();

      if (!$countCommentMain->success) {
        return false;
      }

      $countCommentAnswer = DB::table($this->prefix . 'comments')
        ->where(array(
          'page_id' => $value,
          'posted' => 1,
          'moderation' => 0,
          'type' => 'answer',
        ))
        ->count();

      if (!$countCommentAnswer->success) {
        return false;
      }

      $update = DB::table($this->prefix . 'pages', 'tp')
        ->set(array(
          'count_main' => $countCommentMain->result,
          'count_answer' => $countCommentAnswer->result
        ))
        ->where('tp.id', $value)
        ->update();
    }

    return $update->success && $update->result->affected_rows !== -1 ? true : false;
  }
 
  /**
   * Set rating the comment
   *
   * @param  string $exp increase or decrease
   * @param  int $id
   * @param  int $uid
   * @return array
   */
  public function setRating($exp, $id, $uid)
  {
    $exp = $exp === 'increase' ? 'increase' : 'decrease';
    $score = 0;

    $rating = DB::table($this->prefix . 'rating')
      ->where('cid', (int) $id)
      ->first();

    if ($rating->success && !empty($rating->result)) {
      $uidIncreaseArr = isset($rating->result['uid_increase']) ? json_decode($rating->result['uid_increase']) : array();
      $uidDecreaseArr = isset($rating->result['uid_decrease']) ? json_decode($rating->result['uid_decrease']) : array();
      $increase = $rating->result['increase'];
      $decrease = $rating->result['decrease'];

      if ($exp === 'increase') {
        if (is_array($uidIncreaseArr) && in_array($uid, $uidIncreaseArr)) {
          unset($uidIncreaseArr[array_search($uid, $uidIncreaseArr)]);
          $set['increase'] = array("increase", "-", 1);
          $increase--;
        } else {
          if (is_array($uidDecreaseArr) && in_array($uid, $uidDecreaseArr)) {
            unset($uidDecreaseArr[array_search($uid, $uidDecreaseArr)]);
            $set['decrease'] = array("decrease", "+", 1);
            $decrease++;
          }
          $uidIncreaseArr[] = $uid;
          $set['increase'] = array("increase", "+", 1);
          $increase++;
        }
      } else {
        if (is_array($uidDecreaseArr) && in_array($uid, $uidDecreaseArr)) {
          unset($uidDecreaseArr[array_search($uid, $uidDecreaseArr)]);
          $set['decrease'] = array("decrease", "+", 1);
          $decrease++;
        } else {
          if (is_array($uidIncreaseArr) && in_array($uid, $uidIncreaseArr)) {
            unset($uidIncreaseArr[array_search($uid, $uidIncreaseArr)]);
            $set['increase'] = array("increase", "-", 1);
            $increase--;
          }
          $uidDecreaseArr[] = $uid;
          $set['decrease'] = array("decrease", "-", 1);
          $decrease--;
        }
      }

      $uidIncreaseJson = json_encode($uidIncreaseArr);
      $uidDecreaseJson = json_encode($uidDecreaseArr);

      $set['uid_increase'] = $uidIncreaseJson;
      $set['uid_decrease'] = $uidDecreaseJson;

      $update = DB::table($this->prefix . 'rating')
        ->set($set)
        ->where('cid', (int) $id)
        ->limit(1)
        ->update();

      if ($update->success && $update->result->affected_rows > 0) {
        DB::table($this->prefix . 'comments')
          ->set(
            array(
              'rating' => ($increase + $decrease)
            )
          )
          ->where('id', (int) $id)
          ->limit(1)
          ->update();

        return array(
          'increase' => $increase,
          'decrease' => $decrease,
          'uidIncrease' => $set['uid_increase'],
          'uidDecrease' => $set['uid_decrease']
        );
      }
    } else {
      $uidJson = json_encode(array($uid));
      $score = $exp === 'increase' ? 1 : -1;

      DB::table($this->prefix . 'rating')
        ->set(array(
          'cid' => (int) $id,
          "uid_$exp" => $uidJson,
          $exp => (int) $score
        ))
        ->insert();

      DB::table($this->prefix . 'comments')
        ->set(
          array(
            'rating' => $score
          )
        )
        ->where('id', (int) $id)
        ->limit(1)
        ->update();

      return array(
        'increase' => $exp === 'increase' ? 1 : 0,
        'decrease' => $exp === 'decrease' ? -1 : 0,
        'uidIncrease' => $exp === 'increase' ? array($uid) : array(),
        'uidDecrease' => $exp === 'decrease' ? array($uid) : array(),
      );
    }
  }
 
  /**
   * Send report on comment
   *
   * @param  int $cid comment ID
   * @param  int $uid
   * @param  string $text
   * @return bool
   */
  public function sendReport($cid, $uid, $text)
  {
    $insert = DB::table($this->prefix . 'reports')
      ->set(array(
        'cid' => (int) $cid,
        'uid' => (int) $uid,
        'text' => $text
      ))
      ->insert();

    if ($insert->success && $insert->result->affected_rows > 0) {
      DB::table($this->prefix . 'comments')
        ->set(array(
          'reports' => array('reports', '+', 1)
        ))
        ->where('id', (int) $cid)
        ->update();
    }

    return $insert > 0 ? true : false;
  }
 
  /**
   * Get report on comment
   *
   * @param  array $params
   * @return array
   */
  public function getReports($params)
  {
    $limit = $params['limit'];
    $offset = isset($params['listId']) ? $limit * $params['listId'] : 0;

    $select = DB::table($this->prefix . 'reports', 'tcmpl')
      ->select('tcmpl.id', 'tcmpl.uid', 'tcmpl.cid', 'tcmpl.text', 'tcmpl.new', 'tcmpl.date', 'tu.name', 'tu.avatar', 'tu.role', 'tu.puid')
      ->leftJoin($this->prefix . 'users AS tu', 'tcmpl.uid', '=', 'tu.id')
      ->where('tcmpl.cid', (int) $params['idComment'])
      ->orderBy('tcmpl.id', 'DESC')
      ->limit($limit)
      ->offset($offset)
      ->get();

    $data = array();

    if ($select->success && !empty($select->result)) {
      foreach ($select->result as $key => $value) {
        $data['indexes'][$value['id']] = $key;
        $data['reports'][$key] = array(
          'id' => $value['id'],
          'cid' => $value['cid'],
          'new' => $value['new'],
          'text' => $this->filter($value['text']),
          'authorId' => $value['uid'],
          'authorName' => $value['name'],
          'authorAvatar' => isset($value['avatar']) ? json_decode($value['avatar']) : null,
          'authorRole' => $value['role'],
          'authorPuid' => $value['puid'],
          'datePublished' => array(
            'origin' => $value['date'],
            'seconds' => strtotime($value['date']),
            'title' => (isset($_SESSION['time_zone'])) ?
              Helper::changeDate(date('d.m.Y H:i:s', strtotime($value['date']) + ($_SESSION['time_zone'] * 60 * 60))) :
              Helper::changeDate(date('d.m.Y H:i:s T', strtotime($value['date']))),
            'view' => Helper::changeTime($value['date']),
          )
        );
      }
    }

    return $data;
  }

  /**
   * Get amount of reports
   *
   * @param  int $cid comment ID
   * @return int
   */
  public function getCountReports($cid = '')
  {
    $reports = DB::table($this->prefix . 'reports');
    if (!empty($cid)) {
      $reports->where('cid', (int) $cid);
    }
    $count = $reports->count("id");

    return $count->success ? $count->result : 0;
  }

  /**
   * Mark the report as read
   *
   * @param  int $id
   * @return bool
   */
  public function readReport($id)
  {
    $update = DB::table($this->prefix . 'reports')
      ->set(array(
        'new' => 0
      ))
      ->where('id', $id)
      ->update();

    return $update->success && $update->result->affected_rows > 0 ? true : false;
  }
  
  /**
   * Mark all reports as read
   *
   * @param  int $cid comment ID
   * @return bool
   */
  public function readAllReport($cid)
  {
    $update = DB::table($this->prefix . 'reports')
      ->set(array(
        'new' => 0
      ))
      ->where('cid', $cid)
      ->update();

    return $update->success && $update->result->affected_rows > 0 ? true : false;
  }
  
  /**
   * Delete report by ID
   *
   * @param  int $id
   * @return bool
   */
  public function deleteReport($id)
  {
    $report = DB::table($this->prefix . 'reports')
      ->select('cid')
      ->where('id', (int) $id)
      ->first();

    if (!$report->success || empty($report->result)) {
      return false;
    }

    $cid = $report->result['cid'];

    $delete = DB::table($this->prefix . 'reports')
      ->where('id', (int) $id)
      ->delete();

    if ($delete->success && $delete->result->affected_rows > 0) {
      DB::table($this->prefix . 'comments')
        ->set(array(
          'reports' => array('reports', '-', 1)
        ))
        ->where('id', (int) $cid)
        ->update();
    }

    return $delete > 0 ? true : false;
  }

  /**
   * Delete reports by comment ID
   *
   * @param  int $cid
   * @return bool
   */
  public function deleteReports($cid)
  {
    $delete = DB::table($this->prefix . 'reports')
      ->where('cid', (int) $cid)
      ->delete();

    if ($delete->success && $delete->result->affected_rows > 0) {
      DB::table($this->prefix . 'comments')
        ->set(array(
          'reports' => array('reports', '-', $delete->result->affected_rows)
        ))
        ->where('id', (int) $cid)
        ->update();
    }

    return $delete->success && $delete->result->affected_rows > 0 ? true : false;
  }

  /**
   * Get flood by IP
   *
   * @param  string $ip
   * @return array
   */
  public function getFlood($ip)
  {
    $flood = DB::table($this->prefix . 'flood')
      ->select()
      ->where('ip', $ip)
      ->orderBy('id', 'DESC')
      ->first();

    return $flood->success ? $flood->result : array();
  }
 
  /**
   * Get count flood by IP
   *
   * @param  string $ip
   * @return int
   */
  public function countFloodByIp($ip)
  {
    $countFlood = DB::table($this->prefix . 'flood')
      ->where('ip', $ip)
      ->count();

    return $countFlood->success ? $countFlood->result : 0;
  }
  
  /**
   * Add flood in DB
   *
   * @param  int $uid
   * @return bool
   */
  public function setFlood($uid)
  {
    $User = new User();

    $ip = $User->getIp();
    $date = date("Y-m-d H:i:s");

    $insert = DB::table($this->prefix . 'flood')
      ->set(array(
        'uid' => $uid,
        'ip' => $ip,
        'date_create' => $date,
        'date_update' => $date
      ))
      ->insert();

    return $insert->success && $insert->result->affected_rows > 0 ? true : false;
  }
  
  /**
   * Update flood in DB
   *
   * @param  int $id
   * @return bool
   */
  public function updateFlood($id)
  {
    $date = date("Y-m-d H:i:s");

    $update = DB::table($this->prefix . 'flood')
      ->set(array(
        'date_update' => $date
      ))
      ->where('id', $id)
      ->limit(1)
      ->update();

    return $update->success && $update->result->affected_rows > 0 ? true : false;
  }
 
  /**
   * Start antiflood
   *
   * @param  int $uid
   * @return bool
   */
  public function antiFlood($uid)
  {
    $timeStartFlood = $this->config['timeStartFlood'];
    $countFloodMessage = $this->config['countFloodMessage'];
    $timeoutFlood = $this->config['timeoutFlood'];
    $commonTimeFlood = $this->config['commonTimeFlood'];
    $commonCountFlood = $this->config['commonCountFlood'];
    $timer = 0;

    $User = new User();

    $ip = $User->getIp();
    $flood = $this->getFlood($ip);

    if (!empty($flood) && strtotime($flood['date_create']) + $commonTimeFlood > time()) {
      $timer = $timeoutFlood;

      if (time() - strtotime($flood['date_update']) < $timer) {
        return array(
          'status' => 'timeout',
          'timer' => (strtotime($flood['date_update']) + $timer) - time(),
        );
      } else {
        $this->updateFlood($flood['id']);
      }
    }

    $userComments = $this->getByUid($uid, array('limit' => $countFloodMessage));
    if (!empty($userComments)) {
      $countComments = count($userComments);
      $userCommentFirst = array_pop($userComments);
      $userCommentLast = array_shift($userComments);

      if ($countComments >= $countFloodMessage && time() - strtotime($userCommentFirst['date_create']) < $timeStartFlood) {
        if ($this->countFloodByIp($ip) >= $commonCountFlood) {
          $User->ban(array($uid), null, 'flood');
          $User->banIp(array($ip));
          return array(
            'status' => 'banIp',
          );
        }
        $timer = $timeoutFlood;
        $this->setFlood($uid);
      }

      if (time() - strtotime($userCommentLast['date_create']) < $timer) {
        return array(
          'status' => 'timeout',
          'timer' => strtotime($userCommentLast['date_create']) + $timer - time(),
        );
      }
    }

    return false;
  }
  
  /**
   * Write string in spam
   *
   * @param  string $text
   * @param  string $ip
   * @return int insert_id or 0
   */
  public function setSpam($text, $ip = '0.0.0.0')
  {
    $date = date("Y-m-d H:i:s");

    $insert = DB::table($this->prefix . 'spam')
      ->set(array(
        'ip' => $ip,
        'text' => $text,
        "hash_text = UNHEX(MD5('" . $text . "'))",
        'date_create' => $date,
      ))
      ->insertOrUpdate();

    return $insert->success && $insert->result->insert_id > 0 ? $insert->result->insert_id : 0;
  }

  /**
   * Check exists string in spam
   *
   * @param  string $text
   * @return bool
   */
  public function existsSpam($text)
  {
    $spam = DB::table($this->prefix . 'spam')
      ->where("hash_text = UNHEX(MD5('" . $text . "'))")
      ->count();

    return $spam->success && $spam->result > 0 ? true : false;
  }
 
  /**
   * Check comment for spam
   *
   * @param  string $text
   * @param  int $uid
   * @return bool
   */
  public function checkSpam($text, $uid)
  {
    if ($this->existsSpam($text)) {
      return true;
    }

    $User = new User();

    $ip = $User->getIp();
    $comments = $this->getByIpOrUid($ip, $uid, array('limit' => 5));

    if (empty($comments)) {
      return false;
    }

    $countSpamMedium = 0;
    $countSpamHard = 0;
    foreach ($comments as $comment) {
      similar_text($comment['text'], $text, $percent);

      if ((int) $percent === 100) {
        $countSpamHard++;
      }
      if ($percent > 75) {
        $countSpamMedium++;
      }
    }

    if ($countSpamHard > 1) {
      $this->setSpam($text, $User->getIp());
      return true;
    } elseif ($countSpamMedium > 2) {
      $this->setSpam($text, $User->getIp());
      return true;
    } elseif ($countSpamHard === 1 || $countSpamMedium === 2) {
      return true;
    } else {
      return false;
    }
  }
  
  /**
   * Check Stop-Words
   *
   * @param  string $str
   * @return mixed array or bool
   */
  public function checkStopWords($str)
  {
    $words = DB::table($this->prefix . 'stop_words')->select('word')->get();

    if (!$words->success || empty($words->result)) {
      return false;
    }

    $stopWords = array();
    foreach ($words as $value) {
      $stopWords[] = addcslashes($value['word'], '~');
    }
    $stopWords = implode('|', $stopWords);

    if (preg_match_all("~(?<![a-zA-Zа-яА-ЯеЁ])(?:" . $stopWords . ")(?![a-zA-Zа-яА-ЯеЁ])~iu", $str, $matches)) {
      return array(
        'status' => 'stop_words',
        'words' => implode(', ', $matches[0])
      );
    } else {
      return false;
    }
  }
 
  /**
   * Get Stop-Words
   *
   * @return array
   */
  public function getStopWords()
  {
    $words = DB::table($this->prefix . 'stop_words')->orderBy('id', 'DESC')->get();

    return $words->success ? $words->result : array();
  }

  /**
   * Update Stop-Words
   *
   * @param  array $stopWords
   * @return bool
   */
  public function updateStopWords($stopWords)
  {
    if (empty($stopWords)) {
      return false;
    }

    DB::table($this->prefix . 'stop_words')->truncate();

    $set = array();
    $i = 0;
    foreach ($stopWords as $value) {
      $value->word = trim($value->word);
      if (!empty($value->word)) {
        $set[] = array(
          trim($value->word),
        );
      }
      $i++;
    }

    DB::table($this->prefix . 'stop_words')
      ->values(array('word'), $set)
      ->insert();

    return true;
  }

  /**
   * Add Stop-Words
   *
   * @param  string $word
   * @return int insert_id or 0
   */
  public function setStopWords($word)
  {
    $insert = DB::table($this->prefix . 'stop_words')
      ->set(array(
        'word' => $word
      ))
      ->insertGetId();

    return $insert->success && $insert->result > 0 ? $insert->result : 0;
  }

  /**
   * Delete Stop-Words
   *
   * @param  int $id
   * @return bool
   */
  public function deleteStopWords($id)
  {
    $delete = DB::table($this->prefix . 'stop_words')
      ->where('id', $id)
      ->delete();

    return $delete->success && $delete->result->affected_rows > 0 ? true : false;
  }

  /**
   * Get count spam
   *
   * @return int
   */
  public function getCountSpam()
  {
    $count = DB::table($this->prefix . 'spam')->count();

    return $count->success ? $count->result : 0;
  }

  /**
   * Get spam
   *
   * @param  array $params
   * @return array
   */
  public function getSpam($params = array())
  {
    $limit = $params['limit'];
    $offset = isset($params['listId']) ? $limit * $params['listId'] : 0;

    $spam = DB::table($this->prefix . 'spam')
      ->select('id', 'ip', 'text', 'date_create')
      ->orderBy('id', 'DESC')
      ->limit($limit)
      ->offset($offset)
      ->get();

    return $spam->success ? $spam->result : array();
  }
 
  /**
   * Delete spam
   *
   * @param  int $id
   * @return bool
   */
  public function deleteSpamById($id)
  {
    $delete = DB::table($this->prefix . 'spam')
      ->where('id', $id)
      ->delete();

    return $delete->success && $delete->result->affected_rows > 0 ? true : false;
  }
 
  /**
   * Get emoji
   *
   * @return array
   */
  public function getEmoji()
  {
    $path = $this->config['resource'] . '/img/emoji/';
    $dir = array_slice(scandir($_SERVER['DOCUMENT_ROOT'] . $path), 2);
    $emoji = array();

    foreach ($dir as $file) {
      if (is_file($_SERVER['DOCUMENT_ROOT'] . $path . $file)) {
        $emoji[] = $path . $file;
      }
    }

    return $emoji;
  }
 
  /**
   * Filter comments text
   *
   * @param  string $text
   * @param  array $params
   * @return string
   */
  public function filter($text, $params = array())
  {
    $config = App::config('common');

    if ($config['emoji']) {
      $emoji = App::config('emoji');
      foreach ($emoji as $key => &$value) {
        $value = '[emoji]<span class="cz-emoji-view" data-emoji-code="' . $key . '" style="background-image: url(' . $config['resource'] . '/img/emoji/smile.png); background-position: -' . $value[0] / 20 * (isset($params['emojiSize']) ? $params['emojiSize'] : 20) . 'px -' . $value[1] / 20 * (isset($params['emojiSize']) ? $params['emojiSize'] : 20) . 'px;"></span>[/emoji]';
      }

      $text = strtr($text, $emoji);
    }

    $text = htmlentities($text);

    if ($config['emoji']) {
      $text = preg_replace('/\[emoji\]&lt;span class=&quot;cz-emoji-view&quot; data-emoji-code=&quot;(.+?)&quot; style=&quot;background-image: url\((.+?)\); background-position:(.+?);&quot;&gt;&lt;\/span&gt;\[\/emoji\]/iu', '<span class="cz-emoji-view" data-emoji-code="$1" style="background-image: url($2); background-position:$3;"></span>', $text);
    }
    if ($config['links']) {
      $text = preg_replace('/(?:(?<=[^\w])|(?<![^\s]))(https?:\/\/[^\s\^`\'"*{}<>]+(?<![.,:;!?]))/iu', '<a href="$1" rel="nofollow" target="_blank">$1</a>', $text);
    }

    $text = nl2br($text);

    return $text;
  }
}
