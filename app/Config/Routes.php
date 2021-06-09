<?php namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes(true);

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (file_exists(SYSTEMPATH . 'Config/Routes.php'))
{
	require SYSTEMPATH . 'Config/Routes.php';
}

/**
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Pages');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(false);

/**
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
//$routes->get('/', 'Pages::view');


// MORCEAU & VERSION
$routes->add('ajax_version/(:any)', 'Ajax_version::$1');
$routes->add('ajax_morceau/(:any)', 'Ajax_morceau::$1');
$routes->add('morceau/create_version', 'Morceau::create_version');
$routes->add('morceau/update/(:num)', 'Morceau::update/$1');
$routes->add('morceau/create', 'Morceau::create');
$routes->add('morceau', 'Morceau::view');


// JAM ADMIN
$routes->add('ajax_jam/(:any)', 'Ajax_jam::$1');
$routes->add('jam/generate_file/(:any)', 'Jam::generate_file/$1');
$routes->add('jam/presentation/(:any)', 'Jam::presentation/$1');
$routes->add('jam/update_credits/(:any)', 'Jam::update_credits/$1');
$routes->add('jam/update_repetition/(:any)', 'Jam::update_repetition/$1');
$routes->add('jam/create_repetition/(:any)', 'Jam::create_repetition/$1');
$routes->add('jam/update_text_tab/(:any)', 'Jam::update_text_tab/$1');
$routes->add('jam/affect/(:any)', 'Jam::affect/$1');
$routes->add('jam/add_member/(:any)', 'Jam::add_member/$1');
$routes->add('jam/manage/(:any)', 'Jam::manage/$1');
$routes->add('jam/update/(:any)', 'Jam::update/$1');
$routes->add('jam/create', 'Jam::create');

// JAM
$routes->add('jam/invitations/(:any)', 'Jam::invitations/$1');
$routes->add('jam/view_repetition/(:any)', 'Jam::view_repetition/$1');
$routes->add('jam/repetitions/(:any)', 'Jam::repetitions/$1');
$routes->add('jam/inscriptions/(:any)', 'Jam::inscriptions/$1');
//$routes->add('jam/ajax_add_wish', 'jam::ajax_add_wish');
$routes->add('jam/(:any)/', 'Jam::view/$1');
$routes->add('jam', 'Jam::index');


// PLAYLIST
$routes->add('playlist/update/(:any)', 'Playlist::update/$1');
$routes->add('playlist/delete', 'Playlist::delete');
$routes->add('playlist/create', 'Playlist::create');
$routes->add('playlist/(:num)/', 'Playlist::view/$1');
$routes->add('/playlist', 'Playlist::view');


// NEWS (liées au GROUP)
$routes->add('group/create_news', 'Group::create_news');
$routes->add('group/ajax_create_news', 'Group::ajax_create_news');
$routes->add('group/update_news/(:num)', 'Group::update_news/$1');
$routes->add('group/ajax_update_news/(:num)', 'Group::ajax_update_news/$1');
$routes->add('group/delete_news', 'Group::delete_news');


// GROUP
$routes->add('group/ajax_create', 'Group::ajax_create');
$routes->add('group/ajax_delete', 'Group::ajax_delete');
$routes->add('group/send_mail', 'Group::send_mail');
$routes->add('group/ajax_send_mail', 'Group::ajax_send_mail');
$routes->add('group/(:any)', 'Group::view/$1');
$routes->add('group', 'Group');


// MEMBERS
$routes->add('ajax_members/(:any)', 'Ajax_members::$1');
$routes->add('members/validateMail/(:any)/(:segment)', 'Members::validateMail/$1/$2');
$routes->add('members/update_instrument/(:any)/(:num)', 'Members::update_instrument/$1/$2');	// $1 : memberSlug	$2 : instruId
$routes->add('members/add_instrument/(:any)', 'Members::add_instrument/$1');
$routes->add('members/create', 'Members::create');
$routes->add('members/login', 'Members::login');
$routes->add('members/logout', 'Members::logout');
$routes->add('members/(:any)/', 'Members::view/$1');


// MESSAGE & DISCUSSION
$routes->add('ajax_discussion/(:any)', 'Ajax_discussion::$1');
$routes->add('message/(:any)/(:any)', 'Message::view/$1/$2');	// $1 : url_title(pseudo)	$2 : url_title(dest)
$routes->add('message/(:any)/', 'Message::view/$1');

// RESSOURCES
$routes->add('ressources/(:any)', 'Ressources/$1');


// AJAX GLOBAL
$routes->add('ajax_instruments/(:any)', 'Ajax_instruments::$1');
$routes->add('ajax_file/(:any)', 'Ajax_file::$1');
$routes->add('ajax/(:any)', 'Ajax::$1');


// GLOBAL
$routes->add('cron/(:any)', 'Cron::$1');
$routes->add('/', 'Pages::view');
$routes->add('/(:any)', 'Pages::view/$1');


/**
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need to it be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php'))
{
	require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
