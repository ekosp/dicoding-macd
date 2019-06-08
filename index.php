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
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>

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

    <style>

        div.scroll {
            margin: 5px;
            padding: 5px;
            height: 300px;
            overflow: auto;
            background: #DBE4E1;
        }
    </style>

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
        var sourceImageUrl = document.getElementById("inputImage").innerHTML;
        document.querySelector("#sourceImage").src = sourceImageUrl;

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

                var jsonString = JSON.stringify(data, null, 2);
                $("#responseTextArea").val(jsonString);
                $('#analyzeResult').show();


                $('#finalResultDiv').show();
                $('#analyzeFinalResult').html(JSON.stringify(data['description']['captions'][0]['text']));

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
                <li><a href="<?php $_SERVER['PHP_SELF'] ?>">Upload New Image</a></li>
            </ul>
        </div><!--.nav-collapse -->
    </div>
</nav>

<div class="container">

    <div id="uploadContainer" style="max-width: 100%; margin: auto;">
        <h1 class="page-header">Upload Image</h1>
        <p class="lead">Select a PNG or JPEG image, having maximum size <span id="max-size"></span> KB.</p>

        <form id="upload-image-form" action="" method="post" enctype="multipart/form-data">
            <div id="image-preview-div" style="display: none">
                <label>Selected image:</label>
                <br>
                <img id="preview-img" src="noimage">
            </div>

            <div class="form-group">
                <input type="file" name="file" id="file" required>
            </div>
            <button class="btn btn-lg btn-primary" id="upload-button" type="submit">
                Upload image
            </button>
        </form>

        <br>
        <div class="alert alert-info" id="loading" style="display: none;" role="alert">
            Uploading image...
            <div class="progress">
                <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="45"
                     aria-valuemin="0" aria-valuemax="100" style="width: 100%">
                </div>
            </div>
        </div>

    </div>

    <div id="analyzeContainer" style="max-width: 100%; margin: auto;" hidden="hidden">
        <h1 class="page-header">Analyse Image</h1>

        <div class="alert alert-success" role="alert">
            <p>Image uploaded successful to : </p>
            <p id="inputImage"></p>
        </div>

        <button id="analyze-button" class="btn btn-lg btn-primary" onclick="processImage()">Analyze image</button>

        <div class="scroll" id="analyzeResult" style="width:100%; display:table;" hidden="hidden">
            <div id="jsonOutput" style="width:600px; display:table-cell;">
                Response:
                <br><br>
                <textarea id="responseTextArea" class="UIInput"
                          style="width:50%; height:300px;"></textarea>
            </div>
            <div id="imageDiv" style="width:50%; display:table-cell;">
                Source image:
                <br><br>
                <img id="sourceImage" />
            </div>
        </div>

        <div id="finalResultDiv" class="alert alert-success" role="alert" hidden="hidden">
            <p id="analyzeFinalResult" style="font-size: large; font-weight: bold; align-content: center; width:100%"></p>
        </div>
    </div>

</div>

<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
<script src="upload-image.js"></script>
</body>
</html>