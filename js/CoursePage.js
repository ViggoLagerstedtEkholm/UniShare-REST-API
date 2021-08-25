$( document ).ready(function(){
  var courseID = document.getElementById('courseID').value;
    
  $('select').on('change', function(){
    $.ajax({
       url: "/UniShare/course/setrate",
       type: "POST",
       data:{courseID: courseID, rating: this.value},
       dataType: "json",
       success:function(data){
         alert('Updated rating!');
       },
       error:function(res){
         console.log(res);
       }
     });
  });

  $.ajax ( {
      url: "/UniShare/course/getrate",
      type: 'GET',
      data:{ courseID: courseID},
      dataType: "json",
      success: function ( res ){
        console.log(res);
        var rating = res['data']['rating'];
        document.getElementById("rating").value = rating;
      },
      error: function ( res ){
        console.log(res);
      }
  });
});