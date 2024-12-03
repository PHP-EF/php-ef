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
          <div class="col-xxl-3 col-md-6">
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
          <div class="col-xxl-3 col-md-6">
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
          <div class="col-xxl-3 col-xl-12">
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

          <!-- Reports This Year Card -->
          <div class="col-xxl-3 col-xl-12">
            <div class="card info-card granularity-card">
              <div class="card-body">
                <h5 class="card-title">Granularity</span></h5>
                <div class="d-flex align-items-center">
                  <span>Granularity</span>&nbsp;<a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                  <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <li class="dropdown-header text-start granularity-select">
                      <h6>Granularity</h6>
                    </li>
                    <li><a class="dropdown-item granularity-select" data-granularity="today">Today</a></li>
                    <li><a class="dropdown-item granularity-select" data-granularity="last30Days">Last 30 Days</a></li>
                    <li><a class="dropdown-item granularity-select" data-granularity="thisWeek">This Week</a></li>
                    <li><a class="dropdown-item granularity-select" data-granularity="thisMonth">This Month</a></li>
                    <li><a class="dropdown-item granularity-select" data-granularity="thisYear">This Year</a></li>
                    <li><a class="dropdown-item granularity-select" data-granularity="lastMonth">Last Month</a></li>
                    <li><a class="dropdown-item granularity-select" data-granularity="lastYear">Last Year</a></li>
                  </ul>
                </div>
              </div>
            </div>
          </div><!-- Reports This Year Card -->
        </div>

        <div class="row">
          <!-- Assessments Chart -->
          <div class="col-10">
            <div class="card">
              <div class="card-body">
                <h5 class="card-title">Assessments | <span class="granularity-title">Last 30 Days</span></h5>
                <!-- Line Chart -->
                <div id="reportsChart"></div>
                <!-- End Line Chart -->
              </div>
            </div>
          </div><!-- End Assessments -->

          <!-- Assessment Pie -->
          <div class="col-2">
            <div class="card">
              <div class="card-body pb-0">
                <h5 class="card-title">Assessment Types | <span class="granularity-title">Last 30 Days</span></h5>
                <div id="assessmentTypesChart" style="min-height: 400px;" class="echart"></div>
              </div><!-- End Assessment Pie -->
            </div>
          </div>
        </div>

        <div class="row">
          <!-- Top Users -->
          <div class="col-6">
            <div class="card top-users overflow-auto">
              <div class="card-body pb-0">
                <h5 class="card-title">Top Users | <span class="granularity-title">Last 30 Days</span></h5>

                <!-- <table id="topUsersTable"></table> -->
                <div id="topUsersChart"></div>
              </div>
            </div>
          </div><!-- End Top Users -->
          <!-- Top Customers -->
          <div class="col-6">
            <div class="card top-customers overflow-auto">
              <div class="card-body pb-0">
                <h5 class="card-title">Top Customers | <span class="granularity-title">Last 30 Days</span></h5>

                <!-- <table id="topCustomersTable"></table> -->
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

                <table id="assessmentTable"></table>
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
    const renderChart = (granularity) => {
      $.get( "/api?f=getAssessmentReportsStats&granularity="+granularity).done(function( data, status ) {
        // Extract all unique dates
        const categoriesSet = new Set();
        for (const key in data) {
            if (data.hasOwnProperty(key)) {
                Object.keys(data[key]).forEach(date => categoriesSet.add(date));
            }
        }
        const categories = Array.from(categoriesSet).sort();
        console.log(categories);
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

        console.log(series);

        const options = {
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
          columns: [{
            field: 'id',
            title: 'ID',
            sortable: true,
            visible: false
          },
          {
            field: 'customer',
            title: 'Customer',
            sortable: true
          },{
            field: 'type',
            title: 'Type',
            sortable: true
          },{
            field: 'userid',
            title: 'User ID',
            sortable: true,
            visible: false
          },{
            field: 'apiuser',
            title: 'API User',
            sortable: true
          },{
            field: 'created',
            title: 'Generated At',
            sortable: true,
            formatter: 'dateFormatter'
          }]
        });
        updateTopApiUsers(data,granularity);
        updateTopCustomers(data,granularity);
        updateAssessmentTypes(data);
      });
    }

    const updateAssessmentTypes = (data) => {
      const countByType = data.reduce((acc, obj) => {
        acc[obj.type] = (acc[obj.type] || 0) + 1;
        return acc;
      }, {});

      const pieData = Object.keys(countByType).map(type => ({
        value: countByType[type],
        name: type
      }));

      echarts.init(document.querySelector("#assessmentTypesChart")).setOption({
        tooltip: {
          trigger: 'item'
        },
        legend: {
          top: '5%',
          left: 'center'
        },
        series: [{
          name: 'Assessment Type',
          type: 'pie',
          radius: ['40%', '70%'],
          avoidLabelOverlap: false,
          label: {
            show: false,
            position: 'center'
          },
          emphasis: {
            label: {
              show: true,
              fontSize: '18',
              fontWeight: 'bold'
            }
          },
          labelLine: {
            show: false
          },
          data: pieData
        }]
      });
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
        const apiUsers = sortedApiUsers.map(user => ({ apiuser: user[0], count: user[1] }));
        const options = {
          chart: {
              type: 'bar',
              height: 350
          },
          plotOptions: {
              bar: {
                  horizontal: true
              }
          },
          dataLabels: {
              enabled: false
          },
          series: [{
              data: apiUsers.map(user => user.count)
          }],
          xaxis: {
              categories: apiUsers.map(user => user.apiuser)
          }
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
        const customers = sortedCustomers.map(user => ({ customer: user[0], count: user[1] }));
        const options = {
          chart: {
              type: 'bar',
              height: 350
          },
          plotOptions: {
              bar: {
                  horizontal: true
              }
          },
          dataLabels: {
              enabled: false
          },
          series: [{
              data: customers.map(user => user.count)
          }],
          xaxis: {
              categories: customers.map(user => user.customer)
          }
        };
        const chart = new ApexCharts(document.querySelector("#topCustomersChart"), options);
        chart.render();
    }

    $('.granularity-select').on('click', function(event) {
      $("#reportsChart,#topUsersChart,#topCustomersChart").html('');
      renderChart($(event.currentTarget).data('granularity'));
      updateRecentAssessments($(event.currentTarget).data('granularity'));
      $('.granularity-title').text($(event.currentTarget).text());
    });

    // Initial render
    renderChart('last30Days');
    updateSummaryValues();
    updateRecentAssessments('last30Days');
  });
</script>
