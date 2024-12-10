<?php
require_once(__DIR__.'/../../inc/inc.php');
if ($ib->auth->checkAccess(null,"REPORT-TRACKING") == false) {
  die();
}
?>

<main id="main" class="main">
  <section class="section reporting-section">
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
    return minutes + "m " + (seconds < 10 ? '0' : '') + seconds + 's';
  }



  document.addEventListener("DOMContentLoaded", () => {
    if ($('.dark-theme').length > 0) {
      var theme = 'dark';
    } else {
      var theme = 'light';
    }

    // Colour Palettes
    var barChartColorPalette = ['#FDDD00','#E1DD1A','#C5DE33','#A9DE4D','#8DDF66','#70DF80','#54E099','#38E0B3','#1CE1CC','#00E1E6'];
    var pieChartColorPalette = ['#0fbe4d','#94ce36','#00F9FF','#00d69b','#00F9FF'];

    // Declare a global variable to store active filters
    var appliedFilters = {};
    function resetAppliedFilters() {
      appliedFilters = {
        page: 'all',
        browser: 'all',
        os: 'all'
      };
      $('#clearFilters').css('display','none');
    }

    const updateSummaryValues = () => {
      $.get( "/api?f=getTrackingSummary").done(function( data, status ) {
        const total = data.find(item => item.type === "Total")
        $('#visitsThisDayVal').text(total['count_today']);
        // $('#customersThisDayVal').text(total['unique_customers_today']+' Customers');
        $('#uniqueVisitorsThisDayVal').text(total['unique_visitors_today']+' Unique Visitors');
        $('#visitsThisMonthVal').text(total['count_today']);
        // $('#customersThisMonthVal').text(total['unique_customers_this_month']+' Customers');
        $('#uniqueVisitorsThisMonthVal').text(total['unique_visitors_this_month']+' Unique Visitors');
        $('#visitsThisYearVal').text(total['count_today']);
        // $('#customersThisYearVal').text(total['unique_customers_this_year']+' Customers');
        $('#uniqueVisitorsThisYearVal').text(total['unique_visitors_this_year']+' Unique Visitors');
      });
    };

    const updateRecentAssessments = (granularity, appliedFilters, start = null, end = null) => {
      $.get( "/api?f=getTrackingRecords&granularity="+granularity+"&filters="+JSON.stringify(appliedFilters)+"&start="+start+"&end="+end).done(function( data, status ) {
        $('#assessmentTable').bootstrapTable('destroy');
        $('#assessmentTable').bootstrapTable({
          data: data,
          sortable: true,
          pagination: true,
          search: true,
          sortName: 'dateTime',
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
          },{
            field: 'scheme',
            title: 'Scheme',
            sortable: true,
            filterControl: 'select'
          },{
            field: 'path',
            title: 'Path',
            sortable: true,
            filterControl: 'select'
          },{
            field: 'pageCategory',
            title: 'Page Category',
            sortable: true,
            filterControl: 'select'
          },{
            field: 'pageName',
            title: 'Page Name',
            sortable: true,
            filterControl: 'select'
          },{
            field: 'ipAddress',
            title: 'IP Address',
            sortable: true,
            filterControl: 'input'
          },{
            field: 'browser',
            title: 'Browser',
            sortable: true,
            filterControl: 'select'
          },{
            field: 'os',
            title: 'OS',
            sortable: true,
            filterControl: 'select'
          },{
            field: 'timeSpent',
            title: 'Duration',
            sortable: true,
            formatter: 'msFormatter',
            filterControl: 'input'
          },{
            field: 'clicks',
            title: 'Clicks',
            sortable: true,
            filterControl: 'input'
          },{
            field: 'mouseMovements',
            title: 'Mouse Movements',
            sortable: true,
            visible: false,
            filterControl: 'select'
          },{
            field: 'tId',
            title: 'Tracking ID',
            sortable: true,
            visible: false,
            filterControl: 'input'
          },{
            field: 'dateTime',
            title: 'Date/Time',
            sortable: true,
            formatter: 'dateFormatter',
            filterControl: 'input'
          }]
        });
        updateTopPages(data,granularity);
        updatePageActivityChart(data,granularity);
        updateBrowserTypes(data);
        updateOSTypes(data);
      });
    }

    // Define Area Chart Options
    const areaChartOptions = {
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
      noData: {
        text: 'Loading...'
      },
      dataLabels: {
        enabled: false
      },
      stroke: {
        curve: 'smooth',
        width: 2
      },
      series: [],
      xaxis: {
          categories: []
      }
    };

    // Define Donut Chart Options
    const donutChartOptions = {
        tooltip: {
          theme: theme
        },
        chart: {
          type: 'donut',
          height: '350px',
          events: {
            dataPointSelection: (event, chartContext, config) => {
              chartFilter(event,chartContext.el,config.w.config.labels[config.dataPointIndex]);
            }
          },
        },
        plotOptions: {
          pie: {}
        },
        noData: {
          text: 'Loading...'
        },
        legend: {
          position: 'bottom',
          offsetY: 20,
          itemMargin: {
            horizontal: 5
          },
        },
        dataLabels: {
          enabled: false
        },
        series: [],
        labels: [],
        colors: pieChartColorPalette
    };

    // Define Horizontal Bar Chart Options
    const horizontalBarChartOptions = {
      tooltip: {
        theme: theme
      },
      chart: {
        type: 'bar',
        height: 350,
        events: {
          dataPointSelection: (event, chartContext, config) => {
            chartFilter(event,chartContext.el,chartContext.w.config.xaxis.categories[config.dataPointIndex]);
          }
        },
      },
      plotOptions: {
        bar: {
          horizontal: true,
          distributed: true // This enables different colors for each bar
        }
      },
      noData: {
        text: 'Loading...'
      },
      dataLabels: {
        enabled: false
      },
      series: [],
      colors: barChartColorPalette
    };

    // Define Line Column Chart Options
    const lineColumnChartOptions = {
      chart: {
          type: 'line',
          height: 350
      },
      series: [],
      noData: {
        text: 'Loading...'
      },
      stroke: {
          width: [0, 4]
      },
      dataLabels: {
          enabled: true,
          enabledOnSeries: [1]
      },
      labels: [],
      xaxis: {
          type: 'category'
      },
      colors: barChartColorPalette
    };

    // Render Visitors Area Chart
    const visitorsChart = new ApexCharts(document.querySelector("#visitorsChart"), areaChartOptions);
    visitorsChart.render();

    // Define Visitors Area Chart Update Function
    const updateVisitorsChart = (granularity, appliedFilters, start = null, end = null) => {
      $.get( "/api?f=getTrackingStats&granularity="+granularity+"&filters="+JSON.stringify(appliedFilters)+"&start="+start+"&end="+end).done(function( data, status ) {
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
        visitorsChart.updateOptions({
          series: [{
            name: 'Visitors',
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
    var browserTypesChart = new ApexCharts(document.querySelector("#browserTypesChart"), donutChartOptions);
    browserTypesChart.render();

    // Define Types Chart Update Function
    const updateBrowserTypes = (data) => {
      const countByBrowser = data.reduce((acc, obj) => {
        acc[obj.browser] = (acc[obj.browser] || 0) + 1;
        return acc;
      }, {});
      var types = Object.keys(countByBrowser).map(browser => ({ browser: browser, count: countByBrowser[browser] }));
      browserTypesChart.updateOptions({
        series: types.map(browser => browser.count),
        labels: types.map(browser => browser.browser),
      });
    }
    // Render Browser Types Chart End //


    // Render OS Types Chart
    var osTypesChart = new ApexCharts(document.querySelector("#osTypesChart"), donutChartOptions);
    osTypesChart.render();

    // Define OS Types Chart Update Function
    const updateOSTypes = (data) => {
      const countByOS = data.reduce((acc, obj) => {
        acc[obj.os] = (acc[obj.os] || 0) + 1;
        return acc;
      }, {});

      const osTypes = Object.keys(countByOS).map(os => ({ os: os, count: countByOS[os] }));
      osTypesChart.updateOptions({
        series: osTypes.map(os => os.count),
        labels: osTypes.map(os => os.os)
      });
    }
    // Render Realms Chart End //


    // Render Top Pages Users Chart
    var topPagesChart = new ApexCharts(document.querySelector("#topPagesChart"), horizontalBarChartOptions);
    topPagesChart.render();

    // Define Top Pages Users Chart Update Function
    const updateTopPages = (data,granularity) => {
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
      topPagesChart.updateOptions({
        series: [{
          data: pages.map(page => page.count),
          name: 'Page Visits'
        }],
        xaxis: {
          categories: pages.map(page => page.page)
        },
      });
    }
    // Render Top Pages Chart End //


    // Render Page Activity Chart
    var pageActivityChart = new ApexCharts(document.querySelector("#pageActivityChart"), lineColumnChartOptions);
    pageActivityChart.render();

    const updatePageActivityChart = (data,granularity) => {
      // Aggregating data by pageName
      const aggregatedData = data.reduce((acc, item) => {
        const pageName = item.pageName || 'Home';
        if (!acc[pageName]) {
            acc[pageName] = { timeSpent: 0, visits: 0 };
        }
        acc[pageName].timeSpent += item.timeSpent;
        acc[pageName].visits += 1;
        return acc;
      }, {});

      // Extracting the data for the chart
      const categories = Object.keys(aggregatedData);
      const timeSpent = categories.map(page => aggregatedData[page].timeSpent / 1000);
      const visits = categories.map(page => aggregatedData[page].visits);
      pageActivityChart.updateOptions({
        series: [{
          name: 'Time Spent',
          type: 'column',
          data: timeSpent
        }, {
          name: 'Total Visits',
          type: 'line',
          data: visits
        }],
        title: {
          text: 'Page Activity'
        },
        labels: categories,
        yaxis: [{
          title: {
            text: 'Time Spent (s)'
          }
        }, {
          opposite: true,
          title: {
            text: 'Total Visits'
          }
        }]
      });
    }
    // Render Page Activity Chart End //

    $('#applyCustomRange').on('click', function(event) {
      chartTimeFilter();
      $('#customDateRangeModal').modal('hide');
    });

    // Granularity Button
    $('.granularity-select').on('click', function(event) {
      if ($(event.currentTarget).data('granularity') == 'custom') {
        $('#customDateRangeModal').modal('show');
      } else {
        updateVisitorsChart($(event.currentTarget).data('granularity'),appliedFilters);
        updateRecentAssessments($(event.currentTarget).data('granularity'),appliedFilters);
      }
      $('.granularity-title').text($(event.currentTarget).text());
      $('#granularityBtn').text($(event.currentTarget).text()).attr('data-granularity',$(event.currentTarget).data('granularity'));
    });

    // Filter Button
    $('#clearFilters').on('click', function(event) {
      // Reset Applied Filters
      resetAppliedFilters();
      // Reset Charts
      osTypesChart = resetPieChart(osTypesChart,donutChartOptions);
      browserTypesChart = resetPieChart(browserTypesChart,donutChartOptions);
      topPagesChart = resetPieChart(topPagesChart,horizontalBarChartOptions);
      pageActivityChart = resetPieChart(pageActivityChart,lineColumnChartOptions);
      chartTimeFilter();
    })

    // Filter the chart
    function chartFilter(event = null,el = null, value = null) {
      var parentElementId = $(el).attr('id');
      switch(parentElementId) {
        case 'browserTypesChart':
          appliedFilters['browser'] = value;
          break;
        case 'osTypesChart':
          appliedFilters['os'] = value;
          break;
        case 'topPagesChart':
          appliedFilters['page'] = value;
          break;
        case 'pageActivityChart':
          // appliedFilters['customer'] = value;
          break;
      }
      chartTimeFilter();
      $('#clearFilters').css('display','block');
    }

    // Filter the chart with custom date/time range
    function chartTimeFilter() {
      if($('#granularityBtn').attr('data-granularity') == 'custom') {
        if(!$('#reportingStartAndEndDate')[0].value){
          toast("Error","Missing Required Fields","The Start & End Date is a required field.","danger","30000");
          return null;
        }
        const reportingStartAndEndDate = $('#reportingStartAndEndDate')[0].value.split(" to ");
        const startDateTime = (new Date(reportingStartAndEndDate[0])).toISOString();
        const endDateTime = (new Date(reportingStartAndEndDate[1])).toISOString();
        updateVisitorsChart($('#granularityBtn').attr('data-granularity'),appliedFilters,startDateTime,endDateTime);
        updateRecentAssessments($('#granularityBtn').attr('data-granularity'),appliedFilters,startDateTime,endDateTime);
      } else {
        updateVisitorsChart($('#granularityBtn').attr('data-granularity'),appliedFilters);
        updateRecentAssessments($('#granularityBtn').attr('data-granularity'),appliedFilters);
      }
    }

    function resetPieChart(chart,options) {
      var querySelector = chart.ctx.el.id;
      chart.destroy();
      chart = new ApexCharts(document.querySelector("#"+querySelector), options);
      chart.render();
      return chart;
    }

    // Initial render
    resetAppliedFilters();
    updateVisitorsChart('last30Days',appliedFilters);
    updateSummaryValues();
    updateRecentAssessments('last30Days',appliedFilters);
  });
</script>
