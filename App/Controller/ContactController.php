<?php

namespace App\Controller;

use App\Model\Contact;
use System\App\Controller;

class ContactController extends Controller {

    public function Index($param = []) {

        /* * /
        $DB = new DB();
        $D = $DB->Fetch($DB->Query('select * from Contacts;'));
        /* */
/* */
        $D = Contact::All();
        sdd(
                $D
        );
        
        foreach ($D As $C)
            dd($C['Name']);
/* */

        /* * /
        $C= new Contact('Contacts');
        sdd($C->All());
        /* */
        
//        sdd(Contact::All());
        
    }
}
