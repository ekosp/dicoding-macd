<?php
//session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);


require_once 'vendor/autoload.php';
require_once "random_string.php";

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
use MicrosoftAzure\Storage\Blob\Models\ListBlobsOptions;
use MicrosoftAzure\Storage\Blob\Models\CreateContainerOptions;
use MicrosoftAzure\Storage\Blob\Models\PublicAccessType;

if (isset($_FILES["file"])) {
    $max_size = 500 * 1024; // 500 KB
//    $destination_directory = "upload/";
    $validextensions = array("jpeg", "jpg", "png");

    $temporary = explode(".", $_FILES["file"]["name"]);
    $file_extension = end($temporary);

    // We need to check for image format and size again, because client-side code can be altered
    if ((($_FILES["file"]["type"] == "image/png") ||
            ($_FILES["file"]["type"] == "image/jpg") ||
            ($_FILES["file"]["type"] == "image/jpeg")
        ) && in_array($file_extension, $validextensions)) {
        if ($_FILES["file"]["size"] < ($max_size)) {
            if ($_FILES["file"]["error"] > 0) {
                echo "<div class=\"alert alert-danger\" role=\"alert\">Error: <strong>" . $_FILES["file"]["error"] . "</strong></div>";
            } else {
                    $sourcePath = $_FILES["file"]["tmp_name"];
                    uploadBlob($sourcePath);
                }
        } else {
            echo "<div class=\"alert alert-danger\" role=\"alert\">The size of image you are attempting to upload is " . round($_FILES["file"]["size"] / 1024, 2) . " KB, maximum size allowed is " . round($max_size / 1024, 2) . " KB</div>";
        }
    } else {
        echo "<div class=\"alert alert-danger\" role=\"alert\">Unvalid image format. Allowed formats: JPG, JPEG, PNG.</div>";
    }
} else {
    echo "<div class=\"alert alert-danger\" role=\"alert\">Gambar tidak terdeteksi</div>";
}

function uploadBlob($fileToUpload)
{
//    echo "upload_blob";

    $connectionString = "DefaultEndpointsProtocol=https;AccountName=ekospstrg;AccountKey=dLG+s3PjRlE0rOPpyCS7gVAoB/cDnGdB8cXZD3U0PCnR3/rOOq7A0Lf1Dw+Bh0V6b8v6wDgURi6s7219Hh9HzA==";
    $blobClient = BlobRestProxy::createBlobService($connectionString);
    $createContainerOptions = new CreateContainerOptions();
    $createContainerOptions->setPublicAccess(PublicAccessType::CONTAINER_AND_BLOBS);
    $containerName = "blockblobs" . generateRandomString();

    try {
        $blobClient->createContainer($containerName, $createContainerOptions);

        $content = fopen($fileToUpload, "r");

        //Upload blob
            $blobClient->createBlockBlob($containerName, "eko_dicoding", $content);

        // List blobs.
        $listBlobsOptions = new ListBlobsOptions();

        do {
            $result = $blobClient->listBlobs($containerName, $listBlobsOptions);
            foreach ($result->getBlobs() as $blob) {
//                echo $blob->getUrl();

                // redirect to analytic page
                header("Location: analytic.php?url=".$blob->getUrl()); /* Redirect browser */
                exit();
            }

            $listBlobsOptions->setContinuationToken($result->getContinuationToken());
        } while ($result->getContinuationToken());

    } catch (ServiceException $e) {
        $code = $e->getCode();
        $error_message = $e->getMessage();
        echo $code . ": " . $error_message . "<br />";
    } catch (InvalidArgumentTypeException $e) {
        $code = $e->getCode();
        $error_message = $e->getMessage();
        echo $code . ": " . $error_message . "<br />";
    }
}

?>
