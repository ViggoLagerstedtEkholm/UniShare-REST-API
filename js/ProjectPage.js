$( document ).ready(function() {
  var projectID = document.getElementById('projectID').value;

  $.ajax ( {
      url: "/UniShare/project/get",
      type: 'GET',
      data:{projectID: projectID},
      dataType: "json",
      success: function ( res ){
        console.log(res);
        var name = res['data']['Name'];
        var link = res['data']['Link'];
        var description = res['data']['Description'];
  
        document.getElementById("name").value = name;
        document.getElementById("link").value = link;
        document.getElementById("description").value = description;
      },
      error: function ( res ){
        console.log(res);
      }
  });
})
