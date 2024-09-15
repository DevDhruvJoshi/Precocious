<?php

namespace App\Model;

class Contact extends \System\App\Model {

    public static $_Table = 'Contacts';
    public static $_ID = 'ID';
    public static $_Trash = 'Deleted'; // Int 1 default 0
    public static $_TrashAt = 'DeletedAt'; // Int 11 default null

    /*     * /
      public function __construct() {

      }
      /* */
}
