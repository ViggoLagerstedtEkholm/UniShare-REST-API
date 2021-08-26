$(document).ready(function () {
    $.ajax({
        url: "/UniShare/settings/getsettings",
        type: 'GET',
        dataType: "json",
        success: function (res) {
            const first_name = res['data']['first_name'];
            const last_name = res['data']['last_name'];
            const email = res['data']['email'];
            const display_name = res['data']['display_name'];
            const description = res['data']['description'];

            document.getElementById("first_name").value = first_name;
            document.getElementById("last_name").value = last_name;
            document.getElementById("email").value = email;
            document.getElementById("display_name").value = display_name;
            document.getElementById("description").value = description;
        }
    });

    $.ajax({
        url: "/UniShare/degree/get/names",
        type: 'GET',
        dataType: "json",
        success: function (res) {
            console.log(res);
            const degrees = res['data']['degrees'];
            let result = degrees.map(a => a.name);
            let ID = degrees.map(a => a.ID);
            console.log(result.length);
            for (let i = 0; i < result.length; i++) {
                $('<option/>').val(ID[i]).html(result[i]).appendTo('#degrees');
            }
        },
        error: function (res) {
            console.log(res);
        }
    });
});