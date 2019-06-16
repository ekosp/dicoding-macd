<form id="upload-image-form" action="upload-image2.php" method="post" enctype="multipart/form-data">
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