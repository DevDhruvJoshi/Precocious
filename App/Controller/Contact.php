<?php

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
            sdd($C['Name']);
/* */

        /* * /
        $C= new Contact('Contacts');
        sdd($C->All());
        /* */
        
//        sdd(Contact::All());
        
    }
}
