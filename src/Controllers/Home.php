<?php

namespace App\Controllers;

use Initium\Auth\Cred;
use Initium\PageController;

/**
 * The app's own pages, moved out of the old monolithic User class. These are
 * application concerns, not framework ones, so they live in the skeleton and
 * extend core's PageController (which wires the Plates engine and seeds the
 * layout's login/admin state) while using core's Cred for login state.
 *
 * Templates are referenced through the "app::" folder so a same-named file in
 * this app's templates/ overrides a core default (see Initium\View).
 */
class Home extends PageController {

    public function home_page() {
        $this->templates->addData(['page_title' => SITE_NAME], ['app::basic']);
        echo $this->templates->render('app::home');
    }

    public function logged_in_page() {
        $user = Cred::userDetails();

        if(!$user) {
            header('Location: ' . SITE_URL);
            exit;
        }

        $this->templates->addData(['page_title' => SITE_NAME], ['app::basic']);
        $this->templates->addData(['user' => $user], ['app::logged_in_page']);
        echo $this->templates->render('app::logged_in_page');
    }
}
