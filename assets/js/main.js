// Classes
class Ping {
  constructor() {
      this._version = "0.0.2";
  }

  ping(url, callback, timeout = 0) {
      const img = new Image();
      const start = new Date();
      let timer;

      const onPingComplete = () => {
          if (timer) clearTimeout(timer);
          const duration = new Date() - start;
          if (typeof callback === "function") callback(duration);
      };

      img.onload = img.onerror = onPingComplete;
      if (timeout) timer = setTimeout(onPingComplete, timeout);
      img.src = `${url}/?${+new Date()}`;
  }
}

function initLazyLoad() {
  $('.lazyload').Lazy();
}

function pad(n, width, z) {
	z = z || '0';
	n = n + '';
	return n.length >= width ? n : new Array(width - n.length + 1).join(z) + n;
}

function convertArrayToCSV(value) {
  if (Array.isArray(value)) {
      return value.join(',');
  }
  return value;
}

function generateAPIKey(elemName) {
  generateSecureToken().then(function(token) {
      $(`[name='${elemName}']`).val(token).change();
  });
}

function createTableHtml(index, prefix) {
  return `<table class="table table-striped" id="`+prefix+`-table-` + index +`"></table>`;
}

// Core Functions & Logging
$.xhrPool = [];
function queryAPI(type,path,data=null,contentType="application/json",asyncValue=true){
	if (contentType == 'application/json' && data != null) {
    data = JSON.stringify(data);
  }
  let timeout = 60000;
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
  }).fail(function(jqXHR, textStatus, errorThrown) {
    toast(textStatus,"","Error: "+jqXHR.status+": "+errorThrown,"danger");
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

function escapeHTML(str) {
  return str
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

function arrayContains(needle, arrhaystack){
  return (arrhaystack.indexOf(needle) > -1);
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

function encryptData(key, value) {
  return $.post("/api/auth/crypt", { key: value });
}

function getNestedProperty(obj, path) {
  return path.split(".").reduce((acc, part) => acc && acc[part], obj);
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

function loadContent(element = null, defaultPage = null) {
  $('.mainWindow, .mainFrame').attr('hidden',true);
  // $('.dynamic-plugin-js').remove();
  $('.toggleFrame').removeClass('active');
  var invalid = false;
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
        case 'page':
          elements = $('a.toggleFrame[href="#page=' + decodeURI(name) + '"]');
          element = $('a.toggleFrame[href="#page=' + decodeURI(name) + '"]:not(.link_name)');
          elements.addClass('active');
          $('.title-text').text(element.data('pageName'));
          expandNav = true;
          break;
        default:
          console.info("%c Navigation %c ".concat("Failed to load content. Invalid selector: "+qualifierSplit[0], " "), "color: white; background:rgb(249, 0, 0); font-weight: 700;", "color: rgb(249, 0, 0); background: white; font-weight: 700;");
          invalid = true;
          break;
      }
    } else {
      element = $('a.toggleFrame[href="#page=' + decodeURI(defaultPage) + '"]:not(.link_name)');
      element.addClass('active');
      expandNav = true;
    }
  }
  if (!invalid) {
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
}

function loadiFrame(element) {
  if (element != null) {
    var frameId = element.data('frameId');
    var pageName = element.find('span').text() != '' ? element.find('span').text() : element.text();
    if (frameId) {
      console.info("%c Navigation %c ".concat("Switching iFrame Tab: "+pageName, " "), "color: white; background:rgb(203, 38, 249); font-weight: 700;", "color: rgb(203, 38, 249); background: white; font-weight: 700;");
      $(`#${frameId}`).attr('hidden',false);
      return;
    } else {
      console.info("%c Navigation %c ".concat("Loading New iFrame Tab: "+pageName, " "), "color: white; background:rgb(203, 38, 249); font-weight: 700;", "color: rgb(203, 38, 249); background: white; font-weight: 700;");
      var frameId = createRandomString(12);
      var pageUrl = element.data('pageUrl');
      element.data('frameId',frameId);
      $(".main-container").append(`<iframe id="${frameId}" class="mainFrame"></iframe>`);
      $(".main-container").find(`#${frameId}`).attr('src', pageUrl);
    }
  } else {
    toast("Error","","Unable to load the requested iFrame.","danger");
  }
}

function loadMainWindow(element,type = "page") {
  // clearAllApexCharts();
  var endpoint = null;
  var pageUrl = '';
  switch(type) {
    case 'page':
      endpoint = '/api/page/';
      break;
  }

  if (endpoint != null) {
    if (element != null) {
      var pageName = element.find('span').text() != '' ? element.find('span').text() : element.text();
      var frameId = element.data('frameId');
      if (frameId) {
        console.info("%c Navigation %c ".concat("Switching Native Tab: "+pageName, " "), "color: white; background: #2dd375; font-weight: 700;", "color: #2dd375; background: white; font-weight: 700;");
        $(`#${frameId}`).attr('hidden',false);
        return;
      } else {
        console.info("%c Navigation %c ".concat("Loading New Native Tab: "+pageName, " "), "color: white; background: #2dd375; font-weight: 700;", "color: #2dd375; background: white; font-weight: 700;");
        var frameId = createRandomString(12);
        element.data('frameId',frameId);
      }
      pageUrl = element.data('pageUrl');
    } else {
      pageUrl = 'core/default';
    }
    $(".main-container").append(`<div id="${frameId}" class="mainWindow"></div>`);
    queryAPI('GET',endpoint+pageUrl).done(function(data) {
      $(`#${frameId}`).html('');
      $(`#${frameId}`).html(data);
    }).fail(function(jqXHR, textStatus, errorThrown) {
        toast(textStatus,"","Unable to load the requested page.<br>"+jqXHR.status+": "+errorThrown,"danger");
    });
  } else {
    toast("Error","","Unable to load the requested page.<br>Invalid Type","danger");
  }
}


function toast(title, note, body, theme = "primary", delay = 8000) {
  const formattedBody = (body != null) ? body.replace(/\n/g, "<br>") : "";
  const toastHTML = `
    <div class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="${delay}">
      <div class="toast-header">
        <span class="bg-${theme} p-2 rounded-2 me-2 d-inline-block" style="width: 20px; height: 20px;"></span>
        <strong class="me-auto">${title}</strong>
        <small class="text-muted">${note}</small>
        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
      <div class="toast-body">
        ${formattedBody}
      </div>
    </div>
  `;
  const $toast = $(toastHTML).appendTo('#toastContainer');
  $toast.toast('show').on('hidden.bs.toast', function () {
    $(this).remove();
  });
};

function applyFontSize() {
  var cookie = getCookie('fontSize');
  if (cookie) {
    $('html').css('font-size',cookie);
  } else {
    if (isMobile) {
      $('html').css('font-size','14px');
    }
  }
}

applyFontSize();

let seconds = 0;

function startTimer(elem) {
    let timer;
    seconds = 0;
    timer = setInterval(() => {
        seconds++;
        if (seconds > 60) {
          const minutes = Math.floor(seconds / 60);
          const remainingSeconds = seconds % 60;
          $(elem).text(`Elapsed: ${minutes}m ${remainingSeconds}s`);
        } else {
          $(elem).text(`Elapsed: ${seconds}s`);
        }
    }, 1000);
    return timer;
}

function stopTimer(timer) {
    clearInterval(timer);
    console.log(`Timer stopped at ${seconds} seconds`);
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
  const pagesMap = hash.match(/^#page=([^\/]+)$/);
  if (pagesMap) {
    return {
      pageCategory: '',
      pageName: decodeURI(pagesMap[1])
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

function generateSecureToken() {
  return queryAPI('POST', '/api/auth/crypt', { key: generateUniqueId() }, 'application/json', false).then(function(data) {
    return data.data.split(':')[2];
  }).catch(function(error) {
    console.error('Error generating secure token:', error);
    throw error;
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

function cleanString(string){
	return string.replace(/ +/g, "-").replace(/\W+/g, "-");
}

function appendScript(src) {
  return new Promise((resolve, reject) => {
    if (!document.querySelector(`script[src="${src}"]`)) {
      var script = document.createElement('script');
      script.src = src;
      script.onload = () => resolve(true);
      script.onerror = () => reject(new Error('Script load error'));
      document.head.appendChild(script);
    } else {
      resolve(true);
    }
  });
}

function appendInlineScript(scriptContent, id) {
  return new Promise((resolve, reject) => {
    if (!document.querySelector(`script[data-script-id="${id}"]`)) {
      var script = document.createElement('script');
      script.classList = "dynamic-plugin-js";
      script.innerHTML = scriptContent;
      script.setAttribute('data-script-id', id);
      document.head.appendChild(script);
      resolve(true);
    } else {
      resolve(true);
    }
  });
}

async function loadScript(src) {
  try {
    await appendScript(src);
    console.log('Script loaded successfully');
  } catch (error) {
    console.error('Failed to load script:', error);
  }
}

async function loadInlineScript(scriptContent, id) {
  try {
    await appendInlineScript(scriptContent, id);
    console.log('Inline script loaded successfully');
  } catch (error) {
    console.error('Failed to load inline script:', error);
  }
}

function humanFileSize(bytes, si) {
  var thresh = si ? 1000 : 1024;
  if(Math.abs(bytes) < thresh) {
      return bytes + ' B';
  }
  var units = si
      ? ['kB','MB','GB','TB','PB','EB','ZB','YB']
      : ['KiB','MiB','GiB','TiB','PiB','EiB','ZiB','YiB'];
  var u = -1;
  do {
      bytes /= thresh;
      ++u;
  } while(Math.abs(bytes) >= thresh && u < units.length - 1);
  return bytes.toFixed(1)+' '+units[u];
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
  
  $("body").on("click",".preventDefault", function(event) {
    event.preventDefault();
  });
  
  $("#page-content").on("click", ".addInputEntry", function(elem) {
    var input = $(elem.target).parent().prev();
    if (input.val()) {
        $(".inputEntries").append(`<li class="list-group-item inputEntry">` + input.val() + `<i class="fa fa-trash removeInputEntry"></i></li>`);
        input.val("");
    }
  });
  
  $("#page-content").on("click", ".removeInputEntry", function(elem) {
    $(elem.target).parent().parent().prev().children(':first').addClass('changed');
    $(elem.target).parent().remove();
  });

  // Listener to add changed class to main settings elements
  $("#page-content").on('change', '.info-field, .dynamic-select-input', function(event) {
      const elementName = $(this).data('label');
      const controlLabel = $($(this).parents().eq(2).prev('.control-label')[0]).text();
      const label = elementName || controlLabel;
      if (!changedSettingsElements.has(label)) {
          toast("Configuration", "", label + " has changed.<br><small>Save configuration to apply changes.</small>", "warning");
          changedSettingsElements.add(label);
      }
      $(this).addClass("changed");
  });

  $("body").on('input', '#SettingsModal_Role [name=roleName]', function(event) {
    $('[name=roleSlug]').val(cleanString($(this).val())).change();
  });

  // Custom jQuery to handle nested tabs for mobile
  $("body").on('click', '.tab-sub-dropdown .nav-link', function (event) {
    event.preventDefault();
    $(this).parent().find('.nav-link').removeClass('active');
    $(this).parent().parent().parent().find('li .nav-link').removeClass('show active');
    $(this).addClass('active');
    $("#configSubTabsDropdown").text($($(this).children('span')[0]).text())
    $($(this).attr('href')).addClass('show active');
  });

  // ** COMMON ** //
  $("body").on('click', '.read-more', function(e) {
    e.preventDefault();
    var fullContent = $(this).data('full-content');
    $(this).closest('td').html(fullContent);
  });

  // Read More Popup
  $("body").on('click', '.read-more-popup', function(e) {
    e.preventDefault();
    var fullContent = $(this).data('full-content');
    var title = $(this).data('title');
    var modal = `
      <div class="modal fade" id="readMoreModal" tabindex="-1" aria-labelledby="readMoreModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="readMoreModalLabel">${title}</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="read-more-content">${fullContent}</div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
          </div>
        </div>
      </div>
    `;
    $("body").append(modal);
    $("#readMoreModal").modal('show');
    $("#readMoreModal").on('hidden.bs.modal', function () {
      $(this).remove();
    });
  });

  $(".hover-target").hover(
    function() {
        $(".popover").css({
            display: "block",
        });
    },
    function() {
        $(".popover").hide();
    }
  );
  
  // Set Sidebar State for Mobile
  if (isMobile()) {
    document.querySelector(".sidebar").classList.add("close");
  }

  // Initialize LazyLoad
  initLazyLoad();

  // Initialize tracking
  userTracking.init(trackingConfig);

  console.info("%c Web App %c ".concat("DOM Fully loaded", " "), "color: white; background: #AD80FD; font-weight: 700;", "color: #AD80FD; background: white; font-weight: 700;");
});

  // Modal Backdrop Z-Index Fix
  $(document).on('shown.bs.modal', '.modal', function () {
    $('.modal-backdrop').before($(this));
  });


  // **************** //
  // ** CLEANED UP ** //
  // **************** //

  // ** FORM BUILDER ** //
  function buildFormGroup(array, noTabs = false, noRows = false) {
    var mainCount = 0;
    var ids = {};
    var active = '';
    var first = Object.keys(array)[0];

    // Generate IDs once and store them
    Object.keys(array).forEach((i, index) => {
        ids[i] = createRandomString(10);
    });

    if (noTabs) {
        var group = '';
        var uList = '';
    } else {
        var group = '<div class="tab-content tab-content-sub">';
        var uList = `
            <ul class="nav flex-column nav-tabs info-nav">
                <div class="d-lg-none tab-sub-dropdown">
                    <div class="dropdown">
                        <button class="btn btn-secondary dropdown-toggle" type="button" id="configSubTabsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            ${first}
                        </button>
                        <ul class="dropdown-menu dropdown-menu-top" aria-labelledby="configSubTabsDropdown">
                            ${Object.keys(array).map((i, index) => {
                                return `<li><a href="" data-bs-toggle="tab" data-bs-target="#${ids[i]}${cleanString(i)}" class="nav-link ${active}"><span lang="en">${i}</span></a></li>`;
                            }).join('')}
                        </ul>
                    </div>
                </div>
                ${Object.keys(array).map((i, index) => {
                    active = (index == 0) ? 'active' : '';
                    return `<li role="presentation" class="nav-item d-none d-lg-flex"><a href="" data-bs-toggle="tab" data-bs-target="#${ids[i]}${cleanString(i)}" class="nav-link ${active}"><span lang="en">${i}</span></a></li>`;
                }).join('')}
            </ul>
        `;
    }
    // Section
    $.each(array, function (i, v) {
        mainCount++;
        var total = v.length;
        var active = (mainCount == 1) ? 'active' : '';

        if (i == 'custom') {
            group += v;
        } else {
            if (!noTabs) {
                group += `
                    <!-- FORM GROUP -->
                    <div class="tab-pane ${active}" id="${ids[i]}${cleanString(i)}">
                `;
            }

            var currentRowWidth = 0;
            var sectionCount = 0;
            
            if (noRows) {
              group += '<div class="row start">';
            }

            // Item
            $.each(v, function (j, item) {
                var width = parseInt(item.width) || 6;

                sectionCount++;

                if (currentRowWidth === 0 && !noRows) {
                  group += '<div class="row start">';
                }

                if (currentRowWidth + width > 12 && !noRows) {
                    group += '</div><!--end over 12--><div class="row start">';
                    currentRowWidth = 0;
                }

                var helpID = `#help-info-${item.name}`;
                var helpTip = item.help ? `<sup><a class="help-tip" data-bs-toggle="collapse" href="${helpID}" aria-expanded="true"><i class="ms-1 fa fa-question-circle text-info" title="Help" data-toggle="tooltip"></i></a></sup>` : '';
                var builtItems = '';

                if (item.type == 'title' || item.type == 'hr' || item.type == 'js') {
                    builtItems = `${buildFormItem(item)}`;
                    if (!noRows) {
                      group += '</div><!--end for type--><div class="row start">'; // Close current row and start a new one
                    }
                    currentRowWidth = 0; // Reset row width
                    width = 12;
                } else {
                    builtItems = `
                        <div class="col-md-${width} pb-2">
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
                currentRowWidth += width;

                if ((currentRowWidth >= 12 || sectionCount == total) && !noRows) {
                    group += '</div><!--end over 12 x2-->';
                    currentRowWidth = 0;
                }

            });

            if (currentRowWidth > 0 && !noTabs) {
                group += '</div><!--end noTabs-->';
            }

            if (noRows) {
              group += '</div><!--end noRows-->';
            }
        }
        
        if (sectionCount == total) {
          group += '</div><!--end of section-->';
        }
    });

    if (noTabs) {
        var flex = '';
    } else {
        var flex = 'd-flex';
    }
    return `<div class="${flex}">` + uList + group + '</div>'; // Wrapped in a flex container for alignment
  }
  
  function buildFormItem(item){
    var placeholder = (item.placeholder) ? ' placeholder="'+item.placeholder+'"' : '';
    var id = (item.id) ? ' id="'+item.id+'"' : '';
    var tableId = (item.id) && (item.type == "selectwithtable") ? ' id="'+item.id+'Table"' : '';
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
    var helpInfo = (item.help) ? '<div class="collapse" id="help-info-'+item.name+'"><div class="card help-info-card"><blockquote lang="en" class="pt-3 ps-3">'+item.help+'</blockquote></div></div>' : '';
    var smallLabel = (item.smallLabel) ? '<label><span lang="en">'+item.smallLabel+'</span></label>' : '';
    var dataAttributes = (item.dataAttributes) ? Object.keys(item.dataAttributes).map(key => ` data-${key}="${item.dataAttributes[key]}"`).join(' ') : '';

    switch (item.type) {
      case 'select-input':
        return smallLabel + '<input list="'+item.name+'Options" lang="en" type="text" class="form-control info-field' + extraClass + '"' + placeholder + value + id + name + disabled + type + label + attr + dataAttributes + '/><datalist id="'+item.name+'Options">' + selectOptions(item.options, item.value) + '</datalist>'+helpInfo;
      case 'input':
      case 'text':
        return smallLabel+'<input lang="en" type="text" class="form-control info-field'+extraClass+'"'+placeholder+value+id+name+disabled+type+label+attr+dataAttributes+' />'+helpInfo;
      case 'inputmultiple':
        return '<div class="input-group mb-3"><input lang="en" type="text" class="form-control info-field'+extraClass+'" multiple '+placeholder+id+name+disabled+type+label+attr+dataAttributes+'/><div class="input-group-append"><button class="btn btn-outline-success addInputEntry" type="button">'+text+'</button></div></div><ul class="list-group mt-3 inputEntries">'+multipleInputArr(item)+'</ul>'+helpInfo;
      case 'number':
        return smallLabel+'<input lang="en" type="number" class="form-control info-field'+extraClass+'"'+placeholder+value+id+name+disabled+type+label+attr+dataAttributes+'/>'+helpInfo;
      case 'textbox':
        return smallLabel+'<textarea class="form-control info-field'+extraClass+'"'+placeholder+id+name+disabled+type+label+attr+dataAttributes+' autocomplete="new-password">'+textarea+'</textarea>'+helpInfo;
      case 'password':
        return smallLabel+'<input lang="en" type="password" class="form-control info-field'+extraClass+'"'+placeholder+value+id+name+disabled+type+label+attr+dataAttributes+'/>'+helpInfo;
      case 'password-alt':
        return smallLabel+'<div class="input-group"><input lang="en" type="password" class="password-alt form-control info-field'+extraClass+'"'+placeholder+value+id+name+disabled+type+label+attr+dataAttributes+'/><span class="input-group-btn"> <button class="btn btn-default showPassword" type="button"><i class="fa fa-eye passwordToggle"></i></button></span></div>'+helpInfo;
      case 'password-alt-copy':
        return smallLabel+'<div class="input-group"><input lang="en" type="password" class="password-alt form-control info-field'+extraClass+'"'+placeholder+value+id+name+disabled+type+label+attr+dataAttributes+'/><span class="input-group-btn"> <button class="btn btn-primary clipboard" type="button" data-clipboard-text="'+item.value+'"><i class="fa icon-docs"></i></button></span><span class="input-group-btn"> <button class="btn btn-inverse showPassword" type="button"><i class="fa fa-eye passwordToggle"></i></button></span></div>'+helpInfo;
      case 'hidden':
        return '<input lang="en" type="hidden" class="form-control info-field'+extraClass+'"'+placeholder+value+id+name+disabled+type+label+attr+dataAttributes+'/>'+helpInfo;
      case 'select':
        return smallLabel+'<select class="form-control info-field'+extraClass+'"'+placeholder+value+id+name+disabled+type+label+attr+dataAttributes+'>'+selectOptions(item.options, item.value)+'</select>'+helpInfo;
      case 'selectmultiple':
        return smallLabel+'<select class="form-control info-field'+extraClass+'" multiple '+placeholder+value+id+name+disabled+type+label+attr+dataAttributes+'>'+selectOptions(item.options, item.value)+'</select>'+helpInfo;
      case 'select2':
        var select2ID = (item.id) ? '#'+item.id : '[name=\''+item.name+'\']';
        let settings = (item.settings) ? item.settings : '{}';
        return smallLabel+'<select class="m-b-10 info-field'+extraClass+'"'+placeholder+value+id+name+disabled+type+label+attr+' multiple="multiple" data-placeholder="">'+selectOptions(item.options, item.value)+'</select><script>$("'+select2ID+'").select2('+settings+').on("select2:unselecting", function() { $(this).data("unselecting", true); }).on("select2:opening", function(e) { if ($(this).data("unselecting")) { $(this).removeData("unselecting");  e.preventDefault(); } });</script>';
      case 'imageselect':
        var ret = smallLabel+'<select class="form-control info-field'+extraClass+'"'+placeholder+value+id+name+disabled+type+label+attr+dataAttributes+'>'+selectOptions(item.options, item.value)+'</select>'+helpInfo;
        if (item.initialize == 'true') {
          ret += `<script>
          var dynSelect = new DynamicSelect(document.querySelector('[${name}]'));
          dynSelect.setSelectedValue(${value});
          </script>`;
        }
        return ret;
      case 'switch':
      case 'checkbox':
        return smallLabel+'<div class="form-check form-switch"><input class="form-check-input info-field'+extraClass+'" type="checkbox"'+name+value+id+disabled+type+label+attr+dataAttributes+'/></div>'+helpInfo;
      case 'button':
        return smallLabel+'<button class="btn btn-sm btn-success btn-rounded waves-effect waves-light b-none'+extraClass+'" '+href+attr+dataAttributes+' type="button"><span class="btn-label"><i class="'+icon+'"></i></span><span lang="en">'+text+'</span></button>'+helpInfo;
      case 'colourpicker':
        return smallLabel+'<input type="color" class="form-control form-control-color info-field'+extraClass+'"'+value+id+name+disabled+type+label+attr+dataAttributes+'>'+helpInfo;
      case 'blank':
        return '';
      case 'panel':
        return '<div class="panel-group'+extraClass+'"'+placeholder+value+id+name+disabled+type+label+attr+dataAttributes+'  aria-multiselectable="true" role="tablist">'+panelOptions(item.options, item.id)+'</div>'+helpInfo;
      case 'accordion':
        return '<div class="accordion'+extraClass+'"'+id+disabled+name+disabled+type+label+attr+dataAttributes+'>'+accordionOptions(item.options, item.id)+'</div>'+helpInfo;
      case 'listgroup':
        return buildListGroup(item.items,id);
      case 'title':
        return '<h4'+id+'">'+text+'</h4>';
      case 'hr':
        return '<hr class="mt-3"'+id+'>';
      case 'html':
        return item.html;
      case 'js':
        if (item.src) {
          appendScript(item.src);
        } else if (item.script) {
          appendInlineScript(item.script, item.id);
        }
        return ''; // Return an empty string as the script is already added
      case 'selectwithtable':
        return `
          <form id="multiSelectForm">
              <div class="form-group">
                <select class="form-control select-multiple widgetSelect info-field" multiple name="Widgets" data-type="selectmultiple" `+id+name+disabled+type+label+attr+dataAttributes+`>
                `+selectOptions(item.options, item.value)+`
              </select>
              </div>
              <table `+tableId+` 
                data-pagination="true"
                data-reorderable-rows="true"
                data-drag-handle=">tbody>tr>td>span.dragHandle"
                class="table table-bordered table-striped info-field" `+dataAttributes+`>
                  <thead>
                      <tr>
                          <th data-field="dragHandle"></th>
                          <th data-field="name">Widget</th>
                          <th data-field="size">Size</th>
                      </tr>
                  </thead>
                  <tbody></tbody>
              </table>
          </form>
          `;
        case 'bootstraptable':
          var events = item.events ? item.events : {};
          var tableOptions = {};

          for (const event in events) {
              if (events.hasOwnProperty(event)) {
                  tableOptions[event] = events[event];
              }
          }
          return `
              <table class="table table-bordered table-striped ${extraClass}" ${id} ${name} ${disabled} ${type} ${label} ${attr} ${dataAttributes}>
                  <thead>
                      <tr>
                          ${item.columns.map(column => `
                              <th data-field="${column.field}" ${column.dataAttributes ? Object.keys(column.dataAttributes).map(key => `data-${key}="${column.dataAttributes[key]}"`).join(' ') : ''}>
                                  ${column.title ?? ''}
                              </th>
                          `).join('')}
                      </tr>
                  </thead>
                  <tbody>
                  </tbody>
              </table>
              <script>
                $("#${item.id}").bootstrapTable({
                    onReorderRow: ${events.onReorderRow},
                    onExpandRow: ${events.onExpandRow}
                });
              </script>
          `;
      default:
        return '<span class="text-danger">BuildFormItem Class not setup...';
    }
  }

  function buildListGroup(items,id) {
    let listGroup = `<div class="list-group mb-5 shadow" ${id}>`;
    
    items.forEach(item => {
      const checkbox = item.checkbox ? `
        <div class="col-auto">
          <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input toggle" id="${item.id}" name="${item.name}">
            <label class="custom-control-label" for="${item.id}"></label>
          </div>
        </div>
      ` : '';
  
      listGroup += `
        <div class="list-group-item">
          <div class="row align-items-center">
            <div class="col">
              <strong class="mb-2">${item.title}</strong>
              <p class="text-muted mb-0">${item.description}</p>
            </div>
            ${checkbox}
          </div>
        </div>
      `;
    });
  
    listGroup += '</div>';
    return listGroup;
  }

  function panelOptions(options, parentID){
    var panelOptions = '';
    $.each(options, function(i,v) {
      var id = createRandomString(10);
      var extraClass = (v.class) ? ' '+v.class : '';
      var header = (i) ? ' '+i : '';
      if(typeof v == 'object'){
        var body = '';
        $.each(v, function(val) {
          var helpTip = v[val].helpTip ?? '';
          if (v[val].type == 'title' || v[val].type == 'hr' || v[val].type == 'js') {
            body += buildFormItem(v[val]);
          } else {
            body += `<div class="col-md-6"><label class="control-label"><span lang="en">${v[val].label}</span>${helpTip}</label>`;
            body += buildFormItem(v[val]);
            body += `</div>`;
          }
        });
      }else{
        var body = v.body;
      }
      panelOptions += `
      <div class="panel">
        <div class="panel-heading" id="`+id+`-heading" role="tab">
          <a class="panel-title collapsed" data-bs-toggle="collapse" href="#`+id+`-collapse" data-bs-parent="#`+parentID+`" aria-expanded="false" aria-controls="`+id+`-collapse"><span lang="en">`+header+`</span></a>
        </div>
        <div class="panel-collapse collapse" id="`+id+`-collapse" aria-labelledby="`+id+`-heading" role="tabpanel" aria-expanded="false" style="height: 0px;">
          <div class="panel-body px-3">
            <div class="row pt-2">
              `+body+`
            </div>
          </div>
        </div>
      </div>
      `;
    });
    return panelOptions;
  }

  function accordionOptions(options, parentID){
    var accordionOptions = '';
    $.each(options, function(i,v) {
      var id = createRandomString(10);
      var extraClass = (v.class) ? ' '+v.class : '';
      var header = (i) ? ' '+i : '';
      if(typeof v == 'object'){
        var body = '';
        $.each(v, function(val) {
          var helpID = `#help-info-${v[val].name}`;
          var helpTip = v[val].help ? `<sup><a class="help-tip" data-bs-toggle="collapse" href="${helpID}" aria-expanded="true"><i class="ms-1 fa fa-question-circle text-info" title="Help" data-toggle="tooltip"></i></a></sup>` : '';
          var width = v[val].width ?? '6';
          if (v[val].type == 'title' || v[val].type == 'hr' || v[val].type == 'js') {
            body += buildFormItem(v[val]);
          } else {
            body += `<div class="col-md-${width}"><label class="control-label"><span lang="en">${v[val].label}</span>${helpTip}</label>`;
            body += buildFormItem(v[val]);
            body += `</div>`;
          }
        });
      }else{
        var body = v.body;
      }
      accordionOptions += `
      <div class="accordion-item">
        <h2 class="accordion-header" id="`+id+`-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#`+id+`-collapse" aria-expanded="false" aria-controls="`+id+`">`+header+`</button>
        </h2>
        <div id="`+id+`-collapse" class="accordion-collapse collapse" aria-labelledby="`+id+`-heading" data-bs-parent="#`+id+`">
          <div class="accordion-body">
            <div class="card-body">
              <div class="row">
                `+body+`
              </div>
            </div>
          </div>
        </div>
      </div>
      `;
    });
    return accordionOptions;
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

  function selectOptions(options, active){
    var selectOptions = '';
    var activeTest = [];
    if (active) {
      activeTest = active.split(',');
    }
    
    $.each(options, function(i,v) {
      if(activeTest.length > 1) {
        var selected = (arrayContains(v.value, activeTest)) ? 'selected' : '';
      } else {
        if (active != null) {
          var selected = (active.toString() == v.value) ? 'selected' : '';
        }
      }
      var disabled = (v.disabled) ? ' disabled' : '';
      var attr = (v.attr) ? ' '+v.attr+' ' : '';
      selectOptions += '<option '+selected+disabled+attr+' value="'+v.value+'">'+v.name+'</option>';
    });
    return selectOptions;
  }

  // ** ACTION EVENTS ** //
  window.pluginActionEvents = {
    "click .edit": function (e, value, row, index) {
      buildPluginSettingsModal(row);
    },
    "click .install": function (e, value, row, index) {
      installPlugin(row);
    },
    "click .uninstall": function (e, value, row, index) {
      uninstallPlugin(row);
    },
    "click .reinstall": function (e, value, row, index) {
      reinstallPlugin(row);
    },
    "click .update": function (e, value, row, index) {
      reinstallPlugin(row);
    }
  }

  window.backupActionEvents = {
    "click .download": function (e, value, row, index) {
        var url = '/api/config/backup/'+row.filename;
        var a = document.createElement("a")
        a.href = url;
        a.download = url.split("/").pop()
        document.body.appendChild(a)
        a.click()
        document.body.removeChild(a)
    },
    "click .delete": function (e, value, row, index) {
      if(confirm("Are you sure you want to delete the backup: "+row.filename+"? This is irriversible.") == true) {
        queryAPI("DELETE","/api/config/backup/"+row.filename).done(function(data) {
          if (data["result"] == "Success") {
            toast("Success","","Successfully deleted backup: "+row.filename,"success");
            $("[name=backupsTable]").bootstrapTable("refresh");
          } else if (data["result"] == "Error") {
            toast(data["result"],"",data["message"],"danger","30000");
          } else {
            toast("Error","","Failed to delete backup: "+row.filename,"danger");
          }
        }).fail(function() {
            toast("Error", "", "Failed to delete backup: "+row.filename, "danger");
        });
      }
    }
  }

  window.widgetActionEvents = {
    "click .edit": function (e, value, row, index) {
      buildWidgetSettingsModal(row);
    }
  }

  window.dashboardActionEvents = {
    "click .edit": function (e, value, row, index) {
      buildDashboardSettingsModal(row);
    },
    "click .delete": function (e, value, row, index) {
      if(confirm("Are you sure you want to delete the Dashboard: "+row.Name+"? This is irriversible.") == true) {
        queryAPI("DELETE","/api/config/dashboards/"+row.Name).done(function(data) {
          if (data["result"] == "Success") {
            toast("Success","","Successfully deleted Dashboard: "+row.Name,"success");
            $("#dashboardsTable").bootstrapTable("refresh");
          } else if (data["result"] == "Error") {
            toast(data["result"],"",data["message"],"danger","30000");
          } else {
            toast("Error","","Failed to delete Dashboard: "+row.Name,"danger");
          }
        }).fail(function() {
            toast("Error", "", "Failed to delete Dashboard: "+row.Name, "danger");
        });
      }
    }
  }

  window.userActionEvents = {
    "click .edit": function (e, value, row, index) {
      buildUserSettingsModal(row);
    },
    "click .delete": function (e, value, row, index) {
      if(confirm("Are you sure you want to delete "+row.username+" from the list of Users? This is irriversible.") == true) {
        queryAPI("DELETE","/api/user/"+row.id).done(function(data) {
          if (data["result"] == "Success") {
            toast(data["result"],"",data["message"],"success");
            $("#usersTable").bootstrapTable("refresh");
            $("#editUserModal").modal("hide");
          } else if (data["result"] == "Error") {
            toast(data["result"],"",data["message"],"danger","30000");
          } else {
            toast("Error","","Failed to remove user: "+row.username,"danger","30000");
          }
        }).fail(function() {
          toast("API Error","","Failed to remove user: "+row.username,"danger","30000");
        });
      }
    }
  }

  window.groupsActionEvents = {
    "click .edit": function (e, value, row, index) {
      buildGroupSettingsModal(row);
    },
    "click .delete": function (e, value, row, index) {
      if(confirm("Are you sure you want to delete "+row.Name+" from Role Based Access? This is irriversible.") == true) {
        queryAPI("DELETE","/api/rbac/group/"+row.id).done(function(data) {
          if (data["result"] == "Success") {
            toast("Success","","Successfully deleted "+row.Name+" from Role Based Access","success");
            $("#groupsTable").bootstrapTable("refresh");
          } else if (data["result"] == "Error") {
            toast(data["result"],"",data["message"],"danger","30000");
          } else {
            toast("Error","","Failed to delete "+row.Name+" from Role Based Access","danger");
          }
        }).fail(function() {
            toast("Error", "", "Failed to remove " + row.Name + " from Role Based Access", "danger");
        });
      }
    }
  }

  window.rolesActionEvents = {
    "click .edit": function (e, value, row, index) {
      buildRoleSettingsModal(row);
    },
    "click .delete": function (e, value, row, index) {
      if(confirm("Are you sure you want to delete the "+row.name+" role? This is irriversible.") == true) {
        queryAPI("DELETE","/api/rbac/role/"+row.id).done(function(data) {
          if (data["result"] == "Success") {
            toast("Success","","Successfully deleted "+row.name+" from Role Based Access","success");
            $("#rolesTable").bootstrapTable("refresh");
          } else if (data["result"] == "Error") {
            toast(data["result"],"",data["message"],"danger","30000");
          } else {
            toast("Error","","Failed to delete "+row.name+" from Role Based Access","danger");
          }
        }).fail(function() {
            toast("Error", "", "Failed to remove " + targetid + " from " + group, "danger");
        });
      }
    }
  }

  window.pageActionEvents = {
    "click .edit": function (e, value, row, index) {
      buildPageSettingsModal(row);
    },
    "click .delete": function (e, value, row, index) {
      if(confirm("Are you sure you want to delete the page: "+row.Name+"? This is irriversible.") == true) {
        queryAPI("DELETE","/api/page/"+row.id).done(function(data) {
          if (data["result"] == "Success") {
            toast("Success","","Successfully deleted "+row.Name+" from Pages","success");
            var tableId = `#${$(e.currentTarget).closest("table").attr("id")}`;
            $(tableId).bootstrapTable("refresh");
          } else if (data["result"] == "Error") {
            toast(data["result"],"",data["message"],"danger","30000");
          } else {
            toast("Error","","Failed to delete "+row.Name+" from Pages","danger");
          }
        }).fail(function() {
            toast("Error", "", "Failed to remove " + row.Name + " from Pages", "danger");
        });
      }
    }
  }

  window.newsActionEvents = {
    "click .edit": function (e, value, row, index) {
      buildNewsSettingsModal(row);
    },
    "click .delete": function (e, value, row, index) {
      if(confirm("Are you sure you want to delete the news item: "+row.title+"? This is irriversible.") == true) {
        queryAPI("DELETE","/api/notifications/news/"+row.id).done(function(data) {
          if (data["result"] == "Success") {
            toast("Success","","Successfully deleted "+row.title+" from News","success");
            var tableId = `#${$(e.currentTarget).closest("table").attr("id")}`;
            $(tableId).bootstrapTable("refresh");
          } else if (data["result"] == "Error") {
            toast(data["result"],"",data["message"],"danger","30000");
          } else {
            toast("Error","","Failed to delete "+row.title+" from News","danger");
          }
        }).fail(function() {
            toast("Error", "", "Failed to remove " + row.title + " from News", "danger");
        });
      }
    }
  }

  window.tokenActionEvents = {
    "click .delete": function (e, value, row, index) {
      if(confirm("Are you sure you want to revoke the token: "+row.last_10_chars+"? This is irriversible.") == true) {
        var tableId = $(e.currentTarget).closest("table").attr("id");
        var type = null;
        switch (tableId) {
          case 'sessionTokenTable':
            type = 'session_tokens';
            break;
          case 'apiTokenTable':
            type = 'api_tokens';
            break;
          default:
            return;
        }
        queryAPI("DELETE","/api/auth/tokens/"+type+"/"+row.last_10_chars).done(function(data) {
          if (data["result"] == "Success") {
            toast("Success","","Successfully revoked "+row.last_10_chars+" from Tokens","success");
            $(`#${tableId}`).bootstrapTable("refresh");
          } else if (data["result"] == "Error") {
            toast(data["result"],"",data["message"],"danger","30000");
          } else {
            toast("Error","","Failed to delete "+row.last_10_chars+" from Tokens","danger");
          }
        }).fail(function() {
            toast("Error", "", "Failed to remove " + row.last_10_chars + " from Tokens", "danger");
        });
      }
    }
  }


  // ** ACTION FORMATTERS ** //
  function widgetActionFormatter(value, row, index) {
    var buttons = [
      `<a class="edit" title="Edit"><i class="fa fa-pencil"></i></a>&nbsp;`
    ];
    return buttons.join("");
  }

  function pluginActionFormatter(value, row, index) {
    var buttons = [];
    if (row.settings) {
      buttons.push(`<a class="edit" title="Edit"><i class="fa fa-pencil"></i></a>&nbsp;`);
    }
    if (row.status == "Available") {
      if (row.requirementsMet) {
        buttons.push(`<a class="install" title="Install"><i class="fa-solid fa-download"></i></a>&nbsp;`);
      }
    } else if (row.status == "Installed") {
      buttons.push(`<a class="uninstall" title="Uninstall"><i class="fa-solid fa-trash-can"></i></a>&nbsp;`);
      if (row.version < row.online_version) {
        buttons.push(`<a class="update" title="Update"><i class="fa-solid fa-upload"></i></a>&nbsp;`);      
      } else if (row.source == "Online") {
        if (row.requirementsMet) {
          buttons.push(`<a class="reinstall" title="Reinstall"><i class="fa-solid fa-arrow-rotate-right"></i></a>&nbsp;`);
        }
      }
    }
    return buttons.join("");
  }

  function backupActionFormatter(value, row, index) {
    var buttons = [];
    buttons.push(`<a class="download" title="Download"><i class="fa-solid fa-download"></i></a>&nbsp;`);
    buttons.push(`<a class="delete" title="Delete"><i class="fa-solid fa-trash"></i></a>&nbsp;`);
    return buttons.join("");
  }

  function editAndDeleteActionFormatter(value, row, index) {
    var buttons = [
      `<a class="edit" title="Edit"><i class="fa fa-pencil"></i></a>&nbsp;`,
      `<a class="delete" title="Delete"><i class="fa fa-trash"></i></a>`
    ];
    return buttons.join("");
  }

  function deleteActionFormatter(value, row, index) {
    var buttons = [
      `<a class="delete" title="Delete"><i class="fa fa-trash"></i></a>`
    ];
    return buttons.join("");
  }

  function groupActionFormatter(value, row, index) {
    if (row["Name"] != "Administrators") {
      var actions = `<a class="edit" title="Edit"><i class="fa fa-pencil"></i></a>&nbsp;`
      if (!row["Protected"]) {
        actions += `<a class="delete" title="Delete"><i class="fa fa-trash"></i></a>`
      }
      return actions
    }
  }

  function roleActionFormatter(value, row, index) {
    var actions = ""
    if (!row["Protected"]) {
      actions = `<a class="edit" title="Edit"><i class="fa fa-pencil"></i></a>&nbsp;<a class="delete" title="Delete"><i class="fa fa-trash"></i></a>`
    }
    return actions
  }

  function pageActionFormatter(value, row, index) {
    var actions = `<a class="edit" title="Edit"><i class="fa fa-pencil"></i></a>&nbsp;`
    if (!row["Protected"]) {
      actions += `<a class="delete" title="Delete"><i class="fa fa-trash"></i></a>`
    }
    return actions
  }

  // ** FORMATTERS ** //
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
  
  function pluginUpdatesFormatter(value, row, index) {
    if (row.requirementsMet) {
      if (row.version < row.online_version) {
        return `<span class="badge bg-info">Update Available</span>`;
      } else if (row.source == "Local") {
        return `<span class="badge bg-secondary">Unknown</span>`;
      } else if (row.status == "Available") {
        return `<span class="badge bg-primary">Not Installed</span>`;
      } else {
        return `<span class="badge bg-success">Up to date</span>`;
      }
    } else {
      return `<span class="badge bg-warning" style="text-wrap: auto;">`+row.requirementsReason+`</span>`;
    }
  }

  function groupsFormatter(value, row, index) {
    var html = ""
    $(row.groups).each(function (group) {
      html += `<span class="badge bg-info">`+row.groups[group]+`</span>&nbsp;`;
    });
    return html;
  }

  function pageIconFormatter(value, row, index) {
    if (row.Icon && (row.Icon.startsWith("/assets/images/custom") || row.Icon.startsWith("/api/image/plugin"))) {
      return `<img src="`+value+`" class="navIcon"></img>`
    } else {
      return `<i class="navIcon `+value+`"></i>`
    }
  }

  function typeFormatter(value, row, index) {
    return pagesDetermineType(value);
  }

  function pluginRequirementsFormatter(value, row, index) {
    var html = ""
    $(row.requires).each(function (require) {
      html += `<span class="badge bg-info">`+row.requires[require]+`</span>&nbsp;`;
    });
    return html;
  }

  function pluginChangeLogFormatter(value, row, index) {
    var html = ""
    var title = row.changelog.title ?? 'Change Log'
    switch (row.changelog.type) {
      case "url":
        html += `<a href="`+row.changelog.url+`" target="_blank">`+title+`</a>`;
        break;
      case "builtin":
        var fullContent = "";
        if (row.changelog.data && Array.isArray(row.changelog.data)) {
          row.changelog.data.forEach(function (item) {
            fullContent += `<h2>v`+item.version+`</h2>`;
            fullContent += `<ul>`;
            item.changes.forEach(function (change) {
              fullContent += `<li>`+change+`</li>`;
            });
            fullContent += `</ul>`;
          });
        }
        html += `<a href="#" class="read-more-popup" data-full-content="`+fullContent+`" data-title="`+title+`">`+title+`</a>`;
        break;
    }
    return html;
  }

  function readMoreFormatter(value, row, index) {
    var maxLength = 100; // Adjust the length as needed
    if (value.length > maxLength) {
        var truncated = value.substring(0, maxLength) + '... <a href="#" class="read-more" data-full-content="' + value + '">Read more</a>';
        return truncated;
    }
    return value;
  }

  function booleanTickCrossFormatter(value, row, index) {
    if (value == 1) {
      return '<i class="fa-solid fa-circle-check text-center w-100"></i>';
    } else {
      return '';
    }
  }

  function appendDotsFormatter(value, row, index) {
    return '...'+value;
  }

  function secondsFormatter(value, row, index) {
    const units = [
        { label: 'month', seconds: 2628000 }, // Approximate value for a month (30.44 days)
        { label: 'week', seconds: 604800 },
        { label: 'day', seconds: 86400 },
        { label: 'hour', seconds: 3600 },
        { label: 'minute', seconds: 60 },
        { label: 'second', seconds: 1 }
    ];

    for (let unit of units) {
        if (value >= unit.seconds) {
            const count = Math.floor(value / unit.seconds);
            return `${count} ${unit.label}${count > 1 ? 's' : ''}`;
        }
    }
    return '0 seconds';
  }

  function githubLinkFormatter(value, row, index) {
    return `<a href="${value}" target="_blank" class="text-decoration-none">Github&nbsp;<i class="fa-solid fa-external-link-alt"></i></a>`;
  }


  // ** TABLE DETAIL FORMATTERS ** //
  function pagesDetailFormatter(index, row, prefix) {
    let html = [];
    if (row.Type === "Menu" || row.Type === "SubMenu") {
        html.push(createTableHtml(index, prefix));
    }
    return html.join("");
  }

  function menuDetailFormatter(index,row) {
    return pagesDetailFormatter(index,row,"menu");
  }

  function submenuDetailFormatter(index,row) {
    return pagesDetailFormatter(index,row,"submenu");
  }

  // ** TABLE BUTTONS ** //
  function usersTableButtons() {
    return {
      btnAddUser: {
        text: "Add User",
        icon: "bi-person-fill-add",
        event: function() {
          buildNewUserSettingsModal();
        },
        attributes: {
          title: "Add a new user",
          style: "background-color:#4bbe40;border-color:#4bbe40;"
        }
	    }
    }
  }

  function groupsTableButtons() {
    return {
      btnAddGroup: {
        text: "Add Group",
        icon: "bi-plus-lg",
        event: function() {
          buildNewGroupSettingsModal();
        },
        attributes: {
          title: "Add a new group",
          style: "background-color:#4bbe40;border-color:#4bbe40;"
        }
	    }
    }
  }

  function rolesTableButtons() {
    return {
      btnAddRole: {
        text: "Add Role",
        icon: "bi-plus-lg",
        event: function() {
          buildNewRoleSettingsModal();
        },
        attributes: {
          title: "Add a new role",
          style: "background-color:#4bbe40;border-color:#4bbe40;"
        }
	    }
    }
  }

  function pluginsTableButtons() {
    return {
      btnEditPluginURLs: {
        text: "Edit Plugin URL(s)",
        icon: "bi bi-pencil-square",
        event: function() {
          $("#urlList").html("");
          populatePluginRepositories();
          $("#onlinePluginsModal").modal("show");
        },
        attributes: {
          title: "Edit Plugin URL(s)",
          style: "background-color:#4bbe40;border-color:#4bbe40;"
        }
	    }
    }
  }

  function backupTableButtons() {
    return {
      btnNewBackup: {
        text: "New Backup",
        icon: "bi bi-plus-lg",
        event: function() {
          queryAPI("POST","/api/config/backup").done(function(data) {
            if (data["result"] == "Success") {
              toast("Success","",data["message"],"success");
              $(`[name="backupsTable"]`).bootstrapTable("refresh");
            } else if (data["result"] == "Error") {
              toast(data["result"],"",data["message"],"danger","30000");
            } else {
              toast("Error","","Failed to create backup","danger");
            }
          }).fail(function() {
              toast("Error", "", "Failed to create backup", "danger");
          });
        },
        attributes: {
          title: "New Backup",
          style: "background-color:#4bbe40;border-color:#4bbe40;"
        }
	    }
    }
  }

  function dashboardsTableButtons() {
    return {
      btnAddDashboard: {
        text: "Create new Dashboard",
        icon: "bi bi-plus-lg",
        event: function() {
          buildNewDashboardSettingsModal();
        },
        attributes: {
          title: "Create new Dashboard",
          style: "background-color:#4bbe40;border-color:#4bbe40;"
        }
	    }
    }
  }

  function pagesTableButtons() {
    return {
      btnAddGroup: {
        text: "Add Page",
        icon: "bi-plus-lg",
        event: function() {
          buildNewPageSettingsModal();
        },
        attributes: {
          title: "Add a new page",
          style: "background-color:#4bbe40;border-color:#4bbe40;"
        }
	    }
    }
  }

  function newsTableButtons() {
    return {
      btnAddNews: {
        text: "Add News Item",
        icon: "bi-plus-lg",
        event: function() {
          buildNewNewsSettingsModal();
        },
        attributes: {
          title: "Add a new news item",
          style: "background-color:#4bbe40;border-color:#4bbe40;"
        }
	    }
    }
  }

  function apiTokenTableButtons() {
    return {
      btnAddNews: {
        text: 'Add API Token',
        icon: 'bi-plus-lg',
        html: function() {
          return `
            <div class="d-flex">
              <button name="btnAddNews" class="btn btn-success" title="Add a new API Token" style="background-color:#4bbe40;border-color:#4bbe40;height:40px;width:250px;">
                <i class="bi bi-plus-lg"></i> Add API Token
              </button>
              <input type="number" id="daysInput" class="form-control mt-0" placeholder="Days" min="1" max="365" style="max-width: 100px; margin-left: 10px;height:40px;">
            </div>
          `;
        },
        event: {
          click: function() {
            const days = document.getElementById('daysInput').value;
            if (days >= 1 && days <= 365) {
              newAPIToken(days);
            } else {
              alert('Please enter a valid number of days between 1 and 365.');
            }
          }
        }
      }
    };
  }

  // ** TABLE RESPONSE HANDLER ** //
  function responseHandler(data) {
    if (data.result === "Warning" && Array.isArray(data.message)) {
        data.message.forEach(warning => {
            toast("Warning", "", warning, "warning","30000");
        });
    }
    return data.data;
  }

  function dragHandlerResponseHandler(data) {
    // Iterate through each row in the response data
    data.data.forEach(function(row) {
        // Append the span to the dragHandle field
        row.dragHandle = '<span class="dragHandle" style="font-size:22px;">☰</span>';
    });

    return data;
}

  // ** BUILD / SUBMIT SETTINGS ** //
  function buildSettings(elem, setting, options) {
    // Empty the additional settings array
    selectWithTableArr = {};
    const { dataLocation, noTabs } = options;
    id = $(elem).attr("id");
    if (tabsLoaded.includes(setting)) {
      // Do Nothing
    } else {
      tabsLoaded.push(setting);
      try {
        queryAPI("GET", "/api/settings/"+setting).done(function(settingsResponse) {
          const settingsData = dataLocation ? getNestedProperty(settingsResponse, dataLocation) : settingsResponse.data;
          $(elem).html(buildFormGroup(settingsData,noTabs));
          initPasswordToggle();
          populateSettingsForm(`#`+id);
        }).fail(function(xhr) {
          logConsole("Error", xhr, "error");
        });
      } catch (e) {
        logConsole("Error", e, "error");
      }
    }
  }

  function populateSettingsForm(elem) {
    const updateConfigValues = (config, parentKey = "") => {
      for (const section in config) {
        const value = config[section];
        const fullKey = parentKey ? `${parentKey}[${section}]` : section;
        const selector = `${elem} [name=${$.escapeSelector(fullKey)}]`;

        if (typeof value === "object" && !Array.isArray(value) && value !== null) {
          updateConfigValues(value, fullKey);
        } else if (typeof value === "boolean") {
          $(selector).prop("checked", value);
        } else {
          $(selector).val(value);
        }
      }
    };
    updateConfigValues(config);
  }

  // ** BUILD / SUBMIT SETTINGS MODALS ** //
  function createSettingsModal(row, options, size = "xxl") {
    const { apiUrl, configUrl, name, id, saveFunction, labelPrefix, dataLocation, callback, noTabs, noRows, NoDestroyOnClose } = options;
    // Sanitize any special characters in the labelPrefix to create valid IDs
    const sanitizedLabelPrefix = labelPrefix.replace(/[^a-zA-Z0-9_]/g, '_');
    const modalId = `SettingsModal_${sanitizedLabelPrefix}`;
    const modalLabelId = `SettingsModalLabel_${sanitizedLabelPrefix}`;
    const modalBodyId = `SettingsModalBody_${sanitizedLabelPrefix}`;
    const saveBtnId = `SettingsModalSaveBtn_${sanitizedLabelPrefix}`;
    const itemIdInputId = `modalItemID_${sanitizedLabelPrefix}`;

    // Destroy on re-open instead
    if (NoDestroyOnClose) {
      if (document.getElementById(modalId)) {
        (document.getElementById(modalId)).remove();
      }
    }

    const modalHTML = `
      <div class="modal fade" id="${modalId}" tabindex="-1" role="dialog" aria-labelledby="${modalLabelId}" aria-hidden="true">
        <div class="modal-dialog modal-${size}" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="${modalLabelId}">Settings</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              <input id="${itemIdInputId}" hidden />
            </div>
            <div class="modal-body" id="${modalBodyId}">
              <!-- Dynamic content goes here -->
            </div>
            <div class="modal-footer">
              <button class="btn btn-primary" id="${saveBtnId}">Save</button>
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
          </div>
        </div>
      </div>
    `;

    document.body.insertAdjacentHTML('beforeend', modalHTML);

    changedModalSettingsElements.clear();
    selectWithTableArr = {};

    if (id) {
      $(`#${itemIdInputId}`).val(id);
    }

    function handleCallback(callback) {
      if (callback) {
        let match = callback.match(/(\w+)\((.*)\)/);
        if (match) {
            let functionName = match[1];
            let args = match[2].split(",").map(arg => arg.trim());
            args = args.map(arg => eval(arg));
            window[functionName](args);
        } else {
            console.error("Invalid callback format");
        }
      }
    }

    try {
      queryAPI("GET", apiUrl).done(function(settingsResponse) {
        const settingsData = dataLocation ? getNestedProperty(settingsResponse, dataLocation) : settingsResponse.data;
        $(`#${modalBodyId}`).html(buildFormGroup(settingsData,noTabs,noRows));
        initPasswordToggle();
        $(`#${saveBtnId}`).attr("onclick", saveFunction);
        $(`#${modalLabelId}`).text(`${labelPrefix} Settings: ${name}`);

        if (configUrl) {
          try {
            queryAPI("GET", configUrl, null, 'application/json', false).done(function(configResponse) {
              let data = configResponse.data;
              for (const key in data) {
                if (data.hasOwnProperty(key)) {
                  const value = data[key];
                  const element = $(`#${modalId} [name="${key}"]`);
                  if (element.attr("type") === "checkbox") {
                    element.prop("checked", value);
                  } else if (element.is("input[multiple]")) {
                    // Do Nothing
                  } else {
                    if (element.hasClass("encrypted")) {
                      if (value !== "") {
                        element.val("*********");
                      }
                    } else {
                      element.val(value);
                    }
                  }
                }
              }
              handleCallback(callback);
            }).fail(function(xhr) {
              logConsole("Error", xhr, "error");
            });
          } catch (e) {
            logConsole("Error", e, "error");
          }
        } else {
          handleCallback(callback);
        }

      }).fail(function(xhr) {
        logConsole("Error", xhr, "error");
      });
    } catch (e) {
      logConsole("Error", e, "error");
    }

    const modalElement = document.getElementById(modalId);

    if (modalElement) {
      if (!NoDestroyOnClose) {
        modalElement.addEventListener('hidden.bs.modal', () => {
          modalElement.remove();
        });
      }
      const bootstrapModal = new bootstrap.Modal(modalElement);
      bootstrapModal.show();
    }

    // Listener to add changed class to modal settings elements
    $("body").on('change', `#${modalId} .info-field, .dynamic-select-input`, function(event) {
      const elementName = $(this).data('label');
      const controlLabel = $($(this).parents().eq(2).prev('.control-label')[0]).text();
      const label = elementName || controlLabel;
      if (!changedModalSettingsElements.has(label)) {
          toast("Configuration", "", label + " has changed.<br><small>Save configuration to apply changes.</small>", "warning");
          changedModalSettingsElements.add(label);
      }
      $(this).addClass("changed");
    });

    return {
      modalId,
      modalLabelId,
      modalBodyId,
      saveBtnId,
      itemIdInputId
    };
  }

  function submitSettingsModal(type, labelPrefix, isNew = false, apiStub = null, customData = [], closeOnSubmit = true) {
    const sanitizedLabelPrefix = labelPrefix.replace(/\s+/g, '_');
    var noneCheckboxSelector = `#SettingsModal_${sanitizedLabelPrefix} input.changed[type!=checkbox], #SettingsModal_${sanitizedLabelPrefix} select.changed, #SettingsModal_${sanitizedLabelPrefix} textarea.changed`;
    var checkboxSelector = `#SettingsModal_${sanitizedLabelPrefix} input.changed[type=checkbox]`;
    if (isNew) {
      var noneCheckboxSelector = `#SettingsModal_${sanitizedLabelPrefix} input[type!=checkbox], #SettingsModal_${sanitizedLabelPrefix} select, #SettingsModal_${sanitizedLabelPrefix} textarea`;
      var checkboxSelector = `#SettingsModal_${sanitizedLabelPrefix} input[type=checkbox]`;
    }
    var serializedArray = $(`${noneCheckboxSelector}`).serializeArray();
    // Include unchecked checkboxes in the formData
    $(`${checkboxSelector}`).each(function() {
        serializedArray.push({ name: this.name, value: this.checked ? true : false });
    });

    // Convert the array into an object
    var formData = {};
    var encryptionPromises = [];

    serializedArray.forEach(function(item) {
        var element = $(`[name="${item.name}"]`);
        if (formData[item.name]) {
            if (!Array.isArray(formData[item.name])) {
                formData[item.name] = [formData[item.name]];
            }
            formData[item.name].push(item.value);
        } else {
            // Check if the element is a select with the multiple attribute
            if (element.is("select[multiple]")) {
              formData[item.name] = item.value !== "" ? [convertArrayToCSV([item.value])] : item.value;
            } else if (element.is("input[multiple]")) {
                formData[item.name] = getInputMultipleEntries(element);
            } else if (element.hasClass("encrypted") && item.value !== "") {
                // Encrypt sensitive data
                var promise = encryptData(item.name, item.value).done(function(encryptedValue) {
                    formData[item.name] = encryptedValue.data;
                });
                encryptionPromises.push(promise);
            } else {
                formData[item.name] = item.value;
            }
        }
    });

    var apiStub = apiStub ?? '/api/config/';
    if (isNew) {
        var api = `${apiStub}${type}`;
        var method = "POST";
    } else {
        idElement = `#modalItemID_${sanitizedLabelPrefix}`;
        var api = `${apiStub}${type}/` + $(idElement).val();
        var method = "PATCH";
    }

    // Append selectWithTableArr to formData
    if (selectWithTableArr) {
        formData = Object.assign({}, formData, selectWithTableArr, customData);
    }

    // Return a promise that resolves when all encryption promises and the API call are done
    return $.when.apply($, encryptionPromises).then(function() {
        return queryAPI(method, api, formData).then(function(data) {
            if (data.result === "Success") {
                toast(data.result, "", data.message, "success");
                $(`#SettingsModal_${sanitizedLabelPrefix} .changed`).removeClass("changed");
                changedModalSettingsElements.clear();
                if (closeOnSubmit) {
                  $(`#SettingsModal_${sanitizedLabelPrefix}`).modal("hide");
                }
            } else {
                toast(data.result === "Error" ? data.result : "API Error", "", data.message || "Failed to save configuration", "danger", "30000");
            }
        });
    });
  }

  function buildPluginSettingsModal(row) {
    createSettingsModal(row, {
      apiUrl: row.api,
      configUrl: `/api/config/plugins/${row.name}`,
      name: row.name,
      id: row.name,
      saveFunction: `submitSettingsModal("plugins","Plugin",false,null,[],false);`,
      labelPrefix: "Plugin",
      dataLocation: "data",
      NoDestroyOnClose: true
    },'xxl');
  }

  function buildWidgetSettingsModal(row) {
    createSettingsModal(row, {
      apiUrl: `/api/settings/widgets/${row.info.name}`,
      configUrl: `/api/config/widgets/${row.info.name}`,
      name: row.info.name,
      id: row.info.name,
      saveFunction: `submitSettingsModal("widgets","Widget");`,
      labelPrefix: "Widget",
      dataLocation: "data.Settings"
    },'xl');
  }

  function buildDashboardSettingsModal(row) {
    createSettingsModal(row, {
      apiUrl: `/api/settings/dashboard`,
      configUrl: `/api/config/dashboards/${row.Name}`,
      name: row.Name,
      id: row.Name,
      saveFunction: `submitDashboardSettings();`,
      labelPrefix: "Dashboard",
      dataLocation: "data",
      callback: "widgetSelectCallback(row)"
    },'lg');
  }

  function buildNewDashboardSettingsModal() {
    createSettingsModal([], {
      apiUrl: `/api/settings/dashboard`,
      configUrl: null,
      name: "New Dashboard",
      saveFunction: `submitDashboardSettings(true);`,
      labelPrefix: "Dashboard",
      dataLocation: "data"
    },'lg');
  }

  function buildRoleSettingsModal(row) {
      createSettingsModal(row, {
      apiUrl: `/api/settings/role`,
      name: row.name,
      id: row.id,
      saveFunction: `submitRoleSettings();`,
      labelPrefix: "Role",
      dataLocation: "data",
      noTabs: true,
      callback:  "populateRoleSettingsModal(row)"
    },'md');
  }

  function buildNewRoleSettingsModal() {
    createSettingsModal([], {
      apiUrl: `/api/settings/role`,
      configUrl: null,
      name: "New Role",
      saveFunction: `submitRoleSettings(true);`,
      labelPrefix: "Role",
      dataLocation: "data",
      noTabs: true
    },'md');
  }

  function buildGroupSettingsModal(row) {
    createSettingsModal(row, {
    apiUrl: `/api/settings/group`,
    name: row.Name,
    id: row.id,
    saveFunction: `submitGroupSettings();`,
    labelPrefix: "Group",
    dataLocation: "data",
    noTabs: true,
    callback:  "populateGroupSettingsModal(row)"
  },'lg');
  }

  function buildNewGroupSettingsModal() {
    createSettingsModal([], {
      apiUrl: `/api/settings/group`,
      configUrl: null,
      name: "New Group",
      saveFunction: `submitGroupSettings(true);`,
      labelPrefix: "Group",
      dataLocation: "data",
      noTabs: true
    },'lg');
  }

  function buildUserSettingsModal(row) {
    createSettingsModal(row, {
      apiUrl: `/api/settings/user`,
      name: row.username,
      id: row.id,
      saveFunction: `submitUserSettings();`,
      labelPrefix: "User",
      dataLocation: "data",
      callback:  "populateUserSettingsModal(row)"
    },'lg');
  }

  function buildNewUserSettingsModal() {
    createSettingsModal([], {
      apiUrl: `/api/settings/newuser`,
      configUrl: null,
      name: "New User",
      saveFunction: `submitUserSettings(true);`,
      labelPrefix: "User",
      dataLocation: "data",
      noTabs: true
    },'lg');
  }

  function buildPageSettingsModal(row) {
    createSettingsModal(row, {
      apiUrl: `/api/settings/page`,
      name: row.Name,
      id: row.id,
      saveFunction: `submitPageSettings();`,
      labelPrefix: "Page / Menu",
      dataLocation: "data",
      callback:  "populatePageSettingsModal(row)",
      noTabs: true,
      noRows: true
    },'lg');
  }

  function buildNewPageSettingsModal() {
    createSettingsModal([], {
      apiUrl: `/api/settings/page`,
      configUrl: null,
      name: "New Link / Menu",
      saveFunction: `submitPageSettings(true);`,
      labelPrefix: "Page / Menu",
      dataLocation: "data",
      callback:  "populatePageSettingsModal()",
      noTabs: true,
      noRows: true
    },'lg');
  }

  function buildNewsSettingsModal(row) {
    createSettingsModal(row, {
      apiUrl: `/api/settings/news/`+row.id,
      configUrl: null,
      name: row.title,
      id: row.id,
      saveFunction: `submitNewsSettings();`,
      labelPrefix: "News",
      dataLocation: "data",
      noTabs: true
    },'lg');
  }

  function buildNewNewsSettingsModal() {
    createSettingsModal([], {
      apiUrl: `/api/settings/news`,
      configUrl: null,
      name: "New News Item",
      saveFunction: `submitNewsSettings(true);`,
      labelPrefix: "News",
      dataLocation: "data",
      noTabs: true
    },'lg');
  }

  function submitDashboardSettings(isNew = false) {
    let tableRows = $("#widgetSelectTable tbody tr");
    tableRows.each((index, row) => {
        let cells = $(row).find("td");
        if (cells.length > 1) {
            let widgetName = cells.eq(1).text();
            let selectElement = cells.eq(2).find("select");
            let selectedOption = selectElement.length ? selectElement.val() : null;

            // Ensure the Widgets object exists
            if (!selectWithTableArr["Widgets"]) {
                selectWithTableArr["Widgets"] = {};
            }

            // Ensure the specific widget object exists
            if (!selectWithTableArr["Widgets"][widgetName]) {
                selectWithTableArr["Widgets"][widgetName] = {};
            }

            selectWithTableArr["Widgets"][widgetName]["size"] = selectedOption;
        }
    });

    var submitPromise;
    if (isNew) {
        submitPromise = submitSettingsModal("dashboards", 'Dashboard', isNew);
    } else {
        submitPromise = submitSettingsModal("dashboards", 'Dashboard');
    }

    submitPromise.then(() => {
        $("#dashboardsTable").bootstrapTable("refresh");
    }).catch((error) => {
        console.error("Error submitting settings:", error);
    });
  }

  function submitUserSettings(isNew = false) {
    var submitPromise;
    if (isNew) {
        submitPromise = submitSettingsModal("users", "User", isNew, "/api/");
    } else {
        submitPromise = submitSettingsModal("user", "User", isNew, "/api/");
    }

    submitPromise.then(() => {
        $("#usersTable").bootstrapTable("refresh");
    }).catch((error) => {
        console.error("Error submitting settings:", error);
    });
  }

  function submitGroupSettings(isNew = false) {
    var submitPromise;
    if (isNew) {
        submitPromise = submitSettingsModal("groups", "Group", isNew, "/api/rbac/");
    } else {
        submitPromise = submitSettingsModal("group", "Group", isNew, "/api/rbac/");
    }

    submitPromise.then(() => {
        $("#groupsTable").bootstrapTable("refresh");
    }).catch((error) => {
        console.error("Error submitting settings:", error);
    });
  }

  function submitRoleSettings(isNew = false) {
    var submitPromise;
    if (isNew) {
        submitPromise = submitSettingsModal("roles", "Role", isNew, "/api/rbac/");
    } else {
        submitPromise = submitSettingsModal("role", "Role", isNew, "/api/rbac/");
    }

    submitPromise.then(() => {
        $("#rolesTable").bootstrapTable("refresh");
    }).catch((error) => {
        console.error("Error submitting settings:", error);
    });
  }

  function submitPageSettings(isNew = false) {
    var submitPromise;
    if (isNew) {
        submitPromise = submitSettingsModal("pages", "Page___Menu", isNew, "/api/");
    } else {
      var data = {};
      if (pageImageDynamicSelect.selectedValue != "") {
        data = {
          "pageImage": pageImageDynamicSelect.selectedValue
        }
      }
        submitPromise = submitSettingsModal("page", "Page___Menu", isNew, "/api/", data);
    }

    submitPromise.then(() => {
        $("#combinedTable").bootstrapTable("refresh");
    }).catch((error) => {
        console.error("Error submitting settings:", error);
    });
  }

  function submitNewsSettings(isNew = false) {
    var submitPromise;
    if (isNew) {
        submitPromise = submitSettingsModal("news", "News", isNew, "/api/notifications/");
    } else {
      submitPromise = submitSettingsModal("news", "News", isNew, "/api/notifications/");
    }

    submitPromise.then(() => {
      $("#newsTable").bootstrapTable("refresh");
    }).catch((error) => {
      console.error("Error submitting settings:", error);
    });
  }

  // ** ROLES SETTINGS MODAL ** //

  // Callback from `buildRoleSettingsModal`
  function populateRoleSettingsModal(row) {
    $("[name=roleId]").val("").val(row[0].id);
    $("[name=roleName]").val("").val(row[0].name);
    $("[name=roleSlug]").val("").val(row[0].slug);
    $("[name=roleDescription]").val("").val(row[0].description);
  }

  // ** GROUPS SETTINGS MODAL ** //

  // Callback from `buildGroupSettingsModal`
  function populateGroupSettingsModal(row) {
    $("[name=groupId]").val("").val(row[0].id);
    $("[name=groupName]").val("").val(row[0].Name);
    $("[name=groupDescription]").val("").val(row[0].Description);
    
    if (row[0].PermittedResources) {
      var PermittedResources = row[0].PermittedResources.split(",");
      for (var resource in PermittedResources) {
        $(`[name="`+PermittedResources[resource]+`"]`).prop("checked", "true");
      }
    }
    $("#SettingsModal_Group .list-group .toggle").on("click", function(event) {
      var groupid = $("[name=groupId]").val();
      var group = $("[name=groupName]").val();
      var role = $(event.target).prop("name");
      var toggle = $(event.target).prop("checked") ? "enabled" : "disabled";
      var targetid = event.target.id
      var data = {
        key: role,
        value: toggle
      }
      queryAPI("PATCH","/api/rbac/group/"+groupid,data).done(function(data) {
        if (data["result"] == "Success") {
          if (toggle == "enabled") {
            toast("Success", "", "Successfully added " + role + " to " + group, "success");
          } else if (toggle == "disabled") {
            toast("Success", "", "Successfully removed " + role + " from " + group, "success");
          }
          $("#groupsTable").bootstrapTable("refresh");
        } else if (data["result"] == "Error") {
          toast(data["result"],"",data["message"],"danger","30000");
        } else {
          if (toggle == "enabled") {
            toast("Error", "", "Failed to add " + role + " to " + group, "danger");
          } else if (toggle == "disabled") {
            toast("Error", "", "Failed to remove " + role + " from " + group, "danger");
          }
        }
      }).fail(function() {
          toast("Error", "", "Failed to remove " + role + " from " + group, "danger");
      });
    });
  }

  // ** USERS SETTINGS MODAL ** //

  // Callback from `buildUserSettingsModal`
  function populateUserSettingsModal(row) {
    row = row[0];
    $("[name=userId]").val(row["id"]);
    $("[name=userUsername]").val(row["username"]);
    $("[name=userFirstName]").val(row["firstname"]);
    $("[name=userLastName]").val(row["surname"]);
    $("[name=userEmail]").val(row["email"]);
    $("[name=userType]").val(row["type"]);
    $("[name=userLastLogin]").val(row["lastlogin"]);
    $("[name=userPasswordExpires]").val(row["passwordexpires"]);
    $("[name=userCreated]").val(row["created"]);

    if (row["type"] == "Local") {
      $("[name=userPassword]").attr("disabled",false);
      $("[name=userPassword2]").attr("disabled",false);
    } else {
      $("[name=userPassword]").attr("disabled",true);
      $("[name=userPassword2]").attr("disabled",true);
    }

    if (row['multifactor_enabled']) {
      $("#mfaUserSettings").html(`
        <div class="alert alert-info text-center d-grid" role="alert" id="mfaAlert">
          <span class="h3">${row['multifactor_type'].toUpperCase()}</span>
          <span>Multifactor Authentication is configured.</span>
          <button class="btn btn-danger mt-2" onclick="resetMFA(${row.id});">Reset</button>
        </div>
      `);
    } else {
      $("#mfaUserSettings").html(`
        <div class="alert alert-info text-center d-grid" role="alert" id="mfaAlert">
          <span>Multifactor Authentication is not configured.</span>
        </div>
      `);
    }

    var groupsplit = row.groups;
    if (groupsplit[0] != "") {
      for (var group in groupsplit) {
        $(`[name="`+groupsplit[group]+`"]`).prop("checked", "true");
      }
    }

    $("[name=userPassword], [name=userPassword2]").on("change", function() {
      var password = $("[name=userPassword]").val();
      var confirmPassword = $("[name=userPassword2]").val();

      if (password !== confirmPassword) {
        if (password !== "" && confirmPassword !== "") {
          toast("Warning","","The entered passwords do not match","danger","3000");
          $("#editUserSubmit").attr("disabled",true);
          $("[name=userPassword]").css("color","red").css("border-color","red");
          $("[name=userPassword2]").css("color","red").css("border-color","red");
        }
      } else {
        $("#newUserSubmit").attr("disabled",false);
        $("[name=userPassword]").css("color","green").css("border-color","green");
        $("[name=userPassword2]").css("color","green").css("border-color","green");
      }
    });

    $("#SettingsModal_User .list-group .toggle").on("click", function(event) {
      var userid = $("[name=userId]").val().trim();
      var data = {
        groups: $("#SettingsModal_User .list-group .toggle:checked").map(function() {
          return $(this).attr('name').replaceAll("--"," ");
        }).get().join(",")
      }
      queryAPI("PATCH","/api/user/"+userid,data).done(function(data) {
        if (data["result"] == "Success") {
          toast(data["result"],"",data["message"],"success");
          $("#usersTable").bootstrapTable("refresh");
        } else if (data["result"] == "Error") {
          toast(data["result"],"",data["message"],"danger","30000");
        } else {
          toast("Error","","Failed to update user groups","danger","30000");
        }
      }).fail(function(data) {
        toast("API Error","","Failed to update user groups","danger","30000");
      });
    });
  }

  // ** PAGES SETTINGS MODAL ** //
  var pageImageDynamicSelect = '';
  // Callback from `buildPageSettingsModal`
  function populatePageSettingsModal(row = {}) {
    var pageIcon = $("[name=pageIcon]");
    if (row.length > 0 && row[0] !== undefined) {
      pageIcon.val("").val(row[0].Icon);
      $("[name=pageId]").val("").val(row[0].id);
      $("[name=pageLinkType]").val("").val(row[0].LinkType);
      $("[name=pageName]").val("").val(row[0].Name);
      $("[name=pageTitle]").val("").val(row[0].Title);
      $("[name=pageType]").val("").val(row[0].Type);
      $("[name=pageMenu]").val("").val(row[0].Menu);
      $("[name=pageRole]").val("").val(row[0].ACL);
      row[0].isDefault ? $("[name=pageDefault]").attr('checked',true) : $("[name=pageDefault]").attr('checked',false);
    }

    // Setup Dynamic Image Select
    pageImageDynamicSelect = new DynamicSelect(document.querySelector('[name="pageImage"]'), {
      onChange: (value, text, option) => {
          pageImageOnChange(value, text, option);
      }
    });

    function pageImageOnChange(value, text, option) {
      if (value !== "") {
        pageIcon.attr("disabled",true)
      } else {
        pageIcon.attr("disabled",false)
      }
    }

    pageIcon.on("input", function() {
      if (this.value !== "") {
        pageImageDynamicSelect.setDisabled(true)
      } else {
        pageImageDynamicSelect.setDisabled(false)
      }
    });

    if (row.length > 0 && row[0] !== undefined) {
      // Setup Icon Options
      if (row[0].Icon && (row[0].Icon.startsWith("/assets/images/custom") || row[0].Icon.startsWith("/api/image/plugin"))) {
        pageImageDynamicSelect.setDisabled(false);
        pageImageDynamicSelect.setSelectedValue(row[0].Icon);
        pageIcon.val("").attr("disabled",true);
      } else if (row[0].Icon == "") {
        pageImageDynamicSelect.setDisabled(false)
        pageIcon.attr("disabled",false).val("");
        pageImageDynamicSelect.setSelectedValue("");
      } else {
        pageIcon.val(row[0].Icon).attr("disabled",false);
        pageImageDynamicSelect.setSelectedValue("");
        pageImageDynamicSelect.setDisabled(true)
      }

      // Check Link Type and adjust displayed Page/iFrame URL options
      switch (row[0].LinkType) {
        case "Native":
          $("[name=pageStub]").val(row[0].Url ?? '');
          break;
        case "iFrame":
        case "NewWindow":
          $("[name=pageUrl]").val(row[0].Url ?? '');
          break;
      }

      // Disable Submenu if it is a Menu
      var isMenu = row[0].Type == "Menu";
      if (isMenu) {
        $("[name=pageSubMenu]").attr("disabled", true);
      } else {
        $("[name=pageSubMenu]").attr("disabled", false);
      }

      $("[name=pageType]").val(pagesDetermineType(row[0].Type));
    }

    $("[name=pageSubMenu], [name=pageLinkType]").on("change", function(elem) {
      pagesHideUnneccessaryInputs();
    });
  
    $("[name=pageType],[name=pageMenu]").on("change", function(elem) {
      var menuOpt = $("select[name=pageMenu].form-control").find(":selected").val();
      var isMenu = $("[name=pageType]").val() == "Menu";
      pagesHideUnneccessaryInputs();
      if (isMenu) {
          $("[name=pageSubMenu]").attr("disabled", true);
      } else {
          $("[name=pageSubMenu]").attr("disabled", false);
      }
      pagesUpdateSubMenus(menuOpt);
    });
    var rowMenu = row.length > 0 && row[0] != null ? row[0].Menu : null;
    pagesUpdateSubMenus(rowMenu ? rowMenu : "None",row);
  }

  function pagesUpdateSubMenus(menuOpt,row = {}) {
    queryAPI("GET","/api/pages/submenus?menu="+menuOpt).done(function(subMenuData) {
      pageSubMenuContainer = $("[name=pageSubMenu]");
      pageSubMenuContainer.html("");
      pageSubMenuContainer.append(`<option value="" selected>None</option>`);
      $.each(subMenuData.data, function(index, item) {
          const option = $("<option></option>").val(item.Name).text(item.Name);
          pageSubMenuContainer.append(option);
      });
      if (row.length > 0 && row[0] !== undefined) {
        pageSubMenuContainer = $("[name=pageSubMenu]");
        row[0].Submenu ? pageSubMenuContainer.val(row[0].Submenu) : pageSubMenuContainer.val("");
        row[0].Submenu ? $("[name=pageIcon]").parent().parent().parent().attr("hidden",true) : $("[name=pageIcon]").parent().parent().parent().attr("hidden",false);
        row[0].Submenu ? $("[name=pageImage]").parent().parent().parent().parent().parent().attr("hidden",true) : $("[name=pageImage]").parent().parent().parent().parent().parent().attr("hidden",false);
      }
      pagesHideUnneccessaryInputs();
      return true;
    })
  }

  // ** PLUGINS FUNCTIONS ** //
  function installPlugin(row){
    toast("Installing","","Installing "+row["name"]+"...","info");
    try {
      queryAPI("POST","/api/plugins/install",row).done(function(data) {
        if (data["result"] == "Success") {
          toast(data["result"],"",data["message"],"success");
          $("#pluginsTable").bootstrapTable("refresh");
        } else if (data["result"] == "Error") {
          toast(data["result"],"",data["message"],"danger");
        } else {
          toast("API Error","","Failed to install plugin","danger","30000");
        }
      }).fail(function(xhr) {
        toast("API Error","","Failed to install plugin","danger","30000");
        logConsole("Error",xhr,"error");
      });;
    } catch(e) {
      toast("API Error","","Failed to install plugin","danger","30000");
      logConsole("Error",e,"error");
    }
  }

  function uninstallPlugin(row){
    if(confirm("Are you sure you want to uninstall the "+row.name+" plugin?") == true) {
      toast("Uninstalling","","Uninstalling "+row["name"]+"...","info");
      try {
        queryAPI("POST","/api/plugins/uninstall",row).done(function(data) {
          if (data["result"] == "Success") {
            toast(data["result"],"",data["message"],"success");
            $("#pluginsTable").bootstrapTable("refresh");
          } else if (data["result"] == "Error") {
            toast(data["result"],"",data["message"],"danger");
          } else {
            toast("API Error","","Failed to uninstall plugin","danger","30000");
          }
        }).fail(function(xhr) {
          toast("API Error","","Failed to uninstall plugin","danger","30000");
          logConsole("Error",xhr,"error");
        });;
      } catch(e) {
        toast("API Error","","Failed to uninstall plugin","danger","30000");
        logConsole("Error",e,"error");
      }
    }
  }

  function reinstallPlugin(row){
    if(confirm("Are you sure you want to reinstall the "+row.name+" plugin?") == true) {
      toast("Reinstalling","","Reinstalling "+row["name"]+"...","info");
      try {
        queryAPI("POST","/api/plugins/reinstall",row).done(function(data) {
          if (data["result"] == "Success") {
            toast(data["result"],"",data["message"],"success");
            $("#pluginsTable").bootstrapTable("refresh");
          } else if (data["result"] == "Error") {
            toast(data["result"],"",data["message"],"danger");
          } else {
            toast("API Error","","Failed to reinstall plugin","danger","30000");
          }
        }).fail(function(xhr) {
          toast("API Error","","Failed to reinstall plugin","danger","30000");
          logConsole("Error",xhr,"error");
        });;
      } catch(e) {
        toast("API Error","","Failed to reinstall plugin","danger","30000");
        logConsole("Error",e,"error");
      }
    }
  }

  // ** DASHBOARD FUNTIONS ** //

  // ** WIDGET FUNCTIONS ** //
  // Callback from `buildDashboardSettingsModal`
  function widgetSelectCallback(row) {
    if (row.length > 0) {
      const tableData = {
        "Widgets": Object.keys(row[0].Widgets).map(key => ({
          "dragHandle": `<span class="dragHandle" style="font-size:22px;">☰</span>`,
          "name": key,
          "size": `<select class="form-select" data-label="size">
                      <option value="col-md-1">1</option>
                      <option value="col-md-2">2</option>
                      <option value="col-md-3">3</option>
                      <option value="col-md-4">4</option>
                      <option value="col-md-5">5</option>
                      <option value="col-md-6">6</option>
                      <option value="col-md-7">7</option>
                      <option value="col-md-8">8</option>
                      <option value="col-md-9">9</option>
                      <option value="col-md-10">10</option>
                      <option value="col-md-11">11</option>
                      <option value="col-md-12">12</option>
                  </select>`
        }))
      };
      const uniqueNames = [...new Set(tableData.Widgets.map(widget => widget.name))];
      $("#widgetSelectTable").bootstrapTable({ data: tableData.Widgets});
      $("#widgetSelect").val(uniqueNames);

      tableData.Widgets.forEach(function(item,index) {
        let tablerow = $("#widgetSelectTable tbody tr")[index];
        let cells = $(tablerow).find("td");
        let widgetName = cells[1].textContent;
        let selectElement = $(cells[2]).find("select");
        selectElement.val(row[0].Widgets[widgetName].size);
      });
    }
  }

  // ** PAGES FUNCTIONS ** //
  function pagesRowAttributes(row, index) {
    return {
      "id": "row-"+row.id,
      "data-detail-view": row.Type === "Menu" || row.Type === "SubMenu"
    }
  }

  function pagesRowStyle(row, index) {
    if (row.Type !== "Menu" && row.Type !== "SubMenu") {
        return {
            classes: "no-expand"
        };
    }
    return {};
  }

  function pagesRowOnReorderRow(data,row,oldrow,table) {
    var key = data.findIndex(item => item.id === row.id) + 1;
    queryAPI("PATCH","/api/page/"+row.id+"/weight",{"weight": key}).done(function(data) {
      if (data["result"] == "Success") {
          toast(data["result"],"",data["message"],"success");
      } else if (data["result"] == "Error") {
          toast(data["result"],"",data["message"],"danger");
      } else {
          toast("API Error","","Failed To Edit "+row.Type+" Position","danger","30000");
      }
    }).fail(function() {
      toast("API Error","","Failed To Edit "+row.Type+" Position","danger","30000");
    });
  }

  function pagesInitializeMenuTable(index, row, detail) {
    const childTableId = `#menu-table-${index}`;
    $(childTableId).bootstrapTable({
        url: "/api/pages?menu="+row.Name,
        dataField: "data",
        detailView: true,
        detailFormatter: submenuDetailFormatter,
        onExpandRow: pagesInitializeSubMenuTable,
        reorderableRows: true,
        rowAttributes: pagesRowAttributes,
        rowStyle: pagesRowStyle,
        onReorderRow: pagesRowOnReorderRow,
        dragHandle: '>tbody>tr>td:nth-child(2)',
        responseHandler: 'dragHandlerResponseHandler',
        columns: [{
          field: 'dragHandle',
          width: "25px"
        },{
          field: "Icon",
          title: "Icon",
          formatter: "pageIconFormatter"
        },{
          field: "Type",
          title: "Type",
          formatter: "typeFormatter"
        },{
          field: "Name",
          title: "Name"
        },{
          field: "Title",
          title: "Title"
        },{
          field: "Url",
          title: "URL",
          visible: false
        },{
          field: "ACL",
          title: "Role"
        },{
          field: "LinkType",
          title: "Source"
        },{
          field: "isDefault",
          title: "Default",
          formatter: "booleanTickCrossFormatter"
        },{
          title: "Actions",
          formatter: "pageActionFormatter",
          events: "pageActionEvents"
        }]
    });
  }

  function pagesInitializeSubMenuTable(index, row, detail) {
      const childTableId = `#submenu-table-${index}`;
      $(childTableId).bootstrapTable({
          url: "/api/pages?submenu="+row.Name+"&menu="+row.Menu,
          dataField: "data",
          reorderableRows: true,
          rowAttributes: pagesRowAttributes,
          rowStyle: pagesRowStyle,
          onReorderRow: pagesRowOnReorderRow,
          dragHandle: '>tbody>tr>td:nth-child(2)',
          responseHandler: 'dragHandlerResponseHandler',
          columns: [{
            field: 'dragHandler',
            width: "25px"
          },{
            field: "Type",
            title: "Type",
            formatter: "typeFormatter"
          },{
            field: "Name",
            title: "Name"
          },{
            field: "Title",
            title: "Title"
          },{
            field: "Url",
            title: "URL",
            visible: false
          },{
            field: "ACL",
            title: "Role"
          },{
            field: "LinkType",
            title: "Source"
          },{
            field: "isDefault",
            title: "Default",
            formatter: "booleanTickCrossFormatter"
          },{
            title: "Actions",
            formatter: "pageActionFormatter",
            events: "pageActionEvents"
          }]
      });
  }

  function pagesDetermineType(type) {
    switch(type) {
      case "Link":
      case "MenuLink":
      case "SubMenuLink":
        return "Link";
      case "Menu":
      case "SubMenu":
        return "Menu";
    }
  }

  function pagesHideUnneccessaryInputs() {
    var type = $("[name=pageType]").val();
    var submenu = $("[name=pageSubMenu]").val();
    var linktype = $("[name=pageLinkType]").val();
    switch(type) {
      case "Link":
        $("[name=pageStub],[name=pageTitle],[name=pageStub],[name=pageSubMenu],[name=pageRole],[name=pageLinkType],[name=pageUrl],[name=pageDefault]").parent().parent().parent().attr("hidden",false);
        if (submenu) {
          $("[name=pageIcon]").parent().parent().parent().attr("hidden",true);
          $("[name=pageImage]").parent().parent().parent().parent().parent().attr("hidden",true);
          $("[name=pageIcon], [name=pageImage]").val("");
        } else {
          $("[name=pageIcon]").parent().parent().parent().attr("hidden",false);
          $("[name=pageImage]").parent().parent().parent().parent().parent().attr("hidden",false);
        }
        switch(linktype) {
          case "Native":
            $("[name=pageStub]").parent().parent().parent().attr("hidden",false).val("");
            $("[name=pageUrl]").parent().parent().parent().attr("hidden",true).val("");
            break;
          case "iFrame":
          case "NewWindow":
            $("[name=pageStub]").parent().parent().parent().attr("hidden",true).val("");
            $("[name=pageUrl]").parent().parent().parent().attr("hidden",false).val("");
            break;
        }
        break;
      case "Menu":
        $("[name=pageStub],[name=pageTitle],[name=pageStub],[name=pageSubMenu],[name=pageRole],[name=pageLinkType],[name=pageUrl],[name=pageDefault]").parent().parent().parent().attr("hidden",true).val("");
        break;
    }
  }

  // ** USER FUNCTIONS ** //
  function resetMFA(userId) {
    if(confirm("Are you sure you want to reset multi factor authentication?") == true) {
      queryAPI('POST','/api/auth/mfa/reset/'+userId).done(function(data) {
        if (data['result'] == 'Success') {
          toast("Success","","Successfully reset multi factor authentication","success");
          $("#mfaUserSettings").html(`
            <div class="alert alert-info text-center d-grid" role="alert" id="mfaAlert">
              <span>Multifactor Authentication is not configured.</span>
            </div>
          `);
        } else {
          toast(data['result'],"",data['message'],"danger");
        }
      }).fail(function(jqXHR, textStatus, errorThrown) {
        toast(textStatus,"","Error: "+jqXHR.status+": "+errorThrown,"danger");
      });
    }
  }

  // ** TOKEN FUNCTIONS ** //
  function newAPIToken(days = 90) {
    queryAPI("POST","/api/auth/tokens/api",{"days":days}).done(function(data) {
      const modal = $(`
        <div class="modal fade" id="newAPITokenModal" tabindex="-1" role="dialog" aria-labelledby="newAPITokenModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="newAPITokenModalLabel">New API Token</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true"></span>
                </button>
              </div>
              <div class="modal-body">
                <div class="alert alert-warning">
                  <p>This API Token will only be displayed once. Please save it and keep it safe.</p>
                  <code>${data.data}</code>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              </div>
            </div>
          </div>
        </div>
      `);

      // Append modal to body
      $('body').append(modal);

      // Show the modal
      $('#newAPITokenModal').modal('show');
      $('#profileModal').modal('hide');

      // Remove modal from DOM when hidden
      $('#newAPITokenModal').on('hidden.bs.modal', function () {
          $(this).modal('hide').remove();
          $('#profileModal').modal('show');
          $('#apiTokenTable').bootstrapTable('refresh');
      });
    }).fail(function(jqXHR, textStatus, errorThrown) {
      toast(textStatus,"","Error: "+jqXHR.status+": "+errorThrown,"danger");
    });
  }