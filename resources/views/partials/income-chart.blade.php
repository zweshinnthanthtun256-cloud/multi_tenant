<div class="col-lg-4">

    <div class="card-box">

        <div class="chart-title">
            Net Income
        </div>

        <div id="incomeChart"></div>

    </div>

</div>

<script>

    var incomeOptions = {
        series: [{
            name: 'Income',
            data: [5, 3, 2, 7, 5, 10]
        }],
        chart: {
            type: 'bar',
            height: 280
        },
        xaxis: {
            categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun']
        }
    };

    new ApexCharts(
        document.querySelector("#incomeChart"),
        incomeOptions
    ).render();

</script>