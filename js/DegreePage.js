$( document ).ready(function() {
  var degreeID = document.getElementById('degreeID').value;

  $.ajax ( {
        url: "/UniShare/degree/get",
        type: 'GET',
        data:{degreeID: degreeID},
        dataType: "json",
        success: function ( res ){
          console.log(res);
          var city = res['data']['degree']['city'];
          var country = res['data']['degree']['country'];
          var end_date = res['data']['degree']['end_date'];
          var start_date = res['data']['degree']['start_date'];
          var field_of_study = res['data']['degree']['field_of_study'];
          var name = res['data']['degree']['name'];
          var university = res['data']['degree']['university'];
          var ID = res['data']['degree']['ID'];

          document.getElementById("city").value = city;
          document.getElementById("country").value = country;
          document.getElementById("end_date").value = end_date;
          document.getElementById("start_date").value = start_date;
          document.getElementById("field_of_study").value = field_of_study;
          document.getElementById("name").value = name;
          document.getElementById("university").value = university;
          $("#degreeID").val(ID);
        },
        error: function( res ){
          console.log(res);
          alert('Could not fetch data!');
        }
    });
});
