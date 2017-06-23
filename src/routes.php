<?php


	/*=============  CATEGORIES  ==============*/

	Route::group( [ 'middleware' => 'web' ], function (){
/// packages/egorryaroslavl/partners
		Route::get( '/admin/partners', 'egorryaroslavl\partners\PartnersController@index' );
		Route::get( '/admin/partners/create', 'egorryaroslavl\partners\PartnersController@create' );
		Route::get( '/admin/partners/{id}/edit', 'egorryaroslavl\partners\PartnersController@edit' );
		Route::get( '/admin/partners/{id}/delete', 'egorryaroslavl\partners\PartnersController@destroy' );
		Route::post( '/admin/partners/store', 'egorryaroslavl\partners\PartnersController@store' )->name( 'partners-store' );
		Route::post( '/admin/partners/update', 'egorryaroslavl\partners\PartnersController@update' )->name( 'partners-update' );

		Route::post( '/translite', 'egorryaroslavl\partners\PartnersController@translite' )->name( 'translite' );


		Route::post( '/changestatus', 'egorryaroslavl\partners\PartnersController@changestatus' )->name( 'changestatus' );


		Route::post( '/reorder', 'egorryaroslavl\partners\PartnersController@reorder' )->name( 'reorder' );


	} );

	Route::post( '/iconsave', 'egorryaroslavl\admin\AdminController@iconsave' );
	Route::get( '/iconget', 'egorryaroslavl\admin\AdminController@iconget' );
	Route::any( '/icondelete', 'egorryaroslavl\admin\AdminController@icondelete' );
	/*=============  /CATEGORIES  ==============*/

