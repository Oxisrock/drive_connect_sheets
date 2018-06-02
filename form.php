<?php
require __DIR__ . '/vendor/autoload.php';

use Google\Spreadsheet\DefaultServiceRequest;
use Google\Spreadsheet\ServiceRequestFactory;

$new_call = isset($_POST['new_call']);
$new_meeting = isset($_POST['new_meeting']);
$new_close_job = isset($_POST['new_close_job']);
$celda_call = $_POST['celda_call'];
$celda_meeting = $_POST['celda_meeting'];
$celda_close_job = $_POST['celda_job_close'];
/*
 * We need to get a Google_Client object first to handle auth and api calls, etc.
 */
$client = new \Google_Client();
$client->setApplicationName('agentes');
$client->setScopes([\Google_Service_Sheets::SPREADSHEETS]);
$client->setAccessType('offline');

/*
 * The JSON auth file can be provided to the Google Client in two ways, one is as a string which is assumed to be the
 * path to the json file. This is a nice way to keep the creds out of the environment.
 *
 * The second option is as an array. For this example I'll pull the JSON from an environment variable, decode it, and
 * pass along.
 */
$client->setAuthConfig(__DIR__.'/client_secret.json');
/*
 * With the Google_Client we can get a Google_Service_Sheets service object to interact with sheets
 */
$sheets = new \Google_Service_Sheets($client);

/*
 * To read data from a sheet we need the spreadsheet ID and the range of data we want to retrieve.
 * Range is defined using A1 notation, see https://developers.google.com/sheets/api/guides/concepts#a1_notation
 */
$data = [];

// The first row contains the column titles, so lets start pulling data from row 2
$currentRow = 2;
$count = 1;
// The range of A2:H will get columns A through H and all rows starting from row 2
$spreadsheetId = '1ZE-I59hGBsLrrt4Kar8R2avgmTntFX9DXHYNuYnvyFI';
$range = 'A2:G';
$rows = $sheets->spreadsheets_values->get($spreadsheetId, $range, ['majorDimension' => 'ROWS']);
if (isset($rows['values'])) {
    foreach ($rows['values'] as $row) {
        /*
         * If first column is empty, consider it an empty row and skip (this is just for example)
         */
        if (empty($row[0])) {
            break;
        }

        $data[] = [
            'nombre' => $row[0],
            'correo' => $row[1],
            'cedula' => $row[2],
            'edad' => $row[3],
            '#_of_call' => $row[4],
            '#_of_client_meetings' => $row[5],
            '#_of_jobs_closed' => $row[6],
        ];

        /*
         * Now for each row we've seen, lets update the I column with the current date
         */
         if($new_call) {
             $updateRange = 'E'.$celda_call;
             foreach ($data as $key) {
              $count_call = $key['#_of_call'];
             }

             $count_call++;
             $updateBody = new \Google_Service_Sheets_ValueRange([
                 'range' => $updateRange,
                 'majorDimension' => 'ROWS',
                 'values' => ['values' => $count_call],
             ]);
             $sheets->spreadsheets_values->update(
                 $spreadsheetId,
                 $updateRange,
                 $updateBody,
                 ['valueInputOption' => 'USER_ENTERED']
             );
           }
         if($new_meeting) {
             $updateRange = 'F'.$celda_meeting;
             foreach ($data as $key) {
              $count_meeting = $key['#_of_client_meetings'];
             }
             $count_meeting++;
             $updateBody = new \Google_Service_Sheets_ValueRange([
                 'range' => $updateRange,
                 'majorDimension' => 'ROWS',
                 'values' => ['values' => $count_meeting],
             ]);
             $sheets->spreadsheets_values->update(
                 $spreadsheetId,
                 $updateRange,
                 $updateBody,
                 ['valueInputOption' => 'USER_ENTERED']
             );
           }
         if($new_close_job) {
             $updateRange = 'G'.$celda_close_job;
             foreach ($data as $key) {
              $count_close_job = $key['#_of_jobs_closed'];
             }
             $count_close_job++;
             $updateBody = new \Google_Service_Sheets_ValueRange([
                 'range' => $updateRange,
                 'majorDimension' => 'ROWS',
                 'values' => ['values' => $count_close_job],
             ]);
             $sheets->spreadsheets_values->update(
                 $spreadsheetId,
                 $updateRange,
                 $updateBody,
                 ['valueInputOption' => 'USER_ENTERED']
             );
           }
           $currentRow++;
           $count++;
    }
}

var_dump($data);
