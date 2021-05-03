<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group (['prefix'=>'app','middleware' => 'auth.client','namespace'=>'Api'], function () {

    Route::post('/signup', 'UserController@signUp');
    Route::post('/login', 'UserController@login');
    Route::post('/upload', 'CommonController@upload');
    Route::get('/statelist', 'CommonController@stateList');

});

Route::group(['prefix'=>'app','middleware' => ['jwt.verify'],'namespace'=>'Api'], function(){

    Route::post('/auth', 'UserController@getAuthUser');
    Route::post('/updateprofile/{id}', 'UserController@updteProfile');
    Route::get('/alluser', 'UserController@allUser');
    Route::get('/circulatoruser', 'UserController@circulatorUser');
    Route::delete('/removeuser/{userid}', 'UserController@removeUser');
    Route::post('/checksigner', 'UserController@checkSigner');
    Route::get('/reviewhistory/{userid}', 'PetitionController@reviewHistory');
    Route::post('/addpetition', 'PetitionController@addPetition');
    Route::post('/updatepetition/{id}', 'PetitionController@updatePetition');
    Route::post('/signedpetition', 'PetitionController@signedPetition');

    Route::get('/allpetition', 'PetitionController@allPetition');
    Route::get('/sponsorpetition/{sponsorid}', 'PetitionController@sponsorPetition');
    Route::get('/petitiondetails/{id}', 'PetitionController@petitionDetails');
    Route::get('/signerpetition/{stateid}/{name}', 'PetitionController@signerPetition');
    Route::get('/circulatorpetition/{circulatorid}', 'PetitionController@circulatorPetition');
    
    Route::get('/notificationlist/{id}', 'NotificationController@notificationList');

    Route::get('/circulatorsignerlist/{circulator_id}', 'CirculatorController@circulatorSignerList');
    Route::get('/getvoter/{voter_id}', 'CirculatorController@getVoter');
    Route::get('/rejectsigner/{voterid}', 'CirculatorController@rejectSigner');
    Route::get('/acceptsigner/{voterid}', 'CirculatorController@acceptSigner');
    Route::get('/signercirculatorlist/{petitionid}', 'CirculatorController@signerCirculatorList');
    Route::post('/createmeeting', 'CirculatorController@createMeeting');
    Route::post('/verifycirculator', 'CirculatorController@verifyCirculator');
    Route::post('/addfeedback', 'CirculatorController@addFeedBack');

    Route::get('/paymenthistory', 'PaymentController@paymentHistory');
    Route::get('/donatehistory', 'PaymentController@donateHistory');
    Route::post('/signerpayment', 'PaymentController@addSignerPayment');
    Route::post('/updatesignerpayment/{id}', 'PaymentController@updateSignerPayment');
    Route::post('/donatepayment', 'PaymentController@donatePayment');
    Route::post('/getsignerpayment', 'PaymentController@getSignerPayment');
    Route::get('/paymentgatewaylist/{userid}', 'PaymentController@paymentGatewayList');
    Route::get('/paypaltoken', 'PaymentController@paypalToken');
    Route::post('/contact', 'CommonController@contact');
    Route::get('/getfeedback', 'CommonController@getFeedBack');


});

