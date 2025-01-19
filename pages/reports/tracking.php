<?php
if ($phpef->auth->checkAccess("REPORT-TRACKING") == false) {
  $phpef->api->setAPIResponse('Error','Unauthorized',401);
  return false;
}
return '
<main id="main" class="main">
  <section class="section reporting-section px-3">
    <div class="row">
      <!-- Columns -->
      <div class="col-lg-12">
        <div class="row">
          <!-- Visits Today Card -->
          <div class="col-lg-3 col-md-4 col-sm-6 col-12">
            <div class="card info-card visits-today-card">
              <div class="card-body">
                <h5 class="card-title">Visits <span>| Today</span></h5>
                <div class="d-flex align-items-center">
                  <!-- <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                    <i class="bi bi-cart"></i>
                  </div> -->
                  <div class="pt-1 ps-3">
                    <h6 id="visitsThisDayVal" class="metric-circle border-5"></h6>
                  </div>
                  <div class="p-2 pt-2 ps-4">
                    <!-- <span id="customersThisDayVal" class="ib-green small pt-1 mt-1 fw-bold"></span> -->
                    <span id="uniqueVisitorsThisDayVal" class="ib-black small pt-1 mt-1 fw-bold" style="display:flex;"></span>
                  </div>
                </div>
              </div>
            </div>
          </div><!-- Visits Today Card -->

          <!-- Visits This Month Card -->
          <div class="col-lg-3 col-md-4 col-sm-6 col-12">
            <div class="card info-card visits-month-card">
              <div class="card-body">
                <h5 class="card-title">Visits <span>| This Month</span></h5>
                <div class="d-flex align-items-center">
                  <!-- <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                    <i class="bi bi-currency-dollar"></i>
                  </div> -->
                  <div class="pt-1 ps-3">
                    <h6 id="visitsThisMonthVal" class="metric-circle border-5"></h6>
                  </div>
                  <div class="p-2 pt-2 ps-4">
                    <!-- <span id="customersThisMonthVal" class="ib-green small pt-1 mt-1 fw-bold"></span> -->
                    <span id="uniqueVisitorsThisMonthVal" class="ib-black small pt-1 mt-1 fw-bold" style="display:flex;"></span>
                  </div>
                </div>
              </div>
            </div>
          </div><!-- Visits This Month Card -->

          <!-- Visits This Year Card -->
          <div class="col-lg-3 col-md-4 col-sm-6 col-12">
            <div class="card info-card visits-year-card">
              <div class="card-body">
                <h5 class="card-title">Visits <span>| This Year</span></h5>
                <div class="d-flex align-items-center">
                  <!-- <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                    <i class="bi bi-people"></i>
                  </div> -->
                  <div class="pt-1 ps-3">
                    <h6 id="visitsThisYearVal" class="metric-circle border-5"></h6>
                  </div>
                  <div class="p-2 pt-2 ps-4">
                    <!-- <span id="customersThisYearVal" class="ib-green small pt-1 mt-1 fw-bold"></span> -->
                    <span id="uniqueVisitorsThisYearVal" class="ib-black small pt-1 mt-1 fw-bold" style="display:flex;"></span>
                  </div>
                </div>
              </div>
            </div>
          </div><!-- Visits This Year Card -->

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
          <!-- Visitors Chart -->
          <div class="col-xxl-8 col-lg-6 col-md-12 col-sm-12 col-12">
            <div class="card chart-card">
              <div class="card-body">
                <h5 class="card-title">Visitors | <span class="granularity-title">Last 30 Days</span></h5>
                <!-- Line Chart -->
                <div id="visitorsChart"></div>
                <!-- End Line Chart -->
              </div>
            </div>
          </div><!-- End Visitors -->

          <div class="col-xxl-2 col-lg-3 col-md-6 col-sm-6 col-6"> <!-- Browser Types Pie -->
            <div class="card chart-card">
              <div class="card-body">
                <h5 class="card-title">Browser Types | <span class="granularity-title">Last 30 Days</span></h5>
                <div id="browserTypesChart" class="pie"></div>
              </div>
            </div>
          </div><!-- End Browser Pie -->

          <div class="col-xxl-2 col-lg-3 col-md-6 col-sm-6 col-6"> <!-- OS Types Pie -->
            <div class="card chart-card">
              <div class="card-body">
                <h5 class="card-title">OS Types | <span class="granularity-title">Last 30 Days</span></h5>
                <div id="osTypesChart" class="pie"></div>
              </div>
            </div>
          </div><!-- End OS Types Pie -->

        </div>

        <div class="row">
          <!-- Top Pages -->
          <div class="col-lg-6 col-12">
            <div class="card top-pages bar-chart-card">
              <div class="card-body pb-0">
                <h5 class="card-title">Top 10 Pages | <span class="granularity-title">Last 30 Days</span></h5>
                <div id="topPagesChart" class="bar"></div>
              </div>
            </div>
          </div><!-- End Top Pages -->
          <!-- Page Activity -->
          <div class="col-lg-6 col-12">
            <div class="card top-customers bar-chart-card">
              <div class="card-body pb-0">
                <h5 class="card-title">Page Activity | <span class="granularity-title">Last 30 Days</span></h5>
                <div id="pageActivityChart" class="bar"></div>
              </div>
            </div>
          </div><!-- End Page Activity -->
        </div>
        <div class="row">
          <!-- Visitors List -->
          <div class="col-12">
            <div class="card recent-assessments">
              <div class="card-body">
                <h5 class="card-title">Visitors List | <span class="granularity-title">Last 30 Days</span></h5>
                <table id="assessmentTable" class="table-striped"></table>
              </div>
            </div>
          </div><!-- End Visitors List -->
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

  function msFormatter(value, row, index) {
    var minutes = Math.floor(value / 60000);
    var seconds = ((value % 60000) / 1000).toFixed(0);
    return minutes + "m " + (seconds < 10 ? "0" : "") + seconds + "s";
  }


  // Declare a global variable to store active filters
  var appliedFilters = {};
  function resetAppliedFilters() {
    appliedFilters = {
      page: "all",
      browser: "all",
      os: "all"
    };
    $("#clearFilters").css("display","none");
  }

  var updateTrackingSummaryValues = () => {
    queryAPI("GET", "/api/reports/tracking/summary").done(function( response, status ) {
      let data = response["data"];
      const total = data.find(item => item.type === "Total")
      $("#visitsThisDayVal").text(total["count_today"]);
      $("#uniqueVisitorsThisDayVal").text(total["unique_visitors_today"]+" Unique Visitors");
      $("#visitsThisMonthVal").text(total["count_this_month"]);
      $("#uniqueVisitorsThisMonthVal").text(total["unique_visitors_this_month"]+" Unique Visitors");
      $("#visitsThisYearVal").text(total["count_this_year"]);
      $("#uniqueVisitorsThisYearVal").text(total["unique_visitors_this_year"]+" Unique Visitors");
    });
  };

  var updateRecentTracking = (granularity, appliedFilters, start = null, end = null) => {
    queryAPI("GET", "/api/reports/tracking/records?granularity="+granularity+"&filters="+JSON.stringify(appliedFilters)+"&start="+start+"&end="+end).done(function( response, status ) {
      let data = response["data"];
      $("#assessmentTable").bootstrapTable("destroy");
      $("#assessmentTable").bootstrapTable({
        data: data,
        sortable: true,
        pagination: true,
        search: true,
        sortName: "dateTime",
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
        },{
          field: "username",
          title: "Username",
          sortable: true,
          filterControl: "select"
        },{
          field: "scheme",
          title: "Scheme",
          sortable: true,
          filterControl: "select"
        },{
          field: "path",
          title: "Path",
          sortable: true,
          filterControl: "select"
        },{
          field: "pageCategory",
          title: "Page Category",
          sortable: true,
          filterControl: "select"
        },{
          field: "pageName",
          title: "Page Name",
          sortable: true,
          filterControl: "select"
        },{
          field: "ipAddress",
          title: "IP Address",
          sortable: true,
          filterControl: "input"
        },{
          field: "browser",
          title: "Browser",
          sortable: true,
          filterControl: "select"
        },{
          field: "os",
          title: "OS",
          sortable: true,
          filterControl: "select"
        },{
          field: "timeSpent",
          title: "Duration",
          sortable: true,
          formatter: "msFormatter",
          filterControl: "input"
        },{
          field: "clicks",
          title: "Clicks",
          sortable: true,
          filterControl: "input"
        },{
          field: "mouseMovements",
          title: "Mouse Movements",
          sortable: true,
          visible: false,
          filterControl: "select"
        },{
          field: "tId",
          title: "Tracking ID",
          sortable: true,
          visible: false,
          filterControl: "input"
        },{
          field: "dateTime",
          title: "Date/Time",
          sortable: true,
          formatter: "dateFormatter",
          filterControl: "input"
        }]
      });
      updateTopPages(data,granularity);
      updatePageActivityChart(data,granularity);
      updateBrowserTypes(data);
      updateOSTypes(data);
    });
  }

  // Render Visitors Area Chart
  window.charts.visitorsChart = new ApexCharts(document.querySelector("#visitorsChart"), areaChartOptions);
  window.charts.visitorsChart.render();

  // Define Visitors Area Chart Update Function
  var updateVisitorsChart = (granularity, appliedFilters, start = null, end = null) => {
    queryAPI("GET", "/api/reports/tracking/stats?granularity="+granularity+"&filters="+JSON.stringify(appliedFilters)+"&start="+start+"&end="+end).done(function( response, status ) {
      let data = response["data"];
      // Extract all unique dates
      const categoriesSet = new Set();
      for (const key in data) {
        if (data.hasOwnProperty(key)) {
          categoriesSet.add(key);
        }
      }
      const categories = Array.from(categoriesSet).sort();
      // Prepare the series data
      const series = [];
      for (const key in data) {
        if (data.hasOwnProperty(key)) {
          const seriesData = categories.map(date => data[key] || 0);
          series.push(data[key]);
        }
      }
      window.charts.visitorsChart.updateOptions({
        series: [{
          name: "Visitors",
          data: series
        }],
        xaxis: {
          categories: categories
        }
      });
    });
  };
  // Render Visitors Area Chart End //

  // Render Browser Types Chart
  window.charts.browserTypesChart = new ApexCharts(document.querySelector("#browserTypesChart"), donutChartOptions);
  window.charts.browserTypesChart.render();

  // Define Types Chart Update Function
  var updateBrowserTypes = (data) => {
    const countByBrowser = data.reduce((acc, obj) => {
      acc[obj.browser] = (acc[obj.browser] || 0) + 1;
      return acc;
    }, {});
    var types = Object.keys(countByBrowser).map(browser => ({ browser: browser, count: countByBrowser[browser] }));
    window.charts.browserTypesChart.updateOptions({
      series: types.map(browser => browser.count),
      labels: types.map(browser => browser.browser),
      chart: {
        events: {
          dataPointSelection: (event, chartContext, config) => {
            chartFilter(event,chartContext.el,config.w.config.labels[config.dataPointIndex]);
          }
        }
      },
    });
  }
  // Render Browser Types Chart End //


  // Render OS Types Chart
  window.charts.osTypesChart = new ApexCharts(document.querySelector("#osTypesChart"), donutChartOptions);
  window.charts.osTypesChart.render();

  // Define OS Types Chart Update Function
  var updateOSTypes = (data) => {
    const countByOS = data.reduce((acc, obj) => {
      acc[obj.os] = (acc[obj.os] || 0) + 1;
      return acc;
    }, {});

    const osTypes = Object.keys(countByOS).map(os => ({ os: os, count: countByOS[os] }));
    window.charts.osTypesChart.updateOptions({
      series: osTypes.map(os => os.count),
      labels: osTypes.map(os => os.os),
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


  // Render Top Pages Chart
  window.charts.topPagesChart = new ApexCharts(document.querySelector("#topPagesChart"), horizontalBarChartOptions);
  window.charts.topPagesChart.render();

  // Define Top Pages Chart Update Function
  var updateTopPages = (data,granularity) => {
    const pageCount = {};
    data.forEach(entry => {
      const page = entry.pageName;
      if (page) {
        if (!pageCount[page]) {
          pageCount[page] = 0;
        }
        pageCount[page]++;
      }
    });

    const sortedPages = Object.entries(pageCount).sort((a, b) => b[1] - a[1]);
    const pages = sortedPages.slice(0,10).map(page => ({ page: page[0], count: page[1] }));
    window.charts.topPagesChart.updateOptions({
      series: [{
        data: pages.map(page => page.count),
        name: "Page Visits"
      }],
      xaxis: {
        categories: pages.map(page => page.page)
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
  // Render Top Pages Chart End //


  // Render Page Activity Chart
  window.charts.pageActivityChart = new ApexCharts(document.querySelector("#pageActivityChart"), lineColumnChartOptions);
  window.charts.pageActivityChart.render();

  var updatePageActivityChart = (data,granularity) => {
    // Aggregating data by pageName
    const aggregatedData = data.reduce((acc, item) => {
      const pageName = item.pageName || "Home";
      if (!acc[pageName]) {
          acc[pageName] = { timeSpent: 0, visits: 0 };
      }
      acc[pageName].timeSpent += item.timeSpent;
      acc[pageName].visits += 1;
      return acc;
    }, {});

    // Extracting the data for the page activity chart
    const categories = Object.keys(aggregatedData);
    const timeSpent = categories.map(page => Math.round((aggregatedData[page].timeSpent / 1000)));
    const visits = categories.map(page => aggregatedData[page].visits);
    window.charts.pageActivityChart.updateOptions({
      series: [{
        name: "Time Spent",
        type: "column",
        data: timeSpent
      }, {
        name: "Total Visits",
        type: "line",
        data: visits
      }],
      title: {
        text: "Page Activity"
      },
      labels: categories,
      yaxis: [{
        title: {
          text: "Time Spent"
        },
        labels: {
          formatter: function (val) {
            const days = Math.floor((val / 3600) / 24);
            const hours = Math.floor(val / 3600);
            const minutes = Math.floor((val % 3600) / 60);
            if (days > 0) {
              return `${days}d`;
            } else if (hours > 0) {
              return `${hours}h`;
            } else {
              return `${minutes}m`;
            }
          }
        }
      }, {
        opposite: true,
        title: {
          text: "Total Visits"
        }
      }],
      tooltip: {
        y: {
          formatter: function (val, { series, seriesIndex, dataPointIndex, w }) {
            if (w.globals.seriesNames[seriesIndex] === "Time Spent") {
              const days = Math.floor(val / 86400); // 86400 seconds in a day
              const hours = Math.floor((val % 86400) / 3600); // remaining hours
              const minutes = Math.floor((val % 3600) / 60); // remaining minutes
              const seconds = Math.floor(val % 60); // remaining seconds

              if (days > 0) {
                return `${days}d ${hours}h ${minutes}m ${seconds}s`;
              } else if (hours > 0) {
                return `${hours}h ${minutes}m ${seconds}s`;
              } else if (minutes > 0) {
                return `${minutes}m ${seconds}s`;
              } else {
                return `${seconds}s`;
              }
            } else {
              return val;
            }
          }
        }
      },
      chart: {
        events: {
          dataPointSelection: (event, chartContext, config) => {
            var category = categories[config.dataPointIndex];
            if (category == "Home") {
              category = "";
            };
            chartFilter(event,chartContext.el,category);
          }
        }
      }
    });
  }
  // Render Page Activity Chart End //

  $("#applyCustomRange").on("click", function(event) {
    chartTimeFilter();
    $("#customDateRangeModal").modal("hide");
  });

  // Granularity Button
  $(".granularity-select").on("click", function(event) {
    if ($(event.currentTarget).data("granularity") == "custom") {
      $("#customDateRangeModal").modal("show");
    } else {
      updateVisitorsChart($(event.currentTarget).data("granularity"),appliedFilters);
      updateRecentTracking($(event.currentTarget).data("granularity"),appliedFilters);
    }
    $(".granularity-title").text($(event.currentTarget).text());
    $("#granularityBtn").text($(event.currentTarget).text()).attr("data-granularity",$(event.currentTarget).data("granularity"));
  });

  // Filter Button
  $("#clearFilters").on("click", function(event) {
    // Reset Applied Filters
    resetAppliedFilters();
    // Reset Charts
    window.charts.osTypesChart = resetChart(window.charts.osTypesChart,donutChartOptions);
    window.charts.browserTypesChart = resetChart(window.charts.browserTypesChart,donutChartOptions);
    window.charts.topPagesChart = resetChart(window.charts.topPagesChart,horizontalBarChartOptions);
    window.charts.pageActivityChart = resetChart(window.charts.pageActivityChart,lineColumnChartOptions);
    chartTimeFilter();
  })

  // Filter the chart
  function chartFilter(event = null,el = null, value = null) {
    var parentElementId = $(el).attr("id");
    switch(parentElementId) {
      case "browserTypesChart":
        appliedFilters["browser"] = value;
        break;
      case "osTypesChart":
        appliedFilters["os"] = value;
        break;
      case "topPagesChart":
        appliedFilters["page"] = value;
        break;
      case "pageActivityChart":
        appliedFilters["page"] = value;
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
      updateVisitorsChart($("#granularityBtn").attr("data-granularity"),appliedFilters,startDateTime,endDateTime);
      updateRecentTracking($("#granularityBtn").attr("data-granularity"),appliedFilters,startDateTime,endDateTime);
    } else {
      updateVisitorsChart($("#granularityBtn").attr("data-granularity"),appliedFilters);
      updateRecentTracking($("#granularityBtn").attr("data-granularity"),appliedFilters);
    }
  }

  // Initial render
  resetAppliedFilters();
  updateVisitorsChart("last30Days",appliedFilters);
  updateTrackingSummaryValues();
  updateRecentTracking("last30Days",appliedFilters);
</script>
';