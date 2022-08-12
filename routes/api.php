<?php

use Aws\Credentials\Credentials;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Aws\Rekognition\RekognitionClient;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/detect/face', function (Request $request) {
	$credentials = new Credentials(env("AWS_ACCESS_KEY_ID"), env("AWS_SECRET_ACCESS_KEY"));
	$client = new RekognitionClient([
		"credentials" => $credentials,
		"region" => "eu-central-1",
		"version" => "2016-06-27"
	]);
	try {
		$result = $client->detectFaces([
			"Attributes" => ["DEFAULT"],
			"Image" => [
				"Bytes" => $request->getContent()
			]
		]);
		return response()->json([
			"isFace" => count($result->get("FaceDetails")) != 0,
		]);
	} catch(\Exception $e) {
		return response()->json([
			"message" => $e->getMessage(),
			"error" => true,
		]);
	}
});
