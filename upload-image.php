<?php
session_start();

require_once 'vendor/autoload.php';
require_once "random_string.php";

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
use MicrosoftAzure\Storage\Blob\Models\ListBlobsOptions;
use MicrosoftAzure\Storage\Blob\Models\CreateContainerOptions;
use MicrosoftAzure\Storage\Blob\Models\PublicAccessType;

if (isset($_FILES["file"]["type"])) {
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
//                if (file_exists($destination_directory . $_FILES["file"]["name"])) {
//                    echo "<div class=\"alert alert-danger\" role=\"alert\">Error: File <strong>" . $_FILES["file"]["name"] . "</strong> already exists.</div>";
//                } else {
                    $sourcePath = $_FILES["file"]["tmp_name"];
//                    $targetPath = $destination_directory . $_FILES["file"]["name"];
//                    $targetPath = $destination_directory . "HelloWorld.".pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION);
//                    move_uploaded_file($sourcePath, $targetPath);

//                $sourcePath = $_FILES["file"]["name"];
//                    echo $targetPath;
//                    uploadBlob($targetPath);

                    uploadBlob($sourcePath);
                }
//            }
        } else {
            echo "<div class=\"alert alert-danger\" role=\"alert\">The size of image you are attempting to upload is " . round($_FILES["file"]["size"] / 1024, 2) . " KB, maximum size allowed is " . round($max_size / 1024, 2) . " KB</div>";
        }
    } else {
        echo "<div class=\"alert alert-danger\" role=\"alert\">Unvalid image format. Allowed formats: JPG, JPEG, PNG.</div>";
    }
}

function uploadBlob($fileToUpload)
{
    $connectionString = "DefaultEndpointsProtocol=https;AccountName=ekospstrg;AccountKey=dLG+s3PjRlE0rOPpyCS7gVAoB/cDnGdB8cXZD3U0PCnR3/rOOq7A0Lf1Dw+Bh0V6b8v6wDgURi6s7219Hh9HzA==";
    $blobClient = BlobRestProxy::createBlobService($connectionString);
    $createContainerOptions = new CreateContainerOptions();
    $createContainerOptions->setPublicAccess(PublicAccessType::CONTAINER_AND_BLOBS);
    $containerName = "blockblobs" . generateRandomString();

    try {
        $blobClient->createContainer($containerName, $createContainerOptions);

//        $myfile = fopen($fileToUpload, "w") or die("Unable to open file!");
//        fclose($myfile);

        $content = fopen($fileToUpload, "r");

//        $content = file_get_contents($fileToUpload);
//        $content =addslashes (file_get_contents($fileToUpload));

        //Upload blob
            $blobClient->createBlockBlob($containerName, "dasdasdasda", $content);

        // List blobs.
        $listBlobsOptions = new ListBlobsOptions();

        do {
            $result = $blobClient->listBlobs($containerName, $listBlobsOptions);
            foreach ($result->getBlobs() as $blob) {
                echo $blob->getUrl();
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
