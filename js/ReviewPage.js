$(document).ready(function () {
    $.ajax({
        url: "/UniShare/review/get",
        type: 'GET',
        data: {courseID: document.getElementById('courseID').value},
        dataType: "json",
        success: function (res) {
            console.log(res);

            const text = res['data']['result']['text'];
            const difficulty = res['data']['result']['difficulty'];
            const environment = res['data']['result']['environment'];
            const fulfilling = res['data']['result']['fulfilling'];
            const grading = res['data']['result']['grading'];
            const litterature = res['data']['result']['litterature'];
            const overall = res['data']['result']['overall'];

            document.getElementById("text").value = text;
            document.getElementById("difficulty").value = difficulty;
            document.getElementById("environment").value = environment;
            document.getElementById("fulfilling").value = fulfilling;
            document.getElementById("grading").value = grading;
            document.getElementById("litterature").value = litterature;
            document.getElementById("overall").value = overall;
        },
        error: function (xhr, res) {
            console.log(res);
            if (xhr.status === 404) {
                alert('No matching project ID!');
            }
        }
    });
});