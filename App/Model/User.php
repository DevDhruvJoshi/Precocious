<?php

namespace App\Model;

class User extends \System\App\Model {

    public static $_Table = 'Users';
    public static $_ID = 'ID';
    public static $_Trash = 'Deleted'; // Int 1 default 0
    public static $_TrashAt = 'DeletedAt'; // Int 11 default null

    /*     * /
      public function __construct() {

      }
      /* */
}
