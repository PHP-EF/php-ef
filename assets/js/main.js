// Core Functions & Logging
$.xhrPool = [];
function queryAPI(type,path,data=null,contentType="application/json",asyncValue=true){
	if (contentType == 'application/json' && data != null) {
    data = JSON.stringify(data);
  }
  let timeout = 10000;
	switch (type) {
		case 'get':
		case 'GET':
			return $.ajax({
				url:path,
				method:"GET",
				beforeSend: function(request) {
					$.xhrPool.push(request);
				},
				complete: function(jqXHR) {
					var i = $.xhrPool.indexOf(jqXHR); //  get index for current connection completed
					if (i > -1) $.xhrPool.splice(i, 1); //  removes from list by index
				},
				timeout: timeout,
			});
		case 'delete':
		case 'DELETE':
			return $.ajax({
				url:path,
				method:"DELETE",
				beforeSend: function(request) {
					$.xhrPool.push(request);
				},
				complete: function(jqXHR) {
					var i = $.xhrPool.indexOf(jqXHR); //  get index for current connection completed
					if (i > -1) $.xhrPool.splice(i, 1); //  removes from list by index
				},
				timeout: timeout,
			});
		case 'post':
		case 'POST':
			return $.ajax({
				url:path,
				method:"POST",
				async: asyncValue,
				beforeSend: function(request) {
					$.xhrPool.push(request);
				},
				complete: function(jqXHR) {
					var i = $.xhrPool.indexOf(jqXHR); //  get index for current connection completed
					if (i > -1) $.xhrPool.splice(i, 1); //  removes from list by index
				},
				data:data,
				contentType: contentType
			});
		case 'put':
		case 'PUT':
			return $.ajax({
				url:path,
				method:"PUT",
				async: asyncValue,
				beforeSend: function(request) {
					$.xhrPool.push(request);
				},
				complete: function(jqXHR) {
					var i = $.xhrPool.indexOf(jqXHR); //  get index for current connection completed
					if (i > -1) $.xhrPool.splice(i, 1); //  removes from list by index
				},
				data:data,
				contentType: contentType
			});
    case 'patch':
    case 'PATCH':
      return $.ajax({
        url:path,
        method:"PATCH",
        async: asyncValue,
        beforeSend: function(request) {
          $.xhrPool.push(request);
        },
        complete: function(jqXHR) {
          var i = $.xhrPool.indexOf(jqXHR); //  get index for current connection completed
          if (i > -1) $.xhrPool.splice(i, 1); //  removes from list by index
        },
        data:data,
        contentType: contentType
      });
		default:
			console.warn('API: Method Not Supported');
	}
}

function logConsole(subject,msg,type = 'info'){
	let color;
	switch (type){
		case 'error':
			color = '#ed2e72';
			break;
		case 'warning':
			color = '#272361';
			break;
		default:
			color = '#2cabe3';
			break;
	}
	console.info("%c "+subject+" %c ".concat(msg, " "), "color: white; background: "+color+"; font-weight: 700;", "color: "+color+"; background: white; font-weight: 700;");
}

function setCookie(cName, cValue, expDays) {
        let date = new Date();
        date.setTime(date.getTime() + (expDays * 24 * 60 * 60 * 1000));
        const expires = "expires=" + date.toUTCString();
        document.cookie = cName + "=" + cValue + "; " + expires + "; path=/";
}

function getCookie(name) {
    var cookieArr = document.cookie.split(";");
    for(var i = 0; i < cookieArr.length; i++) {
        var cookiePair = cookieArr[i].split("=");
        if(name == cookiePair[0].trim()) {
            return decodeURIComponent(cookiePair[1]);
        }
    }
    return null;
}

async function heartBeat() {
  const delay = ms => new Promise(res => setTimeout(res, ms));
  try {
    const response = await fetch('/api/auth/heartbeat', {cache: "no-cache"});
    if (response.status == "200") {
      while (true) {
	      let response2 = await fetch('/api/auth/heartbeat', {cache: "no-cache"});
        if (response2.status == "301") {
          window.location.href = "/login.php?redirect_uri="+window.location.href.replace("#","?");
	      }
	      await delay(10000);
      }
    }
  } catch (err) {
    console.log(err);
  }
}

function getNoAsync(url) {
  return JSON.parse($.ajax({
    type: "GET",
    url: url,
    cache: false,
    async: false
  }).responseText);
}

const intToIp4 = int =>
  [(int >>> 24) & 0xFF, (int >>> 16) & 0xFF,
   (int >>> 8) & 0xFF, int & 0xFF].join('.');

const calculateCidrRange = cidr => {
  const [range, bits = 32] = cidr.split('/');
  const mask = ~(2 ** (32 - bits) - 1);
  return [intToIp4(ip4ToInt(range) & mask), intToIp4(ip4ToInt(range) | ~mask)];
};

const ip4ToInt = ip =>
  ip.split('.').reduce((int, oct) => (int << 8) + parseInt(oct, 10), 0) >>> 0;

const isIp4InCidr = ip => cidr => {
  const [range, bits = 32] = cidr.split('/');
  const mask = ~(2 ** (32 - bits) - 1);
  return (ip4ToInt(ip) & mask) === (ip4ToInt(range) & mask);
};

const isIp4InCidrs = (ip, cidrs) => cidrs.some(isIp4InCidr(ip));

const netmaskToCIDR = (netmask) => (netmask.split('.').map(Number)
                                 .map(part => (part >>> 0).toString(2))
                                 .join('')).split('1').length -1;

const cidrToNetmask = (bitCount) => {
  var mask=[];
  for(var i=0;i<4;i++) {
      var n = Math.min(bitCount, 8);
      mask.push(256 - Math.pow(2, 8-n));
      bitCount -= n;
  }
  return mask.join('.');
}

function stringValidate(element, min, max, type) {
  var elementValue = element.val();
  if (elementValue.length < min || elementValue.length > max) {
    if (min === max) {
      toast("Error","","Invalid length. Please specify a value which is "+max+" characters long","danger");
    } else {
      toast("Error","","Invalid length. Please specify a value which is between "+min+" and "+max+" character(s) long","danger");
    }
    return false;
  } else {
    switch(type) {
      case "string":
        if (isNaN(elementValue)) {
          return true;
        } else {
          toast("Error","","This field requires a String. Numbers are not allowed","danger");
          return false;
        }
        break;
      case "int":
        if (isNaN(elementValue)) {
          toast("Error","","This field requires an Integer. Characters are not allowed","danger");
          return false;
        } else {
          return true;
        }
        break;
    }
    return true;
  }
}

function loadiFrame(element = null) {
  if (element != null) {
    var hashsplit = element.split('#page=');
    var linkElem = $('a[href="#page='+hashsplit[1]+'"]');
    $('.toggleFrame').removeClass('active');
    linkElem.addClass('active');
    if (hashsplit[1].startsWith('prx')) {
      var prxsplit = hashsplit[1].split('prx');
      window.parent.document.getElementById('mainFrame').src = prxsplit[1];
    } else {
      window.parent.document.getElementById('mainFrame').src = '/pages/'+hashsplit[1]+".php";
    }
  } else if (window.parent.location.hash) {
    var hashsplit = window.parent.location.hash.split('#page=');
    // Auto-expand and set navbar to active
    var linkElem = $('a[href="'+window.parent.location.hash+'"]');
    linkElem.addClass('active');
    $('.title-text').text(linkElem.data('pageName'));
    var doubleParent = $('.icon-link > .toggleFrame.active, .sub-sub-menu .toggleFrame.active, .icon-link > .toggleFrame.active, .sub-menu .toggleFrame.active').parent().parent();
    if (doubleParent.hasClass('sub-sub-menu')) {
      if (!doubleParent.parent().hasClass('showMenu')) {
        doubleParent.parent().addClass('showMenu');
      }
      if (!doubleParent.parent().parent().parent().hasClass('showMenu')) {
          doubleParent.parent().parent().parent().addClass('showMenu');
      }
    } else if (doubleParent.hasClass('sub-menu') && doubleParent.not('.blank')) {
      if (!doubleParent.parent().hasClass('showMenu')) {
        doubleParent.parent().addClass('showMenu');
      }
    }

    if (hashsplit[1].startsWith('prx')) {
      var prxsplit = hashsplit[1].split('prx');
      window.parent.document.getElementById('mainFrame').src = prxsplit[1];
    } else {
      window.parent.document.getElementById('mainFrame').src = '/pages/'+hashsplit[1]+".php";
    }
  }
}

function loadMainWindow(element) {
  $('#mainWindow').html('');
  clearAllApexCharts();
  if (element != null) {
    var hashsplit = element.split('#page=');
    var linkElem = $('a[href="#page='+hashsplit[1]+'"]');
    $('.toggleFrame').removeClass('active');
    linkElem.addClass('active');
    // Remove proxy support for now
    // if (hashsplit[1].startsWith('prx')) {
    //   var prxsplit = hashsplit[1].split('prx');
    //   window.parent.document.getElementById('mainFrame').src = prxsplit[1];
    // } else {
    //   window.parent.document.getElementById('mainFrame').src = '/pages/'+hashsplit[1]+".php";
    // }
    queryAPI('GET','/api/page/'+hashsplit[1]).done(function(data) {
      $('#mainWindow').html('');
      $('#mainWindow').html(data);
      $('.dark-theme .table-striped').addClass('table-dark');
    });
  } else if (window.parent.location.hash) {
    var hashsplit = window.parent.location.hash.split('#page=');
    // Auto-expand and set navbar to active
    var linkElem = $('a[href="'+window.parent.location.hash+'"]');
    linkElem.addClass('active');
    $('.title-text').text(linkElem.data('pageName'));
    var doubleParent = $('.icon-link > .toggleFrame.active, .sub-sub-menu .toggleFrame.active, .icon-link > .toggleFrame.active, .sub-menu .toggleFrame.active').parent().parent();
    if (doubleParent.hasClass('sub-sub-menu')) {
      if (!doubleParent.parent().hasClass('showMenu')) {
        doubleParent.parent().addClass('showMenu');
      }
      if (!doubleParent.parent().parent().parent().hasClass('showMenu')) {
          doubleParent.parent().parent().parent().addClass('showMenu');
      }
    } else if (doubleParent.hasClass('sub-menu') && doubleParent.not('.blank')) {
      if (!doubleParent.parent().hasClass('showMenu')) {
        doubleParent.parent().addClass('showMenu');
      }
    }
    queryAPI('GET','/api/page/'+hashsplit[1]).done(function(data) {
      $('#mainWindow').html(data);
      $('.dark-theme .table-striped').addClass('table-dark');
    });
  } else {
    queryAPI('GET','/api/page/core/default').done(function(data) {
      $('#mainWindow').html(data);
      $('.dark-theme .table-striped').addClass('table-dark');
    });
  }
}

function toast(title,note,body,theme,delay = "8000") {
  $('#toastContainer').append(`
      <div class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="`+delay+`">
        <div class="toast-header">
          <img class="bg-`+theme+` p-2 rounded-2">&nbsp;
          <strong class="me-auto">`+title+`</strong>
          <small class="text-muted">`+note+`</small>
          <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
          `+body+`
        </div>
      </div>
  `);
  $('.toast').toast('show').on('hidden.bs.toast', function (elem) {
    $(elem.target).remove();
  })
};

function applyFontSize() {
  var cookie = getCookie('fontSize');
  if (cookie) {
    $('html').css('font-size',cookie);
  }
}

applyFontSize();

let seconds = 0;

function startTimer() {
    let timer;
    seconds = 0;
    timer = setInterval(() => {
        seconds++;
        if (seconds > 60) {
          const minutes = Math.floor(seconds / 60);
          const remainingSeconds = seconds % 60;
          $('#elapsed').text(`Elapsed: ${minutes}m ${remainingSeconds}s`);
        } else {
          $('#elapsed').text(`Elapsed: ${seconds}s`);
        }
    }, 1000);
    return timer;
}

function stopTimer(timer) {
    clearInterval(timer);
    console.log(`Timer stopped at ${seconds} seconds`);
}

function dateFormatter(value) {
  const date = new Date(value);
  return date.toLocaleDateString('en-US'); // Format as MM/DD/YYYY
}

function datetimeFormatter(value) {
  const date = new Date(value);
  return date.toLocaleString('en-GB', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
    hour12: true // Format as MM/DD/YYYY
  });
}

// ** Define ApexCharts Options ** //
// Chart Theme
if (getCookie('theme')) {
  var theme = getCookie('theme');
} else {
  var theme = 'light';
}

// Colour Palettes
var barChartColorPalette = ['#FDDD00','#E1DD1A','#C5DE33','#A9DE4D','#8DDF66','#70DF80','#54E099','#38E0B3','#1CE1CC','#00E1E6'];
var pieChartColorPalette = ['#0fbe4d','#94ce36','#00F9FF','#00d69b','#00F9FF'];

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
      height: '350px'
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
  tooltip: {
    theme: theme
  },
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

function resetChart(chart,options) {
  var querySelector = chart.ctx.el.id;
  chart.destroy();
  chart = new ApexCharts(document.querySelector("#"+querySelector), options);
  chart.render();
  return chart;
}

// ** End Define ApexCharts Options ** //

// ** Tracking ** //
// Configuration object
const trackingConfig = {
  mouseMovement: true,
  clicks: true,
  timeOnPage: true,
  currentPage: true,
  browserInfo: true,
  processData: function(data) {
    $.ajax({
      url: '/api/t',
      type: 'POST',
      data: JSON.stringify(data),
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      error: function(xhr, status, error) {
        console.error("Error Status: " + status);
        console.error("Error Thrown: " + error);
        console.error("Response Text: " + xhr.responseText);
        toast("API Error","","Unable to submit tracking data.","danger","3000");
      }
    })
  }
};

// Function to get browser and OS information
function getBrowserInfo() {
  const userAgent = navigator.userAgent;
  let browserName = "Unknown Browser";
  let osName = "Unknown OS";

  if (userAgent.indexOf("Firefox") > -1) {
      browserName = "Mozilla Firefox";
  } else if (userAgent.indexOf("SamsungBrowser") > -1) {
      browserName = "Samsung Internet";
  } else if (userAgent.indexOf("Opera") > -1 || userAgent.indexOf("OPR") > -1) {
      browserName = "Opera";
  } else if (userAgent.indexOf("Trident") > -1) {
      browserName = "Microsoft Internet Explorer";
  } else if (userAgent.indexOf("Edge") > -1) {
      browserName = "Microsoft Edge";
  } else if (userAgent.indexOf("Chrome") > -1) {
      browserName = "Google Chrome";
  } else if (userAgent.indexOf("Safari") > -1) {
      browserName = "Apple Safari";
  }

  if (userAgent.indexOf("Win") > -1) {
      osName = "Windows";
  } else if (userAgent.indexOf("Mac") > -1) {
      osName = "MacOS";
  } else if (userAgent.indexOf("X11") > -1) {
      osName = "UNIX";
  } else if (userAgent.indexOf("Linux") > -1) {
      osName = "Linux";
  }

  return { browserName, osName };
}

// Function to split URL into components
function splitUrl(url) {
  const urlObj = new URL(url);
  return {
      protocol: urlObj.protocol.replace(':',''),
      host: urlObj.host,
      pathname: urlObj.pathname,
      hash: urlObj.hash
  };
}

// Function to split pathname into Page Category and Page Name
function splitPathname(pathname) {
  if (pathname === '/') {
    return {
      pageCategory: 'home',
      pageName: 'home'
    };
  }

  const loginMatch = pathname.match(/^\/([^\/]+)$/);
  if (loginMatch) {
    return {
      pageCategory: 'home',
      pageName: loginMatch[1]
    };
  }

  const singlePageMatch = pathname.match(/^\/pages\/([^\/]+)$/);
  if (singlePageMatch) {
    return {
      pageCategory: 'home',
      pageName: singlePageMatch[1]
    };
  }

  const doublePageMatch = pathname.match(/^\/pages\/([^\/]+)\/([^\/]+)$/);
  if (doublePageMatch) {
    return {
      pageCategory: doublePageMatch[1],
      pageName: doublePageMatch[2]
    };
  }

  return null;
}

// Function to get a cookie by name
function getCookie(name) {
  const nameEQ = name + "=";
  const ca = document.cookie.split(';');
  for (let i = 0; i < ca.length; i++) {
      let c = ca[i];
      while (c.charAt(0) === ' ') c = c.substring(1, c.length);
      if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
  }
  return null;
}

// Function to generate a unique ID
function generateUniqueId() {
  return 'xxxx-xxxx-xxxx-yxxx-xxxx-xxxx-xxxx-xxxx'.replace(/[xy]/g, function(c) {
      const r = Math.random() * 16 | 0, v = c === 'x' ? r : (r & 0x3 | 0x8);
      return v.toString(16);
  });
}

// Check if user ID cookie exists, if not, create one
let tId = getCookie('tId');
if (!tId) {
  tId = generateUniqueId();
  setCookie('tId', tId, 365); // Cookie expires in 1 year
}

// Function to send data using sendBeacon
function sendTrackingData(data) {
  const url = '/api/t';
  const payload = JSON.stringify(data);
  const blob = new Blob([payload], { type: 'application/json; charset=utf-8' });
  navigator.sendBeacon(url, blob);
}

// Tracking object
const userTracking = {
  data: {
    mouseMovements: [],
    clicks: [],
    startTime: Date.now(),
    endTime: null,
    currentPage: window.location.href,
    browserInfo: getBrowserInfo(),
    urlComponents: splitUrl(window.location.href),
    pageDetails: splitPathname(window.location.pathname),
    tId: tId
  },
  init: function(config) {
    if (config.mouseMovement) {
      document.addEventListener('mousemove', this.trackMouseMovement.bind(this));
    }
    if (config.clicks) {
      document.addEventListener('click', this.trackClick.bind(this));
    }
    if (config.timeOnPage) {
      window.addEventListener('beforeunload', this.trackTimeOnPage.bind(this));
    }
  },
  trackMouseMovement: function(event) {
    this.data.mouseMovements.push({ x: event.clientX, y: event.clientY, time: Date.now() });
  },
  trackClick: function(event) {
    this.data.clicks.push({ x: event.clientX, y: event.clientY, time: Date.now(), element: event.target.tagName });
  },
  trackTimeOnPage: function() {
    this.data.endTime = Date.now();
    this.processData();
  },
  processData: function() {
    const timeSpent = this.data.endTime - this.data.startTime;
    const result = {
      mouseMovements: this.data.mouseMovements,
      clicks: this.data.clicks,
      timeSpent: timeSpent,
      currentPage: this.data.currentPage,
      browserInfo: this.data.browserInfo,
      urlComponents: this.data.urlComponents,
      pageDetails: this.data.pageDetails,
      tId: this.data.tId
    };
    sendTrackingData(result);
  }
};

// Destroy any existing charts
function clearAllApexCharts() {
  for (let chart in window.charts) {
    if (window.charts[chart] && typeof window.charts[chart].destroy === "function") {
        window.charts[chart].destroy();
    }
  }
}

document.addEventListener('DOMContentLoaded', function() {
  window.charts = [];

  jQuery(function ($) {
    $(".sidebar-dropdown > a").click(function() {
      $(".sidebar-submenu").slideUp(200);
      if (
        $(this)
          .parent()
          .hasClass("active")
      ) {
        $(".sidebar-dropdown").removeClass("active");
        $(this)
          .parent()
          .removeClass("active");
      } else {
        $(".sidebar-dropdown").removeClass("active");
        $(this)
          .next(".sidebar-submenu")
          .slideDown(200);
        $(this)
          .parent()
          .addClass("active");
      }
    });
  
    $(".sidebar-subdropdown > a").click(function() {
      $(".sidebar-subsubmenu").slideUp(200);
      if (
        $(this)
          .parent()
          .hasClass("active")
      ) {
        $(".sidebar-subdropdown").removeClass("active");
        $(this)
          .parent()
          .removeClass("active");
      } else {
        $(".sidebar-subdropdown").removeClass("active");
        $(this)
          .next(".sidebar-subsubmenu")
          .slideDown(200);
        $(this)
          .parent()
          .addClass("active");
      }
    });
  });
  
  $('.preventDefault').click(function(event){
    event.preventDefault();
  });
  
  $('.dark-theme .table-striped').addClass('table-dark');

  // Initialize tracking
  userTracking.init(trackingConfig);

  console.info("%c Web App %c ".concat("DOM Fully loaded", " "), "color: white; background: #AD80FD; font-weight: 700;", "color: #AD80FD; background: white; font-weight: 700;");
});
