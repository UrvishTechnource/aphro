function readURL(input) { 
if (input.files && input.files[0]) 
{
 var reader = new FileReader();
 reader.onload = function (e) { 
//$('#cov_img').attr('src', e.target.result);
document.getElementById("upld_img").src=e.target.result;
//document.getElementById("work_url").value=e.target.result;
 }
 reader.readAsDataURL(input.files[0]); 
}
}
