<?php
require_once(__DIR__.'/../../inc/inc.php');
if ($ib->auth->checkAccess(null,"REPORT-ASSESSMENTS") == false) {
  die();
}
?>

<main id="main" class="main">
  <section class="section assessment-reporting">
    <div class="row">
      <!-- Columns -->
      <div class="col-lg-12">
        <div class="row">
          <!-- Reports Today Card -->
          <div class="col-lg-2 col-md-4 col-sm-6 col-12">
            <div class="card info-card reports-today-card">
              <div class="card-body">
                <h5 class="card-title">Assessments <span>| Today</span></h5>
                <div class="d-flex align-items-center">
                  <!-- <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                    <i class="bi bi-cart"></i>
                  </div> -->
                  <div class="ps-3">
                    <h6 id="reportsThisDayVal"></h6>
                    <span class="text-success small pt-1 fw-bold" id="reportsThisDayValPerc"></span> <span class="text-muted small pt-2 ps-1" id="reportsThisDayValPercDir"></span>
                  </div>
                </div>
              </div>
            </div>
          </div><!-- Reports Today Card -->

          <!-- Reports This Month Card -->
          <div class="col-lg-2 col-md-4 col-sm-6 col-12">
            <div class="card info-card reports-month-card">
              <div class="card-body">
                <h5 class="card-title">Assessments <span>| This Month</span></h5>
                <div class="d-flex align-items-center">
                  <!-- <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                    <i class="bi bi-currency-dollar"></i>
                  </div> -->
                  <div class="ps-3">
                    <h6 id="reportsThisMonthVal"></h6>
                    <span class="text-success small pt-1 fw-bold" id="reportsThisMonthValPerc"></span> <span class="text-muted small pt-2 ps-1" id="reportsThisMonthValPercDir"></span>
                  </div>
                </div>
              </div>
            </div>
          </div><!-- Reports This Month Card -->

          <!-- Reports This Year Card -->
          <div class="col-lg-2 col-md-4 col-sm-6 col-12">
            <div class="card info-card reports-year-card">
              <div class="card-body">
                <h5 class="card-title">Assessments <span>| This Year</span></h5>
                <div class="d-flex align-items-center">
                  <!-- <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                    <i class="bi bi-people"></i>
                  </div> -->
                  <div class="ps-3">
                    <h6 id="reportsThisYearVal"></h6>
                    <span class="text-success small pt-1 fw-bold" id="reportsThisYearValPerc"></span> <span class="text-muted small pt-2 ps-1" id="reportsThisYearValPercDir"></span>
                  </div>
                </div>
              </div>
            </div>
          </div><!-- Reports This Year Card -->

          <!-- Users Card -->
          <div class="col-lg-2 col-md-4 col-sm-6 col-12">
            <div class="card info-card reports-today-card">
              <div class="card-body">
                <h5 class="card-title">Assessments <span>| Today</span></h5>
                <div class="d-flex align-items-center">
                  <!-- <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                    <i class="bi bi-cart"></i>
                  </div> -->
                  <div class="ps-3">
                    <h6 id="reportsThisDayVal"></h6>
                    <span class="text-success small pt-1 fw-bold" id="reportsThisDayValPerc"></span> <span class="text-muted small pt-2 ps-1" id="reportsThisDayValPercDir"></span>
                  </div>
                </div>
              </div>
            </div>
          </div><!-- Users Card -->

          <!-- Customers Card -->
          <div class="col-lg-2 col-md-4 col-sm-6 col-12">
            <div class="card info-card reports-today-card">
              <div class="card-body">
                <h5 class="card-title">Assessments <span>| Today</span></h5>
                <div class="d-flex align-items-center">
                  <!-- <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                    <i class="bi bi-cart"></i>
                  </div> -->
                  <div class="ps-3">
                    <h6 id="reportsThisDayVal"></h6>
                    <span class="text-success small pt-1 fw-bold" id="reportsThisDayValPerc"></span> <span class="text-muted small pt-2 ps-1" id="reportsThisDayValPercDir"></span>
                  </div>
                </div>
              </div>
            </div>
          </div><!-- Customers Card -->

          <!-- Granularity Card -->
          <div class="col-lg-2 col-md-4 col-sm-6 col-12">
            <div class="card info-card granularity-card">
              <div class="card-body">
                <h5 class="card-title">Granularity</span></h5>
                <div class="d-flex align-items-center">
                  <div class="btn-group">
                    <button id="granularityBtn" class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      Last 30 Days
                    </button>
                    <div class="dropdown-menu">
                      <a class="dropdown-item granularity-select preventDefault" data-granularity="today" href="#">Today</a>
                      <a class="dropdown-item granularity-select preventDefault" data-granularity="last30Days" href="#">Last 30 Days</a>
                      <a class="dropdown-item granularity-select preventDefault" data-granularity="thisWeek" href="#">This Week</a>
                      <a class="dropdown-item granularity-select preventDefault" data-granularity="thisMonth" href="#">This Month</a>
                      <a class="dropdown-item granularity-select preventDefault" data-granularity="thisYear" href="#">This Year</a>
                      <a class="dropdown-item granularity-select preventDefault" data-granularity="lastMonth" href="#">Last Month</a>
                      <a class="dropdown-item granularity-select preventDefault" data-granularity="lastYear" href="#">Last Year</a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div><!-- Granularity Card -->
        </div>

        <div class="row">
          <!-- Assessments Chart -->
          <div class="col-xxl-9 col-lg-8 col-md-7 col-sm-6 col-12">
            <div class="card chart-card">
              <div class="card-body">
                <h5 class="card-title">Assessments | <span class="granularity-title">Last 30 Days</span></h5>
                <!-- Line Chart -->
                <div id="reportsChart"></div>
                <!-- End Line Chart -->
              </div>
            </div>
          </div><!-- End Assessments -->

          <!-- Assessment Pie -->
          <div class="col-xxl-3 col-lg-4 col-md-5 col-sm-6 col-12">
            <div class="card chart-card">
              <div class="card-body pb-0">
                <h5 class="card-title">Assessment Types | <span class="granularity-title">Last 30 Days</span></h5>
                <div id="assessmentTypesChart"></div>
              </div><!-- End Assessment Pie -->
            </div>
          </div>

          <!-- Customers Pie -->
          <div class="col-xxl-3 col-lg-4 col-md-5 col-sm-6 col-12">
            <div class="card chart-card">
              <div class="card-body pb-0">
                <h5 class="card-title">Customers | <span class="granularity-title">Last 30 Days</span></h5>
                <div id="customersChart"></div>
              </div><!-- End Assessment Pie -->
            </div>
          </div>

          <!-- Users Pie -->
          <div class="col-xxl-3 col-lg-4 col-md-5 col-sm-6 col-12">
            <div class="card chart-card">
              <div class="card-body pb-0">
                <h5 class="card-title">Users | <span class="granularity-title">Last 30 Days</span></h5>
                <div id="usersChart"></div>
              </div><!-- End Assessment Pie -->
            </div>
          </div>
        </div>
        

        <div class="row">
          <!-- Top Users -->
          <div class="col-lg-6 col-12">
            <div class="card top-users bar-chart-card overflow-auto">
              <div class="card-body pb-0">
                <h5 class="card-title">Top 10 Users | <span class="granularity-title">Last 30 Days</span></h5>
                <div id="topUsersChart"></div>
              </div>
            </div>
          </div><!-- End Top Users -->
          <!-- Top Customers -->
          <div class="col-lg-6 col-12">
            <div class="card top-customers bar-chart-card overflow-auto">
              <div class="card-body pb-0">
                <h5 class="card-title">Top 10 Customers | <span class="granularity-title">Last 30 Days</span></h5>
                <div id="topCustomersChart"></div>
              </div>
            </div>
          </div><!-- End Top Customers -->
        </div>
        <div class="row">
          <!-- Assessments List -->
          <div class="col-12">
            <div class="card recent-assessments overflow-auto">
              <div class="card-body">
                <h5 class="card-title">Assessments List | <span class="granularity-title">Last 30 Days</span></h5>
                <table id="assessmentTable" class="table-striped"></table>
              </div>
            </div>
          </div><!-- End Assessments List -->
        </div>
      </div><!-- End columns -->
    </div>
  </section>

</main><!-- End #main -->

<script>
  function dateFormatter(value, row, index) {
    var d = new Date(value) // The 0 there is the key, which sets the date to the epoch
    return d.toGMTString();
  }

  document.addEventListener("DOMContentLoaded", () => {
    if ($('.dark-theme').length > 0) {
      var theme = 'dark';
    } else {
      var theme = 'light';
    }

    var barChartColorPallete = ['#FDDD00','#E1DD1A','#C5DE33','#A9DE4D','#8DDF66','#70DF80','#54E099','#38E0B3','#1CE1CC','#00E1E6'];
    var pieChartColorPallete = ['#0fbe4d','#94ce36','#00F9FF','#00d69b','#00F9FF'];

    const updateAssessmentsChart = (granularity) => {
      $.get( "/api?f=getAssessmentReportsStats&granularity="+granularity).done(function( data, status ) {
        // Extract all unique dates
        const categoriesSet = new Set();
        for (const key in data) {
            if (data.hasOwnProperty(key)) {
                Object.keys(data[key]).forEach(date => categoriesSet.add(date));
            }
        }
        const categories = Array.from(categoriesSet).sort();
        // Prepare the series data
        const series = [];
        for (const key in data) {
            if (data.hasOwnProperty(key)) {
                const seriesData = categories.map(date => data[key][date] || 0);
                series.push({
                    name: key,
                    data: seriesData
                });
            }
        }

        const options = {
          tooltip: {
            theme: theme
          },
          chart: {
            height: 350,
            type: 'area',
            toolbar: {
              show: false
            },
          },
          markers: {
            size: 4
          },
          colors: ['#4154f1', '#2eca6a', '#ff771d'],
          fill: {
            type: "gradient",
            gradient: {
              shadeIntensity: 1,
              opacityFrom: 0.3,
              opacityTo: 0.4,
              stops: [0, 90, 100]
            }
          },
          dataLabels: {
            enabled: false
          },
          stroke: {
            curve: 'smooth',
            width: 2
          },
          series: series,
          xaxis: {
              categories: categories
          }
        };

        const chart = new ApexCharts(document.querySelector("#reportsChart"), options);
        chart.render();
      });
    };

    const updateSummaryValues = () => {
      $.get( "/api?f=getAssessmentReportsSummary").done(function( data, status ) {
        const total = data.find(item => item.type === "Total")
        $('#reportsThisDayVal').text(total['count_today']);
        $('#reportsThisMonthVal').text(total['count_this_month']);
        $('#reportsThisYearVal').text(total['count_this_year']);
      });
    };

    const updateRecentAssessments = (granularity) => {
      $.get( "/api?f=getAssessmentReports&granularity="+granularity).done(function( data, status ) {
        $('#assessmentTable').bootstrapTable('destroy');
        $('#assessmentTable').bootstrapTable({
          data: data,
          sortable: true,
          pagination: true,
          search: true,
          sortName: 'created',
          sortOrder: 'desc',
          showExport: true,
          exportTypes: ['json', 'xml', 'csv', 'txt', 'excel', 'sql'],
          showColumns: true,
          filterControl: true,
          filterControlVisible: false,
          showFilterControlSwitch: true,
          columns: [{
            field: 'id',
            title: 'ID',
            sortable: true,
            visible: false
          },
          {
            field: 'customer',
            title: 'Customer',
            sortable: true,
            filterControl: 'select'
          },{
            field: 'type',
            title: 'Type',
            sortable: true,
            filterControl: 'select'
          },{
            field: 'userid',
            title: 'User ID',
            sortable: true,
            visible: false,
            filterControl: 'select'
          },{
            field: 'apiuser',
            title: 'API User',
            sortable: true,
            filterControl: 'select'
          },{
            field: 'created',
            title: 'Generated At',
            sortable: true,
            formatter: 'dateFormatter',
            filterControl: 'input'
          }]
        });
        updateTopApiUsers(data,granularity);
        updateTopCustomers(data,granularity);
        updateAssessmentTypes(data);
      });
    }

    const updateAssessmentTypes = (data) => {
      const countByCustomer = data.reduce((acc, obj) => {
        acc[obj.customer] = (acc[obj.customer] || 0) + 1;
        return acc;
      }, {});

      const types = Object.keys(countByType).map(type => ({ type: type, count: countByType[type] }));
      const options = {
        tooltip: {
          theme: theme
        },
        chart: {
          type: 'donut',
          height: 350
        },
        plotOptions: {
          pie: {
            expandOnClick: true
          }
        },
        legend: {
          position: 'top'
        },
        dataLabels: {
          enabled: false
        },
        series: types.map(type => type.count),
        labels: types.map(type => type.type),
        colors: pieChartColorPallete
      };
      const chart = new ApexCharts(document.querySelector("#assessmentTypesChart"), options);
      chart.render();
    }

    const updateAssessmentCustomers = (data) => {
      const countByType = data.reduce((acc, obj) => {
        acc[obj.type] = (acc[obj.type] || 0) + 1;
        return acc;
      }, {});

      const types = Object.keys(countByType).map(type => ({ type: type, count: countByType[type] }));
      const options = {
        tooltip: {
          theme: theme
        },
        chart: {
          type: 'donut',
          height: 350
        },
        plotOptions: {
          pie: {
            expandOnClick: true
          }
        },
        legend: {
          position: 'top'
        },
        dataLabels: {
          enabled: false
        },
        series: types.map(type => type.count),
        labels: types.map(type => type.type),
        colors: pieChartColorPallete
      };
      const chart = new ApexCharts(document.querySelector("#assessmentTypesChart"), options);
      chart.render();
    }

    const updateTopApiUsers = (data,granularity) => {
      const apiUserCount = {};

      data.forEach(entry => {
        const apiUser = entry.apiuser;
        if (apiUser) {
          if (!apiUserCount[apiUser]) {
              apiUserCount[apiUser] = 0;
          }
          apiUserCount[apiUser]++;
        }
      });

      const sortedApiUsers = Object.entries(apiUserCount).sort((a, b) => b[1] - a[1]);
      const apiUsers = sortedApiUsers.slice(0,10).map(user => ({ apiuser: user[0], count: user[1] }));
      const options = {
        tooltip: {
          theme: theme
        },
        chart: {
          type: 'bar',
          height: 350
        },
        plotOptions: {
          bar: {
            horizontal: true,
            distributed: true // This enables different colors for each bar
          }
        },
        dataLabels: {
          enabled: false
        },
        series: [{
          data: apiUsers.map(user => user.count),
          name: 'Assessment Count'
        }],
        xaxis: {
          categories: apiUsers.map(user => user.apiuser)
        },
        colors: barChartColorPallete
      };
      const chart = new ApexCharts(document.querySelector("#topUsersChart"), options);
      chart.render();
    }

    const updateTopCustomers = (data,granularity) => {
      const customerCount = {};

      data.forEach(entry => {
        const customer = entry.customer;
        if (customer) {
          if (!customerCount[customer]) {
              customerCount[customer] = 0;
          }
          customerCount[customer]++;
        }
      });

      const sortedCustomers = Object.entries(customerCount).sort((a, b) => b[1] - a[1]);
      const customers = sortedCustomers.slice(0,10).map(user => ({ customer: user[0], count: user[1] }));
      const options = {
        tooltip: {
          theme: theme
        },
        chart: {
          type: 'bar',
          height: 350
        },
        plotOptions: {
          bar: {
            horizontal: true,
            distributed: true // This enables different colors for each bar
          }
        },
        dataLabels: {
          enabled: false
        },
        series: [{
          data: customers.map(user => user.count),
          name: 'Assessment Count'
        }],
        xaxis: {
          categories: customers.map(user => user.customer)
        },
        colors: barChartColorPallete
      };
      const chart = new ApexCharts(document.querySelector("#topCustomersChart"), options);
      chart.render();
    }

    $('.granularity-select').on('click', function(event) {
      $("#reportsChart,#topUsersChart,#topCustomersChart,#assessmentTypesChart").html('');
      updateAssessmentsChart($(event.currentTarget).data('granularity'));
      updateRecentAssessments($(event.currentTarget).data('granularity'));
      $('.granularity-title').text($(event.currentTarget).text());
      $('#granularityBtn').text($(event.currentTarget).text())
    });

    // Initial render
    updateAssessmentsChart('last30Days');
    updateSummaryValues();
    updateRecentAssessments('last30Days');
  });
</script>
