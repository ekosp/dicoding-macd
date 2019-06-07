<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
          content="How to create an image upload form without page refresh using Bootstrap, jQuery AJAX and PHP.">
    <meta name="author" content="ShinDarth">

    <title>Azure Vision Image Analytic</title>

    <link rel="icon" href="http://getbootstrap.com/favicon.ico">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap-theme.min.css">
    <style>body {
            padding-top: 50px;
        }

        .navbar-inverse .navbar-nav > li > a {
            color: #DBE4E1;
        }</style>

    <!--[if IE]>
    <script src="https://cdn.jsdelivr.net/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://cdn.jsdelivr.net/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body>

<script type="text/javascript">
    function processImage() {
        // **********************************************
        // *** Update or verify the following values. ***
        // **********************************************

        // Replace <Subscription Key> with your valid subscription key.
        var subscriptionKey = "8a703d408438442db192816f733663c8";

        // You must use the same Azure region in your REST API method as you used to
        // get your subscription keys. For example, if you got your subscription keys
        // from the West US region, replace "westcentralus" in the URL
        // below with "westus".
        //
        // Free trial subscription keys are generated in the "westus" region.
        // If you use a free trial subscription key, you shouldn't need to change
        // this region.
        var uriBase =
            "https://southeastasia.api.cognitive.microsoft.com/vision/v2.0/analyze";

        // Request parameters.
        var params = {
            "visualFeatures": "Categories,Description,Color",
            "details": "",
            "language": "en",
        };

        // Display the image.
        var sourceImageUrl = document.getElementById("inputImage").value;
        // document.querySelector("#sourceImage").src = sourceImageUrl;

        // var sourceImageUrl = "http://upload.wikimedia.org/wikipedia/commons/3/3c/Shaki_waterfall.jpg";

        // Make the REST API call.
        $.ajax({
            url: uriBase + "?" + $.param(params),

            // Request headers.
            beforeSend: function (xhrObj) {
                xhrObj.setRequestHeader("Content-Type", "application/json");
                xhrObj.setRequestHeader(
                    "Ocp-Apim-Subscription-Key", subscriptionKey);
            },

            type: "POST",

            // Request body.
            data: '{"url": ' + '"' + sourceImageUrl + '"}',
        })

            .done(function (data) {
                // Show formatted JSON on webpage.
                $("#responseTextArea").val(JSON.stringify(data, null, 2));
            })

            .fail(function (jqXHR, textStatus, errorThrown) {
                // Display error message.
                var errorString = (errorThrown === "") ? "Error. " :
                    errorThrown + " (" + jqXHR.status + "): ";
                errorString += (jqXHR.responseText === "") ? "" :
                    jQuery.parseJSON(jqXHR.responseText).message;
                alert(errorString);
            });
    };
</script>

<?php

require_once 'vendor/autoload.php';
require_once "random_string.php";

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
use MicrosoftAzure\Storage\Blob\Models\ListBlobsOptions;
use MicrosoftAzure\Storage\Blob\Models\CreateContainerOptions;
use MicrosoftAzure\Storage\Blob\Models\PublicAccessType;

//$connectionString = "DefaultEndpointsProtocol=https;AccountName=".getenv('ACCOUNT_NAME').";AccountKey=".getenv('ACCOUNT_KEY');
$connectionString = "DefaultEndpointsProtocol=https;AccountName=ekospstorage;AccountKey=kFS9qE3paueUy/nVVyuTMKB9u73OPM6yOKW/G6GZ2BDi6D9fhnntYqJ6MAVhIQpY9Zqi8ToiGC+rSQd18wGQ6w==";

// Create blob client.
$blobClient = BlobRestProxy::createBlobService($connectionString);

if (isset($_FILES["file"])) {
    // Create container options object.
    $createContainerOptions = new CreateContainerOptions();

    $fileToUpload = $_FILES["file"]["tmp_name"];

    // Set public access policy. Possible values are
    // PublicAccessType::CONTAINER_AND_BLOBS and PublicAccessType::BLOBS_ONLY.
    // CONTAINER_AND_BLOBS:
    // Specifies full public read access for container and blob data.
    // proxys can enumerate blobs within the container via anonymous
    // request, but cannot enumerate containers within the storage account.
    //
    // BLOBS_ONLY:
    // Specifies public read access for blobs. Blob data within this
    // container can be read via anonymous request, but container data is not
    // available. proxys cannot enumerate blobs within the container via
    // anonymous request.
    // If this value is not specified in the request, container data is
    // private to the account owner.

    $createContainerOptions->setPublicAccess(PublicAccessType::CONTAINER_AND_BLOBS);

// Set container metadata.
// $createContainerOptions->addMetaData("key1", "value1");
// $createContainerOptions->addMetaData("key2", "value2");

    $containerName = "blockblobs" . generateRandomString();

    try {
// Create container.
        $blobClient->createContainer($containerName, $createContainerOptions);

// Getting local file so that we can upload it to Azure
// $myfile = fopen($fileToUpload, "w") or die("Unable to open file!");
// fclose($myfile);

# Upload file as a block blob
        echo "Uploading BlockBlob: " . PHP_EOL;
        echo $fileToUpload;
        echo "<br/>";

        $content = fopen($fileToUpload, "r");

//Upload blob
        $blobClient->createBlockBlob($containerName, $fileToUpload, $content);

// List blobs.
        $listBlobsOptions = new ListBlobsOptions();
        $listBlobsOptions->setPrefix("HelloWorld");

        echo "These are the blobs present in the container: ";

        do {
            $result = $blobClient->listBlobs($containerName, $listBlobsOptions);
            foreach ($result->getBlobs() as $blob) {
                echo $blob->getName() . ": " . $blob->getUrl() . "<br/>";
            }

            $listBlobsOptions->setContinuationToken($result->getContinuationToken());
        } while ($result->getContinuationToken());
        echo "<br/>";

// Get blob.
        echo "This is the content of the blob uploaded: ";
        $blob = $blobClient->getBlob($containerName, $fileToUpload);

// header("Content-Type:image/jpeg");
// header('Content-Disposition: attachment; filename="' . $blob_name . '"');

        fpassthru($blob->getContentStream());
        echo "<br/>";
    } catch (ServiceException $e) {
// Handle exception based on error codes and messages.
// Error codes and messages are here:
// http://msdn.microsoft.com/library/azure/dd179439.aspx
        $code = $e->getCode();
        $error_message = $e->getMessage();
        echo $code . ": " . $error_message . "<br/>";
    } catch (InvalidArgumentTypeException $e) {
// Handle exception based on error codes and messages.
// Error codes and messages are here:
// http://msdn.microsoft.com/library/azure/dd179439.aspx
        $code = $e->getCode();
        $error_message = $e->getMessage();
        echo $code . ": " . $error_message . "<br/>";
    }
}

?>

<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="#">Azure Storage and Vision</a>
        </div>

        <div class="collapse navbar-collapse">
            <ul class="nav navbar-nav">
                <li class="active"><a href="#">Live demo</a></li>
                <!--<li><a target="_blank" href="https://github.com/ShinDarth/Bootstrap-image-upload-form/blob/master/index.html">index.php</a></li>-->
                <!--<li><a target="_blank" href="https://github.com/ShinDarth/Bootstrap-image-upload-form/blob/master/upload-image.js">upload-image.js</a></li>-->
                <!--<li><a target="_blank" href="https://github.com/ShinDarth/Bootstrap-image-upload-form/blob/master/upload-image.php">upload-image.php</a></li>-->
                <!--<li><a target="_blank" href="https://github.com/ShinDarth/Bootstrap-image-upload-form/archive/master.zip">Download  full source code</a></li>-->
            </ul>
        </div><!--.nav-collapse -->
    </div>
</nav>

<div class="container">

    <div style="max-width: 650px; margin: auto;">
        <h1 class="page-header">Upload and Analyse Image</h1>
        <p class="lead">Select a PNG or JPEG image, having maximum size <span id="max-size"></span> KB.</p>

        <form id="upload-image-form" action="" method="post" enctype="multipart/form-data">
            <div id="image-preview-div" style="display: none">
                <label>Selected image:</label>
                <br>
                <img id="preview-img" src="noimage">
            </div>

            <!--<input type="text" name="inputImage" id="inputImage" hidden="hidden"-->
            <!--value="http://upload.wikimedia.org/wikipedia/commons/3/3c/Shaki_waterfall.jpg" />-->

            <div class="form-group">
                <input type="file" name="file" id="file" required>
            </div>
            <button class="btn btn-lg btn-primary" id="upload-button" type="submit">
                Upload image
            </button>
        </form>

        <br>
        <!--        <div class="alert alert-info" id="loading" style="display: none;" role="alert">-->
        <!--            Uploading image...-->
        <!--            <div class="progress">-->
        <!--                <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="45"-->
        <!--                     aria-valuemin="0" aria-valuemax="100" style="width: 100%">-->
        <!--                </div>-->
        <!--            </div>-->
        <!--        </div>-->
        <!--        <div id="message"></div>-->
        <!---->
        <!--        <div id="jsonOutput" style="width:600px; display:table-cell;">-->
        <!--            Response:-->
        <!--            <br><br>-->
        <!--            <textarea id="responseTextArea" class="UIInput"-->
        <!--                      style="width:580px; height:400px;"></textarea>-->
        <!--        </div>-->

    </div>

    <!--<a target="_blank" href="https://github.com/ShinDarth/Bootstrap-image-upload-form"><img style="position: absolute; top: 50px; right: 0; border: 0;" src="https://camo.githubusercontent.com/38ef81f8aca64bb9a64448d0d70f1308ef5341ab/68747470733a2f2f73332e616d617a6f6e6177732e636f6d2f6769746875622f726962626f6e732f666f726b6d655f72696768745f6461726b626c75655f3132313632312e706e67" alt="Fork me on GitHub" data-canonical-src="https://s3.amazonaws.com/github/ribbons/forkme_right_darkblue_121621.png"></a>-->

</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
<!--<script src="upload-image.js"></script>-->
</body>
</html>