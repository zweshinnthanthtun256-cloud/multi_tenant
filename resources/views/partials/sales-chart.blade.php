<div class="col-lg-4">

    <div class="card-box">

        <div class="chart-title">
            Total Sales
        </div>

        <div id="salesChart"></div>

    </div>

</div>

<script>

    var salesOptions = {
        series: [44, 30, 26],
        chart: {
            type: 'donut',
            height: 280
        },
        labels: ['Direct', 'Referral', 'Social'],
        legend: {
            show: false
        }
    };

    new ApexCharts(
        document.querySelector("#salesChart"),
        salesOptions
    ).render();

</script>