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
use GuzzleHttp\Psr7\Stream;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

function stringToBinary($string)
{
    $characters = str_split($string);
 
    $binary = [];
    foreach ($characters as $character) {
        $data = unpack('H*', $character);
        $binary[] = base_convert($data[1], 16, 2);
    }
 
    return implode(' ', $binary);    
}

Route::post('/singleFaceWithoutMask', function (Request $request) {
	$credentials = new Credentials(env("AWS_ACCESS_KEY_ID"), env("AWS_SECRET_ACCESS_KEY"));
	$client = new RekognitionClient([
		"credentials" => $credentials,
		"region" => "eu-central-1",
		"version" => "2016-06-27"
	]);

	try {
		$result = $client->detectProtectiveEquipment([
			"Image" => [
				"Bytes" => $request->getContent(),
			],
			"SummarizationAttributes" => [
				"MinConfidence" => 90,
				"RequiredEquipmentTypes" => ["FACE_COVER"]
			]
		]);
		return response()->json([
			"singleFaceWithoutMask" => count($result->get("Persons")) == 1 && count($result->get("Summary")["PersonsWithoutRequiredEquipment"]) == 1,
		]);
	} catch(\Exception $e) {
		return response()->json([
			"message" => $e->getMessage(),
			"error" => true,
		]);
	}
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
				"Bytes" => base64_decode(str_replace("data:image/jpeg;base64,", "", $request->base64)),
			]
		]);
		return response()->json([
			"isFace" => count($result->get("FaceDetails")) != 0,
			"results" => $result->get("FaceDetails")
		]);
	} catch(\Exception $e) {
		return response()->json([
			"message" => $e->getMessage(),
			"error" => true,
		]);
	}
});
