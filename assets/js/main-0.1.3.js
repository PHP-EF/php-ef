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

function searchTable(searchId,tableId) {
  // Declare variables
  var input, filter, table, tr, td, i, txtValue;
  input = document.getElementById(searchId);
  filter = input.value.toUpperCase();
  table = document.getElementById(tableId);
  tr = table.getElementsByTagName("tr");

  for (i = 1; i < tr.length; i++) {
    td = tr[i];
    if (td) {
      txtValue = td.textContent || td.innerText;
      if (txtValue.toUpperCase().indexOf(filter) > -1) {
        tr[i].style.display = "";
      } else {
        tr[i].style.display = "none";
      }
    }
  }

}

function sortTable(table,n,type) {
  var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
  table = document.getElementById(table);
  switching = true;
  // Set the sorting direction to ascending:
  dir = "asc";
  /* Make a loop that will continue until
  no switching has been done: */
  while (switching) {
    // Start by saying: no switching is done:
    switching = false;
    rows = table.rows;
    /* Loop through all table rows (except the
    first, which contains table headers): */
    for (i = 1; i < (rows.length - 1); i++) {
      // Start by saying there should be no switching:
      shouldSwitch = false;
      /* Get the two elements you want to compare,
      one from current row and one from the next: */
      x = rows[i].getElementsByTagName("TD")[n];
      y = rows[i + 1].getElementsByTagName("TD")[n];
      /* Check if the two rows should switch place,
      based on the direction, asc or desc: */
      if (type == "string") {
        if (dir == "asc") {
          if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
            // If so, mark as a switch and break the loop:
            shouldSwitch = true;
            break;
          }
        } else if (dir == "desc") {
          if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
            // If so, mark as a switch and break the loop:
            shouldSwitch = true;
            break;
          }
        }
      } else if (type == "aria") {
	if (dir == "asc") {
	  divx = x.getElementsByTagName("div")[1]
	  divy = y.getElementsByTagName("div")[1]
          if (parseInt(divx.getAttribute('aria-valuenow')) > parseInt(divy.getAttribute('aria-valuenow'))) {
            // If so, mark as a switch and break the loop:
            shouldSwitch = true;
            break;
          }
        } else if (dir == "desc") {
          if (parseInt(divx.getAttribute('aria-valuenow')) < parseInt(divy.getAttribute('aria-valuenow'))) {
	    // If so, mark as a switch and break the loop:
            shouldSwitch = true;
            break;
          }
        }
      }
    }
    if (shouldSwitch) {
      /* If a switch has been marked, make the switch
      and mark that a switch has been done: */
      rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
      switching = true;
      // Each time a switch is done, increase this count by 1:
      switchcount ++;
    } else {
      /* If no switching has been done AND the direction is "asc",
      set the direction to "desc" and run the while loop again. */
      if (switchcount == 0 && dir == "asc") {
        dir = "desc";
        switching = true;
      }
    }
  }
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
    const response = await fetch('/api?f=heartbeat', {cache: "no-cache"});
    if (response.status == "200") {
      while (true) {
	      let response2 = await fetch('/api?f=heartbeat', {cache: "no-cache"});
        if (response2.status == "301") {
      	  console.log("Session timed out.");
          window.location.href = "/login.php?redirect_uri="+window.location.href.replace("#","?");
	      }
	      await delay(10000);
      }
    }
  } catch (err) {
    console.log(err);
  }
}

$('.preventDefault').click(function(event){
  event.preventDefault();
});

window.addEventListener("load", function() {
  $('.dark-theme .table-striped').addClass('table-dark');
});

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

// New Stuff
function saveAPIKey(key) {
  $.post( "/api?f=crypt", {key: key}).done(function( data, status ) {
      setCookie('crypt',data,7);
      checkAPIKey();
      toast("Success","","Saved API Key.","success","30000");
  }).fail(function( data, status ) {
      toast("API Error","","Unable to save API Key.","danger","30000");
  })
}

function checkAPIKey() {
  if (getCookie('crypt')) {
    $('#APIKey').prop('disabled',true).attr('placeholder','== Using Saved API Key ==').val('');
    $("#saveBtn").removeClass('fa-save').addClass('fa-trash')
    checkInput('saved');
  } else {
    $('#APIKey').prop('disabled',false).attr('placeholder','Enter API Key');
    $("#saveBtn").removeClass('fa-trash').addClass('fa-save')
  }
}

function removeAPIKey() {
  setCookie('crypt',null,-1);
  checkAPIKey();
  toast("Success","","Removed API Key.","success","30000");
}

function checkInput(text) {
  if (text) {
      $("#saveBtn").addClass("saveBtnShow");
  } else {
      $("#saveBtn").removeClass("saveBtnShow");
  }
}

function enableDateTime() {
  $('.datetimepicker').datetimepicker({
    onGenerate:function( ct ){
      jQuery(this).find('.xdsoft_date')
        .toggleClass('xdsoft_disabled');
    },
    formatDate:'d/m/Y H:i:s',
    minDate:'-1970/01/02',//yesterday is minimum date(for today use 0 or -1970/01/01)
    maxDate:'+1970/01/02',//tomorrow is maximum date calendar
    timepicker:true
  });
}

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

// END

document.addEventListener('DOMContentLoaded', function() {
  const maxDaysApart = 31;
  const today = new Date();
  const maxPastDate = new Date(today);
  maxPastDate.setDate(today.getDate() - 31);

  flatpickr("#assessmentStartAndEndDate", {
    mode: "range",
    minDate: maxPastDate,
    maxDate: today,
    enableTime: true,
    dateFormat: "Y-m-d H:i",
    onChange: function(selectedDates, dateStr, instance) {
      if (selectedDates.length === 1) {
        const startDate = selectedDates[0];
        const maxEndDate = new Date(startDate.getTime() + 31 * 24 * 60 * 60 * 1000); // 31 days later
        const today = new Date();
        instance.set('maxDate', maxEndDate > today ? today : maxEndDate);
      }
      if (selectedDates.length === 2) {
        const startDate = selectedDates[0];
        const endDate = selectedDates[1];
        const diffInDays = (endDate - startDate) / (1000 * 60 * 60 * 24);
        if (diffInDays > 31) {
          toast("Error","","The start and end date cannot exceed 31 days.","warning");
          instance.clear();
        }
      }
    }
  });

  flatpickr("#reportingStartAndEndDate", {
    mode: "range",
    maxDate: today,
    enableTime: true,
    dateFormat: "Y-m-d H:i"
  });
});

$( document ).ready(function() {
  checkAPIKey();
  $('#saveBtn').click(function(){
    if ($('#saveBtn').hasClass('fa-save')) {
      saveAPIKey($('#APIKey').val());
    } else if ($('#saveBtn').hasClass('fa-trash')) {
      removeAPIKey();
    }
  });
  enableDateTime();
});



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
      url: '/api?f=t',
      type: 'POST',
      data: JSON.stringify(data),
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      error: function(xhr, status, error) {
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
  const match = pathname.match(/^\/pages\/([^\/]+)\/([^\/]+)/);
  if (match) {
      return {
          pageCategory: match[1],
          pageName: match[2]
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
      trackingConfig.processData(result);
  }
};

// Initialize tracking
userTracking.init(trackingConfig);