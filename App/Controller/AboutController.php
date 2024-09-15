<?php

namespace App\Controller;

use App\Model\Contact As Contact;

class AboutController extends \System\App\Controller {

    function index($ID) {
        sdd('ID------------'.$ID);

        //phpinfo();
        /*         * /
          sdd('___________Cache Start__________');

          $Cache = new Cache();
          sdd($Cache->set('MyName','Piyu'));
          sdd($Cache->get('MyName'));

          sdd('___________Cache End__________');
          /* */

        /*         * /
          $CID = Contact::Create([
          'Name' => 'Create',
          'Email' => 'Create@gmail.com',
          'Phone' => 4654654555,
          ]);
          /* */
        /*         * /
          $CID = Contact::Update([
          'Name' => 'DLoveP',
          'Email' => 'DLoveP@gmail.com',
          'Phone' => 4444444444,
          'Status' => 2,
          ], 3, $Where = []);
          /* */


        sdd($CID = Contact::SoftDelete(7));

        sdd('PrimaryKey ------------');
        vd(' Afected Rows ===================== ' . $CID);
        sdd('PrimaryKey ------------');
        /*         */
        $C = new Contact();
        sdd($C->Fetch('select * from Contacts where ID =2;'));
        sdd(Contact::All([], '1 desc', false));
        throw new Exc('test error', 404);
        /* */
    }

    public function current() {
        sdd('call ' . __CLASS__ . ' Fun ' . __FUNCTION__);
        return $this->modelObj->message = "About us today changed by aboutController.";
    }
}
