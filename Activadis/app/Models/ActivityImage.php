<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityImage extends Model
{
  protected $fillable = ['activity_id', 'path', 'position'];

  /**
   * Publieke url van de foto.
   * als APP_URL niet overeenkomt met de adresbalk (bijv. 127.0.0.1 of localhost).
   */
  public function url(): string
  {
    return asset('storage/' . $this->path);
  }
}
