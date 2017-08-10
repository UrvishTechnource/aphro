<?php

$max_size = 800; //max image size in Pixels
$destination_folder = 'generated_images';
//$watermark_png_file = 'watermark.png'; //watermark png file
$watermark_png_file = $_GET['watermark']; //watermark png file
//$image_name = $_FILES['image_file']['name']; //file name
//$image_size = $_FILES['image_file']['size']; //file size
$image_temp = $_GET['image']; //$_FILES['image_file']['tmp_name']; //file temp
//$image_type = mime_content_type($image_temp); //$_FILES['image_file']['type']; //file type
$image_type = pathinfo($image_temp, PATHINFO_EXTENSION);

switch (strtolower($image_type)) { //determine uploaded image type 
    //Create new image from file
    case 'png': //'image/png':
        $image_resource = imagecreatefrompng($image_temp);
        break;
    case 'gif'://'image/gif':
        $image_resource = imagecreatefromgif($image_temp);
        break;
    case 'jpeg': case 'jpg': //'image/jpeg': case 'image/pjpeg':
//'image/jpeg': case 'image/pjpeg':
        $image_resource = imagecreatefromjpeg($image_temp);
        break;
    default:
        $image_resource = false;
}

if ($image_resource) {
    //Copy and resize part of an image with resampling
    list($img_width, $img_height) = getimagesize($image_temp);

    //Construct a proportional size of new image
    $image_scale = min($max_size / $img_width, $max_size / $img_height);
    $new_image_width = ceil($image_scale * $img_width);
    $new_image_height = ceil($image_scale * $img_height);
    $new_canvas = imagecreatetruecolor($new_image_width, $new_image_height);

    if (imagecopyresampled($new_canvas, $image_resource, 0, 0, 0, 0, $new_image_width, $new_image_height, $img_width, $img_height)) {

        if (!is_dir($destination_folder)) {
            //mkdir($destination_folder); //create dir if it doesn't exist
        }

        //center watermark
        $watermark_left = ($new_image_width / 2) - (300 / 2); //watermark left
        $watermark_bottom = ($new_image_height / 2) - (100 / 2); //watermark bottom

        $watermark = imagecreatefrompng($watermark_png_file); //watermark image
        imagecopy($new_canvas, $watermark, $watermark_left, $watermark_bottom, 0, 0, 300, 100); //merge image
        //output image direcly on the browser.
        header('Content-Type: image/jpeg');
        imagejpeg($new_canvas, NULL, 90);

        //Or Save image to the folder
        //imagejpeg($new_canvas, $destination_folder.'/'.$image_name , 90);
        //free up memory
        imagedestroy($new_canvas);
        imagedestroy($image_resource);
        die();
    }
}
?>
