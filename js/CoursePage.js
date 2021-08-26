$(document).ready(function () {
    const courseID = document.getElementById('courseID').value;
    const ctx = document.getElementById('myChart');

    $('select').on('change', function () {
        $.ajax({
            url: "/UniShare/course/setrate",
            type: "POST",
            data: {courseID: courseID, rating: this.value},
            dataType: "json",
            success: function (data) {
                alert('Updated rating!');
            },
            error: function (res) {
                console.log(res);
            }
        });
    });

    $.ajax({
        url: "/UniShare/course/getrate",
        type: 'GET',
        data: {courseID: courseID},
        dataType: "json",
        success: function (res) {
            console.log(res);
            const rating = res['data']['rating'];
            document.getElementById("rating").value = rating;
        },
        error: function (res) {
            console.log(res);
        }
    });

    $.ajax({
        url: "/UniShare/course/getGraphData",
        type: 'GET',
        data: {courseID: courseID},
        dataType: "json",
        success: function (res) {
            console.log(res);
            loadGraph(res['data']['ratings']);
        },
        error: function (res) {
            console.log(res);
        }
    });

    function loadGraph(data) {
        const labels = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
        const count = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        for (let i = 0; i < data.length; i++) {
            count[data[i][0] - 1] = data[i][1];
        }

        ctx.height = 100;

        const myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Amount of ratings',
                    data: count,
                    backgroundColor: [
                        'rgba(54, 162, 235, 1)',
                    ],
                    borderColor: [
                        'rgba(54, 162, 235, 1)',
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    x: {
                        grid: {
                            display: false,
                        }
                    },
                    y: {
                        grid: {
                            display: true
                        }
                    }
                },
                indexAxis: 'y',
            }
        });
    }
});
