function readURL(input) { 
if (input.files && input.files[0]) 
{
 var reader = new FileReader();
 reader.onload = function (e) { 
//$('#cov_img').attr('src', e.target.result);
document.getElementById("cov_img").src=e.target.result;
 }
 reader.readAsDataURL(input.files[0]); 
}
}

function profile(input) { 
if (input.files && input.files[0]) 
{
 var reader = new FileReader();
 reader.onload = function (e) { 
//$('#cov_img').attr('src', e.target.result);
document.getElementById("pro_img").src=e.target.result;
 }
 reader.readAsDataURL(input.files[0]); 
}
}
