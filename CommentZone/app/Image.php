<?php

namespace app;

use app\App;
use finfo;

class Image
{
  private $resource;
  private $tmp;
  private $extension;
  private $newName;
  private $size;
  private $mime;
  private $path;

  public function __construct()
  {
    $App = new App();
    $config = $App->config('common');

    $this->resource = $config['resource'];
    $this->tmp = $config['tmpFolder'];
    $this->extension = array('image/jpeg' => '.jpg', 'image/png' => '.png');

    $this->path['dir'] = $_SERVER['DOCUMENT_ROOT'];
    $this->path['resource'] = $this->resource;
  }

  private function createTmpFolder()
  {
    $this->path['tmp'] = $this->tmp;
    $pathStr = implode('/', $this->path);
    if (!file_exists($pathStr)) {
      mkdir($pathStr);
    }

    return $this;
  }

  private function createTmpCurrentFolder()
  {
    $this->path['current'] = date("Y-m-d");
    $pathStr = implode('/', $this->path);
    if (!file_exists($pathStr)) {
      mkdir($pathStr);
    }

    return $this;
  }

  private function createTmpImageFolder($file)
  {
    $this->path['folder'] = md5_file($file);
    $pathStr = implode('/', $this->path);
    if (!file_exists($pathStr)) {
      mkdir($pathStr);
    }

    return $this;
  }

  private function validate($file)
  {
    if (empty($file)) {
      return false;
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file);

    if (!isset($this->extension[$mime])) {
      return false;
    }

    $this->mime = $mime;

    return true;
  }

  private function save($tmp_name, $path, $w, $h)
  {
    $ImageResize = new ImageResize($tmp_name);
    if ($w === 'auto') {
      $ImageResize->resizeToHeight($h);
    } elseif ($h === 'auto') {
      $ImageResize->resizeToWidth($w);
    } else {
      $ImageResize->crop($w, $h);
    }
    $ImageResize->save($path);
  }

  public function name($name)
  {
    $this->newName = $name;
    return $this;
  }

  public function size($width, $height)
  {
    $this->size = array(
      'width' => $width,
      'height' => $height,
    );
    return $this;
  }

  public function upload($file)
  {
    if (!$this->validate($file)) {
      return false;
    }

    $this->createTmpFolder();
    $this->createTmpCurrentFolder();
    $this->createTmpImageFolder($file);

    $pathStr = implode('/', $this->path);
    $this->save($file, $pathStr . '/' . $this->newName . $this->extension[$this->mime], $this->size['width'], $this->size['height']);
    $response = '/' . implode('/', array_slice($this->path, 2)) . '/' . $this->newName . $this->extension[$this->mime];

    return $response;
  }

  public function uploadLink($prefolder, $file)
  {
    $fileFolder = md5($file);
    $fileName = $this->newName . '.jpg';
    $img = file_get_contents($file);

    if (!$img) {
      return '';
    }

    $this->createPathInStorage($prefolder . '/' . $fileFolder);

    $pathStr = implode('/', $this->path);

    if (file_put_contents($pathStr . '/' . $fileName, $img)) {
      return '/' . implode('/', array_slice($this->path, 2)) . '/' . $fileName;
    } else {
      return '';
    }
  }

  public function createPathInStorage($prefolder)
  {
    $prefolder = substr_replace($prefolder, '/', 2, 0);
    $folder = substr_replace($prefolder, '/', 5, 0);

    $this->path['storage'] = 'files';
    $this->path['folder'] = $folder;
    $pathStr = implode('/', $this->path);

    if (!file_exists($pathStr)) {
      mkdir($pathStr, 755, true);
    }

    return $this;
  }

  public function moveInStorage($prefolder, $file)
  {
    $tmpDir = dirname($file);
    $fileFolder = md5($tmpDir);

    $this->createPathInStorage($prefolder . '/' . $fileFolder);

    if (file_exists($this->path['dir'] . $this->path['resource'] . '/' . $file)) {
      $pathStr = implode('/', $this->path);
      rename($this->path['dir'] . $this->path['resource'] . '/' . $file, $pathStr . '/' . basename($file));
      @rmdir($this->path['dir'] . $this->path['resource'] . '/' . $tmpDir);

      $response = '/' . implode('/', array_slice($this->path, 2)) . '/' . basename($file);
      
      return $response;
    } else return null;
  }

  public function deleteImage($path)
  {
    if (file_exists($path)) {
      if (unlink($path)) {
        return true;
      } else return false;
    } else return true;
  }
}
