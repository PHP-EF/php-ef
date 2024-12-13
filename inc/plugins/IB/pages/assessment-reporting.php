<?php
  $ibPlugin = new ibPlugin();
  if ($ibPlugin->rbac->checkAccess("REPORT-ASSESSMENTS") == false) {
    die();
  }
  return <<<EOF
  <main id="main" class="main">
    <section class="section reporting-section">
      <div class="row">
        <!-- Columns -->
        <div class="col-lg-12">
          <div class="row">
            <!-- Reports Today Card -->
            <div class="col-lg-3 col-md-4 col-sm-6 col-12">
              <div class="card info-card reports-today-card">
                <div class="card-body">
                  <h5 class="card-title">Assessments <span>| Today</span></h5>
                  <div class="d-flex align-items-center">
                    <!-- <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-cart"></i>
                    </div> -->
                    <div class="pt-1 ps-3">
                      <h6 id="reportsThisDayVal" class="metric-circle border-5"></h6>
                    </div>
                    <div class="p-2 pt-2 ps-4">
                      <span id="customersThisDayVal" class="ib-green small pt-1 mt-1 fw-bold"></span>
                      <span id="usersThisDayVal" class="ib-black small pt-1 mt-1 fw-bold" style="display:flex;"></span>
                    </div>
                  </div>
                </div>
              </div>
            </div><!-- Reports Today Card -->

            <!-- Reports This Month Card -->
            <div class="col-lg-3 col-md-4 col-sm-6 col-12">
              <div class="card info-card reports-month-card">
                <div class="card-body">
                  <h5 class="card-title">Assessments <span>| This Month</span></h5>
                  <div class="d-flex align-items-center">
                    <!-- <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-currency-dollar"></i>
                    </div> -->
                    <div class="pt-1 ps-3">
                      <h6 id="reportsThisMonthVal" class="metric-circle border-5"></h6>
                    </div>
                    <div class="p-2 pt-2 ps-4">
                      <span id="customersThisMonthVal" class="ib-green small pt-1 mt-1 fw-bold"></span>
                      <span id="usersThisMonthVal" class="ib-black small pt-1 mt-1 fw-bold" style="display:flex;"></span>
                    </div>
                  </div>
                </div>
              </div>
            </div><!-- Reports This Month Card -->

            <!-- Reports This Year Card -->
            <div class="col-lg-3 col-md-4 col-sm-6 col-12">
              <div class="card info-card reports-year-card">
                <div class="card-body">
                  <h5 class="card-title">Assessments <span>| This Year</span></h5>
                  <div class="d-flex align-items-center">
                    <!-- <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-people"></i>
                    </div> -->
                    <div class="pt-1 ps-3">
                      <h6 id="reportsThisYearVal" class="metric-circle border-5"></h6>
                    </div>
                    <div class="p-2 pt-2 ps-4">
                      <span id="customersThisYearVal" class="ib-green small pt-1 mt-1 fw-bold"></span>
                      <span id="usersThisYearVal" class="ib-black small pt-1 mt-1 fw-bold" style="display:flex;"></span>
                    </div>
                  </div>
                </div>
              </div>
            </div><!-- Reports This Year Card -->

            <!-- Granularity Card -->
            <div class="col-lg-3 col-md-4 col-sm-6 col-12">
              <div class="card info-card granularity-card">
                <div class="card-body">
                  <h5 class="card-title">Granularity</span></h5>
                  <div class="d-flex align-items-center">
                    <div class="btn-group">
                      <button id="granularityBtn" class="btn btn-secondary btn-sm dropdown-toggle" data-granularity="last30Days" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
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
                        <a class="dropdown-item granularity-select preventDefault" data-granularity="custom" href="#">Custom</a>
                      </div>
                      <button id="clearFilters" class="btn btn-info btn-sm clearFilters" type="button">
                        Clear Filters
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div><!-- Granularity Card -->
          </div>

          <div class="row">
            <!-- Assessments Chart -->
            <div class="col-xxl-8 col-lg-6 col-md-12 col-sm-12 col-12">
              <div class="card chart-card">
                <div class="card-body">
                  <h5 class="card-title">Assessments | <span class="granularity-title">Last 30 Days</span></h5>
                  <!-- Line Chart -->
                  <div id="assessmentsChart"></div>
                  <!-- End Line Chart -->
                </div>
              </div>
            </div><!-- End Assessments -->

            <div class="col-xxl-2 col-lg-3 col-md-6 col-sm-6 col-6"> <!-- Assessment Types Pie -->
              <div class="card chart-card">
                <div class="card-body">
                  <h5 class="card-title">Assessment Types | <span class="granularity-title">Last 30 Days</span></h5>
                  <div id="assessmentTypesChart" class="pie"></div>
                </div>
              </div>
            </div><!-- End Assessment Pie -->

            <div class="col-xxl-2 col-lg-3 col-md-6 col-sm-6 col-6"> <!-- Assessment Realm Pie -->
              <div class="card chart-card">
                <div class="card-body">
                  <h5 class="card-title">Infoblox Realms | <span class="granularity-title">Last 30 Days</span></h5>
                  <div id="assessmentRealmsChart" class="pie"></div>
                </div>
              </div>
            </div><!-- Assessment Realm Pie -->

          </div>

          <div class="row">
            <!-- Top Users -->
            <div class="col-lg-6 col-12">
              <div class="card top-users bar-chart-card overflow-auto">
                <div class="card-body pb-0">
                  <h5 class="card-title">Top 10 Users | <span class="granularity-title">Last 30 Days</span></h5>
                  <div id="topUsersChart" class="bar"></div>
                </div>
              </div>
            </div><!-- End Top Users -->
            <!-- Top Customers -->
            <div class="col-lg-6 col-12">
              <div class="card top-customers bar-chart-card overflow-auto">
                <div class="card-body pb-0">
                  <h5 class="card-title">Top 10 Customers | <span class="granularity-title">Last 30 Days</span></h5>
                  <div id="topCustomersChart" class="bar"></div>
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

  <!-- Custom date range modal -->
  <div class="modal fade" id="customDateRangeModal" tabindex="-1" role="dialog" aria-labelledby="customDateRangeModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="customDateRangeModalLabel">Select Custom Date Range</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
            <span aria-hidden="true"></span>
          </button>
        </div>
        <div class="modal-body">
          <div class="toolsMenu">
            <label for="reportingStartAndEndDate">Select Date and Time Range:</label>
            <div class="col-md-12">
              <input type="text" id="reportingStartAndEndDate" placeholder="Start & End Date/Time">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" id="applyCustomRange" class="btn btn-primary">Apply</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    function dateFormatter(value, row, index) {
      var d = new Date(value) // The 0 there is the key, which sets the date to the epoch
      return d.toGMTString();
    }

    // document.addEventListener("DOMContentLoaded", () => {
    // Declare a global variable to store active filters
    var appliedFilters = {};
    function resetAppliedFilters() {
      appliedFilters = {
        type: "all",
        realm: "all",
        user: "all",
        customer: "all"
      };
      $("#clearFilters").css("display","none");
    }

    var updateAssessmentSummaryValues = () => {
      queryAPI("GET", "/api?f=getAssessmentReportsSummary").done(function( data, status ) {
        const total = data.find(item => item.type === "Total")
        $("#reportsThisDayVal").text(total["count_today"]);
        $("#customersThisDayVal").text(total["unique_customers_today"]+" Customers");
        $("#usersThisDayVal").text(total["unique_apiusers_today"]+" Users");
        $("#reportsThisMonthVal").text(total["count_this_month"]);
        $("#customersThisMonthVal").text(total["unique_customers_this_month"]+" Customers");
        $("#usersThisMonthVal").text(total["unique_apiusers_this_month"]+" Users");
        $("#reportsThisYearVal").text(total["count_this_year"]);
        $("#customersThisYearVal").text(total["unique_customers_this_year"]+" Customers");
        $("#usersThisYearVal").text(total["unique_apiusers_this_year"]+" Users");
      });
    };

    var updateRecentAssessments = (granularity, appliedFilters, start = null, end = null) => {
      queryAPI("GET", "/api?f=getAssessmentReports&granularity="+granularity+"&filters="+JSON.stringify(appliedFilters)+"&start="+start+"&end="+end).done(function( data, status ) {
        $("#assessmentTable").bootstrapTable("destroy");
        $("#assessmentTable").bootstrapTable({
          data: data,
          sortable: true,
          pagination: true,
          search: true,
          sortName: "created",
          sortOrder: "desc",
          showExport: true,
          exportTypes: ["json", "xml", "csv", "txt", "excel", "sql"],
          showColumns: true,
          filterControl: true,
          filterControlVisible: false,
          showFilterControlSwitch: true,
          columns: [{
            field: "id",
            title: "ID",
            sortable: true,
            visible: false
          },
          {
            field: "customer",
            title: "Customer",
            sortable: true,
            filterControl: "select"
          },{
            field: "realm",
            title: "Realm",
            sortable: true,
            filterControl: "select"
          },{
            field: "type",
            title: "Type",
            sortable: true,
            filterControl: "select"
          },{
            field: "userid",
            title: "User ID",
            sortable: true,
            visible: false,
            filterControl: "select"
          },{
            field: "apiuser",
            title: "API User",
            sortable: true,
            filterControl: "select"
          },{
            field: "status",
            title: "Status",
            sortable: true,
            filterControl: "select"
          },{
            field: "uuid",
            title: "UUID",
            sortable: true,
            visible: false,
            filterControl: "input"
          },{
            field: "created",
            title: "Generated At",
            sortable: true,
            formatter: "dateFormatter",
            filterControl: "input"
          }]
        });
        updateTopApiUsers(data,granularity);
        updateTopCustomers(data,granularity);
        updateAssessmentTypes(data);
        updateAssessmentRealms(data);
      });
    }
    // Render Assessments Area Chart
    window.charts.assessmentsChart = new ApexCharts(document.querySelector("#assessmentsChart"), areaChartOptions);
    window.charts.assessmentsChart.render();

    // Define Assessments Area Chart Update Function
    var updateAssessmentsChart = (granularity, appliedFilters, start = null, end = null) => {
      queryAPI("GET", "/api?f=getAssessmentReportsStats&granularity="+granularity+"&filters="+JSON.stringify(appliedFilters)+"&start="+start+"&end="+end).done(function( data, status ) {
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
        window.charts.assessmentsChart.updateOptions({
          series: series,
          xaxis: {
            categories: categories
          }
        });
      });
    };
    // Render Assessments Area Chart End //

    // Render Types Chart
    window.charts.typesChart = new ApexCharts(document.querySelector("#assessmentTypesChart"), donutChartOptions);
    window.charts.typesChart.render();

    // Define Types Chart Update Function
    var updateAssessmentTypes = (data) => {
      const countByType = data.reduce((acc, obj) => {
        acc[obj.type] = (acc[obj.type] || 0) + 1;
        return acc;
      }, {});
      var types = Object.keys(countByType).map(type => ({ type: type, count: countByType[type] }));
      window.charts.typesChart.updateOptions({
        series: types.map(type => type.count),
        labels: types.map(type => type.type),
        chart: {
          events: {
            dataPointSelection: (event, chartContext, config) => {
              chartFilter(event,chartContext.el,config.w.config.labels[config.dataPointIndex]);
            }
          }
        }
      });
    }
    // Render Types Chart End //

    // Render Realms Chart
    window.charts.realmsChart = new ApexCharts(document.querySelector("#assessmentRealmsChart"), donutChartOptions);
    window.charts.realmsChart.render();

    // Define Realms Chart Update Function
    var updateAssessmentRealms = (data) => {
      const countByRealm = data.reduce((acc, obj) => {
        acc[obj.realm] = (acc[obj.realm] || 0) + 1;
        return acc;
      }, {});

      const realms = Object.keys(countByRealm).map(realm => ({ realm: realm, count: countByRealm[realm] }));
      window.charts.realmsChart.updateOptions({
        series: realms.map(realm => realm.count),
        labels: realms.map(realm => realm.realm),
        chart: {
          events: {
            dataPointSelection: (event, chartContext, config) => {
              chartFilter(event,chartContext.el,config.w.config.labels[config.dataPointIndex]);
            }
          }
        }
      });
    }
    // Render Realms Chart End //


    // Render Top API Users Chart
    window.charts.topApiUsersChart = new ApexCharts(document.querySelector("#topUsersChart"), horizontalBarChartOptions);
    window.charts.topApiUsersChart.render();

    // Define Top API Users Chart Update Function
    var updateTopApiUsers = (data,granularity) => {
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
      window.charts.topApiUsersChart.updateOptions({
        series: [{
          data: apiUsers.map(user => user.count),
          name: "Assessment Count"
        }],
        xaxis: {
          categories: apiUsers.map(user => user.apiuser)
        },
        chart: {
          events: {
            dataPointSelection: (event, chartContext, config) => {
              chartFilter(event,chartContext.el,chartContext.w.config.xaxis.categories[config.dataPointIndex]);
            }
          }
        }
      });
    }
    // Render Top API Users Chart End //


    // Render Top Customers Chart
    window.charts.topCustomersChart = new ApexCharts(document.querySelector("#topCustomersChart"), horizontalBarChartOptions);
    window.charts.topCustomersChart.render();

    // Define Top Customers Chart Update Function
    var updateTopCustomers = (data,granularity) => {
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
      const customers = sortedCustomers.slice(0,10).map(customer => ({ customer: customer[0], count: customer[1] }));
      window.charts.topCustomersChart.updateOptions({
        series: [{
          data: customers.map(customer => customer.count),
          name: "Assessment Count"
        }],
        xaxis: {
          categories: customers.map(customer => customer.customer)
        },
        chart: {
          events: {
            dataPointSelection: (event, chartContext, config) => {
              chartFilter(event,chartContext.el,chartContext.w.config.xaxis.categories[config.dataPointIndex]);
            }
          }
        }
      });
    }
    // Render Top Customers Chart End //

    $("#applyCustomRange").on("click", function(event) {
      chartTimeFilter();
      $("#customDateRangeModal").modal("hide");
    });

    // Granularity Button
    $(".granularity-select").on("click", function(event) {
      if ($(event.currentTarget).data("granularity") == "custom") {
        $("#customDateRangeModal").modal("show");
      } else {
        updateAssessmentsChart($(event.currentTarget).data("granularity"),appliedFilters);
        updateRecentAssessments($(event.currentTarget).data("granularity"),appliedFilters);
      }
      $(".granularity-title").text($(event.currentTarget).text());
      $("#granularityBtn").text($(event.currentTarget).text()).attr("data-granularity",$(event.currentTarget).data("granularity"));
    });

    // Filter Button
    $("#clearFilters").on("click", function(event) {
      // Reset Applied Filters
      resetAppliedFilters();
      // Reset Charts
      window.charts.realmsChart = resetChart(window.charts.realmsChart,donutChartOptions);
      window.charts.typesChart = resetChart(window.charts.typesChart,donutChartOptions);
      window.charts.topApiUsersChart = resetChart(window.charts.topApiUsersChart,horizontalBarChartOptions);
      window.charts.topCustomersChart = resetChart(window.charts.topCustomersChart,horizontalBarChartOptions);
      chartTimeFilter();
    })

    // Filter the chart
    function chartFilter(event = null,el = null, value = null) {
      var parentElementId = $(el).attr("id");
      switch(parentElementId) {
        case "assessmentTypesChart":
          appliedFilters["type"] = value;
          break;
        case "assessmentRealmsChart":
          appliedFilters["realm"] = value;
          break;
        case "topUsersChart":
          appliedFilters["user"] = value;
          break;
        case "topCustomersChart":
          appliedFilters["customer"] = value;
          break;
      }
      chartTimeFilter();
      $("#clearFilters").css("display","block");
    }

    // Filter the chart with custom date/time range
    function chartTimeFilter() {
      if($("#granularityBtn").attr("data-granularity") == "custom") {
        if(!$("#reportingStartAndEndDate")[0].value){
          toast("Error","Missing Required Fields","The Start & End Date is a required field.","danger","30000");
          return null;
        }
        const reportingStartAndEndDate = $("#reportingStartAndEndDate")[0].value.split(" to ");
        const startDateTime = (new Date(reportingStartAndEndDate[0])).toISOString();
        const endDateTime = (new Date(reportingStartAndEndDate[1])).toISOString();
        updateAssessmentsChart($("#granularityBtn").attr("data-granularity"),appliedFilters,startDateTime,endDateTime);
        updateRecentAssessments($("#granularityBtn").attr("data-granularity"),appliedFilters,startDateTime,endDateTime);
      } else {
        updateAssessmentsChart($("#granularityBtn").attr("data-granularity"),appliedFilters);
        updateRecentAssessments($("#granularityBtn").attr("data-granularity"),appliedFilters);
      }
    }
    // Initial render
    resetAppliedFilters();
    updateAssessmentsChart("last30Days",appliedFilters);
    updateAssessmentSummaryValues();
    updateRecentAssessments("last30Days",appliedFilters);
  </script>
EOF;