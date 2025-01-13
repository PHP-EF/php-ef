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

function testAPI(type,path){
  toast("Info","","Starting..","info","5000");
  queryAPI(type,path).done(function(data) {
    if (data["result"] == "Success") {
      toast(data["result"],"",data["message"],"success","10000");
    } else {
      toast("Error", "", data["message"], "danger","30000");
    }
  }).fail(function() {
      toast("Error", "", "API Error", "danger","30000");
  });
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

function newPopup(url, title, w, h) {
  var dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : window.screenX;
  var dualScreenTop = window.screenTop != undefined ? window.screenTop : window.screenY;
  var width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
  var height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;
  var left = ((width / 2) - (w / 2)) + dualScreenLeft;
  var top = ((height / 2) - (h / 2)) + dualScreenTop;
  var newWindow = window.open(url, title, 'scrollbars=yes, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);
  if (window.focus) {
      newWindow.focus();
  }
  return newWindow;
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

function loadContent(element = null) {
  $('.toggleFrame').removeClass('active');
  var expandNav = false;
  var type = 'page';
  if (element != null) {
    element = $(element.currentTarget);
    element.addClass('active');
  } else {
    var hashsplit = window.parent.location.hash.split('#');
    if (hashsplit[1]) {
      var qualifierSplit = hashsplit[1].split('=');
      type = qualifierSplit[0];
      var name = qualifierSplit[1];
      switch (qualifierSplit[0]) {
        case 'dashboard':
          loadDashboardPane(name)
          return;
        case 'page':
          element = $('a[href="#page='+decodeURI(name)+'"]');
          element.addClass('active');
          $('.title-text').text(element.data('pageName'));
          expandNav = true;
          break;
      }
    } else {
      loadMainWindow();
      return;
    }
  }

  switch (element.data('pageType')) {
    case 'Native':
      loadMainWindow(element,type);
      break;
    case 'iFrame':
      loadiFrame(element);
      break;
  }

  if (expandNav) {
    var doubleParent = element.parent().parent();
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
  }
}

function loadiFrame(element) {
  if (element != null) {
    $('#mainWindow').html('').attr('hidden',true);
    $('#mainFrame').attr('hidden',false);
    var pageUrl = element.data('pageUrl');
    window.parent.document.getElementById('mainFrame').src = pageUrl;
  } else {
    toast("Error","","Unable to load the requested iFrame.","danger");
  }
}

function loadMainWindow(element,type = "page") {
  $('#mainFrame').attr('src', '').attr('hidden',true);
  $('#mainWindow').attr('hidden',false).html('');
  clearAllApexCharts();
  var endpoint = null;
  var pageUrl = '';
  switch(type) {
    case 'page':
      endpoint = '/api/page/';
      break;
    case 'dashboard':
      endpoint = '/api/dashboards/page/';
      break;
  }
  if (endpoint != null) {
    if (type == "dashboard") {
      pageUrl = element;
    } else if (element != null) {
      pageUrl = element.data('pageUrl');
    } else {
      pageUrl = 'core/default';
    }
    queryAPI('GET',endpoint+pageUrl).done(function(data) {
      $('#mainWindow').html('');
      $('#mainWindow').html(data);
    }).fail(function(jqXHR, textStatus, errorThrown) {
        toast(textStatus,"","Unable to load the requested page.<br>"+jqXHR.status+": "+errorThrown,"danger");
    });
  } else {
    toast("Error","","Unable to load the requested page.<br>Invalid Type","danger");
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

// Function to split hash into Page Category and Page Name
function splitHash(hash) {
  const pagesMap = hash.match(/^#page=(plugin\/)?([^\/]+)\/([^\/]+)$/);
  if (pagesMap) {
    return {
      pageCategory: pagesMap[2],
      pageName: pagesMap[3]
    };
  }

  const homeMatch = window.location.pathname.match(/^\/([^\/]+)$/);
  if (homeMatch) {
    return {
      pageCategory: 'home',
      pageName: homeMatch[1]
    };
  }

  if (hash === '') {
    return {
      pageCategory: 'home',
      pageName: 'home'
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
  queryAPI("POST","/api/t",data).fail(function() {
    logConsole('Error','Unable to submit tracking data','error');
  });
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
    pageDetails: splitHash(window.location.hash),
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
    window.addEventListener('hashchange', this.trackNavigation.bind(this));
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
  trackNavigation: function() {
    this.trackTimeOnPage();
    this.data.currentPage = window.location.href;
    this.data.urlComponents = splitUrl(window.location.href);
    this.data.pageDetails = splitHash(window.location.hash);
    this.data.startTime = Date.now();
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

function isMobile() {
  return /Mobi|Android/i.test(navigator.userAgent);
}

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

  $("#mainWindow").on("click", ".addInputEntry", function(elem) {
    var input = $(elem.target).parent().prev();
    if (input.val()) {
        $(".inputEntries").append(`<li class="list-group-item inputEntry">` + input.val() + `<i class="fa fa-trash removeInputEntry"></i></li>`);
        input.val("");
    }
  });
  
  $("#mainWindow").on("click", ".removeInputEntry", function(elem) {
    $(elem.target).parent().parent().prev().children(':first').addClass('changed');
    $(elem.target).parent().remove();
  });

  // Set Sidebar State for Mobile
    if (isMobile()) {
      document.querySelector(".sidebar").classList.add("close");
    }

  // Initialize tracking
  userTracking.init(trackingConfig);

  console.info("%c Web App %c ".concat("DOM Fully loaded", " "), "color: white; background: #AD80FD; font-weight: 700;", "color: #AD80FD; background: white; font-weight: 700;");
});

function initPasswordToggle() {
  $('.passwordToggle').on('click',function(elem) {
    let el = $(elem.target).parent().parent().prev();
    if (el.attr('type') == "password") {
      el.attr('type','text');
    } else {
      el.attr('type','password');
    }
  })
}

function createRandomString(length) {
  const letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
  const characters = letters+'0123456789';
  let result = letters.charAt(Math.floor(Math.random() * letters.length)); // Ensure the first character is a letter
  const charactersLength = characters.length;
  for (let i = 1; i < length; i++) {
      result += characters.charAt(Math.floor(Math.random() * charactersLength));
  }
  return result;
}

function cleanClass(string){
	return string.replace(/ +/g, "-").replace(/\W+/g, "-");
}

function selectOptions(options, active){
	var selectOptions = '';
	$.each(options, function(i,v) {
		activeTest = active.split(',');
		if(activeTest.length > 1){
			var selected = (arrayContains(v.value, activeTest)) ? 'selected' : '';
		}else{
			var selected = (active.toString() == v.value) ? 'selected' : '';
		}
		var disabled = (v.disabled) ? ' disabled' : '';
		selectOptions += '<option '+selected+disabled+' value="'+v.value+'">'+v.name+'</option>';
	});
	return selectOptions;
}

function multipleInputArr(item) {
  var valueArr = item.values;
  var multipleInputArr = '';
  var disabled = (item.disabled) ? ' disabled' : '';
  $.each(valueArr, function(index, value) {
    multipleInputArr += '<li class="list-group-item inputEntry"' + disabled + '>' + value + '<i class="fa fa-trash removeInputEntry"></i></li>';
  });
  return multipleInputArr;
}

function getInputMultipleEntries(elem) {
  const entryList = elem.parent().next();
  const listItems = entryList.find("li");
  const values = [];

  for (let i = 0; i < listItems.length; i++) {
    const listItem = listItems[i];
    const value = $(listItem).text();
    values.push(value);
  }
  return values;
}

function buildFormGroup(array) {
  var mainCount = 0;
  var group = '<div id="tabsJustifiedContent" class="tab-content">';
  var uList = '<ul id="tabsJustified" class="nav flex-column nav-tabs info-nav">'; // Changed to flex-column for vertical alignment
  
  $.each(array, function(i, v) {
    mainCount++;
    var count = 0;
    var total = v.length;
    var active = (mainCount == 1) ? 'active' : '';
    var customID = createRandomString(10);
    
    if (i == 'custom') {
      group += v;
    } else {
      uList += `<li role="presentation" class="nav-item"><a href="" data-bs-target="#${customID}${cleanClass(i)}" data-bs-toggle="tab" class="nav-link small text-uppercase ${active}"><span lang="en">${i}</span></a></li>`;
      group += `
        <!-- FORM GROUP -->
        <div class="tab-pane ${active}" id="${customID}${cleanClass(i)}">
      `;
      
      var sectionCount = 0;
      $.each(v, function(j, item) {
        var override = item.override || '6';
        sectionCount++;
        count++;
        
        if (count % 2 !== 0) {
          group += '<div class="row start">';
        }
        
        var helpID = `#help-info-${item.name}`;
        var helpTip = item.help ? `<sup><a class="help-tip" data-toggle="collapse" href="${helpID}" aria-expanded="true"><i class="m-l-5 fa fa-question-circle text-info" title="Help" data-toggle="tooltip"></i></a></sup>` : '';
        var builtItems = '';
        
        if (item.type == 'title' || item.type == 'hr' || item.type == 'js') {
          builtItems = `${buildFormItem(item)}`;
          count = 0; // Reset count
          group += '</div><!--end--><div class="row start">'; // Close current row and start a new one
        } else {
          builtItems = `
            <div class="col-md-${override} p-b-10">
              <div class="form-group">
                <label class="control-label col-md-12"><span lang="en">${item.label}</span>${helpTip}</label>
                <div class="col-md-12">
                  ${buildFormItem(item)}
                </div>
              </div>
            </div>
          `;
        }
        
        group += builtItems;
        
        if (count % 2 === 0 || sectionCount === total) {
          group += '</div><!--end-->';
        }
      });
      
      group += '</div>';
    }
  });
  
  return '<div class="d-flex">' + uList + '</ul>' + group + '</div>'; // Wrapped in a flex container for alignment
}

function buildFormItem(item){
  var placeholder = (item.placeholder) ? ' placeholder="'+item.placeholder+'"' : '';
  var id = (item.id) ? ' id="'+item.id+'"' : '';
  var type = (item.type) ? ' data-type="'+item.type+'"' : '';
  var label = (item.label) ? ' data-label="'+item.label+'"' : '';
  var value = (item.value) ? ' value="'+item.value+'"' : '';
  var textarea = (item.value) ? item.value : '';
  var name = (item.name) ? ' name="'+item.name+'"' : '';
  var extraClass = (item.class) ? ' '+item.class : '';
  var icon = (item.icon) ? ' '+item.icon : '';
  var text = (item.text) ? ' '+item.text : '';
  var attr = (item.attr) ? ' '+item.attr : '';
  var disabled = (item.disabled) ? ' disabled' : '';
  var href = (item.href) ? ' href="'+item.href+'"' : '';
  var pwd1 = createRandomString(6);
  var pwd2 = createRandomString(6);
  var pwd3 = createRandomString(6);
  var helpInfo = (item.help) ? '<div class="collapse" id="help-info-'+item.name+'"><blockquote lang="en">'+item.help+'</blockquote></div>' : '';
  var smallLabel = (item.smallLabel) ? '<label><span lang="en">'+item.smallLabel+'</span></label>'+helpInfo : ''+helpInfo;

  //+tof(item.value,'c')+`
  switch (item.type) {
    case 'select-input':
      return smallLabel + '<input list="'+item.name+'Options" lang="en" type="text" class="form-control info-field' + extraClass + '"' + placeholder + value + id + name + disabled + type + label + attr + '/><datalist id="'+item.name+'Options">' + selectOptions(item.options, item.value) + '</datalist>';
    case 'input':
    case 'text':
      return smallLabel+'<input lang="en" type="text" class="form-control info-field'+extraClass+'"'+placeholder+value+id+name+disabled+type+label+attr+' />';
    case 'inputmultiple':
      return '<div class="input-group mb-3"><input lang="en" type="text" class="form-control info-field'+extraClass+'" multiple '+placeholder+id+name+disabled+type+label+attr+'/><div class="input-group-append"><button class="btn btn-outline-success addInputEntry" type="button">'+text+'</button></div></div><ul class="list-group mt-3 inputEntries">'+multipleInputArr(item)+'</ul>';
    case 'number':
      return smallLabel+'<input lang="en" type="number" class="form-control info-field'+extraClass+'"'+placeholder+value+id+name+disabled+type+label+attr+'/>';
    case 'textbox':
      return smallLabel+'<textarea class="form-control info-field'+extraClass+'"'+placeholder+id+name+disabled+type+label+attr+' autocomplete="new-password">'+textarea+'</textarea>';
    case 'password':
      return smallLabel+'<input lang="en" type="password" class="form-control info-field'+extraClass+'"'+placeholder+value+id+name+disabled+type+label+attr+'/>';
    case 'password-alt':
      return smallLabel+'<div class="input-group"><input lang="en" type="password" class="password-alt form-control info-field'+extraClass+'"'+placeholder+value+id+name+disabled+type+label+attr+'/><span class="input-group-btn"> <button class="btn btn-default showPassword" type="button"><i class="fa fa-eye passwordToggle"></i></button></span></div>';
    case 'password-alt-copy':
      return smallLabel+'<div class="input-group"><input lang="en" type="password" class="password-alt form-control info-field'+extraClass+'"'+placeholder+value+id+name+disabled+type+label+attr+'/><span class="input-group-btn"> <button class="btn btn-primary clipboard" type="button" data-clipboard-text="'+item.value+'"><i class="fa icon-docs"></i></button></span><span class="input-group-btn"> <button class="btn btn-inverse showPassword" type="button"><i class="fa fa-eye passwordToggle"></i></button></span></div>';
    case 'hidden':
      return '<input lang="en" type="hidden" class="form-control info-field'+extraClass+'"'+placeholder+value+id+name+disabled+type+label+attr+'/>';
    case 'select':
      return smallLabel+'<select class="form-control info-field'+extraClass+'"'+placeholder+value+id+name+disabled+type+label+attr+'>'+selectOptions(item.options, item.value)+'</select>';
    case 'selectmultiple':
      return smallLabel+'<select class="form-control info-field'+extraClass+'" multiple '+placeholder+value+id+name+disabled+type+label+attr+'>'+selectOptions(item.options, item.value)+'</select>';
    case 'switch':
    case 'checkbox':
      return smallLabel+'<div class="form-check form-switch"><input class="form-check-input info-field'+extraClass+'" type="checkbox"'+name+value+id+disabled+type+label+attr+'/></div>';
    case 'button':
      return smallLabel+'<button class="btn btn-sm btn-success btn-rounded waves-effect waves-light b-none'+extraClass+'" '+href+attr+' type="button"><span class="btn-label"><i class="'+icon+'"></i></span><span lang="en">'+text+'</span></button>';
    case 'blank':
      return '';
    case 'accordion':
      return '<div class="panel-group'+extraClass+'"'+placeholder+value+id+name+disabled+type+label+attr+'  aria-multiselectable="true" role="tablist">'+accordionOptions(item.options, item.id)+'</div>';
    case 'title':
      return '<h4>'+text+'</h4>';
    case 'hr':
      return '<hr class="mt-3">';
    case 'html':
      return item.html;
    case 'js':
      if (item.src) {
        if (!document.querySelector(`script[src="${item.src}"]`)) {
          var script = document.createElement('script');
          script.src = item.src;
          document.head.appendChild(script);
        }
      } else if (item.script) {
        var script = document.createElement('script');
        script.innerHTML = item.script;
        document.head.appendChild(script);
      }
      return ''; // Return an empty string as the script is already added
    default:
      return '<span class="text-danger">BuildFormItem Class not setup...';
  }
}

// Modal Backdrop Z-Index Fix
$(document).on('shown.bs.modal', '.modal', function () {
  $('.modal-backdrop').before($(this));
});